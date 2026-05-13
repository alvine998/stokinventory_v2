<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamAccessController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    // ─── Approval Workflows ───────────────────────────────────────────────────

    public function approvalWorkflows()
    {
        $workflows = ApprovalWorkflow::where('business_id', $this->bid())
            ->withCount('requests')
            ->orderByDesc('created_at')
            ->get();

        $users = User::where('business_id', $this->bid())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('app.team-access.approval-workflows', compact('workflows', 'users'));
    }

    public function storeWorkflow(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'module'        => ['required', 'string', 'in:purchasing,sales,inventory,finance,general'],
            'trigger_event' => ['nullable', 'string', 'max:100'],
            'min_amount'    => ['nullable', 'numeric', 'min:0'],
            'approver_ids'  => ['required', 'array', 'min:1'],
            'approver_ids.*'=> ['exists:users,id'],
            'description'   => ['nullable', 'string', 'max:500'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        ApprovalWorkflow::create([
            'business_id'   => $this->bid(),
            'name'          => $data['name'],
            'module'        => $data['module'],
            'trigger_event' => $data['trigger_event'] ?? null,
            'min_amount'    => $data['min_amount'] ?? null,
            'approver_ids'  => $data['approver_ids'],
            'description'   => $data['description'] ?? null,
            'is_active'     => $request->has('is_active'),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateWorkflow(Request $request, ApprovalWorkflow $approvalWorkflow)
    {
        abort_unless($approvalWorkflow->business_id === $this->bid(), 403);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'module'        => ['required', 'string', 'in:purchasing,sales,inventory,finance,general'],
            'trigger_event' => ['nullable', 'string', 'max:100'],
            'min_amount'    => ['nullable', 'numeric', 'min:0'],
            'approver_ids'  => ['required', 'array', 'min:1'],
            'approver_ids.*'=> ['exists:users,id'],
            'description'   => ['nullable', 'string', 'max:500'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $approvalWorkflow->update([
            'name'          => $data['name'],
            'module'        => $data['module'],
            'trigger_event' => $data['trigger_event'] ?? null,
            'min_amount'    => $data['min_amount'] ?? null,
            'approver_ids'  => $data['approver_ids'],
            'description'   => $data['description'] ?? null,
            'is_active'     => $request->has('is_active'),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyWorkflow(ApprovalWorkflow $approvalWorkflow)
    {
        abort_unless($approvalWorkflow->business_id === $this->bid(), 403);
        $approvalWorkflow->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Approval Requests ────────────────────────────────────────────────────

    public function approvalRequests(Request $request)
    {
        $status = $request->input('status', '');
        $module = $request->input('module', '');

        $query = ApprovalRequest::where('approval_requests.business_id', $this->bid())
            ->with(['requester', 'workflow', 'actions.actor'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }
        if ($module) {
            $query->whereHas('workflow', fn($q) => $q->where('module', $module));
        }

        $requests = $query->paginate(25)->withQueryString();

        $pendingCount   = ApprovalRequest::where('business_id', $this->bid())->where('status', 'pending')->count();
        $approvedCount  = ApprovalRequest::where('business_id', $this->bid())->where('status', 'approved')->count();
        $rejectedCount  = ApprovalRequest::where('business_id', $this->bid())->where('status', 'rejected')->count();

        return view('app.team-access.approval-requests', compact(
            'requests', 'pendingCount', 'approvedCount', 'rejectedCount', 'status', 'module'
        ));
    }

    public function approveRequest(Request $request, ApprovalRequest $approvalRequest)
    {
        abort_unless($approvalRequest->business_id === $this->bid(), 403);
        abort_if($approvalRequest->status !== 'pending', 422);

        $data = $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        ApprovalAction::create([
            'approval_request_id' => $approvalRequest->id,
            'actor_id'            => Auth::id(),
            'step'                => $approvalRequest->current_step,
            'action'              => 'approved',
            'notes'               => $data['notes'] ?? null,
            'acted_at'            => now(),
        ]);

        // Determine next step
        $workflow     = $approvalRequest->workflow;
        $approvers    = $workflow ? $workflow->approver_ids : [];
        $nextStep     = $approvalRequest->current_step + 1;
        $isLastStep   = $nextStep > count($approvers);

        if ($isLastStep) {
            $approvalRequest->update(['status' => 'approved']);
        } else {
            $approvalRequest->update(['current_step' => $nextStep]);
        }

        return back()->with('status', __('messages.saved'));
    }

    public function rejectRequest(Request $request, ApprovalRequest $approvalRequest)
    {
        abort_unless($approvalRequest->business_id === $this->bid(), 403);
        abort_if($approvalRequest->status !== 'pending', 422);

        $data = $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        ApprovalAction::create([
            'approval_request_id' => $approvalRequest->id,
            'actor_id'            => Auth::id(),
            'step'                => $approvalRequest->current_step,
            'action'              => 'rejected',
            'notes'               => $data['notes'] ?? null,
            'acted_at'            => now(),
        ]);

        $approvalRequest->update(['status' => 'rejected']);

        return back()->with('status', __('messages.saved'));
    }

    public function cancelRequest(Request $request, ApprovalRequest $approvalRequest)
    {
        abort_unless($approvalRequest->business_id === $this->bid(), 403);
        abort_if(in_array($approvalRequest->status, ['approved', 'rejected']), 422);

        $approvalRequest->update(['status' => 'cancelled']);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Audit Log ────────────────────────────────────────────────────────────

    public function auditLog(Request $request)
    {
        $event      = $request->input('event', '');
        $user_id    = $request->input('user_id', '');
        $model_type = $request->input('model_type', '');
        $date_from  = $request->input('date_from', '');
        $date_to    = $request->input('date_to', '');

        $query = AuditLog::where('business_id', $this->bid())
            ->with('user')
            ->orderByDesc('created_at');

        if ($event) {
            $query->where('event', $event);
        }
        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        if ($model_type) {
            $query->where('auditable_type', 'like', '%' . $model_type . '%');
        }
        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        $logs = $query->paginate(30)->withQueryString();

        $users = User::where('business_id', $this->bid())->orderBy('name')->get(['id', 'name']);

        return view('app.team-access.audit-log', compact('logs', 'users', 'event', 'user_id', 'model_type', 'date_from', 'date_to'));
    }

    // ─── Login History ────────────────────────────────────────────────────────

    public function loginHistory(Request $request)
    {
        $user_id   = $request->input('user_id', '');
        $date_from = $request->input('date_from', '');
        $date_to   = $request->input('date_to', '');
        $success   = $request->input('success', '');

        $query = LoginHistory::where('login_histories.business_id', $this->bid())
            ->with('user')
            ->orderByDesc('login_at');

        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        if ($date_from) {
            $query->whereDate('login_at', '>=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('login_at', '<=', $date_to);
        }
        if ($success !== '') {
            $query->where('is_successful', (bool) $success);
        }

        $histories = $query->paginate(30)->withQueryString();

        $totalLogins   = LoginHistory::where('business_id', $this->bid())->where('is_successful', true)->count();
        $failedLogins  = LoginHistory::where('business_id', $this->bid())->where('is_successful', false)->count();
        $activeNow     = LoginHistory::where('business_id', $this->bid())->whereNull('logout_at')->where('is_successful', true)->count();

        $users = User::where('business_id', $this->bid())->orderBy('name')->get(['id', 'name']);

        return view('app.team-access.login-history', compact(
            'histories', 'users', 'totalLogins', 'failedLogins', 'activeNow',
            'user_id', 'date_from', 'date_to', 'success'
        ));
    }

    // ─── Activity Tracking ────────────────────────────────────────────────────

    public function activityLog(Request $request)
    {
        $user_id    = $request->input('user_id', '');
        $action     = $request->input('action', '');
        $date_from  = $request->input('date_from', '');
        $date_to    = $request->input('date_to', '');
        $search     = $request->input('search', '');

        $query = ActivityLog::where('business_id', $this->bid())
            ->with('user')
            ->orderByDesc('created_at');

        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        if ($action) {
            $query->where('action', $action);
        }
        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }
        if ($search) {
            $query->where('description', 'like', '%' . $search . '%');
        }

        $logs = $query->paginate(30)->withQueryString();

        $users = User::where('business_id', $this->bid())->orderBy('name')->get(['id', 'name']);

        $actions = ActivityLog::where('business_id', $this->bid())
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('app.team-access.activity-log', compact(
            'logs', 'users', 'actions',
            'user_id', 'action', 'date_from', 'date_to', 'search'
        ));
    }
}

<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /** List all support rooms for the authenticated user's business. */
    public function index()
    {
        $rooms = SupportRoom::where('business_id', Auth::user()->business_id)
            ->withCount('messages')
            ->latest()
            ->get();

        return view('app.support.index', compact('rooms'));
    }

    /** Show a single support room thread. */
    public function show(SupportRoom $room)
    {
        $this->authorizeRoom($room);

        $room->load(['messages.user']);

        return view('app.support.show', compact('room'));
    }

    /** Create a new support room + first message. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'support_type' => ['required', 'in:billing,technical,onboarding,general'],
            'subject'      => ['required', 'string', 'max:160'],
            'message'      => ['required', 'string', 'max:5000'],
        ]);

        $room = SupportRoom::create([
            'business_id'  => Auth::user()->business_id,
            'support_type' => $data['support_type'],
            'subject'      => $data['subject'],
            'status'       => 'open',
        ]);

        SupportMessage::create([
            'support_room_id' => $room->id,
            'user_id'         => Auth::id(),
            'message'         => $data['message'],
            'is_staff_reply'  => false,
        ]);

        return redirect()->route('support.show', $room)
            ->with('status', __('messages.ticket_created'));
    }

    /** Customer replies to an existing room. */
    public function reply(Request $request, SupportRoom $room)
    {
        $this->authorizeRoom($room);

        if ($room->status === 'closed') {
            return back()->withErrors(['message' => __('messages.ticket_closed_note')]);
        }

        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        SupportMessage::create([
            'support_room_id' => $room->id,
            'user_id'         => Auth::id(),
            'message'         => $data['message'],
            'is_staff_reply'  => false,
        ]);

        return back()->with('status', __('messages.reply_sent'));
    }

    /** Customer closes their own ticket. */
    public function close(SupportRoom $room)
    {
        $this->authorizeRoom($room);

        $room->update(['status' => 'closed']);

        return back()->with('status', __('messages.ticket_closed'));
    }

    /** Customer reopens a closed ticket. */
    public function reopen(SupportRoom $room)
    {
        $this->authorizeRoom($room);

        $room->update(['status' => 'open']);

        return back()->with('status', __('messages.ticket_reopened'));
    }

    /** Ensure the room belongs to the current user's business. */
    private function authorizeRoom(SupportRoom $room): void
    {
        abort_if($room->business_id !== Auth::user()->business_id, 403);
    }
}

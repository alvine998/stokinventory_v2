<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically logs create / update / delete events to the activity_logs table.
 *
 * Apply this trait to any Eloquent model that should be audited.
 * Events are only recorded when a real authenticated user is present,
 * so seeding, migrations, and console commands are silently skipped.
 */
trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function (self $model) {
            if (Auth::check()) {
                ActivityLog::record(
                    'created',
                    $model->activityLabel('created'),
                    static::class,
                    $model->id
                );
            }
        });

        static::updated(function (self $model) {
            if (Auth::check()) {
                ActivityLog::record(
                    'updated',
                    $model->activityLabel('updated'),
                    static::class,
                    $model->id
                );
            }
        });

        static::deleted(function (self $model) {
            if (Auth::check()) {
                ActivityLog::record(
                    'deleted',
                    $model->activityLabel('deleted'),
                    static::class,
                    $model->id
                );
            }
        });
    }

    /**
     * Build a human-readable description for the activity log.
     * Tries common identifier fields in priority order, falls back to "#id".
     */
    protected function activityLabel(string $event): string
    {
        $modelName = class_basename(static::class);

        $identifier = $this->so_no
            ?? $this->po_no
            ?? $this->pr_no
            ?? $this->grn_no
            ?? $this->do_no
            ?? $this->return_no
            ?? $this->invoice_no
            ?? $this->reference_no
            ?? $this->name
            ?? $this->subject
            ?? $this->title
            ?? $this->sku
            ?? ('#' . $this->id);

        return ucfirst($event) . " {$modelName}: {$identifier}";
    }
}

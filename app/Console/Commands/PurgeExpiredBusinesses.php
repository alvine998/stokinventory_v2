<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeExpiredBusinesses extends Command
{
    protected $signature = 'businesses:purge-expired';

    protected $description = 'Delete business data for trials expired more than 7 days ago';

    public function handle(): int
    {
        $cutoff = now()->subDays(7);

        $businesses = Business::whereNotNull('trial_expired_at')
            ->where('trial_expired_at', '<=', $cutoff)
            ->get();

        if ($businesses->isEmpty()) {
            $this->info('No expired businesses to purge.');

            return self::SUCCESS;
        }

        $count = 0;

        DB::transaction(function () use ($businesses, &$count) {
            foreach ($businesses as $business) {
                try {
                    $business->delete();
                    $count++;
                } catch (\Throwable $e) {
                    Log::error("Failed to purge business {$business->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Purged {$count} expired business(es).");

        return self::SUCCESS;
    }
}

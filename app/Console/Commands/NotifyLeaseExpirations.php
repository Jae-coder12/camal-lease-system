<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\Stall;
use App\Models\User;
use App\Notifications\MarketSystemNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyLeaseExpirations extends Command
{
    protected $signature = 'market:notify-lease-expirations';

    protected $description = 'Notify applicants and administrators about expiring or expired leases';

    public function handle(): int
    {
        $warningDate = today()->addDays(config('market.lease_warning_days', 30));
        $leases = Lease::with(['registrant.user', 'stall.building'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', $warningDate)
            ->get();

        foreach ($leases->where('end_date', '<', today())->groupBy('stall_id') as $stallId => $expiredLeases) {
            DB::transaction(function () use ($stallId) {
                Stall::query()->lockForUpdate()->findOrFail($stallId)->closeExpiredLeases();
            }, 5);
        }

        foreach ($leases as $lease) {
            $expired = $lease->end_date->lt(today());
            $key = $expired ? 'lease-expired-' . $lease->id : 'lease-expiring-' . $lease->id;
            $title = $expired ? 'Lease expired' : 'Lease expiring soon';
            $message = sprintf(
                'Lease %s for stall %s expires on %s.',
                $lease->contract_number,
                $lease->stall->stall_number,
                $lease->end_date->format('M d, Y'),
            );
            $recipients = collect([$lease->registrant->user])->filter();
            $recipients = $recipients->merge(User::where('role', 'admin')->get())->unique('id');

            foreach ($recipients as $recipient) {
                $alreadyNotified = DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $recipient->id)
                    ->where('type', MarketSystemNotification::class)
                    ->where('data', 'like', '%"notification_key":"' . $key . '"%')
                    ->exists();
                if ($alreadyNotified) continue;

                $recipient->notify(new MarketSystemNotification(
                    $title,
                    $message,
                    $recipient->isAdmin() ? route('leases.index') : route('my.leases'),
                    ['notification_key' => $key, 'lease_id' => $lease->id],
                ));
            }
        }

        $this->info('Lease expiration notifications checked.');
        return self::SUCCESS;
    }
}

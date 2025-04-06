<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use Illuminate\Console\Command;

class ExpirePlans extends Command
{
    protected $signature = 'plans:expire';
    protected $description = 'Expire plans that have reached their end date';

    public function handle()
    {
        $expiredPlans = Purchase::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired $expiredPlans plans successfully.");
    }
}

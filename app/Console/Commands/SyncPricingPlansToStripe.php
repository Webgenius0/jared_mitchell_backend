<?php

namespace App\Console\Commands;

use App\Models\PricingPlan;
use App\Services\StripeProductSyncService;
use Illuminate\Console\Command;

class SyncPricingPlansToStripe extends Command
{
    protected $signature = 'stripe:sync-plans
        {--plan= : Sync only a specific plan by ID}
        {--force : Re-sync even if stripe_product_id already exists}';

    protected $description = 'Sync pricing plans to Stripe, creating products and recurring prices';

    public function handle(StripeProductSyncService $syncService): int
    {
        $query = PricingPlan::query();

        if ($planId = $this->option('plan')) {
            $query->where('id', $planId);
        }

        if (!$this->option('force')) {
            $query->whereNull('stripe_product_id');
        }

        $plans = $query->get();

        if ($plans->isEmpty()) {
            $this->warn('No plans to sync.');

            return self::SUCCESS;
        }

        $this->info("Syncing {$plans->count()} plan(s) to Stripe...");

        $bar = $this->output->createProgressBar($plans->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($plans as $plan) {
            try {
                $result = $syncService->sync($plan);
                $this->line("\n  ✓ {$plan->plan_name} → {$result['stripe_price_id']}");
                $success++;
            } catch (\Exception $e) {
                $this->error("\n  ✗ {$plan->plan_name}: {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done. {$success} synced, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

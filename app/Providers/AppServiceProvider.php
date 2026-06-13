<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Schedule;
use App\Policies\ClientPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\QuotationPolicy;
use App\Policies\SchedulePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}

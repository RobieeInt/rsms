<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Finding;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Schedule;
use App\Models\Asset;

class DashboardController extends Controller
{
    public function index()
    {
        $isAdmin = auth()->user()->hasRole('admin');

        $stats = [
            'total_clients' => Client::where('is_active', true)->count(),
            'active_assets' => Asset::count(),
            'upcoming_visits' => Schedule::where('status', 'scheduled')
                ->where('visit_date', '>=', now()->toDateString())
                ->count(),
            'open_findings' => Finding::where('status', '!=', 'resolved')->count(),
        ];

        // Financial figures (quotations, invoices, revenue) are admin-only —
        // technicians must not see pricing/revenue data.
        if ($isAdmin) {
            $stats['pending_quotations'] = Quotation::whereIn('status', ['draft', 'sent'])->count();
            $stats['unpaid_invoices'] = Invoice::whereIn('status', ['sent', 'overdue'])->count();
            $stats['monthly_revenue'] = Invoice::where('status', 'paid')
                ->whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('total_amount');
            $stats['annual_revenue'] = Invoice::where('status', 'paid')
                ->whereYear('payment_date', now()->year)
                ->sum('total_amount');
        }

        $recentSchedules = Schedule::with(['client', 'technician'])
            ->where('visit_date', '>=', now()->toDateString())
            ->orderBy('visit_date')
            ->limit(5)
            ->get();

        $recentFindings = Finding::with(['client', 'asset'])
            ->where('status', 'open')
            ->where('severity', 'critical')
            ->latest()
            ->limit(5)
            ->get();

        $overdueInvoices = collect();
        $revenueData = [];

        if ($isAdmin) {
            $overdueInvoices = Invoice::with('client')
                ->where('status', 'overdue')
                ->latest()
                ->limit(5)
                ->get();

            $revenueData = $this->getRevenueChartData();
        }

        $clientHealthData = Client::select('health_status')
            ->selectRaw('count(*) as count')
            ->groupBy('health_status')
            ->get();

        return view('dashboard', compact(
            'isAdmin', 'stats', 'recentSchedules', 'recentFindings',
            'overdueInvoices', 'revenueData', 'clientHealthData'
        ));
    }

    private function getRevenueChartData(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('total_amount');
            $months[] = [
                'month' => $date->format('M Y'),
                'revenue' => (float) $revenue,
            ];
        }
        return $months;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FindingResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\ScheduleResource;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Finding;
use App\Models\Invoice;
use App\Models\Schedule;

class DashboardController extends Controller
{
    public function index()
    {
        $revenueThisMonth = (float) Invoice::where('status', 'paid')
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('total_amount');

        $lastMonth = now()->subMonthNoOverflow();
        $revenueLastMonth = (float) Invoice::where('status', 'paid')
            ->whereYear('payment_date', $lastMonth->year)
            ->whereMonth('payment_date', $lastMonth->month)
            ->sum('total_amount');

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0.0;

        $stats = [
            'total_clients' => Client::where('is_active', true)->count(),
            'total_assets' => Asset::count(),
            'pending_schedules' => Schedule::where('status', 'scheduled')
                ->where('visit_date', '>=', now()->toDateString())
                ->count(),
            'open_findings' => Finding::where('status', '!=', 'resolved')->count(),
            'pending_invoices' => Invoice::whereIn('status', ['draft', 'sent'])->count(),
            'overdue_invoices_count' => Invoice::where('status', 'overdue')->count(),
            'revenue_this_month' => $revenueThisMonth,
            'revenue_last_month' => $revenueLastMonth,
            'revenue_growth' => $revenueGrowth,
            'total_revenue' => (float) Invoice::where('status', 'paid')->sum('total_amount'),
        ];

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

        $overdueInvoices = Invoice::with('client')
            ->where('status', 'overdue')
            ->latest()
            ->limit(5)
            ->get();

        $revenueData = $this->getRevenueChartData();
        $clientHealthData = Client::selectRaw('health_status as status, count(*) as count')
            ->groupBy('health_status')
            ->get();

        return response()->json([
            'data' => [
                'stats' => $stats,
                'recent_schedules' => ScheduleResource::collection($recentSchedules),
                'recent_findings' => FindingResource::collection($recentFindings),
                'overdue_invoices' => InvoiceResource::collection($overdueInvoices),
                'revenue_data' => $revenueData,
                'client_health_data' => $clientHealthData,
            ],
        ]);
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

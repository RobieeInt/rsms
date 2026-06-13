<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly';
    protected $description = 'Generate monthly retainer invoices for active clients';

    public function __construct(private InvoiceService $invoiceService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Generating monthly invoices...');
        $this->invoiceService->generateMonthlyInvoices();
        $this->invoiceService->updateOverdueStatus();
        $this->info('Done.');
    }
}

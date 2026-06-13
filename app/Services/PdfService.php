<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\VisitReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfService
{
    public function generateVisitReport(VisitReport $report): \Barryvdh\DomPDF\PDF
    {
        $report->load([
            'client', 'technician', 'assetChecklists.asset',
            'networkChecklist', 'findings', 'photos',
        ]);

        $company = CompanySetting::getSettings();

        return Pdf::loadView('pdfs.visit-report', [
            'report' => $report,
            'company' => $company,
        ])->setPaper('a4');
    }

    public function generateQuotation(Quotation $quotation): \Barryvdh\DomPDF\PDF
    {
        $quotation->load(['client', 'creator', 'items']);
        $company = CompanySetting::getSettings();

        return Pdf::loadView('pdfs.quotation', [
            'quotation' => $quotation,
            'company' => $company,
        ])->setPaper('a4');
    }

    public function generateInvoice(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client', 'creator', 'items']);
        $company = CompanySetting::getSettings();

        Carbon::setLocale('id');

        return Pdf::loadView('pdfs.invoice', [
            'invoice' => $invoice,
            'company' => $company,
        ])->setPaper('a4');
    }
}

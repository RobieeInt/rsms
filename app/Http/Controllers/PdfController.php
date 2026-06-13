<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\VisitReport;
use App\Services\PdfService;

class PdfController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    public function report(VisitReport $report)
    {
        $pdf = $this->pdfService->generateVisitReport($report);
        return $pdf->download('report-' . $report->report_number . '.pdf');
    }

    public function quotation(Quotation $quotation)
    {
        $pdf = $this->pdfService->generateQuotation($quotation);
        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }

    public function invoice(Invoice $invoice)
    {
        $pdf = $this->pdfService->generateInvoice($invoice);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\VisitReport;
use App\Services\PdfService;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    public function report(VisitReport $report): Response
    {
        $pdf = $this->pdfService->generateVisitReport($report);

        return $pdf->download('report-'.$report->report_number.'.pdf');
    }

    public function quotation(Quotation $quotation): Response
    {
        $pdf = $this->pdfService->generateQuotation($quotation);

        return $pdf->download('quotation-'.$quotation->quotation_number.'.pdf');
    }

    public function invoice(Invoice $invoice): Response
    {
        $pdf = $this->pdfService->generateInvoice($invoice);

        return $pdf->download('invoice-'.$invoice->invoice_number.'.pdf');
    }
}

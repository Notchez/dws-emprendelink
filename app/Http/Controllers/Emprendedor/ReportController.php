<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\FilterEntrepreneurReportRequest;
use App\Services\Reports\EntrepreneurReportExporter;
use App\Services\Reports\EntrepreneurReportService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private EntrepreneurReportService $reports,
        private EntrepreneurReportExporter $exports,
    ) {}

    public function index(FilterEntrepreneurReportRequest $request): View
    {
        $business = $request->user()->emprendimiento()->firstOrFail();
        $filters = $request->filters();
        $status = $request->orderStatus();
        $report = $this->reports->report($business, $filters, $status);

        return view('entrepreneur.reports.index', [
            'emprendimiento' => $business,
            'filters' => $filters,
            'report' => $report,
            'charts' => $this->reports->charts($report),
            'status' => $status,
            'statuses' => OrderStatus::cases(),
            'query' => array_filter([
                ...$filters->toQuery(),
                'estado' => $status?->value,
            ], fn (mixed $value): bool => $value !== null && $value !== ''),
        ]);
    }

    public function exportPdf(FilterEntrepreneurReportRequest $request): Response
    {
        $business = $request->user()->emprendimiento()->firstOrFail();
        $filters = $request->filters();
        $report = $this->reports->report($business, $filters, $request->orderStatus());
        $filename = $this->exports->filename($business, $filters, 'pdf');

        return response($this->exports->pdf($business, $filters, $report), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function exportExcel(FilterEntrepreneurReportRequest $request): StreamedResponse
    {
        $business = $request->user()->emprendimiento()->firstOrFail();
        $filters = $request->filters();
        $report = $this->reports->report($business, $filters, $request->orderStatus());
        $filename = $this->exports->filename($business, $filters, 'xlsx');

        return response()->streamDownload(function () use ($business, $filters, $report): void {
            $this->exports->writeXlsx($business, $filters, $report, 'php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}

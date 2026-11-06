<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterAdminReportRequest;
use App\Models\Emprendimiento;
use App\Services\Reports\AdminReportExporter;
use App\Services\Reports\AdminReportService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private AdminReportService $reports,
        private AdminReportExporter $exports,
    ) {}

    public function index(FilterAdminReportRequest $request): View
    {
        [$filters, $businessId, $status, $report] = $this->reportData($request);

        return view('admin.reports.index', [
            'filters' => $filters,
            'businessId' => $businessId,
            'status' => $status,
            'report' => $report,
            'charts' => $this->reports->charts($report),
            'businesses' => Emprendimiento::query()->orderBy('nombre')->get(['id', 'nombre']),
            'statuses' => OrderStatus::cases(),
            'query' => array_filter([
                ...$filters->toQuery(),
                'emprendimiento_id' => $businessId,
                'estado' => $status?->value,
            ], fn (mixed $value): bool => $value !== null && $value !== ''),
        ]);
    }

    public function exportPdf(FilterAdminReportRequest $request): Response
    {
        [$filters, $businessId, $status, $report] = $this->reportData($request);
        $business = $businessId ? Emprendimiento::findOrFail($businessId) : null;

        return response($this->exports->pdf($filters, $report, $business, $status), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->exports->filename($filters, 'pdf').'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function exportExcel(FilterAdminReportRequest $request): StreamedResponse
    {
        [$filters, $businessId, $status, $report] = $this->reportData($request);
        $business = $businessId ? Emprendimiento::findOrFail($businessId) : null;

        return response()->streamDownload(function () use ($filters, $report, $business, $status): void {
            $this->exports->writeXlsx($filters, $report, $business, $status, 'php://output');
        }, $this->exports->filename($filters, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function reportData(FilterAdminReportRequest $request): array
    {
        $filters = $request->filters();
        $businessId = $request->businessId();
        $status = $request->orderStatus();

        return [
            $filters,
            $businessId,
            $status,
            $this->reports->report($filters, $businessId, $status),
        ];
    }
}

<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emprendedor\FilterEntrepreneurReportRequest;
use App\Services\Reports\EntrepreneurReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private EntrepreneurReportService $reports) {}

    public function index(FilterEntrepreneurReportRequest $request): View
    {
        $business = $request->user()->emprendimiento()->firstOrFail();
        $filters = $request->filters();

        return view('entrepreneur.reports.index', [
            'emprendimiento' => $business,
            'filters' => $filters,
            'report' => $this->reports->report($business, $filters),
        ]);
    }
}

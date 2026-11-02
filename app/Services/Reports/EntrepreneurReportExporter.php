<?php

namespace App\Services\Reports;

use App\Models\Emprendimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EntrepreneurReportExporter
{
    private const CURRENCY_FORMAT = '"$"#,##0.00';

    private const INTEGER_FORMAT = '0';

    private const EMPTY_PERIOD_MESSAGE = 'No hay pedidos en el período seleccionado.';

    private const CRITERIA_NOTE = 'Las ventas entregadas suman los pedidos creados en el período que actualmente están en estado Entregado. Las comisiones reflejan los cargos históricos generados en el período seleccionado.';

    public function filename(Emprendimiento $business, ReportFilters $filters, string $extension): string
    {
        $slug = Str::slug((string) ($business->slug ?: $business->nombre));

        if ($slug === '') {
            $slug = 'emprendimiento';
        }

        return sprintf(
            'reporte-%s-%s_%s.%s',
            $slug,
            $filters->desde->toDateString(),
            $filters->hasta->toDateString(),
            ltrim(strtolower($extension), '.'),
        );
    }

    /**
     * @param  array{
     *     pedidosPorEstado: array<string, int>,
     *     totalPedidos: int,
     *     ventas: array{total: string, pedidos: int},
     *     comisiones: array{generadas: string, pagadas: string, pendientes: string, cantidad: int},
     *     ventasPorPeriodo: list<array{periodo: string, ventas: string, pedidos: int}>,
     *     granularidad: 'dia'|'mes',
     *     topProductos: list<array{producto_id: int, nombre: string, unidades: int, ingresos: string}>
     * }  $report
     * @return array{
     *     emprendimiento: Emprendimiento,
     *     filters: ReportFilters,
     *     report: array<string, mixed>,
     *     generadoEn: CarbonImmutable
     * }
     */
    public function pdfData(Emprendimiento $business, ReportFilters $filters, array $report): array
    {
        return [
            'emprendimiento' => $business,
            'filters' => $filters,
            'report' => $report,
            'generadoEn' => CarbonImmutable::now(),
        ];
    }

    /**
     * @param  array{
     *     pedidosPorEstado: array<string, int>,
     *     totalPedidos: int,
     *     ventas: array{total: string, pedidos: int},
     *     comisiones: array{generadas: string, pagadas: string, pendientes: string, cantidad: int},
     *     ventasPorPeriodo: list<array{periodo: string, ventas: string, pedidos: int}>,
     *     granularidad: 'dia'|'mes',
     *     topProductos: list<array{producto_id: int, nombre: string, unidades: int, ingresos: string}>
     * }  $report
     */
    public function pdf(Emprendimiento $business, ReportFilters $filters, array $report): string
    {
        return Pdf::loadView('entrepreneur.reports.pdf', $this->pdfData($business, $filters, $report))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
            ])
            ->output();
    }

    /**
     * @param  array{
     *     pedidosPorEstado: array<string, int>,
     *     totalPedidos: int,
     *     ventas: array{total: string, pedidos: int},
     *     comisiones: array{generadas: string, pagadas: string, pendientes: string, cantidad: int},
     *     ventasPorPeriodo: list<array{periodo: string, ventas: string, pedidos: int}>,
     *     granularidad: 'dia'|'mes',
     *     topProductos: list<array{producto_id: int, nombre: string, unidades: int, ingresos: string}>
     * }  $report
     */
    public function writeXlsx(Emprendimiento $business, ReportFilters $filters, array $report, string $target): void
    {
        $spreadsheet = new Spreadsheet;
        $periodLabel = sprintf('%s – %s', $filters->desde->format('d/m/Y'), $filters->hasta->format('d/m/Y'));

        $spreadsheet->getProperties()
            ->setCreator('EmprendeLink')
            ->setTitle('Reporte — '.$periodLabel);

        $this->populateSummarySheet($spreadsheet->getActiveSheet(), $business, $periodLabel, $report);
        $this->populateStatusSheet($spreadsheet->createSheet(), $report);
        $this->populateSalesByPeriodSheet($spreadsheet->createSheet(), $report);
        $this->populateTopProductsSheet($spreadsheet->createSheet(), $report);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($target);

        $spreadsheet->disconnectWorksheets();
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function populateSummarySheet(
        Worksheet $sheet,
        Emprendimiento $business,
        string $periodLabel,
        array $report,
    ): void {
        $sheet->setTitle('Resumen');

        $this->writeString($sheet, 'A1', 'Indicador');
        $this->writeString($sheet, 'B1', 'Valor');
        $this->writeString($sheet, 'C1', 'Detalle');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $this->writeString($sheet, 'A2', 'Emprendimiento');
        $this->writeString($sheet, 'B2', (string) $business->nombre);

        $this->writeString($sheet, 'A3', 'Período consultado');
        $this->writeString($sheet, 'B3', $periodLabel);

        $this->writeString($sheet, 'A4', 'Fecha de generación');
        $this->writeString($sheet, 'B4', CarbonImmutable::now()->format('d/m/Y H:i'));

        $this->writeString($sheet, 'A5', 'Ventas entregadas');
        $this->writeAmount($sheet, 'B5', $report['ventas']['total']);
        $this->writeString($sheet, 'C5', ((int) $report['ventas']['pedidos']).' pedidos entregados');

        $this->writeString($sheet, 'A6', 'Pedidos entregados');
        $this->writeInteger($sheet, 'B6', (int) $report['ventas']['pedidos']);

        $this->writeString($sheet, 'A7', 'Pedidos del período');
        $this->writeInteger($sheet, 'B7', (int) $report['totalPedidos']);
        $this->writeString($sheet, 'C7', 'Agrupados por estado actual');

        $this->writeString($sheet, 'A8', 'Comisiones generadas');
        $this->writeAmount($sheet, 'B8', $report['comisiones']['generadas']);
        $this->writeString($sheet, 'C8', ((int) $report['comisiones']['cantidad']).' cargos registrados');

        $this->writeString($sheet, 'A9', 'Comisiones pagadas');
        $this->writeAmount($sheet, 'B9', $report['comisiones']['pagadas']);

        $this->writeString($sheet, 'A10', 'Comisiones pendientes');
        $this->writeAmount($sheet, 'B10', $report['comisiones']['pendientes']);

        $this->writeString($sheet, 'A11', 'Criterios del reporte');
        $this->writeString($sheet, 'B11', self::CRITERIA_NOTE);

        if ((int) $report['totalPedidos'] === 0) {
            $this->writeString($sheet, 'A12', 'Estado del período');
            $this->writeString($sheet, 'B12', self::EMPTY_PERIOD_MESSAGE);
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(36);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function populateStatusSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Pedidos por estado');

        $this->writeString($sheet, 'A1', 'Estado');
        $this->writeString($sheet, 'B1', 'Cantidad');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $row = 2;

        foreach ($report['pedidosPorEstado'] as $estado => $cantidad) {
            $this->writeString($sheet, 'A'.$row, (string) $estado);
            $this->writeInteger($sheet, 'B'.$row, (int) $cantidad);
            $row++;
        }

        $this->writeString($sheet, 'A'.$row, 'Total');
        $this->writeInteger($sheet, 'B'.$row, (int) $report['totalPedidos']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);

        if ((int) $report['totalPedidos'] === 0) {
            $row++;
            $this->writeString($sheet, 'A'.$row, self::EMPTY_PERIOD_MESSAGE);
        }

        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(16);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function populateSalesByPeriodSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Ventas por período');

        $granularity = (string) $report['granularidad'];
        $this->writeString($sheet, 'A1', $granularity === 'dia' ? 'Fecha' : 'Mes');
        $this->writeString($sheet, 'B1', 'Pedidos entregados');
        $this->writeString($sheet, 'C1', 'Total vendido');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $row = 2;

        if ((int) $report['ventas']['pedidos'] === 0) {
            $this->writeString($sheet, 'A'.$row, self::EMPTY_PERIOD_MESSAGE);
            $this->writeInteger($sheet, 'B'.$row, 0);
            $this->writeAmount($sheet, 'C'.$row, 0);
            $row++;
        } else {
            foreach ($report['ventasPorPeriodo'] as $fila) {
                $this->writeString(
                    $sheet,
                    'A'.$row,
                    EntrepreneurReportService::periodLabel((string) $fila['periodo'], $granularity),
                );
                $this->writeInteger($sheet, 'B'.$row, (int) $fila['pedidos']);
                $this->writeAmount($sheet, 'C'.$row, $fila['ventas']);
                $row++;
            }
        }

        $this->writeString($sheet, 'A'.$row, 'Total');
        $this->writeInteger($sheet, 'B'.$row, (int) $report['ventas']['pedidos']);
        $this->writeAmount($sheet, 'C'.$row, $report['ventas']['total']);
        $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(20);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function populateTopProductsSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Productos más vendidos');

        $this->writeString($sheet, 'A1', '#');
        $this->writeString($sheet, 'B1', 'Producto');
        $this->writeString($sheet, 'C1', 'Unidades');
        $this->writeString($sheet, 'D1', 'Ingresos');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        if ($report['topProductos'] === []) {
            $this->writeString($sheet, 'A2', '-');
            $this->writeString($sheet, 'B2', self::EMPTY_PERIOD_MESSAGE);
            $this->writeInteger($sheet, 'C2', 0);
            $this->writeAmount($sheet, 'D2', 0);
        } else {
            $row = 2;

            foreach ($report['topProductos'] as $index => $producto) {
                $this->writeInteger($sheet, 'A'.$row, $index + 1);
                $this->writeString($sheet, 'B'.$row, (string) $producto['nombre']);
                $this->writeInteger($sheet, 'C'.$row, (int) $producto['unidades']);
                $this->writeAmount($sheet, 'D'.$row, $producto['ingresos']);
                $row++;
            }
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(18);
    }

    private function writeString(Worksheet $sheet, string $coordinate, string $value): void
    {
        $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
    }

    private function writeAmount(Worksheet $sheet, string $coordinate, string|int|float $amount): void
    {
        $sheet->setCellValueExplicit($coordinate, round((float) $amount, 2), DataType::TYPE_NUMERIC);
        $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
    }

    private function writeInteger(Worksheet $sheet, string $coordinate, int $value): void
    {
        $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC);
        $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode(self::INTEGER_FORMAT);
    }
}

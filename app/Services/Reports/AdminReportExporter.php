<?php

namespace App\Services\Reports;

use App\Enums\OrderStatus;
use App\Models\Emprendimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminReportExporter
{
    private const CURRENCY_FORMAT = '"$"#,##0.00';

    public function filename(ReportFilters $filters, string $extension): string
    {
        return sprintf(
            'reporte-administrativo-%s_%s.%s',
            $filters->desde->toDateString(),
            $filters->hasta->toDateString(),
            ltrim(strtolower($extension), '.'),
        );
    }

    public function pdfData(
        ReportFilters $filters,
        array $report,
        ?Emprendimiento $business,
        ?OrderStatus $status,
    ): array {
        return [
            'filters' => $filters,
            'report' => $report,
            'emprendimiento' => $business,
            'status' => $status,
            'generadoEn' => CarbonImmutable::now(),
        ];
    }

    public function pdf(
        ReportFilters $filters,
        array $report,
        ?Emprendimiento $business,
        ?OrderStatus $status,
    ): string {
        return Pdf::loadView('admin.reports.pdf', $this->pdfData($filters, $report, $business, $status))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
            ])
            ->output();
    }

    public function writeXlsx(
        ReportFilters $filters,
        array $report,
        ?Emprendimiento $business,
        ?OrderStatus $status,
        string $target,
    ): void {
        $spreadsheet = new Spreadsheet;
        $period = sprintf('%s – %s', $filters->desde->format('d/m/Y'), $filters->hasta->format('d/m/Y'));

        $spreadsheet->getProperties()
            ->setCreator('EmprendeLink')
            ->setTitle('Reporte administrativo — '.$period);

        $this->summarySheet($spreadsheet->getActiveSheet(), $period, $report, $business, $status);
        $this->statusSheet($spreadsheet->createSheet(), $report);
        $this->salesSheet($spreadsheet->createSheet(), $report);
        $this->plansSheet($spreadsheet->createSheet(), $report);
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($target);
        $spreadsheet->disconnectWorksheets();
    }

    private function summarySheet(
        Worksheet $sheet,
        string $period,
        array $report,
        ?Emprendimiento $business,
        ?OrderStatus $status,
    ): void {
        $sheet->setTitle('Resumen');
        $rows = [
            ['Indicador', 'Valor', 'Detalle'],
            ['Período consultado', $period, ''],
            ['Emprendimiento', $business?->nombre ?? 'Todos', ''],
            ['Estado de pedido', $status?->value ?? 'Todos', ''],
            ['Ventas entregadas', (float) $report['ventas']['total'], $report['ventas']['pedidos'].' pedidos'],
            ['Pedidos del período', (int) $report['totalPedidos'], ''],
            ['Comisiones generadas', (float) $report['comisiones']['generadas'], $report['comisiones']['cantidad'].' cargos'],
            ['Comisiones pagadas', (float) $report['comisiones']['pagadas'], ''],
            ['Comisiones pendientes', (float) $report['comisiones']['pendientes'], ''],
            ['Emprendimientos activos', (int) $report['emprendimientosActivos'], ''],
            ['Fecha de generación', CarbonImmutable::now()->format('d/m/Y H:i'), ''],
        ];

        foreach ($rows as $index => $row) {
            $number = $index + 1;
            $this->writeString($sheet, 'A'.$number, (string) $row[0]);
            if (in_array($number, [5, 7, 8, 9], true)) {
                $this->writeAmount($sheet, 'B'.$number, $row[1]);
            } elseif (is_int($row[1])) {
                $this->writeInteger($sheet, 'B'.$number, $row[1]);
            } else {
                $this->writeString($sheet, 'B'.$number, (string) $row[1]);
            }
            $this->writeString($sheet, 'C'.$number, (string) $row[2]);
        }

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(24);
    }

    private function statusSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Pedidos por estado');
        $this->writeString($sheet, 'A1', 'Estado');
        $this->writeString($sheet, 'B1', 'Cantidad');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $row = 2;
        foreach ($report['pedidosPorEstado'] as $status => $count) {
            $this->writeString($sheet, 'A'.$row, (string) $status);
            $this->writeInteger($sheet, 'B'.$row, (int) $count);
            $row++;
        }
        $this->writeString($sheet, 'A'.$row, 'Total');
        $this->writeInteger($sheet, 'B'.$row, (int) $report['totalPedidos']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(16);
    }

    private function salesSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Ventas por período');
        $this->writeString($sheet, 'A1', $report['granularidad'] === 'dia' ? 'Fecha' : 'Mes');
        $this->writeString($sheet, 'B1', 'Pedidos entregados');
        $this->writeString($sheet, 'C1', 'Ventas');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $row = 2;
        foreach ($report['ventasPorPeriodo'] as $item) {
            $this->writeString(
                $sheet,
                'A'.$row,
                EntrepreneurReportService::periodLabel($item['periodo'], $report['granularidad']),
            );
            $this->writeInteger($sheet, 'B'.$row, (int) $item['pedidos']);
            $this->writeAmount($sheet, 'C'.$row, $item['ventas']);
            $row++;
        }
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(18);
    }

    private function plansSheet(Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Distribución de planes');
        $this->writeString($sheet, 'A1', 'Plan vigente');
        $this->writeString($sheet, 'B1', 'Emprendimientos activos');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $row = 2;
        foreach ($report['planesVigentes'] as $plan) {
            $this->writeString($sheet, 'A'.$row, $plan['nombre']);
            $this->writeInteger($sheet, 'B'.$row, (int) $plan['emprendimientos']);
            $row++;
        }
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(24);
    }

    private function writeString(Worksheet $sheet, string $coordinate, string $value): void
    {
        $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
    }

    private function writeAmount(Worksheet $sheet, string $coordinate, string|int|float $value): void
    {
        $sheet->setCellValueExplicit($coordinate, round((float) $value, 2), DataType::TYPE_NUMERIC);
        $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
    }

    private function writeInteger(Worksheet $sheet, string $coordinate, int $value): void
    {
        $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC);
        $sheet->getStyle($coordinate)->getNumberFormat()->setFormatCode('0');
    }
}

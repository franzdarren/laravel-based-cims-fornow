<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\TransactionLog;
use App\Services\ReportBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        $pastReports = Report::with('generatedBy')->latest()->get();
        $locations = SettingController::locationList();

        return view('reports.index', compact('pastReports', 'locations'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:Stock Balance,Transaction History,Equipment Registry / Status'],
            'transaction_filter' => ['nullable', 'in:RECEIVING,DISPOSAL,ISSUANCE,ADJUSTMENT'],
            'item_category_filter' => ['nullable', 'in:medicine,supply,equipment'],
            'equipment_site' => ['nullable', 'string', 'max:100'],
        ]);

        $transactionFilter = $data['transaction_filter'] ?? '';
        $itemCategoryFilter = $data['item_category_filter'] ?? '';
        $equipmentSite = $data['equipment_site'] ?? '';

        $snapshot = match ($data['type']) {
            'Stock Balance' => ReportBuilder::stockBalance(),
            'Transaction History' => ReportBuilder::transactionHistory($transactionFilter, $itemCategoryFilter),
            'Equipment Registry / Status' => ReportBuilder::equipmentRegistry($equipmentSite),
        };

        $filterSuffix = match ($data['type']) {
            'Transaction History' => ' · '.($transactionFilter ? ucfirst(strtolower($transactionFilter)) : 'All transactions').' · '.($itemCategoryFilter ? ucfirst($itemCategoryFilter) : 'All item types'),
            'Equipment Registry / Status' => ' · '.($snapshot['clinic_site'] ?? $equipmentSite).' · '.now()->year,
            default => '',
        };

        $report = Report::create([
            'type' => $data['type'],
            'generated_by' => $request->user()->user_id,
            'period' => 'As of '.now()->format('d M Y').$filterSuffix,
            'data' => $snapshot,
        ]);

        TransactionLog::note($request->user(), "Generated {$report->type} report", (string) $report->id);

        return redirect()->route('reports.show', $report);
    }

    public function show(Report $report)
    {
        return view('reports.show', compact('report'));
    }

    public function exportCsv(Report $report)
    {
        $filename = str($report->type)->slug().'-'.$report->id.'.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens special characters correctly
            foreach ($this->flatRows($report) as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportExcel(Report $report)
    {
        // HTML dressed as .xls — opens natively in Excel/LibreOffice without
        // needing a spreadsheet-writing package for this report set.
        $filename = str($report->type)->slug().'-'.$report->id.'.xls';
        $rows = $this->flatRows($report);

        $html = '<table border="1">';
        foreach ($rows as $i => $row) {
            $cell = $i === 0 ? 'th' : 'td';
            $html .= '<tr>'.collect($row)->map(fn ($v) => "<{$cell}>".e($v)."</{$cell}>")->implode('').'</tr>';
        }
        $html .= '</table>';

        return Response::make('<html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>', 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Report $report)
    {
        $filename = str($report->type)->slug().'-'.$report->id.'.pdf';
        $pdf = \App\Services\SimplePdf::fromRows($report->type, $report->period, $this->flatRows($report));

        return Response::make($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Reduces any of the three report layouts to a simple header+rows grid
     * for CSV/Excel/PDF export, since those formats don't need the show
     * page's rich weekly/monthly column grouping to be useful.
     */
    protected function flatRows(Report $report): array
    {
        $data = $report->data;

        if (($data['layout'] ?? '') === 'stock-balance-monthly') {
            $header = ['Item', 'Running Balance', 'Total Monthly Dispensed', 'Beginning Inventory'];
            for ($w = 1; $w <= 5; $w++) {
                $header = array_merge($header, ["W{$w} Delivered", "W{$w} Pullout/Returns", "W{$w} Issued", "W{$w} Dispensed", "W{$w} Ending Inventory"]);
            }
            $header[] = 'Actual Inventory';
            $header[] = 'Variance';

            $rows = [$header];
            foreach ($data['rows'] as $row) {
                $line = [$row['item'], $row['running_bal'], $row['total_dispensed'], $row['beginning_inv']];
                foreach ($row['weeks'] as $w) {
                    $line = array_merge($line, [$w['del'], $w['pullout'], $w['issued'], $w['dispensed'], $w['ending_inv']]);
                }
                $line[] = $row['actual_inv'];
                $line[] = $row['var'];
                $rows[] = $line;
            }

            return $rows;
        }

        if (($data['layout'] ?? '') === 'equipment-monthly') {
            $rows = [array_merge(['Property Number', 'Item'], $data['months'])];
            foreach ($data['rows'] as $row) {
                if ($row['kind'] === 'section') {
                    $rows[] = [$row['label']];

                    continue;
                }
                $rows[] = array_merge([$row['property_number'], $row['item']], $row['months']);
            }

            return $rows;
        }

        // transaction-history
        $rows = [['Date', 'Type', 'Reference', 'User', 'Activity']];
        foreach ($data['rows'] ?? [] as $row) {
            $rows[] = [$row['date'], $row['type'], $row['reference'], $row['user'], $row['activity']];
        }

        return $rows;
    }
}

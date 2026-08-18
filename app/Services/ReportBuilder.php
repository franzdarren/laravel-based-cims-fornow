<?php

namespace App\Services;

use App\Http\Controllers\SettingController;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\TransactionLog;
use App\Models\User;
use Carbon\Carbon;

/**
 * Builds the three report snapshots the way the clinic's paper worksheets
 * are laid out. Each method returns a plain array that's stored verbatim in
 * reports.data (json) and rendered by resources/views/reports/show.blade.php.
 */
class ReportBuilder
{
    protected static function week(string $date): int
    {
        return min(4, intdiv(Carbon::parse($date)->day - 1, 7));
    }

    /**
     * Monthly stock balance worksheet: one row per active medicine/supply
     * item, reconstructing the month's beginning inventory by working
     * backward from current on-hand using this month's approved receiving
     * lines, disposals, and issuance lines (bucketed into weeks 1-5).
     */
    public static function stockBalance(): array
    {
        $today = Carbon::parse(now()->toDateString());
        $monthStart = $today->copy()->startOfMonth();
        $inMonth = fn (?string $date) => $date && Carbon::parse($date)->between($monthStart, $today);

        $items = InventoryItem::active()->whereIn('item_category', ['MEDICINE', 'SUPPLY'])->orderBy('item_name')->get();

        $receivingLogs = TransactionLog::with(['lines.batch', 'receivingTransaction'])
            ->where('transaction_type', 'RECEIVING')->get()
            ->filter(fn ($log) => $inMonth($log->receivingTransaction?->date_received));
        $disposalLogs = TransactionLog::with('lines.batch')
            ->where('transaction_type', 'DISPOSAL')->get()
            ->filter(fn ($log) => $inMonth($log->transaction_datetime));
        $issuanceLogs = TransactionLog::with('lines.batch')
            ->where('transaction_type', 'ISSUANCE')->get()
            ->filter(fn ($log) => $inMonth($log->transaction_datetime));

        $rows = [];
        foreach ($items as $item) {
            $deliveries = array_fill(0, 5, 0);
            $pullouts = array_fill(0, 5, 0);
            $issued = array_fill(0, 5, 0);

            foreach ($receivingLogs as $log) {
                $week = self::week($log->receivingTransaction->date_received);
                foreach ($log->lines as $line) {
                    if ($line->batch?->item_id === $item->item_id) {
                        $deliveries[$week] += max(0, ($line->qty_after ?? 0) - ($line->qty_before ?? 0));
                    }
                }
            }
            foreach ($disposalLogs as $log) {
                $week = self::week($log->transaction_datetime);
                foreach ($log->lines as $line) {
                    if ($line->batch?->item_id === $item->item_id) {
                        $pullouts[$week] += max(0, ($line->qty_before ?? 0) - ($line->qty_after ?? 0));
                    }
                }
            }
            foreach ($issuanceLogs as $log) {
                $week = self::week($log->transaction_datetime);
                foreach ($log->lines as $line) {
                    if ($line->batch?->item_id === $item->item_id) {
                        $issued[$week] += (int) $line->quantity_issued;
                    }
                }
            }

            $current = $item->stockOnHand();
            $totalDelivered = array_sum($deliveries);
            $totalDispensed = array_sum($pullouts) + array_sum($issued);
            $beginning = $current - $totalDelivered + $totalDispensed;

            $weeks = [];
            $balance = $beginning;
            for ($w = 0; $w < 5; $w++) {
                $dispensed = $pullouts[$w] + $issued[$w];
                $balance = $balance + $deliveries[$w] - $dispensed;
                $weeks[] = [
                    'del' => $deliveries[$w], 'pullout' => $pullouts[$w], 'issued' => $issued[$w],
                    'dispensed' => $dispensed, 'ending_inv' => $balance, 'actual_inv' => $balance, 'var' => 0,
                ];
            }

            $rows[] = [
                'category' => strtolower($item->item_category),
                'item' => $item->item_name,
                'running_bal' => $current,
                'total_dispensed' => $totalDispensed,
                'beginning_inv' => $beginning,
                'weeks' => $weeks,
                'actual_inv' => $current,
                'var' => $balance - $current,
            ];
        }

        return [
            'layout' => 'stock-balance-monthly',
            'month_label' => $today->format('F Y'),
            'rows' => $rows,
        ];
    }

    public static function transactionHistory(string $typeFilter = '', string $categoryFilter = ''): array
    {
        $logs = TransactionLog::with(['user', 'lines.batch.item', 'lines.equipment.item'])
            ->orderByDesc('transaction_datetime')->limit(500)->get()
            ->filter(function (TransactionLog $l) use ($typeFilter, $categoryFilter) {
                if ($typeFilter && $l->transaction_type !== $typeFilter) {
                    return false;
                }
                if ($categoryFilter && ! in_array(strtoupper($categoryFilter), self::logItemCategories($l))) {
                    return false;
                }

                return true;
            })->values();

        return [
            'layout' => 'transaction-history',
            'rows' => $logs->map(fn (TransactionLog $l) => [
                'date' => optional($l->transaction_datetime)->format('Y-m-d'),
                'type' => $l->transaction_type,
                'reference' => $l->reference_no,
                'user' => $l->user->fullname ?? 'System',
                'activity' => $l->summary(),
            ])->all(),
        ];
    }

    protected static function logItemCategories(TransactionLog $log): array
    {
        $categories = [];
        foreach ($log->lines as $line) {
            $categories[] = $line->batch?->item?->item_category ?? $line->equipment?->item?->item_category;
        }

        return array_values(array_filter(array_unique($categories)));
    }

    // The clinic's fixed monthly equipment inventory template — canonical
    // equipment names in a set order, independent of what's actually seeded.
    public static function equipmentTemplateSections(): array
    {
        return [
            '' => [
                'BP APPARATUS', 'COLD COMPRESS', 'HOT COMPRESS',
                'DELIVERY SET (FORCEPS, SURGICAL/MAYO SCISSOR, ETC)', 'FIRST AID KIT',
                'HOSPITAL BED W/BED FOAM', 'KIDNEY BASIN', 'LINENS', 'MEDICINE TRAY',
                'NEBULIZER SET', 'OPTIUM GLUCOMETER with Lancet Device', 'OTOSCOPE',
                'OXYGEN 15/20 LBS TANK', 'OXYGEN 5 LBS (PORTABLE OXYGEN) TANK',
                'OXYGEN 15/20 LBS  LEVEL', 'OXYGEN 5 LBS (PORTABLE OXYGEN) LEVEL',
                'OXYGEN REGULATOR', 'PENLIGHT', 'PILLOWS', 'PULSE OXIMETER', 'SPINE BOARD',
                'STRETCHER', 'STETHOSCOPE', 'WHEELCHAIR', 'EMERGENCY TACKLE BOX',
            ],
            'OTHERS' => [
                'CLERICAL CHAIR / JAM CHAIR (CHAIR W/0 ARM REST & WHEEL)',
                'CASALA CHAIR (CHAIR W/O ARM REST-FOR PATIENTS)', 'LATERAL CABINET',
                'REFRIGERATOR', 'WEIGHING SCALE', 'PEDESTAL WITH WHEELS', 'OXYGEN TROLLY',
                'ELECTRIC HOT COMPRESS', 'LOUNGE CHAIR', 'LOUNGE TABLE', 'SOFA CHAIR',
                'FOOT STOOL', 'CLINIC DESK', 'CLINIC CHAIR', 'WINDOW BLINDS', 'TRASH BIN', 'CURTAIN',
            ],
        ];
    }

    protected static function canonicalEquipmentName(string $name): string
    {
        $key = strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
        if ((str_contains($key, 'BP') || str_contains($key, 'BLOOD PRESSURE')) && (str_contains($key, 'MONITOR') || str_contains($key, 'APPARATUS'))) {
            return 'BP APPARATUS';
        }
        if (str_contains($key, 'GLUCOMETER')) {
            return 'OPTIUM GLUCOMETER with Lancet Device';
        }
        if (str_contains($key, 'NEBULIZER')) {
            return 'NEBULIZER SET';
        }

        return trim($name);
    }

    public static function equipmentRegistry(string $site = ''): array
    {
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $today = Carbon::parse(now()->toDateString());
        $year = $today->year;
        $currentMonthIndex = $today->month - 1;
        $site = $site ?: (SettingController::locationList()[0] ?? '');

        $siteEquipment = Equipment::with('item')
            ->whereHas('location', fn ($q) => $q->whereRaw('LOWER(location_name) = ?', [strtolower($site)]))
            ->get();

        $byName = [];
        foreach ($siteEquipment as $e) {
            $name = self::canonicalEquipmentName($e->item->item_name ?? 'Unnamed equipment');
            $byName[$name][] = $e;
        }

        $disposalByEquipment = \App\Models\TransactionLine::whereNotNull('equipment_id')
            ->whereHas('transaction', fn ($q) => $q->where('transaction_type', 'DISPOSAL'))
            ->with('transaction')
            ->get()
            ->keyBy('equipment_id');

        $template = self::equipmentTemplateSections();
        $templateNames = array_map('strtoupper', array_merge($template[''], $template['OTHERS']));
        $extras = array_values(array_diff(array_keys($byName), $templateNames));
        sort($extras);

        $buildPair = function (string $label) use ($byName, $disposalByEquipment, $months, $currentMonthIndex, $year) {
            $records = $byName[$label] ?? [];
            $propertyNumber = collect($records)->map(fn ($e) => $e->asset_tag ?: $e->serial_number)->filter()->unique()->implode(', ');

            $monthValues = [];
            $remarks = [];
            foreach ($months as $i => $label2) {
                if ($i > $currentMonthIndex || ! count($records)) {
                    $monthValues[] = '';
                    $remarks[] = '';
                    continue;
                }
                $monthEnd = Carbon::create($year, $i + 1, 1)->endOfMonth();
                $count = 0;
                $events = [];
                foreach ($records as $e) {
                    $acquired = $e->created_at ? Carbon::parse($e->created_at) : null;
                    $disposalLine = $disposalByEquipment->get($e->equipment_id);
                    $disposedAt = $disposalLine?->transaction?->transaction_datetime ? Carbon::parse($disposalLine->transaction->transaction_datetime) : null;
                    if ($acquired && $acquired->lte($monthEnd) && (! $disposedAt || $disposedAt->gt($monthEnd))) {
                        $count++;
                    }
                    if ($disposedAt && $disposedAt->year === $year && $disposedAt->month - 1 === $i) {
                        $events[] = ($e->asset_tag ?: $e->serial_number ?: 'Unit').' disposed';
                    }
                    if ($i === $currentMonthIndex && $e->equipment_status && ! in_array($e->equipment_status, ['AVAILABLE', 'DISPOSED'])) {
                        $events[] = ($e->asset_tag ?: $e->serial_number ?: 'Unit').': '.str_replace('_', ' ', $e->equipment_status);
                    }
                }
                $monthValues[] = $count;
                $remarks[] = implode('; ', $events);
            }

            return [
                ['kind' => 'item', 'property_number' => $propertyNumber, 'item' => $label, 'months' => $monthValues],
                ['kind' => 'remarks', 'property_number' => '', 'item' => 'Remarks', 'months' => $remarks],
            ];
        };

        $rows = [];
        foreach ($template[''] as $name) {
            $rows = array_merge($rows, $buildPair($name));
        }
        $rows[] = ['kind' => 'section', 'label' => 'OTHERS'];
        foreach (array_unique(array_merge($template['OTHERS'], $extras)) as $name) {
            $rows = array_merge($rows, $buildPair($name));
        }

        $supervisor = User::whereHas('role', fn ($q) => $q->where('role_name', 'Supervisor'))->first();

        return [
            'layout' => 'equipment-monthly',
            'clinic_site' => $site,
            'year' => $year,
            'months' => $months,
            'rows' => $rows,
            'prepared_by' => $supervisor?->fullname ?? '—',
            'date_submitted' => $today->format('Y-m-d'),
            'notes' => ['Indicate defective/returned items', 'Indicate as well number of defective/returned items'],
            'property_note' => 'Indicated in BDO Sticker placed in the item (if available only)',
            'submission_note' => 'First Week of the following month',
        ];
    }
}

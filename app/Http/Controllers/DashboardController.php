<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\ReceivingTransaction;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role->role_name;

        $activeItemsCount = InventoryItem::active()->count();

        $nearExpiryBatches = Batch::active()->whereNotNull('expiry_date')->get()
            ->filter(fn (Batch $b) => $b->isNearExpiry())
            ->values();

        $lowStockItems = InventoryItem::active()
            ->where('item_category', '!=', 'EQUIPMENT')
            ->get()
            ->filter(fn (InventoryItem $i) => $i->isLowStock())
            ->values();

        $pendingReceivingCount = ReceivingTransaction::where('status', 'PENDING')->count();
        $equipmentInUseCount = Equipment::where('equipment_status', 'AVAILABLE')->count();

        $fourthCard = match ($role) {
            'Supervisor' => ['label' => 'Awaiting approval', 'value' => $pendingReceivingCount, 'hint' => 'Nurse requests requiring review'],
            'Nurse' => ['label' => 'My pending requests', 'value' => $pendingReceivingCount, 'hint' => 'You may cancel before supervisor action'],
            default => ['label' => 'Equipment in use', 'value' => $equipmentInUseCount, 'hint' => 'Individually tracked clinic assets'],
        };

        $canViewLogs = $user->role->hasPermission('transaction_log');
        $recentLogs = $canViewLogs
            ? TransactionLog::with('user')->orderByDesc('transaction_datetime')->limit(7)->get()
            : collect();

        $workflow = $this->workflowForRole($role);

        return view('dashboard.index', compact(
            'activeItemsCount', 'nearExpiryBatches', 'lowStockItems',
            'fourthCard', 'recentLogs', 'workflow', 'role', 'canViewLogs'
        ));
    }

    protected function workflowForRole(string $role): array
    {
        return match ($role) {
            'Nurse' => [
                ['Log deliveries', 'Record what arrives from suppliers in Receiving'],
                ['Issue stock', 'Dispense medicines and supplies using FEFO'],
                ['Keep records current', 'Edit batches, manage equipment, record disposals'],
            ],
            'Supervisor' => [
                ['Review requests', 'Check receiving requests submitted by Nurses'],
                ['Approve or return', 'Post inventory, or send a request back with a reason'],
                ['Report and oversee', 'Generate reports and monitor activity'],
            ],
            default => [
                ['Maintain the catalog', 'Keep Item Master accurate and current'],
                ['Manage accounts', 'Create and maintain Users and Roles'],
                ['Configure thresholds', 'Set the global near-expiry rule'],
            ],
        };
    }
}

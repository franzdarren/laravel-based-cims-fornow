<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\IssuanceTransaction;
use App\Models\Location;
use App\Models\ReceivingTransaction;
use App\Models\ReceivingTransactionLine;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\TransactionLog;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Reproduces the same sample data used in the V34.html click-through
 * prototype (same three staff members, same items/suppliers, and the
 * same in-flight pending/returned deliveries), rebuilt against the
 * team's ERD schema.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Role permissions (junction table rows) ----------------------
        $permissionKeys = \App\Http\Controllers\RoleController::permissionKeys();
        foreach ($permissionKeys as $key) {
            RolePermission::create(['role_permission_name' => $key]);
        }

        // ---- Roles --------------------------------------------------------
        $adminRole = Role::create(['role_name' => 'Administrator', 'role_description' => 'System setup: item catalog, accounts, roles, global settings', 'status' => 'active']);
        $nurseRole = Role::create(['role_name' => 'Nurse', 'role_description' => 'Day-to-day transactions: receiving, issuance, batches, equipment', 'status' => 'active']);
        $supervisorRole = Role::create(['role_name' => 'Supervisor', 'role_description' => 'Approvals, oversight, and reporting', 'status' => 'active']);

        $adminRole->permissions()->sync(RolePermission::whereIn('role_permission_name', ['items', 'users', 'roles', 'system_settings', 'locations', 'suppliers', 'transaction_log'])->pluck('role_permission_id'));
        $nurseRole->permissions()->sync(RolePermission::whereIn('role_permission_name', ['receiving', 'issuance', 'batches', 'equipment', 'disposal', 'suppliers', 'transaction_log'])->pluck('role_permission_id'));
        $supervisorRole->permissions()->sync(RolePermission::whereIn('role_permission_name', ['approvals', 'reports', 'receiving', 'batches', 'equipment', 'transaction_log'])->pluck('role_permission_id'));

        // ---- Users (same names as the prototype) --------------------------
        $ana = User::create(['fullname' => 'Ana Villanueva', 'email' => 'avillanueva@clinic.local', 'password' => Hash::make('password'), 'role_id' => $adminRole->role_id, 'is_active' => true]);
        $nina = User::create(['fullname' => 'Nina Cruz', 'email' => 'ncruz@clinic.local', 'password' => Hash::make('password'), 'role_id' => $nurseRole->role_id, 'is_active' => true]);
        $marco = User::create(['fullname' => 'Marco Lim', 'email' => 'mlim@clinic.local', 'password' => Hash::make('password'), 'role_id' => $supervisorRole->role_id, 'is_active' => true]);

        // ---- Global settings -----------------------------------------------
        Setting::set('near_expiry_days', '90');

        $tablet = UnitOfMeasurement::create(['uom_name' => 'tablet']);
        $capsule = UnitOfMeasurement::create(['uom_name' => 'capsule']);
        $box = UnitOfMeasurement::create(['uom_name' => 'box']);
        $unit = UnitOfMeasurement::create(['uom_name' => 'unit']);
        foreach (['piece', 'pack', 'bottle', 'vial'] as $u) {
            UnitOfMeasurement::create(['uom_name' => $u]);
        }

        $alabang = Location::create(['location_name' => 'Alabang']);
        $cebu = Location::create(['location_name' => 'Cebu']);
        $makati = Location::create(['location_name' => 'Makati']);

        // ---- Suppliers ------------------------------------------------------
        $metroMedical = Supplier::create(['supplier_name' => 'Metro Medical Trading', 'contact_person' => 'Lara Santos', 'contact_no' => '0917-555-2231', 'address' => 'Makati City', 'status' => 'active']);
        $healthSource = Supplier::create(['supplier_name' => 'HealthSource Pharma', 'contact_person' => 'Paolo Reyes', 'contact_no' => '0917-555-8890', 'address' => 'Quezon City', 'status' => 'active']);

        // ---- Item Master ------------------------------------------------------
        $paracetamol = InventoryItem::create(['item_code' => 'MED-PCM500', 'item_name' => 'Paracetamol 500mg', 'item_category' => 'MEDICINE', 'uom_id' => $tablet->uom_id, 'supplier_id' => $healthSource->supplier_id, 'reorder_threshold' => 100, 'reorder_qty' => 500, 'item_status' => 'active']);
        $amoxicillin = InventoryItem::create(['item_code' => 'MED-AMX500', 'item_name' => 'Amoxicillin 500mg', 'item_category' => 'MEDICINE', 'uom_id' => $capsule->uom_id, 'supplier_id' => $healthSource->supplier_id, 'reorder_threshold' => 60, 'reorder_qty' => 300, 'item_status' => 'active']);
        $mask = InventoryItem::create(['item_code' => 'SUP-MASK', 'item_name' => 'Surgical Face Mask', 'item_category' => 'SUPPLY', 'uom_id' => $box->uom_id, 'supplier_id' => $metroMedical->supplier_id, 'reorder_threshold' => 10, 'reorder_qty' => 40, 'item_status' => 'active']);
        $gloves = InventoryItem::create(['item_code' => 'SUP-GLOVE', 'item_name' => 'Nitrile Gloves', 'item_category' => 'SUPPLY', 'uom_id' => $box->uom_id, 'supplier_id' => $metroMedical->supplier_id, 'reorder_threshold' => 15, 'reorder_qty' => 50, 'item_status' => 'active']);
        $bpMonitor = InventoryItem::create(['item_code' => 'EQ-BPMON', 'item_name' => 'Digital BP Monitor', 'item_category' => 'EQUIPMENT', 'uom_id' => $unit->uom_id, 'supplier_id' => $metroMedical->supplier_id, 'reorder_threshold' => 0, 'reorder_qty' => 0, 'item_status' => 'active']);

        // ---- Historical (already-approved) receiving transaction, so batches exist ----
        $rtHistoric = ReceivingTransaction::create([
            'ref_no' => 'DR-40118', 'supplier_id' => $healthSource->supplier_id, 'received_by' => $nina->user_id,
            'approved_by' => $marco->user_id, 'date_received' => now()->subDays(70), 'status' => 'APPROVED', 'decided_at' => now()->subDays(69),
        ]);
        $batchPcmA = Batch::create(['item_id' => $paracetamol->item_id, 'receive_transaction_id' => $rtHistoric->receiving_transaction_id, 'batch_no' => 'PCM-2405A', 'brand' => 'Biogesic', 'expiry_date' => now()->addDays(37), 'quantity_received' => 600, 'quantity_on_hand' => 595, 'batch_status' => 'ACTIVE']);
        Batch::create(['item_id' => $paracetamol->item_id, 'receive_transaction_id' => $rtHistoric->receiving_transaction_id, 'batch_no' => 'PCM-2409B', 'brand' => 'Biogesic', 'expiry_date' => now()->addMonths(7), 'quantity_received' => 800, 'quantity_on_hand' => 800, 'batch_status' => 'ACTIVE']);
        Batch::create(['item_id' => $amoxicillin->item_id, 'receive_transaction_id' => $rtHistoric->receiving_transaction_id, 'batch_no' => 'AMX-2406A', 'brand' => 'Amoxil', 'expiry_date' => now()->addMonths(10), 'quantity_received' => 300, 'quantity_on_hand' => 300, 'batch_status' => 'ACTIVE']);
        $batchMask = Batch::create(['item_id' => $mask->item_id, 'receive_transaction_id' => $rtHistoric->receiving_transaction_id, 'batch_no' => 'MASK-2407', 'brand' => 'SafeCare', 'expiry_date' => null, 'quantity_received' => 60, 'quantity_on_hand' => 58, 'batch_status' => 'ACTIVE']);
        $bpEquipment = Equipment::create(['item_id' => $bpMonitor->item_id, 'receive_transaction_id' => $rtHistoric->receiving_transaction_id, 'asset_tag' => 'CLINIC-BP-01', 'serial_number' => 'SN-88213', 'brand' => 'Omron', 'model' => 'HEM-7120', 'location_id' => $alabang->location_id, 'equipment_status' => 'AVAILABLE']);

        $receivingLog = TransactionLog::create(['transaction_type' => 'RECEIVING', 'user_id' => $marco->user_id, 'receiving_transaction_id' => $rtHistoric->receiving_transaction_id, 'reference_no' => $rtHistoric->ref_no, 'transaction_datetime' => now()->subDays(69)]);
        $receivingLog->lines()->create(['batch_id' => $batchPcmA->batch_id, 'qty_before' => 0, 'qty_after' => 600, 'status_before' => null, 'status_after' => 'ACTIVE', 'line_remarks' => 'Paracetamol 500mg (batch PCM-2405A): stock 0 → 600 tablet']);
        $receivingLog->lines()->create(['equipment_id' => $bpEquipment->equipment_id, 'qty_before' => 0, 'qty_after' => 1, 'status_before' => null, 'status_after' => 'AVAILABLE', 'line_remarks' => 'Digital BP Monitor: received 1 unit (asset CLINIC-BP-01)']);

        // ---- A historical issuance (drawn from the near-expiry batch, FEFO) ----
        $issuanceLog = TransactionLog::create(['transaction_type' => 'ISSUANCE', 'user_id' => $nina->user_id, 'reference_no' => 'ISS-2026-0718-001', 'transaction_datetime' => now()->subDays(25)]);
        IssuanceTransaction::create(['employee_no' => 'EMP-1042', 'employee_name' => 'Alex Reyes', 'department' => 'Warehouse', 'employee_supervisor' => 'Rico Domingo', 'disposition' => 'Returned to work', 'chief_complaint' => 'Headache', 'transaction_id' => $issuanceLog->transaction_id]);
        $issuanceLog->lines()->create(['batch_id' => $batchPcmA->batch_id, 'qty_before' => 600, 'qty_after' => 595, 'quantity_issued' => 5, 'line_remarks' => 'Paracetamol 500mg (batch PCM-2405A): stock 600 → 595 tablet; issued 5 tablet']);

        // ---- A historical disposal (damaged face masks) ----
        $disposalLog = TransactionLog::create(['transaction_type' => 'DISPOSAL', 'user_id' => $nina->user_id, 'reference_no' => 'DSP-2026-0708-001', 'reason' => 'Damaged — Torn box found during storage check', 'transaction_datetime' => now()->subDays(35)]);
        $disposalLog->lines()->create(['batch_id' => $batchMask->batch_id, 'qty_before' => 60, 'qty_after' => 58, 'status_before' => 'ACTIVE', 'status_after' => 'ACTIVE', 'line_remarks' => 'Disposed 2 box of Surgical Face Mask from batch MASK-2407']);

        // ---- The one still-pending receiving request (for the Approvals demo) ----
        $rtPending = ReceivingTransaction::create(['ref_no' => 'DR-57902', 'supplier_id' => $metroMedical->supplier_id, 'received_by' => $nina->user_id, 'date_received' => now()->subDays(4), 'status' => 'PENDING']);
        ReceivingTransactionLine::create(['receiving_transaction_id' => $rtPending->receiving_transaction_id, 'item_id' => $mask->item_id, 'quantity' => 50, 'brand' => 'SafeCare', 'batch_no' => 'MASK-2410', 'location_id' => $makati->location_id]);
        TransactionLog::note($nina, "Submitted receiving transaction {$rtPending->ref_no} for supervisor approval", $rtPending->ref_no);

        // ---- A returned request (for the returned-request edit/resubmit demo) ----
        $rtReturned = ReceivingTransaction::create(['ref_no' => 'DR-58011', 'supplier_id' => $metroMedical->supplier_id, 'received_by' => $nina->user_id, 'date_received' => now()->subDays(2), 'status' => 'RETURNED', 'return_reason' => 'Missing location for the delivered gloves — please specify where they were stored.', 'decided_at' => now()->subDay()]);
        ReceivingTransactionLine::create(['receiving_transaction_id' => $rtReturned->receiving_transaction_id, 'item_id' => $gloves->item_id, 'quantity' => 40, 'brand' => 'SafeCare', 'batch_no' => 'GLV-2411', 'location_id' => null]);
        TransactionLog::note($marco, "Returned receiving request {$rtReturned->ref_no} to Nurse: Missing location for the delivered gloves.", $rtReturned->ref_no);

        $this->command?->info('Seeded roles, permissions, users (avillanueva@clinic.local / ncruz@clinic.local / mlim@clinic.local, all password: "password"), suppliers, items, batches, equipment, locations, UOMs, and sample transaction history.');
    }
}

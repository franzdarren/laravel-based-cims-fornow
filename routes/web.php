<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposalController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\IssuanceController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReceivingRecordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('role')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transaction Log — every role can view it.
    Route::get('/transaction-log', [TransactionLogController::class, 'index'])->name('logs.index');

    /*
    |--------------------------------------------------------------------
    | Administrator
    |--------------------------------------------------------------------
    */
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/items', [ItemMasterController::class, 'index'])->name('items.index');
        Route::get('/items/create', [ItemMasterController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemMasterController::class, 'store'])->name('items.store');
        Route::get('/items/{item}/edit', [ItemMasterController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [ItemMasterController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ItemMasterController::class, 'destroy'])->name('items.destroy');
        Route::post('/items/{item}/reactivate', [ItemMasterController::class, 'reactivate'])->name('items.reactivate');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/global', [SettingController::class, 'updateGlobal'])->name('settings.global');
        Route::put('/settings/items/{item}/reorder', [SettingController::class, 'updateReorder'])->name('settings.reorder');
        Route::post('/settings/uoms', [SettingController::class, 'addUom'])->name('settings.uoms.add');
        Route::post('/settings/locations', [SettingController::class, 'addLocation'])->name('settings.locations.add');
    });

    /*
    |--------------------------------------------------------------------
    | Nurse
    |--------------------------------------------------------------------
    */
    Route::middleware('role:Nurse')->group(function () {
        Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
        Route::get('/receiving/create', [ReceivingController::class, 'create'])->name('receiving.create');
        Route::post('/receiving/lines', [ReceivingController::class, 'addLine'])->name('receiving.lines.add');
        Route::delete('/receiving/lines/{index}', [ReceivingController::class, 'removeLine'])->name('receiving.lines.remove');
        Route::post('/receiving/draft/clear', [ReceivingController::class, 'clearDraft'])->name('receiving.draft.clear');
        Route::post('/receiving', [ReceivingController::class, 'store'])->name('receiving.store');
        Route::post('/receiving/{receiving}/cancel', [ReceivingController::class, 'cancel'])->name('receiving.cancel');
        Route::put('/receiving/{receiving}/returned/details', [ReceivingController::class, 'updateReturnedDetails'])->name('receiving.returned.details');
        Route::put('/receiving/{receiving}/returned/lines/{line}', [ReceivingController::class, 'updateReturnedLine'])->name('receiving.returned.line');
        Route::post('/receiving/{receiving}/returned/resubmit', [ReceivingController::class, 'resubmitReturned'])->name('receiving.returned.resubmit');

        Route::get('/issuance', [IssuanceController::class, 'index'])->name('issuance.index');
        Route::get('/issuance/create', [IssuanceController::class, 'create'])->name('issuance.create');
        Route::get('/issuance/allocation-preview', [IssuanceController::class, 'allocationPreview'])->name('issuance.allocation-preview');
        Route::post('/issuance/lines', [IssuanceController::class, 'addLine'])->name('issuance.lines.add');
        Route::delete('/issuance/lines/{index}', [IssuanceController::class, 'removeLine'])->name('issuance.lines.remove');
        Route::post('/issuance/draft/clear', [IssuanceController::class, 'clearDraft'])->name('issuance.draft.clear');
        Route::post('/issuance', [IssuanceController::class, 'store'])->name('issuance.store');
        Route::put('/issuance/{issuance}', [IssuanceController::class, 'update'])->name('issuance.update');

        Route::get('/equipment/{equipmentItem}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
        Route::put('/equipment/{equipmentItem}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::post('/equipment/{equipmentItem}/dispose', [EquipmentController::class, 'dispose'])->name('equipment.dispose');

        Route::get('/batches/{batch}/edit', [BatchController::class, 'edit'])->name('batches.edit');
        Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
        Route::post('/batches/{batch}/dispose', [BatchController::class, 'dispose'])->name('batches.dispose');
    });

    /*
    |--------------------------------------------------------------------
    | Suppliers — Nurse and Administrator both manage supplier records.
    |--------------------------------------------------------------------
    */
    Route::middleware('role:Nurse,Administrator')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    /*
    |--------------------------------------------------------------------
    | Supervisor
    |--------------------------------------------------------------------
    */
    Route::middleware('role:Supervisor')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{receiving}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{receiving}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{receiving}/return', [ApprovalController::class, 'return'])->name('approvals.return');

        Route::get('/receiving-records', [ReceivingRecordController::class, 'index'])->name('receiving.records');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{report}/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('/reports/{report}/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('/reports/{report}/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    /*
    |--------------------------------------------------------------------
    | Shared read-only views — Batches, Equipment, Disposals list pages
    | are visible to both Nurse (with edit/dispose buttons, above) and
    | Supervisor (view only). We register the index routes here, outside
    | the role-specific groups, and hide action buttons in the view
    | itself based on the logged-in user's role.
    |--------------------------------------------------------------------
    */
    Route::middleware('role:Nurse,Supervisor')->group(function () {
        Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
        Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
        Route::get('/disposals', [DisposalController::class, 'index'])->name('disposals.index');
    });
});

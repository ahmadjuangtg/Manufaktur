<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\MachineCategoryController;
use App\Http\Controllers\Master\MachineController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\ShopFloorController;
use App\Http\Controllers\ProductionReportController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // MASTER DATA
    Route::prefix('master')->middleware('permission:master_data_view')->group(function () {
        Route::get('/categories', [MasterController::class, 'indexCategory'])->name('categories.index');
        Route::post('/categories', [MasterController::class, 'storeCategory'])->name('categories.store');
        Route::post('/categories/update/{id}', [MasterController::class, 'updateCategory'])->name('categories.update');
        Route::post('/categories/delete/{id}', [MasterController::class, 'destroyCategory'])->name('categories.delete');

        Route::get('/types', [MasterController::class, 'indexType'])->name('types.index');
        Route::post('/types', [MasterController::class, 'storeType'])->name('types.store');
        Route::post('/types/update/{id}', [MasterController::class, 'updateType'])->name('types.update');
        Route::post('/types/delete/{id}', [MasterController::class, 'destroyType'])->name('types.delete');

        Route::get('/manufacturers', [MasterController::class, 'indexManufacturer'])->name('manufacturers.index');
        Route::post('/manufacturers', [MasterController::class, 'storeManufacturer'])->name('manufacturers.store');
        Route::post('/manufacturers/update/{id}', [MasterController::class, 'updateManufacturer'])->name('manufacturers.update');
        Route::post('/manufacturers/delete/{id}', [MasterController::class, 'destroyManufacturer'])->name('manufacturers.delete');
        Route::post('/manufacturers/copy-to-supplier/{id}', [MasterController::class, 'copyManufacturerToSupplier'])->name('manufacturers.copy');

        Route::get('/units', [MasterController::class, 'indexUnit'])->name('units.index');
        Route::post('/units', [MasterController::class, 'storeUnit'])->name('units.store');
        Route::post('/units/update/{id}', [MasterController::class, 'updateUnit'])->name('units.update');
        Route::post('/units/delete/{id}', [MasterController::class, 'destroyUnit'])->name('units.delete');

        Route::get('/items', [MasterController::class, 'indexItem'])->name('items.index');
        Route::post('/items', [MasterController::class, 'storeItem'])->name('items.store');
        Route::post('/items/update/{id}', [MasterController::class, 'updateItem'])->name('items.update');
        Route::post('/items/delete/{id}', [MasterController::class, 'destroyItem'])->name('items.delete');

        // New Master Routes
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::post('/customers/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::post('/customers/delete/{id}', [CustomerController::class, 'destroy'])->name('customers.delete');

        Route::get('/machine-categories', [MachineCategoryController::class, 'index'])->name('machine_categories.index');
        Route::post('/machine-categories', [MachineCategoryController::class, 'store'])->name('machine_categories.store');
        Route::post('/machine-categories/update/{id}', [MachineCategoryController::class, 'update'])->name('machine_categories.update');
        Route::post('/machine-categories/delete/{id}', [MachineCategoryController::class, 'destroy'])->name('machine_categories.delete');

        Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
        Route::post('/machines', [MachineController::class, 'store'])->name('machines.store');
        Route::post('/machines/update/{id}', [MachineController::class, 'update'])->name('machines.update');
        Route::post('/machines/delete/{id}', [MachineController::class, 'destroy'])->name('machines.delete');

        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::post('/warehouses/update/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');

        Route::get('/suppliers', [MasterController::class, 'indexSupplier'])->name('suppliers.index');
        Route::post('/suppliers', [MasterController::class, 'storeSupplier'])->name('suppliers.store');
        Route::post('/suppliers/update/{id}', [MasterController::class, 'updateSupplier'])->name('suppliers.update');
        Route::post('/suppliers/delete/{id}', [MasterController::class, 'destroySupplier'])->name('suppliers.delete');
        Route::post('/suppliers/copy-to-manufacturer/{id}', [MasterController::class, 'copySupplierToManufacturer'])->name('suppliers.copy');

        Route::get('/priorities', [\App\Http\Controllers\PriorityController::class, 'index'])->name('priorities.index');
        Route::post('/priorities', [\App\Http\Controllers\PriorityController::class, 'store'])->name('priorities.store');
        Route::post('/priorities/update/{id}', [\App\Http\Controllers\PriorityController::class, 'update'])->name('priorities.update');
        Route::post('/priorities/delete/{id}', [\App\Http\Controllers\PriorityController::class, 'destroy'])->name('priorities.delete');

        // Substitutions & Capabilities
        Route::get('/substitutions', [\App\Http\Controllers\Master\SubstitutionController::class, 'index'])->name('substitutions.index');
        Route::post('/substitutions/machine', [\App\Http\Controllers\Master\SubstitutionController::class, 'storeMachine'])->name('substitutions.machine.store');
        Route::post('/substitutions/item', [\App\Http\Controllers\Master\SubstitutionController::class, 'storeItem'])->name('substitutions.item.store');
        Route::post('/substitutions/capability', [\App\Http\Controllers\Master\SubstitutionController::class, 'storeCapability'])->name('substitutions.capability.store');
        Route::post('/substitutions/delete/{type}/{id}', [\App\Http\Controllers\Master\SubstitutionController::class, 'destroy'])->name('substitutions.delete');

        // Price List
        Route::get('/price-lists', [\App\Http\Controllers\PriceListController::class, 'index'])->name('price_lists.index');
        Route::post('/price-lists', [\App\Http\Controllers\PriceListController::class, 'store'])->name('price_lists.store');
        Route::post('/price-lists/update/{id}', [\App\Http\Controllers\PriceListController::class, 'update'])->name('price_lists.update');
        Route::post('/price-lists/delete/{id}', [\App\Http\Controllers\PriceListController::class, 'destroy'])->name('price_lists.delete');
        Route::get('/price-lists/get-price', [\App\Http\Controllers\PriceListController::class, 'getPrice'])->name('price_lists.get_price');
        Route::get('/price-lists/check-warehouses', [\App\Http\Controllers\PriceListController::class, 'checkItemWarehouses'])->name('price_lists.check_warehouses');
        Route::get('/suppliers/get-items/{id}', [MasterController::class, 'getSupplierItems'])->name('suppliers.get_items');
    });

    // SECURITY
    Route::prefix('security')->group(function () {
        Route::get('/roles', [SecurityController::class, 'indexRole'])->name('roles.index')->middleware('permission:master_role_view');
        Route::post('/roles', [SecurityController::class, 'storeRole'])->name('roles.store')->middleware('permission:security_create');
        Route::post('/roles/update/{id}', [SecurityController::class, 'updateRole'])->name('roles.update');
        Route::post('/roles/delete/{id}', [SecurityController::class, 'destroyRole'])->name('roles.delete');

        Route::get('/accounts', [SecurityController::class, 'indexAccount'])->name('accounts.index')->middleware('permission:master_account_view');
        Route::post('/accounts', [SecurityController::class, 'storeAccount'])->name('accounts.store');
        Route::post('/accounts/update/{id}', [SecurityController::class, 'updateAccount'])->name('accounts.update');
        Route::post('/accounts/delete/{id}', [SecurityController::class, 'destroyAccount'])->name('accounts.delete');
    });

    // ORDER MODULE
    Route::prefix('orders')->middleware('permission:order_view')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\OrderController::class, 'indexRequest'])->name('orders.requests.index');
        Route::post('/requests', [\App\Http\Controllers\OrderController::class, 'storeRequest'])->name('orders.requests.store');
        
        Route::get('/approvals', [\App\Http\Controllers\OrderController::class, 'indexApproval'])->name('orders.approvals.index');
        Route::post('/approvals/approve/{id}', [\App\Http\Controllers\OrderController::class, 'approveRequest'])->name('orders.approvals.approve');
        Route::post('/approvals/reject/{id}', [\App\Http\Controllers\OrderController::class, 'rejectRequest'])->name('orders.approvals.reject');
        Route::get('/requests/get-details/{id}', [\App\Http\Controllers\OrderController::class, 'getRequestDetails'])->name('orders.requests.get_details');
        Route::post('/requests/cancel/{id}', [\App\Http\Controllers\OrderController::class, 'cancelRequest'])->name('orders.requests.cancel');

        Route::get('/purchase-orders', [\App\Http\Controllers\OrderController::class, 'indexPO'])->name('orders.po.index');
        Route::post('/purchase-orders', [\App\Http\Controllers\OrderController::class, 'storePO'])->name('orders.po.store');

        Route::get('/receives', [\App\Http\Controllers\OrderController::class, 'indexReceive'])->name('orders.receives.index');
        Route::post('/receives/{id}', [\App\Http\Controllers\OrderController::class, 'storeReceive'])->name('orders.receives.store');
    });

    // PRODUCTION MODULE
    Route::prefix('production')->group(function () {
        Route::get('/work-orders', [\App\Http\Controllers\ProductionController::class, 'index'])->name('production.work_orders.index');
        Route::get('/work_orders/create', [\App\Http\Controllers\ProductionController::class, 'create'])->name('production.work_orders.create');
        Route::post('/work_orders', [\App\Http\Controllers\ProductionController::class, 'store'])->name('production.work_orders.store');
        Route::get('/work-orders/get-template/{id}', [\App\Http\Controllers\ProductionController::class, 'getTemplate'])->name('production.work_orders.get_template');
        Route::post('/work-orders/update-status/{id}', [\App\Http\Controllers\ProductionController::class, 'updateStatus'])->name('production.work_orders.update_status');
        Route::get('/work-orders/get-stages/{id}', [\App\Http\Controllers\ProductionController::class, 'getStages'])->name('production.work_orders.get_stages');
        
        // Scheduling
        Route::get('/scheduling', [\App\Http\Controllers\SchedulingController::class, 'index'])->name('production.scheduling.index');
        Route::post('/scheduling/update/{id}', [\App\Http\Controllers\SchedulingController::class, 'updateSchedule'])->name('production.scheduling.update');
        Route::post('/scheduling/bulk-update', [\App\Http\Controllers\SchedulingController::class, 'bulkUpdate'])->name('production.scheduling.bulk_update');
        Route::post('/scheduling/repair', [\App\Http\Controllers\SchedulingController::class, 'repairSchedules'])->name('production.scheduling.repair');
        Route::get('/scheduling/get-substitutes', [\App\Http\Controllers\SchedulingController::class, 'getSubstitutes'])->name('production.scheduling.get_substitutes');

        // Templates
        Route::resource('templates', \App\Http\Controllers\ProductionTemplateController::class)->names([
            'index' => 'production.templates.index',
            'create' => 'production.templates.create',
            'store' => 'production.templates.store',
            'show' => 'production.templates.show',
            'edit' => 'production.templates.edit',
            'update' => 'production.templates.update',
            'destroy' => 'production.templates.destroy',
        ]);
    });

    // SHOP FLOOR MODULE
    Route::prefix('shop-floor')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\ShopFloorController::class, 'index'])->name('shop_floor.index');
        Route::post('/stage/start/{id}', [\App\Http\Controllers\ShopFloorController::class, 'startStage'])->name('shop_floor.stage.start');
        Route::post('/stage/finish/{id}', [\App\Http\Controllers\ShopFloorController::class, 'finishStage'])->name('shop_floor.stage.finish');
        Route::post('/machine/status/{id}', [\App\Http\Controllers\ShopFloorController::class, 'updateMachineStatus'])->name('shop_floor.machine.status');
        Route::post('/output/report/{id}', [\App\Http\Controllers\ShopFloorController::class, 'reportOutput'])->name('shop_floor.output.report');
        Route::post('/material-request/{id}', [\App\Http\Controllers\ShopFloorController::class, 'storeMaterialRequest'])->name('shop_floor.material_request');
        Route::get('/get-stage-items/{id}', [\App\Http\Controllers\ShopFloorController::class, 'getStageItems'])->name('shop_floor.get_stage_items');
    });

    // PRODUCTION REPORTS (LHP, NPB, PHP)
    Route::prefix('production/reports')->group(function () {
        Route::get('/lhp', [\App\Http\Controllers\ProductionReportController::class, 'indexLHP'])->name('production.reports.lhp');
        Route::get('/handover', [\App\Http\Controllers\ProductionReportController::class, 'indexHandover'])->name('production.reports.handover');
        Route::get('/handover/print/{id}', [\App\Http\Controllers\ProductionReportController::class, 'printHandover'])->name('production.reports.handover.print');
        Route::post('/handover', [\App\Http\Controllers\ProductionReportController::class, 'storeHandover'])->name('production.reports.handover.store');
        Route::post('/handover/verify/{id}', [\App\Http\Controllers\ProductionReportController::class, 'verifyHandover'])->name('production.reports.handover.verify');
    });

    // TRANSACTIONS
    Route::prefix('transactions')->group(function () {
        Route::get('/inventory', [TransactionController::class, 'indexInventory'])->name('inventory.index')->middleware('permission:inventory_view');
        Route::post('/inventory', [TransactionController::class, 'storeInventory'])->name('inventory.store')->middleware('permission:inventory_create');

        Route::get('/stock-opname', [TransactionController::class, 'indexOpname'])->name('opname.index')->middleware('permission:stock_opname_view');
        Route::get('/stock-opname/approval', [TransactionController::class, 'indexOpnameApproval'])->name('opname.approval.index')->middleware('permission:stock_opname_approval_view');
        Route::get('/stock-opname/create', [TransactionController::class, 'createOpname'])->name('opname.create')->middleware('permission:stock_opname_create');
        Route::post('/stock-opname', [TransactionController::class, 'storeOpname'])->name('opname.store')->middleware('permission:stock_opname_create');
        Route::post('/stock-opname/approve/{id}', [TransactionController::class, 'approveOpname'])->name('opname.approve')->middleware('permission:stock_opname_approval_view');
        Route::post('/stock-opname/reject/{id}', [TransactionController::class, 'rejectOpname'])->name('opname.reject')->middleware('permission:stock_opname_approval_view');
        Route::get('/stock-opname/get-stock', [TransactionController::class, 'getStock'])->name('opname.get_stock');

        Route::get('/stock-card', [TransactionController::class, 'indexStockCard'])->name('stock_card.index')->middleware('permission:stock_card_view');
        Route::get('/stock-card/print-all', [TransactionController::class, 'printAllStockCards'])->name('stock_card.print_all')->middleware('permission:stock_card_view');
        Route::get('/stock-card/export-excel', [TransactionController::class, 'exportExcelStockCard'])->name('stock_card.export_excel')->middleware('permission:stock_card_view');
        Route::get('/stock-card/print/{id}', [TransactionController::class, 'printStockCard'])->name('stock_card.print')->middleware('permission:stock_card_view');
        Route::get('/stock-card/export-excel-single/{id}', [TransactionController::class, 'exportExcelSingleStockCard'])->name('stock_card.export_excel_single')->middleware('permission:stock_card_view');

        // Mutation Routes
        Route::prefix('mutations')->group(function () {
            Route::get('/request', [\App\Http\Controllers\StockMutationController::class, 'indexRequest'])->name('mutations.request.index');
            Route::post('/request', [\App\Http\Controllers\StockMutationController::class, 'storeRequest'])->name('mutations.request.store');
            
            Route::get('/approval', [\App\Http\Controllers\StockMutationController::class, 'indexApproval'])->name('mutations.approval.index');
            Route::post('/approval/approve/{id}', [\App\Http\Controllers\StockMutationController::class, 'approve'])->name('mutations.approval.approve');
            Route::post('/approval/reject/{id}', [\App\Http\Controllers\StockMutationController::class, 'reject'])->name('mutations.approval.reject');
            
            Route::get('/index', [\App\Http\Controllers\StockMutationController::class, 'indexMutation'])->name('mutations.index');
            Route::post('/send/{id}', [\App\Http\Controllers\StockMutationController::class, 'send'])->name('mutations.send');
            Route::post('/receive/{id}', [\App\Http\Controllers\StockMutationController::class, 'receive'])->name('mutations.receive');
            Route::get('/get-details/{id}', [\App\Http\Controllers\StockMutationController::class, 'show'])->name('mutations.get_details');
            Route::get('/print/{id}', [\App\Http\Controllers\StockMutationController::class, 'print'])->name('mutations.print');

            // Rekap PM & Realisasi Cicilan
            Route::get('/rekap', [\App\Http\Controllers\StockMutationController::class, 'indexRekap'])->name('mutations.rekap.index');
            Route::post('/deliver-partial/{id}', [\App\Http\Controllers\StockMutationController::class, 'deliverPartial'])->name('mutations.deliver_partial');
            Route::post('/receive-partial/{id}', [\App\Http\Controllers\StockMutationController::class, 'receivePartial'])->name('mutations.receive_partial');
        });
    });

    // LOGISTICS MODULE
    Route::prefix('logistics')->group(function () {
        Route::get('/packing', [\App\Http\Controllers\LogisticsController::class, 'indexPacking'])->name('logistics.packing.index')->middleware('permission:logistics_packing_view');
        Route::post('/packing', [\App\Http\Controllers\LogisticsController::class, 'storePacking'])->name('logistics.packing.store')->middleware('permission:logistics_packing_create');
        Route::post('/packing/ready/{id}', [\App\Http\Controllers\LogisticsController::class, 'updateStatusPacking'])->name('logistics.packing.ready')->middleware('permission:logistics_packing_create');
        
        Route::get('/delivery', [\App\Http\Controllers\LogisticsController::class, 'indexDelivery'])->name('logistics.delivery.index')->middleware('permission:logistics_delivery_view');
        Route::post('/delivery', [\App\Http\Controllers\LogisticsController::class, 'storeDelivery'])->name('logistics.delivery.store')->middleware('permission:logistics_delivery_create');
        Route::get('/delivery/print/{id}', [\App\Http\Controllers\LogisticsController::class, 'printSuratJalan'])->name('logistics.delivery.print')->middleware('permission:logistics_delivery_view');

        Route::get('/tracking', [\App\Http\Controllers\LogisticsController::class, 'indexTracking'])->name('logistics.tracking.index')->middleware('permission:logistics_tracking_view');
        Route::post('/tracking/update/{id}', [\App\Http\Controllers\LogisticsController::class, 'updateStatusDelivery'])->name('logistics.tracking.update')->middleware('permission:logistics_tracking_create');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\Admin\PendingIngredientController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceViewController;
use App\Http\Controllers\ProductionDayController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DriveFileController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\VendorController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Custom route to serve purchase photos (fixes 403 error)
Route::get('/storage/purchases/{type}/{filename}', function ($type, $filename) {
    $path = "purchases/{$type}/{$filename}";

    if (!\Illuminate\Support\Facades\Storage::exists($path)) {
        abort(404);
    }

    $file = \Illuminate\Support\Facades\Storage::get($path);
    $mimeType = \Illuminate\Support\Facades\Storage::mimeType($path);

    return response($file, 200)->header('Content-Type', $mimeType);
})->where(['type' => 'invoices|goods', 'filename' => '.*']);

Route::get('/time-clock', [AttendanceViewController::class, 'tablet'])->name('attendance.tablet');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('attendance', AdminAttendanceController::class)->only(['index', 'update']);
    Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class);

    // Settings Routes
    Route::get('settings/location', [\App\Http\Controllers\Admin\SettingsController::class, 'location'])->name('settings.location');
    Route::get('settings/devices', [\App\Http\Controllers\Admin\SettingsController::class, 'devices'])->name('settings.devices');

    // Purchases moved to main group

    // Inventory
    Route::get('inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/upload', [\App\Http\Controllers\Admin\InventoryController::class, 'upload'])->name('inventory.upload');
    Route::post('inventory/upload', [\App\Http\Controllers\Admin\InventoryController::class, 'import'])->name('inventory.import');
    Route::post('inventory/import-sales', [\App\Http\Controllers\Admin\InventoryController::class, 'importSalesReport'])->name('inventory.import_sales');
    Route::post('inventory/{ingredient}/adjust', [\App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('inventory/{ingredient}/history', [\App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('inventory.show');
    Route::delete('inventory/bulk-destroy', [\App\Http\Controllers\Admin\InventoryController::class, 'bulkDestroy'])->name('inventory.bulk_destroy');
    Route::delete('inventory/{ingredient}', [\App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('inventory.destroy');
});

// Auth Routes (Simple manual auth if not using Breeze/UI)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    // Password Change
    Route::get('change-password', [LoginController::class, 'changePasswordForm'])->name('password.change_form');
    Route::post('change-password', [LoginController::class, 'updatePassword'])->name('password.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Production / Cooking
    Route::get('production/cook', [\App\Http\Controllers\ProductionController::class, 'create'])->name('production.create');
    Route::post('production/cook', [\App\Http\Controllers\ProductionController::class, 'store'])->name('production.store');

    // Recipes
    Route::get('recipes/{recipe}/print', [RecipeController::class, 'print'])->name('recipes.print');
    Route::get('recipes/export/{type}', [RecipeController::class, 'export'])->name('recipes.export');
    Route::resource('recipes', RecipeController::class);
    Route::delete('categories/bulk-destroy', [\App\Http\Controllers\CategoryController::class, 'bulkDestroy'])->name('categories.bulk_destroy');
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::post('recipes/{recipe}/approve', [RecipeController::class, 'approve'])->name('recipes.approve');
    Route::post('recipes/{recipe}/reject', [RecipeController::class, 'reject'])->name('recipes.reject');
    Route::post('recipes/{recipe}/scale', [RecipeController::class, 'scale'])->name('recipes.scale');



    // Purchases
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class)->only(['index', 'create', 'store']);
    Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
    // Admin only actions
    Route::middleware(['admin'])->group(function () {
        Route::post('purchases/{purchase}/approve', [\App\Http\Controllers\PurchaseController::class, 'approve'])->name('purchases.approve');
        Route::post('purchases/{purchase}/reject', [\App\Http\Controllers\PurchaseController::class, 'reject'])->name('purchases.reject');
    });
    // Ingredients
    Route::post('ingredients/quick-store', [App\Http\Controllers\IngredientController::class, 'storeQuick'])->name('ingredients.storeQuick');
    Route::resource('ingredients', IngredientController::class);
    // Pending Ingredients (Admin)
    Route::get('admin/ingredients/pending', [PendingIngredientController::class, 'index'])->name('admin.ingredients.pending');
    Route::put('admin/ingredients/{ingredient}/approve', [PendingIngredientController::class, 'update'])->name('admin.ingredients.approve');

    Route::get('ingredients/search/ajax', [IngredientController::class, 'search'])->name('ingredients.search');

    // Production Days
    Route::resource('production', ProductionDayController::class);
    Route::post('production/{production_day}/add-item', [ProductionDayController::class, 'addItem'])->name('production.add_item');
    Route::get('production/{production_day}/print', [ProductionDayController::class, 'print'])->name('production.print');
    Route::post('production-items/{item}/status', [ProductionDayController::class, 'toggleItemStatus'])->name('production_items.status');

    // Tasks
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');

    // Drive Files
    Route::post('drive-files', [DriveFileController::class, 'store'])->name('drive_files.store');
    Route::delete('drive-files/{drive_file}', [DriveFileController::class, 'destroy'])->name('drive_files.destroy');
    Route::get('drive-files/{drive_file}/preview', [DriveFileController::class, 'preview'])->name('drive_files.preview');

    // Excel Import/Export
    Route::get('excel/import', [ExcelController::class, 'importForm'])->name('excel.import_form');
    Route::post('excel/import', [ExcelController::class, 'import'])->name('excel.import');
    Route::get('excel/export-recipes', [ExcelController::class, 'exportRecipes'])->name('excel.export_recipes');
    Route::get('excel/template', [ExcelController::class, 'downloadTemplate'])->name('excel.template');
    Route::get('excel/inventory-template', [ExcelController::class, 'inventoryTemplate'])->name('excel.inventory_template');
    Route::get('excel/purchase-template', [ExcelController::class, 'purchaseTemplate'])->name('excel.purchase_template');
    Route::get('excel/sales-template', [ExcelController::class, 'salesTemplate'])->name('excel.sales_template');
    Route::get('excel/export-cost', [ExcelController::class, 'exportCostSummary'])->name('excel.export_cost');
    Route::get('excel/errors/{id}', [ExcelController::class, 'downloadErrors'])->name('excel.download_errors');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

    // POS Integration
    Route::get('pos/upload', [\App\Http\Controllers\PosController::class, 'uploadForm'])->name('pos.upload');
    Route::post('pos/parse', [\App\Http\Controllers\PosController::class, 'parse'])->name('pos.parse');
    Route::post('pos/process', [\App\Http\Controllers\PosController::class, 'process'])->name('pos.process');

    // Admin SOP Management
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class);
        Route::get('shift-assignments', [\App\Http\Controllers\Admin\ShiftAssignmentController::class, 'index'])->name('shifts.assignments.index');
        Route::post('shift-assignments', [\App\Http\Controllers\Admin\ShiftAssignmentController::class, 'store'])->name('shifts.assignments.store');

        // Review Routes
        Route::get('sop/reviews', [\App\Http\Controllers\Admin\SopReviewController::class, 'index'])->name('sop.reviews.index');
        Route::get('sop/reviews/{run}', [\App\Http\Controllers\Admin\SopReviewController::class, 'show'])->name('sop.reviews.show');
        Route::post('sop/reviews/{run}/approve', [\App\Http\Controllers\Admin\SopReviewController::class, 'approveRun'])->name('sop.reviews.approve_run');
        Route::post('sop/reviews/{run}/approve/{completion}', [\App\Http\Controllers\Admin\SopReviewController::class, 'approveItem'])->name('sop.reviews.approve_item');
        Route::post('sop/reviews/{run}/reject/{completion}', [\App\Http\Controllers\Admin\SopReviewController::class, 'rejectItem'])->name('sop.reviews.reject');

        Route::resource('sop', \App\Http\Controllers\Admin\SopController::class);
        Route::post('sop/{checklist}/archive', [\App\Http\Controllers\Admin\SopController::class, 'archive'])->name('sop.archive');
        Route::post('sop/{checklist}/pause', [\App\Http\Controllers\Admin\SopController::class, 'pause'])->name('sop.pause');
        Route::post('sop/reorder', [\App\Http\Controllers\Admin\SopController::class, 'reorder'])->name('sop.reorder');
    });

    // Staff SOP Execution
    Route::get('sop', [\App\Http\Controllers\SopController::class, 'index'])->name('sop.index');
    Route::get('sop/{checklist}/execute', [\App\Http\Controllers\SopController::class, 'execute'])->name('sop.execute');
    Route::post('sop/{checklist}/item/{itemId}', [\App\Http\Controllers\SopController::class, 'updateItem'])->name('sop.update_item');
    Route::post('sop/{checklist}/complete', [\App\Http\Controllers\SopController::class, 'complete'])->name('sop.complete');
    Route::get('sop/report', [\App\Http\Controllers\SopController::class, 'report'])->name('sop.report');
});

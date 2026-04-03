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

// Custom route to serve attendance photos (fixes 403 error)
Route::get('/storage/attendance/{path}', function ($path) {
    $full_path = "attendance/{$path}";

    // Check public disk first
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($full_path)) {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
    }
    // Fallback to local (private) disk if it's there
    elseif (\Illuminate\Support\Facades\Storage::disk('local')->exists($full_path)) {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
    }
    else {
        abort(404);
    }

    $file = $disk->get($full_path);
    $mimeType = $disk->mimeType($full_path);

    return response($file, 200)->header('Content-Type', $mimeType);
})->where('path', '.*');

// Onboarding Wizard (Public with Token)
Route::get('/onboarding/{token}', [\App\Http\Controllers\Employee\OnboardingWizardController::class , 'show'])->name('onboarding.wizard');
Route::post('/onboarding/check-email', [\App\Http\Controllers\Employee\OnboardingWizardController::class , 'checkEmail'])->name('onboarding.check_email');
Route::post('/onboarding/{token}', [\App\Http\Controllers\Employee\OnboardingWizardController::class , 'submit'])->name('onboarding.submit');

Route::get('/time-clock', [AttendanceViewController::class , 'tablet'])->name('attendance.tablet');

// Admin Routes
// Redirect legacy admin routes to tenant-aware routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('{any?}', function ($any = null) {
            $user = auth()->user();
            if ($user->kitchen) {
                return redirect('/k/' . $user->kitchen->slug . '/admin/' . $any);
            }
            return redirect()->route('superadmin.dashboard');
        }
        )->where('any', '.*');
    });

// Auth Routes (Simple manual auth if not using Breeze/UI)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class , 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class , 'login']);
});

Route::post('logout', [LoginController::class , 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    // Password Change
    Route::get('change-password', [LoginController::class , 'changePasswordForm'])->name('password.change_form');
    Route::post('change-password', [LoginController::class , 'updatePassword'])->name('password.update');

    // Simple /dashboard redirect based on role + kitchen
    Route::get('/dashboard', function () {
            $user = auth()->user();
            if (!$user) {
                return redirect('/login');
            }

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            if ($user->kitchen_id && $user->kitchen) {
                return redirect("/k/{$user->kitchen->slug}/dashboard");
            }

            return redirect('/');
        }
        );

        // My Profile (staff / any logged-in user)
        Route::get('my-profile', [\App\Http\Controllers\ProfileController::class , 'show'])->name('profile.show');
        Route::get('my-profile/edit', [\App\Http\Controllers\ProfileController::class , 'edit'])->name('profile.edit');
        Route::put('my-profile', [\App\Http\Controllers\ProfileController::class , 'update'])->name('profile.update');
    });

Route::middleware(['auth', 'tenant'])->prefix('k/{kitchen_slug}')->group(function () {
    // Tenant Root Redirect
    Route::get('/', function () {
            return redirect()->route('dashboard');
        }
        );

        // Dashboard
        Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

        // Production / Cooking
        Route::get('production/cook', [\App\Http\Controllers\ProductionController::class , 'create'])->name('production.create');
        Route::post('production/cook', [\App\Http\Controllers\ProductionController::class , 'store'])->name('production.store');
        Route::get('production/template', [\App\Http\Controllers\ProductionController::class , 'downloadTemplate'])->name('production.template');
        Route::post('production/upload', [\App\Http\Controllers\ProductionController::class , 'uploadExcel'])->name('production.upload');

        // Recipes
        Route::get('recipes/{recipe}/print', [RecipeController::class , 'print'])->name('recipes.print');
        Route::get('recipes/export/{type}', [RecipeController::class , 'export'])->name('recipes.export');
        Route::post('categories/store-quick', [\App\Http\Controllers\CategoryController::class , 'storeQuick'])->name('categories.storeQuick');
        Route::resource('recipes', RecipeController::class);
        Route::delete('categories/bulk-destroy', [\App\Http\Controllers\CategoryController::class , 'bulkDestroy'])->name('categories.bulk_destroy');
        Route::resource('vendors', \App\Http\Controllers\VendorController::class);
        Route::resource('categories', \App\Http\Controllers\CategoryController::class);
        Route::post('recipes/{recipe}/approve', [RecipeController::class , 'approve'])->name('recipes.approve');
        Route::post('recipes/{recipe}/reject', [RecipeController::class , 'reject'])->name('recipes.reject');
        Route::post('recipes/{recipe}/scale', [RecipeController::class , 'scale'])->name('recipes.scale');



        // Purchases
        Route::resource('purchases', \App\Http\Controllers\PurchaseController::class)->only(['index', 'create', 'store']);
        Route::get('purchases/download/90days', [\App\Http\Controllers\PurchaseController::class , 'downloadInvoices90Days'])->name('purchases.download.90days');
        Route::get('purchases/{purchase}/download-invoice', [\App\Http\Controllers\PurchaseController::class , 'downloadInvoice'])->name('purchases.download.invoice');
        Route::post('vendors', [VendorController::class , 'store'])->name('vendors.store');
        // Admin only actions
        Route::middleware(['admin'])->group(function () {
            Route::post('purchases/bulk-approve', [\App\Http\Controllers\PurchaseController::class , 'bulkApprove'])->name('purchases.bulk_approve');
            Route::post('purchases/{purchase}/approve', [\App\Http\Controllers\PurchaseController::class , 'approve'])->name('purchases.approve');
            Route::post('purchases/{purchase}/reject', [\App\Http\Controllers\PurchaseController::class , 'reject'])->name('purchases.reject');
        }
        );

        // Ingredients
        Route::post('ingredients/quick-store', [App\Http\Controllers\IngredientController::class , 'storeQuick'])->name('ingredients.storeQuick');
        Route::resource('ingredients', IngredientController::class);
        // Pending Ingredients (Admin)
        Route::get('admin/ingredients/pending', [PendingIngredientController::class , 'index'])->name('admin.ingredients.pending');
        Route::put('admin/ingredients/{ingredient}/approve', [PendingIngredientController::class , 'update'])->name('admin.ingredients.approve');
        Route::delete('admin/ingredients/{ingredient}/reject', [PendingIngredientController::class , 'destroy'])->name('admin.ingredients.reject');

        Route::get('ingredients/search/ajax', [IngredientController::class , 'search'])->name('ingredients.search');

        // Production Days
        Route::resource('production', ProductionDayController::class);
        Route::post('production/{production_day}/add-item', [ProductionDayController::class , 'addItem'])->name('production.add_item');
        Route::get('production/{production_day}/print', [ProductionDayController::class , 'print'])->name('production.print');
        Route::post('production-items/{item}/status', [ProductionDayController::class , 'toggleItemStatus'])->name('production_items.status');

        // Tasks
        Route::post('tasks', [TaskController::class , 'store'])->name('tasks.store');
        Route::delete('tasks/{task}', [TaskController::class , 'destroy'])->name('tasks.destroy');
        Route::post('tasks/{task}/complete', [TaskController::class , 'complete'])->name('tasks.complete');
        Route::post('tasks/{task}/reopen', [TaskController::class , 'reopen'])->name('tasks.reopen');

        // Drive Files
        Route::post('drive-files', [DriveFileController::class , 'store'])->name('drive_files.store');
        Route::delete('drive-files/{drive_file}', [DriveFileController::class , 'destroy'])->name('drive_files.destroy');
        Route::get('drive-files/{drive_file}/preview', [DriveFileController::class , 'preview'])->name('drive_files.preview');

        // Excel Import/Export
        Route::get('excel/import', [ExcelController::class , 'importForm'])->name('excel.import_form');
        Route::post('excel/import', [ExcelController::class , 'import'])->name('excel.import');
        Route::get('excel/export-recipes', [ExcelController::class , 'exportRecipes'])->name('excel.export_recipes');
        Route::get('excel/template', [ExcelController::class , 'downloadTemplate'])->name('excel.template');
        Route::get('excel/inventory-template', [ExcelController::class , 'inventoryTemplate'])->name('excel.inventory_template');
        Route::get('excel/purchase-template', [ExcelController::class , 'purchaseTemplate'])->name('excel.purchase_template');
        Route::get('excel/sales-template', [ExcelController::class , 'salesTemplate'])->name('excel.sales_template');
        Route::get('excel/export-cost', [ExcelController::class , 'exportCostSummary'])->name('excel.export_cost');
        Route::get('excel/errors/{id}', [ExcelController::class , 'downloadErrors'])->name('excel.download_errors');

        // Audit Logs
        Route::get('audit-logs', [AuditLogController::class , 'index'])->name('audit_logs.index');

        // POS Integration
        Route::get('pos/upload', [\App\Http\Controllers\PosController::class , 'uploadForm'])->name('pos.upload');
        Route::post('pos/parse', [\App\Http\Controllers\PosController::class , 'parse'])->name('pos.parse');
        Route::post('pos/process', [\App\Http\Controllers\PosController::class , 'process'])->name('pos.process');

        // Admin SOP & Staff Management
        Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
            // Attendance & Leaves
            Route::resource('attendance', AdminAttendanceController::class)->only(['index', 'update']);
            Route::post('attendance/{attendance}/force-clock-out', [AdminAttendanceController::class , 'forceClockOut'])->name('attendance.force_clock_out');

            Route::get('leaves', [\App\Http\Controllers\Admin\LeaveController::class , 'index'])->name('leave.index');
            Route::post('leaves/{leave}/approve', [\App\Http\Controllers\Admin\LeaveController::class , 'approve'])->name('leave.approve');
            Route::post('leaves/{leave}/reject', [\App\Http\Controllers\Admin\LeaveController::class , 'reject'])->name('leave.reject');

            Route::get('performance', [\App\Http\Controllers\Admin\PerformanceController::class , 'index'])->name('performance.index');
            Route::get('performance/create', [\App\Http\Controllers\Admin\PerformanceController::class , 'create'])->name('performance.create');
            Route::post('performance', [\App\Http\Controllers\Admin\PerformanceController::class , 'store'])->name('performance.store');

            Route::get('payroll', [\App\Http\Controllers\Admin\PayrollController::class , 'index'])->name('payroll.index');
            Route::post('payroll/generate', [\App\Http\Controllers\Admin\PayrollController::class , 'generate'])->name('payroll.generate');
            Route::get('payroll/export', [\App\Http\Controllers\Admin\PayrollController::class , 'export'])->name('payroll.export');
            Route::post('payroll/{payroll}/pay', [\App\Http\Controllers\Admin\PayrollController::class , 'markAsPaid'])->name('payroll.pay');
            Route::get('payroll/{payroll}/download', [\App\Http\Controllers\Admin\PayrollController::class , 'downloadPayslip'])->name('payroll.download');

            Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->names('roles');
            Route::get('schedule', [\App\Http\Controllers\Admin\ScheduleController::class , 'index'])->name('schedule.index');
            Route::post('schedule/requirements', [\App\Http\Controllers\Admin\ScheduleController::class , 'saveRequirements'])->name('schedule.requirements');
            Route::post('schedule/assignments', [\App\Http\Controllers\Admin\ScheduleController::class , 'saveAssignments'])->name('schedule.assignments');

            Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class);
            Route::post('staff/{staff}/approve', [\App\Http\Controllers\Admin\StaffController::class , 'approve'])->name('staff.approve');
            Route::get('staff/{user}/permissions', [\App\Http\Controllers\Admin\PermissionController::class , 'edit'])->name('staff.permissions.edit');
            Route::put('staff/{user}/permissions', [\App\Http\Controllers\Admin\PermissionController::class , 'update'])->name('staff.permissions.update');

            // Settings Routes
            Route::get('settings/location', [\App\Http\Controllers\Admin\SettingsController::class , 'location'])->name('settings.location');
            Route::post('settings/location', [\App\Http\Controllers\Admin\SettingsController::class , 'updateLocation'])->name('settings.location.update');
            Route::get('settings/devices', [\App\Http\Controllers\Admin\SettingsController::class , 'devices'])->name('settings.devices');

            // Inventory
            Route::get('inventory', [\App\Http\Controllers\Admin\InventoryController::class , 'index'])->name('inventory.index');
            Route::get('inventory/upload', [\App\Http\Controllers\Admin\InventoryController::class , 'upload'])->name('inventory.upload');
            Route::post('inventory/upload', [\App\Http\Controllers\Admin\InventoryController::class , 'import'])->name('inventory.import');
            Route::post('inventory/import-sales', [\App\Http\Controllers\Admin\InventoryController::class , 'importSalesReport'])->name('inventory.import_sales');
            Route::post('inventory/{ingredient}/adjust', [\App\Http\Controllers\Admin\InventoryController::class , 'adjust'])->name('inventory.adjust');
            Route::get('inventory/{ingredient}/history', [\App\Http\Controllers\Admin\InventoryController::class , 'show'])->name('inventory.show');
            Route::delete('inventory/bulk-destroy', [\App\Http\Controllers\Admin\InventoryController::class , 'bulkDestroy'])->name('inventory.bulk_destroy');
            Route::delete('inventory/{ingredient}', [\App\Http\Controllers\Admin\InventoryController::class , 'destroy'])->name('inventory.destroy');

            Route::post('shifts/auto-generate', [\App\Http\Controllers\Admin\ShiftController::class , 'autoGenerate'])->name('shifts.auto_generate');
            Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class);
            Route::get('shift-assignments', [\App\Http\Controllers\Admin\ShiftAssignmentController::class , 'index'])->name('shifts.assignments.index');
            Route::post('shift-assignments', [\App\Http\Controllers\Admin\ShiftAssignmentController::class , 'store'])->name('shifts.assignments.store');

            // Review Routes
            Route::get('sop/reviews', [\App\Http\Controllers\Admin\SopReviewController::class , 'index'])->name('sop.reviews.index');
            Route::get('sop/reviews/{run}', [\App\Http\Controllers\Admin\SopReviewController::class , 'show'])->name('sop.reviews.show');
            Route::post('sop/reviews/{run}/approve', [\App\Http\Controllers\Admin\SopReviewController::class , 'approveRun'])->name('sop.reviews.approve_run');
            Route::post('sop/reviews/{run}/approve/{completion}', [\App\Http\Controllers\Admin\SopReviewController::class , 'approveItem'])->name('sop.reviews.approve_item');
            Route::post('sop/reviews/{run}/reject/{completion}', [\App\Http\Controllers\Admin\SopReviewController::class , 'rejectItem'])->name('sop.reviews.reject');

            Route::resource('sop', \App\Http\Controllers\Admin\SopController::class);
            Route::post('sop/{checklist}/archive', [\App\Http\Controllers\Admin\SopController::class , 'archive'])->name('sop.archive');
            Route::post('sop/{checklist}/pause', [\App\Http\Controllers\Admin\SopController::class , 'pause'])->name('sop.pause');
            Route::post('sop/reorder', [\App\Http\Controllers\Admin\SopController::class , 'reorder'])->name('sop.reorder');
        }
        );

        // Staff SOP Execution
        Route::get('sop', [\App\Http\Controllers\SopController::class , 'index'])->name('sop.index');

        // Employee Portal (Personal)
        Route::get('my-attendance', [\App\Http\Controllers\Employee\AttendanceController::class , 'index'])->name('employee.attendance.index');
        Route::get('my-leaves', [\App\Http\Controllers\Employee\LeaveController::class , 'index'])->name('employee.leave.index');
        Route::get('my-payslips', [\App\Http\Controllers\Admin\PayrollController::class , 'myPayslips'])->name('employee.payroll.index');
        Route::get('my-schedule', [\App\Http\Controllers\Admin\ShiftController::class , 'mySchedule'])->name('employee.shifts.index');
        Route::get('my-leaves/create', [\App\Http\Controllers\Employee\LeaveController::class , 'create'])->name('employee.leave.create');
        Route::post('my-leaves', [\App\Http\Controllers\Employee\LeaveController::class , 'store'])->name('employee.leave.store');

        Route::get('sop/{checklist}/execute', [\App\Http\Controllers\SopController::class , 'execute'])->name('sop.execute');
        Route::post('sop/{checklist}/item/{itemId}', [\App\Http\Controllers\SopController::class , 'updateItem'])->name('sop.update_item');
        Route::post('sop/{checklist}/complete', [\App\Http\Controllers\SopController::class , 'complete'])->name('sop.complete');
        Route::get('sop/report', [\App\Http\Controllers\SopController::class , 'report'])->name('sop.report');
    });

// Super Admin Routes (Central Management)
Route::middleware(['auth', 'super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class , 'index'])->name('dashboard');
    Route::resource('kitchens', \App\Http\Controllers\SuperAdmin\KitchenController::class);
});
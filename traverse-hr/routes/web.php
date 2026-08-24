<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\DocumentTemplateVersionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeeLockerController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\JobRoleDocumentController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\Public\EmployeeLockerPublicController;
use App\Http\Controllers\SignedDocumentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // 2FA setup/challenge live outside the 2fa.verified gate — that's the point.
    Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorController::class, 'confirmSetup']);
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge']);
});

Route::middleware(['auth', '2fa.verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('outlets', OutletController::class)->except('show')->middleware('permission:admin.settings.manage');
    Route::resource('job-roles', JobRoleController::class)->except('show')->middleware('permission:admin.settings.manage');

    Route::middleware('permission:admin.settings.manage')->group(function () {
        Route::resource('document-templates', DocumentTemplateController::class)->except('show');
        Route::post('/document-templates/{documentTemplate}/variants', [DocumentTemplateController::class, 'storeVariant'])->name('document-templates.variants.store');
        Route::post('/document-template-variants/{variant}/make-default', [DocumentTemplateController::class, 'makeVariantDefault'])->name('document-template-variants.make-default');
        Route::post('/document-template-variants/{variant}/versions', [DocumentTemplateController::class, 'storeVersion'])->name('document-template-variants.versions.store');
        Route::post('/document-template-variants/{variant}/text-versions', [DocumentTemplateController::class, 'storeTextVersion'])->name('document-template-variants.text-versions.store');

        Route::get('/job-roles/{jobRole}/documents', [JobRoleDocumentController::class, 'edit'])->name('job-roles.documents.edit');
        Route::put('/job-roles/{jobRole}/documents', [JobRoleDocumentController::class, 'update'])->name('job-roles.documents.update');
    });

    Route::middleware('permission:document.view')->group(function () {
        Route::get('/document-template-versions/{version}/download', [DocumentTemplateVersionController::class, 'download'])->name('document-template-versions.download');
    });

    Route::middleware('permission:employee.view')->group(function () {
        Route::resource('employees', EmployeeController::class)->except('show');
        Route::get('/employees/{employee}/photo', [EmployeeController::class, 'photo'])->name('employees.photo');
        Route::post('/employees/{employee}/reveal/{field}', [EmployeeController::class, 'reveal'])
            ->middleware(['password.confirm', 'permission:employee.view-unmasked'])
            ->name('employees.reveal');
    });

    Route::middleware('permission:document.manage')->group(function () {
        Route::get('/employees/{employee}/documents', [EmployeeDocumentController::class, 'index'])->name('employees.documents.index');
        Route::get('/employees/{employee}/documents/{documentTemplate}', [EmployeeDocumentController::class, 'show'])->name('employees.documents.show');
        Route::post('/employees/{employee}/documents/{documentTemplate}/accept', [EmployeeDocumentController::class, 'accept'])->name('employees.documents.accept');

        Route::get('/signed-documents/{employeeDocument}/download', [SignedDocumentController::class, 'download'])->name('signed-documents.download');
        Route::post('/signed-documents/{employeeDocument}/regenerate', [SignedDocumentController::class, 'regenerate'])->name('signed-documents.regenerate');

        Route::get('/employees/{employee}/locker', [EmployeeLockerController::class, 'show'])->name('employees.locker.show');
        Route::post('/employees/{employee}/locker/regenerate', [EmployeeLockerController::class, 'regenerate'])->name('employees.locker.regenerate');
    });

    // Creating HR / Accounts / Outlet Manager logins from the browser.
    Route::middleware('permission:user.manage')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::post('/users/{user}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');
    });

    Route::middleware('permission:audit-log.view')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
        Route::get('/audit-log/export', [AuditLogController::class, 'export'])->name('audit-log.export');
    });
});

/*
|--------------------------------------------------------------------------
| Staff document locker — unauthenticated, token-gated
|--------------------------------------------------------------------------
| Reached by scanning a QR code or opening a WhatsApp link. No staff login
| exists in this phase (§3.6.2) — the token IS the access control. Kept
| outside the `auth`/`2fa.verified` groups deliberately, and rate-limited
| per §6 ("rate-limited, expiring, revocable").
*/
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/d/{token}', [EmployeeLockerPublicController::class, 'show'])->name('locker.show');
    Route::get('/d/{token}/documents/{employeeDocument}', [EmployeeLockerPublicController::class, 'document'])->name('locker.document');
    Route::get('/d/{token}/documents/{employeeDocument}/verify', [EmployeeLockerPublicController::class, 'verify'])->name('locker.verify');
});

<?php

use App\Http\Controllers\Web\Admin\UserManagement\UserController;
use App\Http\Controllers\Web\Admin\UserManagement\RoleController;
use App\Http\Controllers\Web\Admin\UserManagement\PermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Management Routes
|--------------------------------------------------------------------------
|
| All routes require 'admin.auth' middleware (applied in bootstrap/app.php).
| Additionally, access is gated by 'manage users' permission or super-admin
| role via the permission middleware.
|
*/

// ── Users ────────────────────────────────────────────────────────────────
Route::prefix('admin/users')->name('admin.users.')->middleware('permission:manage users')->group(function () {
    Route::get('/',             [UserController::class, 'index'])->name('index');
    Route::get('/getdata',      [UserController::class, 'getData'])->name('getdata');
    Route::get('/create',       [UserController::class, 'create'])->name('create');
    Route::post('/',            [UserController::class, 'store'])->name('store');
    Route::get('/{user}',       [UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit',  [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}',       [UserController::class, 'update'])->name('update');
    Route::delete('/{user}',    [UserController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/toggle',  [UserController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{user}/roles',   [UserController::class, 'assignRoles'])->name('assign-roles');
});

// ── Roles ────────────────────────────────────────────────────────────────
Route::prefix('admin/roles')->name('admin.roles.')->middleware('permission:manage roles|manage users')->group(function () {
    Route::get('/',                      [RoleController::class, 'index'])->name('index');
    Route::get('/getdata',               [RoleController::class, 'getData'])->name('getdata');
    Route::get('/create',                [RoleController::class, 'create'])->name('create');
    Route::post('/',                     [RoleController::class, 'store'])->name('store');
    Route::get('/{role}/edit',           [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}',                [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}',             [RoleController::class, 'destroy'])->name('destroy');
    Route::post('/{role}/permissions',   [RoleController::class, 'syncPermissions'])->name('sync-permissions');
});

// ── Permissions ──────────────────────────────────────────────────────────
Route::prefix('admin/permissions')->name('admin.permissions.')->middleware('permission:manage permissions|manage users')->group(function () {
    Route::get('/',             [PermissionController::class, 'index'])->name('index');
    Route::get('/getdata',      [PermissionController::class, 'getData'])->name('getdata');
    Route::get('/create',       [PermissionController::class, 'create'])->name('create');
    Route::post('/',            [PermissionController::class, 'store'])->name('store');
    Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
    Route::put('/{permission}',      [PermissionController::class, 'update'])->name('update');
    Route::delete('/{permission}',   [PermissionController::class, 'destroy'])->name('destroy');
});

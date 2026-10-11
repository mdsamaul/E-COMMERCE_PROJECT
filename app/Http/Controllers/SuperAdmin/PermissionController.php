<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions grouped by module.
     */
    public function index(): View
    {
        $permissions = Permission::with('roles')->get()->groupBy('module');

        return view('superadmin.permissions.index', compact('permissions'));
    }
}

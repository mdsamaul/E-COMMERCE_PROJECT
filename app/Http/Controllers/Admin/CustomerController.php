<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::customers()
            ->withCount('orders')
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total_amount')
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $customer->load(['addresses', 'orders' => function ($q) {
            $q->latest()->take(10);
        }]);

        $totalSpent = (float) $customer->orders()->where('payment_status', 'paid')->sum('total_amount');
        $ordersCount = $customer->orders()->count();

        return view('admin.customers.show', compact('customer', 'totalSpent', 'ordersCount'));
    }

    public function toggleStatus(User $customer): RedirectResponse
    {
        if ($customer->role !== 'customer') {
            abort(403, 'Unauthorized action on staff accounts.');
        }

        $customer->update(['is_active' => !$customer->is_active]);

        $statusText = $customer->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Customer [{$customer->name}] has been {$statusText} successfully.");
    }
}

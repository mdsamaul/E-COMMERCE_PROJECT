<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $query = Order::with('user')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Summary metrics
        $paidQuery = clone $query;
        $totalSales = (float) $paidQuery->where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = (clone $query)->count();
        $deliveredOrders = (clone $query)->where('order_status', 'delivered')->count();
        $totalDiscount = (float) (clone $query)->sum('discount_amount');
        $averageOrderValue = $totalOrders > 0 ? round($totalSales / max(1, (clone $query)->where('payment_status', 'paid')->count()), 2) : 0;

        // Payment method breakdown
        $paymentBreakdown = (clone $query)
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Top selling products in this period
        $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->select('product_name', 'product_sku as sku', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(subtotal) as total_sales'))
        ->groupBy('product_name', 'product_sku')
        ->orderByDesc('total_qty')
        ->take(5)
        ->get();

        $orders = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.reports.sales', compact(
            'startDate',
            'endDate',
            'totalSales',
            'totalOrders',
            'deliveredOrders',
            'totalDiscount',
            'averageOrderValue',
            'paymentBreakdown',
            'topProducts',
            'orders'
        ));
    }
}

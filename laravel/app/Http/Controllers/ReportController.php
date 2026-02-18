<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * @return array<string, mixed>
     */
    public function salesReport(): array
    {
        $today = Carbon::today();

        $salesToday = Order::whereDate('created_at', $today)
            ->where('estado', 'cerrada')
            ->sum('total');

        $salesByDay = Order::selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->where('estado', 'cerrada')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->limit(7)
            ->get();

        return [
            'sales_today' => $salesToday,
            'sales_by_day' => $salesByDay,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Product>
     */
    public function lowStockReport()
    {
        return Product::whereColumn('stock_actual', '<', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get();
    }

    public function dashboard(): View
    {
        $salesData = $this->salesReport();
        $lowStockProducts = $this->lowStockReport();

        return view('reports.dashboard', [
            'salesToday' => $salesData['sales_today'],
            'salesByDay' => $salesData['sales_by_day'],
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    public function payments(Request $request): View
    {
        $query = Payment::query()
            ->with(['order.table', 'order.customer', 'paymentMethod', 'currency']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        if ($request->filled('customer_id')) {
            $query->whereHas('order', function ($orderQuery) use ($request): void {
                $orderQuery->where('customer_id', $request->integer('customer_id'));
            });
        }

        if ($request->filled('sale_type')) {
            $saleType = $request->string('sale_type')->toString();

            if ($saleType === 'directa') {
                $query->whereHas('order', function ($orderQuery): void {
                    $orderQuery->where('tipo', 'directa')
                        ->orWhereNull('table_id');
                });
            }

            if ($saleType === 'mesa') {
                $query->whereHas('order', function ($orderQuery): void {
                    $orderQuery->whereNotNull('table_id');
                });
            }
        }

        if ($request->filled('table_id') && $request->string('sale_type')->toString() === 'mesa') {
            $query->whereHas('order', function ($orderQuery) use ($request): void {
                $orderQuery->where('table_id', $request->integer('table_id'));
            });
        }

        $paymentMethodIds = $request->input('payment_method_ids', []);
        if (is_array($paymentMethodIds) && count($paymentMethodIds) > 0) {
            $query->whereIn('payment_method_id', $paymentMethodIds);
        }

        $totalAmount = (clone $query)->sum('monto');

        $payments = $query
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $paymentMethods = PaymentMethod::orderBy('nombre')->get();
        $customers = Customer::orderBy('nombre')->get();
        $tables = Table::orderBy('numero')->get();

        return view('reports.payments', compact('payments', 'paymentMethods', 'customers', 'tables', 'totalAmount'));
    }
}

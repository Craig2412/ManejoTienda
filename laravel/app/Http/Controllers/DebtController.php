<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class DebtController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::where('tipo', 'deuda')
            ->with(['customer', 'payments']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        if ($request->filled('status')) {
            $query->where('estado', $request->string('status'));
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $customers = Customer::orderBy('nombre')->get();

        return view('debts.index', compact('orders', 'customers'));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('nombre')->get();

        return view('debts.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::create([
            'customer_id' => $validated['customer_id'],
            'table_id' => null,
            'tipo' => 'deuda',
            'estado' => 'abierta',
            'total' => 0,
            'tip_amount' => 0,
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('debts.show', $order)
            ->with('status', 'Cuenta por cobrar creada.');
    }

    public function show(Order $order): View
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        $order->load(['items.product', 'customer', 'payments.paymentMethod', 'payments.currency']);
        $products = Product::orderBy('nombre')->get();

        $totalPaid = $order->payments->sum('monto');
        $remaining = max(0, (float) $order->total - (float) $totalPaid);

        return view('debts.show', compact('order', 'products', 'totalPaid', 'remaining'));
    }

    public function updateInfo(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('debts.show', $order)
                ->withErrors(['estado' => 'La cuenta ya esta cerrada.']);
        }

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('debts.show', $order)
            ->with('status', 'Datos de la cuenta actualizados.');
    }

    public function storeItem(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('debts.show', $order)
                ->withErrors(['estado' => 'La cuenta ya esta cerrada.']);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = (int) $validated['cantidad'];

        try {
            DB::transaction(function () use ($order, $product, $quantity): void {
                $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

                if ($quantity > $lockedProduct->stock_actual) {
                    throw new RuntimeException('Stock insuficiente para este producto.');
                }

                $item = $order->items()->where('product_id', $lockedProduct->id)->lockForUpdate()->first();
                $existingQty = $item?->cantidad ?? 0;
                $newQty = $existingQty + $quantity;

                $lockedProduct->stock_actual -= $quantity;
                $lockedProduct->save();

                if ($item) {
                    $item->cantidad = $newQty;
                    $item->precio_unitario = $lockedProduct->precio;
                    $item->subtotal = $newQty * $item->precio_unitario;
                    $item->save();
                } else {
                    $order->items()->create([
                        'product_id' => $lockedProduct->id,
                        'cantidad' => $quantity,
                        'precio_unitario' => $lockedProduct->precio,
                        'subtotal' => $quantity * $lockedProduct->precio,
                    ]);
                }

                $order->total = $order->items()->sum('subtotal');
                $order->save();
            });
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('debts.show', $order)
                ->withErrors(['stock' => $exception->getMessage()]);
        }

        return redirect()
            ->route('debts.show', $order)
            ->with('status', 'Producto agregado a la cuenta.');
    }

    public function destroyItem(Order $order, OrderItem $orderItem): RedirectResponse
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('debts.show', $order)
                ->withErrors(['estado' => 'La cuenta ya esta cerrada.']);
        }

        if ($orderItem->order_id !== $order->id) {
            return redirect()
                ->route('debts.show', $order)
                ->withErrors(['stock' => 'No se pudo eliminar el producto.']);
        }

        DB::transaction(function () use ($order, $orderItem): void {
            $product = Product::whereKey($orderItem->product_id)->lockForUpdate()->first();

            if ($product) {
                $product->stock_actual += $orderItem->cantidad;
                $product->save();
            }

            $orderItem->delete();
            $order->total = $order->items()->sum('subtotal');
            $order->save();
        });

        return redirect()
            ->route('debts.show', $order)
            ->with('status', 'Producto eliminado de la cuenta.');
    }

    public function payments(Order $order): View
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        $order->load(['payments.paymentMethod', 'payments.currency', 'customer']);
        $paymentMethods = PaymentMethod::where('activo', true)->orderBy('nombre')->get();
        $currencies = Currency::where('activo', true)->orderBy('nombre')->get();

        $totalPaid = $order->payments()->sum('monto');
        $remaining = max(0, (float) $order->total - (float) $totalPaid);

        return view('debts.payments', compact('order', 'paymentMethods', 'currencies', 'totalPaid', 'remaining'));
    }

    public function storePayment(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('debts.payments', $order)
                ->withErrors(['estado' => 'La cuenta ya esta cerrada.']);
        }

        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ]);

        $method = PaymentMethod::whereKey($validated['payment_method_id'])
            ->where('activo', true)
            ->first();
        $currency = Currency::whereKey($validated['currency_id'])
            ->where('activo', true)
            ->first();

        if (! $method || ! $currency) {
            return redirect()->back()->withErrors(['pago' => 'Metodo o moneda no disponible.']);
        }

        if ($method->requiere_referencia && empty($validated['referencia'])) {
            return redirect()->back()->withErrors(['referencia' => 'Referencia requerida para este metodo.']);
        }

        try {
            DB::transaction(function () use ($order, $validated): void {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'currency_id' => $validated['currency_id'],
                    'monto' => $validated['monto'],
                    'referencia' => $validated['referencia'] ?? null,
                ]);

                $totalPaid = $order->payments()->sum('monto');

                if ($totalPaid >= (float) $order->total) {
                    $order->update(['estado' => 'cerrada']);
                }
            });
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('debts.payments', $order)
                ->withErrors(['pago' => $exception->getMessage()]);
        }

        $order->refresh();

        return redirect()
            ->route('debts.payments', $order)
            ->with('status', 'Pago registrado.');
    }

    public function destroyPayment(Order $order, Payment $payment): RedirectResponse
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('debts.payments', $order)
                ->withErrors(['estado' => 'La cuenta ya esta cerrada.']);
        }

        if ($payment->order_id !== $order->id) {
            return redirect()
                ->route('debts.payments', $order)
                ->withErrors(['pago' => 'No se pudo eliminar el pago.']);
        }

        $payment->delete();

        return redirect()
            ->route('debts.payments', $order)
            ->with('status', 'Pago eliminado.');
    }

    public function close(Order $order): RedirectResponse
    {
        if ($order->tipo !== 'deuda') {
            abort(404);
        }

        $totalPaid = $order->payments()->sum('monto');

        if ($totalPaid < (float) $order->total) {
            return redirect()
                ->route('debts.payments', $order)
                ->withErrors(['pago' => 'El saldo debe quedar en cero para cerrar la cuenta.']);
        }

        $order->update(['estado' => 'cerrada']);

        return redirect()
            ->route('debts.index')
            ->with('status', 'Cuenta cerrada.');
    }
}

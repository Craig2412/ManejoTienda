<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class DirectSaleController extends Controller
{
    public function index(): View
    {
        $orders = Order::where('tipo', 'directa')
            ->where('estado', 'abierta')
            ->orderByDesc('created_at')
            ->get();

        return view('direct_sales.index', compact('orders'));
    }

    public function paid(Request $request): View
    {
        $query = Order::where('tipo', 'directa')
            ->where('estado', 'cerrada')
            ->with(['customer', 'payments.paymentMethod']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $customers = Customer::orderBy('nombre')->get();

        return view('direct_sales.paid', compact('orders', 'customers'));
    }

    public function create(): RedirectResponse
    {
        $order = Order::create([
            'table_id' => null,
            'tipo' => 'directa',
            'estado' => 'abierta',
            'total' => 0,
            'tip_amount' => 0,
        ]);

        return redirect()->route('direct-sales.show', $order);
    }

    public function show(Order $order): View
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        $order->load(['items.product', 'customer']);
        $products = Product::orderBy('nombre')->get();
        $customers = Customer::orderBy('nombre')->get();

        return view('direct_sales.show', compact('order', 'products', 'customers'));
    }

    public function assignCustomer(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.show', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_identification' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['customer_id'])) {
            $order->update(['customer_id' => $validated['customer_id']]);

            return redirect()
                ->route('direct-sales.show', $order)
                ->with('status', 'Cliente asignado a la venta.');
        }

        if (! empty($validated['customer_name']) && ! empty($validated['customer_identification'])) {
            $customer = Customer::updateOrCreate(
                ['identificacion' => $validated['customer_identification']],
                ['nombre' => $validated['customer_name']]
            );

            $order->update(['customer_id' => $customer->id]);

            return redirect()
                ->route('direct-sales.show', $order)
                ->with('status', 'Cliente creado y asignado a la venta.');
        }

        return redirect()
            ->route('direct-sales.show', $order)
            ->withErrors(['customer_name' => 'Selecciona un cliente o registra uno nuevo.']);
    }

    public function storeItem(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.show', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
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
                ->route('direct-sales.show', $order)
                ->withErrors(['stock' => $exception->getMessage()]);
        }

        return redirect()
            ->route('direct-sales.show', $order)
            ->with('status', 'Producto agregado a la venta.');
    }

    public function destroyItem(Order $order, OrderItem $orderItem): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.show', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
        }

        if ($orderItem->order_id !== $order->id) {
            return redirect()
                ->route('direct-sales.show', $order)
                ->withErrors(['stock' => 'No se pudo eliminar el producto de la venta.']);
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
            ->route('direct-sales.show', $order)
            ->with('status', 'Producto eliminado de la venta.');
    }

    public function updateTip(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.show', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
        }

        $validated = $request->validate([
            'tip_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tip_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tipPercent = isset($validated['tip_percent']) && $validated['tip_percent'] !== ''
            ? (float) $validated['tip_percent']
            : null;
        $tipAmount = isset($validated['tip_amount']) && $validated['tip_amount'] !== ''
            ? (float) $validated['tip_amount']
            : 0.0;

        if ($tipPercent !== null && $tipPercent > 0) {
            $tipAmount = round(((float) $order->total) * ($tipPercent / 100), 2);
        } else {
            $tipPercent = null;
        }

        $order->update([
            'tip_percent' => $tipPercent,
            'tip_amount' => $tipAmount,
        ]);

        return redirect()
            ->route('direct-sales.show', $order)
            ->with('status', 'Propina actualizada.');
    }

    public function checkout(Order $order): View|RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        $order->load(['items.product', 'payments.paymentMethod', 'payments.currency', 'customer']);

        if ($order->items->isEmpty() || (float) $order->total <= 0) {
            $order->update(['estado' => 'cerrada']);

            return redirect()
                ->route('direct-sales.index')
                ->with('status', 'Venta cerrada sin cobro.');
        }

        $paymentMethods = PaymentMethod::where('activo', true)->orderBy('nombre')->get();
        $currencies = Currency::where('activo', true)->orderBy('nombre')->get();

        $totalPaid = $order->payments()->sum('monto');
        $tipAmount = (float) ($order->tip_amount ?? 0);
        $totalDue = (float) $order->total + $tipAmount;
        $remaining = max(0, $totalDue - (float) $totalPaid);

        return view('direct_sales.checkout', compact('order', 'paymentMethods', 'currencies', 'totalPaid', 'tipAmount', 'totalDue', 'remaining'));
    }

    public function storePayment(Request $request, Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
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
                $tipAmount = (float) ($order->tip_amount ?? 0);
                $totalDue = (float) $order->total + $tipAmount;

                if ($totalPaid >= $totalDue) {
                    if (! $order->customer_id) {
                        throw new RuntimeException('Debe registrar los datos del cliente antes de cerrar la venta.');
                    }
                    $order->update(['estado' => 'cerrada']);
                }
            });
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => $exception->getMessage()]);
        }

        $order->refresh();

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.index')
                ->with('status', 'Pago completado y venta cerrada.');
        }

        return redirect()
            ->route('direct-sales.checkout', $order)
            ->with('status', 'Pago agregado.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        DB::transaction(function () use ($order): void {
            $items = $order->items()->get();

            foreach ($items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                if ($product) {
                    $product->stock_actual += $item->cantidad;
                    $product->save();
                }
            }

            $order->payments()->delete();
            $order->items()->delete();
            $order->delete();
        });

        return redirect()
            ->route('direct-sales.index')
            ->with('status', 'Venta eliminada.');
    }

    public function destroyPayment(Order $order, Payment $payment): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
        }

        if ($payment->order_id !== $order->id) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'No se pudo eliminar el pago.']);
        }

        $payment->delete();

        return redirect()
            ->route('direct-sales.checkout', $order)
            ->with('status', 'Pago eliminado.');
    }

    public function close(Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['estado' => 'La venta ya esta cerrada.']);
        }

        $totalPaid = $order->payments()->sum('monto');
        $tipAmount = (float) ($order->tip_amount ?? 0);
        $totalDue = (float) $order->total + $tipAmount;

        if ($totalPaid < $totalDue) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'El pago total debe cubrir el monto de la venta.']);
        }

        if (! $order->customer_id) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'Debe registrar los datos del cliente antes de cerrar la venta.']);
        }

        $order->update(['estado' => 'cerrada']);

        return redirect()
            ->route('direct-sales.index')
            ->with('status', 'Venta cerrada.');
    }

    public function convertToDebt(Order $order): RedirectResponse
    {
        if ($order->tipo !== 'directa') {
            abort(404);
        }

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'La venta ya esta cerrada.']);
        }

        if ($order->items()->count() === 0 || (float) $order->total <= 0) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'No hay productos para anexar a deuda.']);
        }

        if (! $order->customer_id) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'Debe asignar un cliente antes de anexar la deuda.']);
        }

        $totalPaid = $order->payments()->sum('monto');
        $tipAmount = (float) ($order->tip_amount ?? 0);
        $totalDue = (float) $order->total + $tipAmount;

        if ($totalPaid >= $totalDue) {
            return redirect()
                ->route('direct-sales.checkout', $order)
                ->withErrors(['pago' => 'La venta ya esta pagada.']);
        }

        $order->update([
            'tipo' => 'deuda',
            'estado' => 'abierta',
            'total' => $totalDue,
            'tip_percent' => null,
            'tip_amount' => 0,
        ]);

        return redirect()
            ->route('debts.show', $order)
            ->with('status', 'Venta anexada a cuentas por cobrar.');
    }
}

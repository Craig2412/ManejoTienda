<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Table;
use App\Models\Currency;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class PosController extends Controller
{
    public function index(): View
    {
        $tables = Table::orderBy('numero')->get();

        return view('pos.index', compact('tables'));
    }

    public function show(Table $table): View
    {
        $order = Order::firstOrCreate(
            ['table_id' => $table->id, 'estado' => 'abierta'],
            ['user_id' => null, 'total' => 0]
        );

        $order->load(['items.product', 'customer']);
        $products = Product::orderBy('nombre')->get();
        $customers = Customer::orderBy('nombre')->get();

        return view('pos.show', compact('table', 'order', 'products', 'customers'));
    }

    public function storeItem(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = (int) $validated['cantidad'];

        try {
            DB::transaction(function () use ($table, $product, $quantity): void {
                $order = Order::firstOrCreate(
                    ['table_id' => $table->id, 'estado' => 'abierta'],
                    ['user_id' => null, 'total' => 0]
                );

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

                $table->update(['estado' => 'ocupada']);
            });
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('pos.tables.show', $table)
                ->withErrors(['stock' => $exception->getMessage()]);
        }

        return redirect()
            ->route('pos.tables.show', $table)
            ->with('status', 'Producto agregado a la orden.');
    }

    public function occupy(Table $table): RedirectResponse
    {
        $table->update(['estado' => 'ocupada']);

        return redirect()
            ->route('pos.tables.show', $table)
            ->with('status', 'Mesa marcada como ocupada.');
    }

    public function assignCustomer(Request $request, Table $table): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->firstOrFail();

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_identification' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['customer_id'])) {
            $order->update(['customer_id' => $validated['customer_id']]);

            return redirect()
                ->route('pos.tables.show', $table)
                ->with('status', 'Cliente asignado a la mesa.');
        }

        if (! empty($validated['customer_name']) && ! empty($validated['customer_identification'])) {
            $customer = Customer::updateOrCreate(
                ['identificacion' => $validated['customer_identification']],
                ['nombre' => $validated['customer_name']]
            );

            $order->update(['customer_id' => $customer->id]);

            return redirect()
                ->route('pos.tables.show', $table)
                ->with('status', 'Cliente creado y asignado a la mesa.');
        }

        return redirect()
            ->route('pos.tables.show', $table)
            ->withErrors(['customer_name' => 'Selecciona un cliente o registra uno nuevo.']);
    }

    public function checkout(Table $table): View|RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->with(['items.product', 'payments.paymentMethod', 'payments.currency', 'customer'])
            ->firstOrFail();

        if ($order->items->isEmpty() || (float) $order->total <= 0) {
            $order->update(['estado' => 'cerrada']);
            $table->update(['estado' => 'disponible']);

            return redirect()
                ->route('pos.index')
                ->with('status', 'Mesa liberada sin cobro.');
        }

        $paymentMethods = PaymentMethod::where('activo', true)->orderBy('nombre')->get();
        $currencies = Currency::where('activo', true)->orderBy('nombre')->get();

        $totalPaid = $order->payments()->sum('monto');
        $tipAmount = (float) ($order->tip_amount ?? 0);
        $totalDue = (float) $order->total + $tipAmount;
        $remaining = max(0, $totalDue - (float) $totalPaid);

        return view('pos.checkout', compact('table', 'order', 'paymentMethods', 'currencies', 'totalPaid', 'tipAmount', 'totalDue', 'remaining'));
    }

    public function storePayment(Request $request, Table $table): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->with('payments')
            ->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_identification' => ['nullable', 'string', 'max:255'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ]);

        if (($validated['customer_name'] ?? null) || ($validated['customer_identification'] ?? null)) {
            if (empty($validated['customer_name']) || empty($validated['customer_identification'])) {
                return redirect()->back()->withErrors([
                    'customer_name' => 'Nombre e identificacion son obligatorios.',
                ]);
            }
        }

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
            DB::transaction(function () use ($order, $table, $validated): void {
                if (! empty($validated['customer_name']) && ! empty($validated['customer_identification'])) {
                    $customer = Customer::updateOrCreate(
                        ['identificacion' => $validated['customer_identification']],
                        ['nombre' => $validated['customer_name']]
                    );

                    $order->customer_id = $customer->id;
                    $order->save();
                }

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
                        throw new RuntimeException('Debe registrar los datos del cliente antes de cerrar la mesa.');
                    }
                    $order->update(['estado' => 'cerrada']);
                    $table->update(['estado' => 'disponible']);
                }
            });
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => $exception->getMessage()]);
        }

        $order->refresh();

        if ($order->estado === 'cerrada') {
            return redirect()
                ->route('pos.index')
                ->with('status', 'Pago completado y mesa liberada.');
        }

        return redirect()
            ->route('pos.tables.checkout', $table)
            ->with('status', 'Pago agregado.');
    }

    public function destroyPayment(Table $table, Payment $payment): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->first();

        if (! $order || $payment->order_id !== $order->id) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => 'No se pudo eliminar el pago.']);
        }

        $payment->delete();

        return redirect()
            ->route('pos.tables.checkout', $table)
            ->with('status', 'Pago eliminado.');
    }

    public function closeWithPayment(Request $request, Table $table): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->with('payments')
            ->firstOrFail();

        if ($order->items()->count() === 0 || (float) $order->total <= 0) {
            $order->update(['estado' => 'cerrada']);
            $table->update(['estado' => 'disponible']);

            return redirect()
                ->route('pos.index')
                ->with('status', 'Mesa liberada sin cobro.');
        }

        $totalPaid = $order->payments->sum('monto');
        $tipAmount = (float) ($order->tip_amount ?? 0);
        $totalDue = (float) $order->total + $tipAmount;

        if ($totalPaid < $totalDue) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => 'El pago total debe cubrir el monto de la orden.']);
        }

        if (! $order->customer_id) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => 'Debe registrar los datos del cliente antes de cerrar la mesa.']);
        }

        DB::transaction(function () use ($order, $table): void {
            $order->update(['estado' => 'cerrada']);
            $table->update(['estado' => 'disponible']);
        });

        return redirect()
            ->route('pos.index')
            ->with('status', 'Mesa liberada.');
    }

    public function convertToDebt(Table $table): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->with('payments')
            ->firstOrFail();

        if ($order->items()->count() === 0 || (float) $order->total <= 0) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => 'No hay productos para anexar a deuda.']);
        }

        if (! $order->customer_id) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => 'Debe asignar un cliente antes de anexar la deuda.']);
        }

        $totalPaid = $order->payments->sum('monto');
        $tipAmount = (float) ($order->tip_amount ?? 0);
        $totalDue = (float) $order->total + $tipAmount;

        if ($totalPaid >= $totalDue) {
            return redirect()
                ->route('pos.tables.checkout', $table)
                ->withErrors(['pago' => 'La orden ya esta pagada.']);
        }

        DB::transaction(function () use ($order, $table, $totalDue): void {
            $order->update([
                'tipo' => 'deuda',
                'table_id' => null,
                'estado' => 'abierta',
                'total' => $totalDue,
                'tip_percent' => null,
                'tip_amount' => 0,
            ]);

            $table->update(['estado' => 'disponible']);
        });

        return redirect()
            ->route('debts.show', $order)
            ->with('status', 'Orden anexada a cuentas por cobrar.');
    }

    public function updateTip(Request $request, Table $table): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->firstOrFail();

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
            ->route('pos.tables.show', $table)
            ->with('status', 'Propina actualizada.');
    }

    public function destroyItem(Table $table, OrderItem $orderItem): RedirectResponse
    {
        $order = Order::where('table_id', $table->id)
            ->where('estado', 'abierta')
            ->first();

        if (! $order || $orderItem->order_id !== $order->id) {
            return redirect()
                ->route('pos.tables.show', $table)
                ->withErrors(['stock' => 'No se pudo eliminar el producto de la orden.']);
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
            ->route('pos.tables.show', $table)
            ->with('status', 'Producto eliminado de la orden.');
    }
}

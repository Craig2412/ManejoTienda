@extends('layouts.app')

@section('content')
    @php
        $isClosed = $order->estado === 'cerrada';
    @endphp
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Cobro venta directa #{{ $order->id }}</h1>
                <p class="text-sm text-slate-500">Selecciona el metodo y la moneda para cerrar la venta.</p>
            </div>
            <a href="{{ route('direct-sales.show', $order) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                Volver
            </a>
        </div>

        @if ($errors->has('pago'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('pago') }}
            </div>
        @endif

        @if ($errors->has('estado'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ $errors->first('estado') }}
            </div>
        @endif

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($isClosed)
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                Esta venta ya esta cerrada. Solo puedes consultar la informacion.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold">Pagos registrados</h2>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Metodo</th>
                                <th class="px-4 py-3 text-left">Moneda</th>
                                <th class="px-4 py-3 text-right">Monto</th>
                                <th class="px-4 py-3 text-right">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($order->payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $payment->paymentMethod?->nombre }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $payment->currency?->codigo }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($payment->monto, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if (! $isClosed)
                                            <form method="POST" action="{{ route('direct-sales.payments.destroy', [$order, $payment]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                    Quitar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">Aun no hay pagos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Pago</h2>
                    @if (! $isClosed)
                        <form method="POST" action="{{ route('direct-sales.payments.store', $order) }}" class="mt-4 space-y-4">
                            @csrf
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Cliente</span>
                                    <span class="font-semibold text-slate-800">{{ $order->customer?->nombre ?? 'Sin asignar' }}</span>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-slate-500">Identificacion</span>
                                    <span class="font-semibold text-slate-800">{{ $order->customer?->identificacion ?? '-' }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Metodo de pago</label>
                                <select name="payment_method_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                    <option value="">Selecciona un metodo</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}" @selected(old('payment_method_id') == $method->id)>
                                            {{ $method->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Moneda</label>
                                <select name="currency_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                    <option value="">Selecciona una moneda</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}" @selected(old('currency_id') == $currency->id)>
                                            {{ $currency->nombre }} ({{ $currency->codigo }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Monto</label>
                                <input type="number" step="0.01" name="monto" value="{{ old('monto', $remaining) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                @error('monto')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Referencia (si aplica)</label>
                                <input type="text" name="referencia" value="{{ old('referencia') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                @error('referencia')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="text-sm text-slate-500">
                                    Subtotal: <span class="font-semibold text-slate-800">${{ number_format($order->total, 2) }}</span>
                                    | Propina: <span class="font-semibold text-slate-700">${{ number_format($tipAmount, 2) }}</span>
                                    | Total: <span class="font-semibold text-slate-800">${{ number_format($totalDue, 2) }}</span>
                                    | Pagado: <span class="font-semibold text-emerald-700">${{ number_format($totalPaid, 2) }}</span>
                                    | Pendiente: <span class="font-semibold text-red-600">${{ number_format($remaining, 2) }}</span>
                                </div>
                                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Agregar pago
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Cliente</span>
                                <span class="font-semibold text-slate-800">{{ $order->customer?->nombre ?? 'Sin asignar' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-slate-500">Identificacion</span>
                                <span class="font-semibold text-slate-800">{{ $order->customer?->identificacion ?? '-' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if (! $isClosed)
                    <form method="POST" action="{{ route('direct-sales.close', $order) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" @disabled($remaining > 0)>
                            Finalizar pago y cerrar venta
                        </button>
                        @if ($remaining > 0)
                            <p class="mt-2 text-xs text-slate-500">Debes completar el pago total para cerrar la venta.</p>
                        @endif
                    </form>

                    @if ($remaining > 0)
                        <form method="POST" action="{{ route('direct-sales.debt', $order) }}" onsubmit="return confirm('Deseas anexar esta venta a cuentas por cobrar?');">
                            @csrf
                            <button type="submit" class="mt-2 w-full rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                Anexar deuda
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    @php
        $isClosed = $order->estado === 'cerrada';
    @endphp
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Cuenta por cobrar #{{ $order->id }}</h1>
                <p class="text-sm text-slate-500">Gestiona los productos y el saldo.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('debts.payments', $order) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Ver pagos
                </a>
                <a href="{{ route('debts.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Volver
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('stock'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('stock') }}
            </div>
        @endif

        @if ($errors->has('estado'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ $errors->first('estado') }}
            </div>
        @endif

        @if ($isClosed)
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                Esta cuenta ya esta cerrada. Solo puedes consultar la informacion.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Cliente</h2>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nombre</span>
                            <span class="font-semibold text-slate-800">{{ $order->customer?->nombre ?? 'Sin cliente' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Identificacion</span>
                            <span class="font-semibold text-slate-800">{{ $order->customer?->identificacion ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Datos de la cuenta</h2>
                    @if (! $isClosed)
                        <form method="POST" action="{{ route('debts.update-info', $order) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Fecha limite</label>
                                <input type="date" name="due_date" value="{{ old('due_date', optional($order->due_date)->format('Y-m-d')) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm font-semibold text-slate-700">Notas</label>
                                <textarea name="notes" rows="3" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">{{ old('notes', $order->notes) }}</textarea>
                            </div>
                            <div class="sm:col-span-2 flex items-center justify-end">
                                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Guardar
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Fecha limite</span>
                                <span class="font-semibold text-slate-800">{{ $order->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Notas</span>
                                <p class="mt-1 text-slate-700">{{ $order->notes ?: 'Sin notas' }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Productos</h2>
                    <p class="text-sm text-slate-500">Agrega productos a la cuenta.</p>

                    @if (! $isClosed)
                        <form action="{{ route('debts.items.store', $order) }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-3">
                            @csrf
                            <div class="sm:col-span-2">
                                <label class="text-sm font-semibold text-slate-700">Producto</label>
                                <select name="product_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                    <option value="">Selecciona un producto</option>
                                    @foreach ($products as $product)
                                        @php
                                            $isOutOfStock = $product->stock_actual <= 0;
                                        @endphp
                                        <option value="{{ $product->id }}" @disabled($isOutOfStock)>
                                            {{ $product->nombre }} - ${{ number_format($product->precio, 2) }} (Stock: {{ $product->stock_actual }}){{ $isOutOfStock ? ' (No disponible)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Cantidad</label>
                                <input type="number" name="cantidad" value="1" min="1" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                @error('cantidad')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-3">
                                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Agregar producto
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold">Detalle de la cuenta</h2>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-right">Cantidad</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-right">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $item->product?->nombre }}</td>
                                    <td class="px-4 py-3 text-right">{{ $item->cantidad }}</td>
                                    <td class="px-4 py-3 text-right">${{ number_format($item->precio_unitario, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">${{ number_format($item->subtotal, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if (! $isClosed)
                                            <form method="POST" action="{{ route('debts.items.destroy', [$order, $item]) }}">
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
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay productos en la cuenta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Resumen</h2>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Estado</span>
                            <span class="font-semibold text-slate-700">{{ ucfirst($order->estado) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Total</span>
                            <span class="text-lg font-semibold text-emerald-600">${{ number_format($order->total, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Pagado</span>
                            <span class="font-semibold text-slate-700">${{ number_format($totalPaid, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Saldo</span>
                            <span class="font-semibold text-emerald-700">${{ number_format($remaining, 2) }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('debts.payments', $order) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Gestionar pagos
                </a>
            </div>
        </div>
    </div>
@endsection

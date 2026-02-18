@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Mesa {{ $table->numero }}</h1>
                <p class="text-sm text-slate-500">Gestiona la orden activa de la mesa.</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($table->estado !== 'ocupada')
                    <form method="POST" action="{{ route('pos.tables.occupy', $table) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50">
                            Marcar ocupada
                        </button>
                    </form>
                @endif
                <a href="{{ route('pos.tables.checkout', $table) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Liberar mesa
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

        @if ($errors->has('customer_name'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('customer_name') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Cliente</h2>
                    @if ($order->customer)
                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Nombre</span>
                                <span class="font-semibold text-slate-800">{{ $order->customer->nombre }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Identificacion</span>
                                <span class="font-semibold text-slate-800">{{ $order->customer->identificacion }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Asigna un cliente registrado o crea uno nuevo.</p>
                        <form method="POST" action="{{ route('pos.tables.customer.assign', $table) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                            @csrf
                            <div class="sm:col-span-2">
                                <label class="text-sm font-semibold text-slate-700">Cliente registrado</label>
                                <select name="customer_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                    <option value="">Selecciona un cliente</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected($order->customer_id == $customer->id)>
                                            {{ $customer->nombre }} - {{ $customer->identificacion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Nombre nuevo</label>
                                <input type="text" name="customer_name" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none" placeholder="Nombre del cliente">
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Identificacion nueva</label>
                                <input type="text" name="customer_identification" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none" placeholder="Numero de identificacion">
                            </div>

                            <div class="sm:col-span-2 flex items-center justify-between">
                                <div class="text-sm text-slate-500">
                                    Cliente actual: <span class="font-semibold text-slate-800">Sin asignar</span>
                                </div>
                                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Guardar cliente
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Productos</h2>
                    <p class="text-sm text-slate-500">Agrega productos a la orden.</p>

                    <form action="{{ route('pos.tables.items.store', $table) }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-3">
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
                                Agregar a la orden
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold">Detalle de la orden</h2>
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
                                        <form method="POST" action="{{ route('pos.tables.items.destroy', [$table, $item]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                Quitar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aun no hay productos en la orden.</td>
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
                            <span class="text-slate-500">Propina</span>
                            <span class="font-semibold text-slate-700">${{ number_format($order->tip_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Total con propina</span>
                            <span class="font-semibold text-slate-800">${{ number_format(($order->total + ($order->tip_amount ?? 0)), 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Propina</h2>
                    <form method="POST" action="{{ route('pos.tables.tip.update', $table) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Porcentaje (%)</label>
                            <input type="number" step="0.01" name="tip_percent" value="{{ old('tip_percent', $order->tip_percent) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            @error('tip_percent')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Monto fijo</label>
                            <input type="number" step="0.01" name="tip_amount" value="{{ old('tip_amount', $order->tip_amount) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            @error('tip_amount')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">Si defines porcentaje, el monto fijo se ignora.</p>
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Guardar propina
                        </button>
                    </form>
                </div>

                <a href="{{ route('pos.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Volver al tablero
                </a>
            </div>
        </div>
    </div>
@endsection

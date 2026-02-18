@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Productos</h1>
                <p class="text-sm text-slate-500">Gestiona el inventario y el stock disponible.</p>
            </div>
            <a href="{{ route('inventory.products.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Nuevo producto
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Producto</th>
                        <th class="px-4 py-3 text-left">Categoria</th>
                        <th class="px-4 py-3 text-right">Precio</th>
                        <th class="px-4 py-3 text-right">Stock actual</th>
                        <th class="px-4 py-3 text-right">Stock minimo</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        @php
                            $lowStock = $product->stock_actual < $product->stock_minimo;
                        @endphp
                        <tr class="{{ $lowStock ? 'bg-red-50' : 'bg-white' }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $product->nombre }}</div>
                                <div class="text-xs text-slate-500">{{ $product->descripcion ?? 'Sin descripcion' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $product->category?->nombre }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ number_format($product->precio, 2) }}</td>
                            <td class="px-4 py-3 text-right {{ $lowStock ? 'font-semibold text-red-700' : 'text-slate-700' }}">
                                {{ $product->stock_actual }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600">{{ $product->stock_minimo }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $product->estado === 'activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ ucfirst($product->estado) }}
                                </span>
                                @if ($lowStock)
                                    <span class="ml-2 inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Bajo stock</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inventory.products.edit', $product) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Editar
                                    </a>
                                    <form action="{{ route('inventory.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Eliminar producto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                Aun no hay productos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

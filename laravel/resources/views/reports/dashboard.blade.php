@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard de Reportes</h1>
            <p class="text-sm text-slate-500">Resumen de ventas y alertas de inventario.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">Ventas de hoy</div>
                <div class="mt-2 text-2xl font-semibold text-emerald-600">${{ number_format($salesToday, 2) }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">Dias con ventas</div>
                <div class="mt-2 text-2xl font-semibold text-slate-800">{{ $salesByDay->count() }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">Productos en bajo stock</div>
                <div class="mt-2 text-2xl font-semibold text-red-600">{{ $lowStockProducts->count() }}</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">Ventas por dia</h2>
                </div>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Fecha</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($salesByDay as $row)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $row->fecha }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">${{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-500">Aun no hay ventas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">Reabastecimiento urgente</h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($lowStockProducts as $product)
                        <li class="px-6 py-4 text-sm">
                            <div class="font-semibold text-slate-800">{{ $product->nombre }}</div>
                            <div class="text-xs text-slate-500">Stock: {{ $product->stock_actual }} / Minimo: {{ $product->stock_minimo }}</div>
                        </li>
                    @empty
                        <li class="px-6 py-6 text-sm text-slate-500">No hay productos en bajo stock.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

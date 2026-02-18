@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Ventas directas pagadas</h1>
                <p class="text-sm text-slate-500">Consulta de ventas cerradas.</p>
            </div>
            <a href="{{ route('direct-sales.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                Volver
            </a>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Cliente</label>
                    <select name="customer_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="">Todos</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                {{ $customer->nombre }} - {{ $customer->identificacion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('direct-sales.paid') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Ventas cerradas</h2>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Venta</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Metodos</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">#{{ $order->id }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->customer?->nombre ?? 'Sin cliente' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $order->payments->pluck('paymentMethod.nombre')->filter()->unique()->implode(', ') ?: 'Sin pagos' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <span class="font-semibold text-emerald-700">
                                        ${{ number_format(($order->total + ($order->tip_amount ?? 0)), 2) }}
                                    </span>
                                    <a href="{{ route('direct-sales.show', $order) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                        Ver venta
                                    </a>
                                    <a href="{{ route('direct-sales.checkout', $order) }}" class="rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                        Ver pago
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay ventas pagadas con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $orders->links() }}
        </div>
    </div>
@endsection

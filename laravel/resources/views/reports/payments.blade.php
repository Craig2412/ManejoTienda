@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Consulta de pagos</h1>
            <p class="text-sm text-slate-500">Filtra pagos por fecha, cliente, metodo, mesa o venta directa.</p>
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
                <div>
                    <label class="text-sm font-semibold text-slate-700">Metodos de pago</label>
                    <select name="payment_method_ids[]" multiple class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}" @selected(collect(request('payment_method_ids'))->contains($method->id))>
                                {{ $method->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Tipo de venta</label>
                    <select name="sale_type" data-sale-type class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="">Todas</option>
                        <option value="mesa" @selected(request('sale_type') === 'mesa')>Mesa</option>
                        <option value="directa" @selected(request('sale_type') === 'directa')>Venta directa</option>
                    </select>
                </div>
                <div data-table-filter>
                    <label class="text-sm font-semibold text-slate-700">Mesa</label>
                    <select name="table_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="">Todas</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table->id }}" @selected(request('table_id') == $table->id)>
                                Mesa {{ $table->numero }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('reports.payments') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                        Limpiar
                    </a>
                    <div class="text-sm text-slate-500">
                        Total filtrado: <span class="font-semibold text-slate-800">${{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Pagos registrados</h2>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Origen</th>
                        <th class="px-4 py-3 text-left">Metodo</th>
                        <th class="px-4 py-3 text-left">Moneda</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                        <th class="px-4 py-3 text-left">Referencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $payment->order?->customer?->nombre ?? 'Sin cliente' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if ($payment->order?->table)
                                    Mesa {{ $payment->order->table->numero }}
                                @else
                                    Venta directa
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->paymentMethod?->nombre }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->currency?->codigo }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ number_format($payment->monto, 2) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $payment->referencia ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay pagos con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $payments->links() }}
        </div>
    </div>
@endsection

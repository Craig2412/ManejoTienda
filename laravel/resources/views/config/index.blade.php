@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Configuracion</h1>
            <p class="text-sm text-slate-500">Administra metodos de pago y monedas disponibles.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Metodos de pago</h2>
                    <form method="POST" action="{{ route('config.payment-methods.store') }}" class="mt-4 grid gap-3">
                        @csrf
                        <input type="text" name="nombre" placeholder="Nuevo metodo" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="requiere_referencia" value="1" class="rounded border-slate-300">
                            Requiere referencia
                        </label>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Agregar metodo
                        </button>
                    </form>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Metodo</th>
                                <th class="px-4 py-3 text-left">Referencia</th>
                                <th class="px-4 py-3 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($paymentMethods as $method)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $method->nombre }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $method->requiere_referencia ? 'Si' : 'No' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('config.payment-methods.toggle', $method) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $method->activo ? 'border-emerald-200 text-emerald-600' : 'border-slate-200 text-slate-500' }}">
                                                {{ $method->activo ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Monedas</h2>
                    <form method="POST" action="{{ route('config.currencies.store') }}" class="mt-4 grid gap-3">
                        @csrf
                        <input type="text" name="nombre" placeholder="Nombre" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <input type="text" name="codigo" placeholder="Codigo" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <input type="text" name="simbolo" placeholder="Simbolo" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Agregar moneda
                        </button>
                    </form>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Moneda</th>
                                <th class="px-4 py-3 text-left">Codigo</th>
                                <th class="px-4 py-3 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($currencies as $currency)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $currency->nombre }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $currency->codigo }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('config.currencies.toggle', $currency) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $currency->activo ? 'border-emerald-200 text-emerald-600' : 'border-slate-200 text-slate-500' }}">
                                                {{ $currency->activo ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

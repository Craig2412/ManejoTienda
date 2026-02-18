@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Venta directa</h1>
                <p class="text-sm text-slate-500">Ventas para llevar sin mesa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('direct-sales.paid') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Ventas pagadas
                </a>
                <form method="POST" action="{{ route('direct-sales.create') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Nueva venta
                    </button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($orders as $order)
                <a href="{{ route('direct-sales.show', $order) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1">
                    <div class="text-sm text-slate-500">Venta #{{ $order->id }}</div>
                    <div class="mt-2 text-lg font-semibold text-slate-800">${{ number_format($order->total, 2) }}</div>
                    <div class="mt-2 text-xs text-slate-500">Creada: {{ $order->created_at->format('d/m/Y H:i') }}</div>
                </a>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                    No hay ventas directas abiertas.
                </div>
            @endforelse
        </div>
    </div>
@endsection

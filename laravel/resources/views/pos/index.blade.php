@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Tablero de Mesas</h1>
                <p class="text-sm text-slate-500">Selecciona una mesa para gestionar la orden.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($tables as $table)
                @php
                    $isAvailable = $table->estado === 'disponible';
                @endphp
                <a href="{{ route('pos.tables.show', $table) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-slate-700">Mesa {{ $table->numero }}</div>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $isAvailable ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $isAvailable ? 'Disponible' : 'Ocupada' }}
                        </span>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">Capacidad: {{ $table->capacidad }} personas</div>
                    <div class="mt-4 h-2 w-full rounded-full {{ $isAvailable ? 'bg-emerald-200' : 'bg-red-200' }}"></div>
                </a>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                    No hay mesas registradas.
                </div>
            @endforelse
        </div>
    </div>
@endsection

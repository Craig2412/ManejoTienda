@extends('layouts.app')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Nuevo cliente</h1>
            <p class="text-sm text-slate-500">Registra los datos del cliente.</p>
        </div>

        <form action="{{ route('admin.customers.store') }}" method="POST" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="text-sm font-semibold text-slate-700">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                @error('nombre')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Identificacion</label>
                <input type="text" name="identificacion" value="{{ old('identificacion') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                @error('identificacion')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Volver</a>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
@endsection

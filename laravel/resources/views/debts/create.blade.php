@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Nueva cuenta por cobrar</h1>
            <p class="text-sm text-slate-500">Selecciona el cliente y crea la cuenta abierta.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('debts.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-slate-700">Cliente</label>
                    <select name="customer_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="">Selecciona un cliente</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->nombre }} - {{ $customer->identificacion }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Fecha limite (opcional)</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Notas (opcional)</label>
                    <textarea name="notes" rows="3" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>
                <div class="flex items-center justify-between">
                    <a href="{{ route('debts.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                        Cancelar
                    </a>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Crear cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

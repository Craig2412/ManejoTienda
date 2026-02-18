@extends('layouts.app')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Nuevo usuario</h1>
            <p class="text-sm text-slate-500">Solo administradores pueden crear usuarios.</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="text-sm font-semibold text-slate-700">Nombre</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Correo</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Contrasena</label>
                <input type="password" name="password" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Rol</label>
                <select name="role" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="barista" @selected(old('role') === 'barista')>Barista</option>
                    <option value="mesero" @selected(old('role') === 'mesero')>Mesero</option>
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Volver</a>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Crear usuario
                </button>
            </div>
        </form>
    </div>
@endsection

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Iniciar sesion - {{ config('app.name', 'Cafe Gourmet') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-900">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Iniciar sesion</h1>
                    <p class="text-sm text-slate-500">Acceso solo para personal autorizado.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

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

                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>

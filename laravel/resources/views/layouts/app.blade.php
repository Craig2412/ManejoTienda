<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Cafe Gourmet') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 text-slate-900">
        <div class="min-h-screen md:flex">
            <aside class="w-full md:w-64 bg-slate-900 text-slate-100 md:min-h-screen flex flex-col">
                <div class="px-6 py-6 border-b border-slate-800">
                    <div class="text-lg font-semibold tracking-wide">Espresso VTV</div>
                    <div class="text-xs text-slate-400">Gestion integral</div>
                </div>
                <nav class="px-4 py-6 space-y-2">
                    <a href="{{ route('inventory.products.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium bg-slate-800 text-white">
                        Inventario y Stock
                    </a>
                    <a href="{{ route('pos.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                        Punto de Venta
                    </a>
                    <a href="{{ route('direct-sales.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                        Venta directa
                    </a>
                    <a href="{{ route('debts.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                        Cuentas por cobrar
                    </a>
                    <a href="{{ route('reports.dashboard') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                        Reportes
                    </a>
                    <a href="{{ route('reports.payments') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                        Consulta de pagos
                    </a>
                    @if (auth()->check() && auth()->user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                            Usuarios
                        </a>
                        <a href="{{ route('admin.customers.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                            Clientes
                        </a>
                        <a href="{{ route('config.index') }}" class="block rounded-lg px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">
                            Configuracion
                        </a>
                    @endif
                </nav>
                <div class="mt-auto px-4 pb-6">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">
                            Cerrar sesion
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 p-6 md:p-10">
                @yield('content')
            </main>
        </div>
    </body>
</html>

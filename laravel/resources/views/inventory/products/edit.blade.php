@extends('layouts.app')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Editar producto</h1>
            <p class="text-sm text-slate-500">Actualiza la informacion del producto.</p>
        </div>

        <form action="{{ route('inventory.products.update', $product) }}" method="POST" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $product->nombre) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    @error('nombre')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Categoria</label>
                    <select name="category_id" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="">Selecciona una categoria</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                {{ $category->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Estado</label>
                    <select name="estado" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="activo" @selected(old('estado', $product->estado) === 'activo')>Activo</option>
                        <option value="inactivo" @selected(old('estado', $product->estado) === 'inactivo')>Inactivo</option>
                    </select>
                    @error('estado')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Descripcion</label>
                    <textarea name="descripcion" rows="3" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">{{ old('descripcion', $product->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Precio</label>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', $product->precio) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    @error('precio')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Stock actual</label>
                    <input type="number" name="stock_actual" value="{{ old('stock_actual', $product->stock_actual) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    @error('stock_actual')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Stock minimo</label>
                    <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $product->stock_minimo) }}" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    @error('stock_minimo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('inventory.products.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Volver</a>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection

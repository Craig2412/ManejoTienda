<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')
            ->orderBy('nombre')
            ->get();

        return view('inventory.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('nombre')->get();

        return view('inventory.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock_actual' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        Product::create($validated);

        return redirect()
            ->route('inventory.products.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('nombre')->get();

        return view('inventory.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock_actual' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('inventory.products.index')
            ->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('inventory.products.index')
            ->with('status', 'Producto eliminado correctamente.');
    }
}

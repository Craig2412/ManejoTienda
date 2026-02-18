<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::orderBy('nombre')->get();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'identificacion' => ['required', 'string', 'max:255', 'unique:customers,identificacion'],
        ]);

        Customer::create($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('status', 'Cliente creado correctamente.');
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'identificacion' => ['required', 'string', 'max:255', 'unique:customers,identificacion,' . $customer->id],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('status', 'Cliente actualizado correctamente.');
    }
}

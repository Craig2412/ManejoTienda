<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfigController extends Controller
{
    public function index(): View
    {
        $paymentMethods = PaymentMethod::orderBy('nombre')->get();
        $currencies = Currency::orderBy('nombre')->get();

        return view('config.index', compact('paymentMethods', 'currencies'));
    }

    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:payment_methods,nombre'],
            'requiere_referencia' => ['nullable', 'boolean'],
        ]);

        PaymentMethod::create([
            'nombre' => $validated['nombre'],
            'requiere_referencia' => (bool) ($validated['requiere_referencia'] ?? false),
            'activo' => true,
        ]);

        return redirect()->route('config.index')->with('status', 'Metodo de pago agregado.');
    }

    public function togglePaymentMethod(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->activo = ! $paymentMethod->activo;
        $paymentMethod->save();

        return redirect()->route('config.index');
    }

    public function storeCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:10', 'unique:currencies,codigo'],
            'simbolo' => ['nullable', 'string', 'max:10'],
        ]);

        Currency::create([
            'nombre' => $validated['nombre'],
            'codigo' => strtoupper($validated['codigo']),
            'simbolo' => $validated['simbolo'] ?? null,
            'activo' => true,
        ]);

        return redirect()->route('config.index')->with('status', 'Moneda agregada.');
    }

    public function toggleCurrency(Currency $currency): RedirectResponse
    {
        $currency->activo = ! $currency->activo;
        $currency->save();

        return redirect()->route('config.index');
    }
}

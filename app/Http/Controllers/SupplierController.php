<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('name', 'like', $t)
                    ->orWhere('name_ur', 'like', $t)
                    ->orWhere('contact_person', 'like', $t)
                    ->orWhere('phone', 'like', $t)
                    ->orWhere('city', 'like', $t));
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $supplier = new Supplier(['return_policy_days' => 7, 'response_time_days' => 3, 'is_active' => true]);

        return view('suppliers.form', compact('supplier'));
    }

    public function store(SupplierRequest $request)
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', __('suppliers.saved'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', __('suppliers.updated'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete(); // soft delete: purane claims ka record bacha rehta hai

        return redirect()->route('suppliers.index')->with('success', __('suppliers.deleted'));
    }
}
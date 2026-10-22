<?php

namespace App\Http\Controllers;

use App\Http\Requests\DamagedProductRequest;
use App\Models\DamagedProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DamagedProductController extends Controller
{
    public function index(Request $request)
    {
        $records = DamagedProduct::with(['product', 'claim'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('reason', 'like', $t)
                    ->orWhereHas('product', fn ($p) => $p
                        ->where('name', 'like', $t)
                        ->orWhere('name_ur', 'like', $t)
                        ->orWhere('sku', 'like', $t)));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('stage'), fn ($q) => $q->where('damage_stage', $request->stage))
            ->orderByDesc('reported_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totals = [
            'records'   => DamagedProduct::count(),
            'units'     => (int) DamagedProduct::sum('qty'),
            'loss'      => (float) DamagedProduct::sum('loss_value'),
            'unclaimed' => DamagedProduct::where('status', 'reported')->count(),
        ];

        return view('damaged.index', compact('records', 'totals'));
    }

    public function create()
    {
        $record = new DamagedProduct([
            'qty' => 1, 'damage_stage' => 'in_store', 'status' => 'reported', 'reported_at' => today(),
        ]);

        return view('damaged.form', $this->formData($record));
    }

    public function store(DamagedProductRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data    = Arr::except($request->validated(), ['photo', 'deduct_stock']);
            $product = Product::findOrFail($data['product_id']);

            $data['supplier_id'] = $product->supplier_id;
            $data['loss_value']  = filled($data['loss_value'] ?? null) ? $data['loss_value'] : $product->cost_price * $data['qty'];

            if ($request->hasFile('photo')) {
                $data['photo_path'] = $request->file('photo')->store('damaged', 'public');
            }

            DamagedProduct::create($data);

            // Naya record banate waqt chahein to stock se kharab maal kam kar do
            if ($request->boolean('deduct_stock')) {
                $product->update(['stock_qty' => max(0, $product->stock_qty - $data['qty'])]);
            }
        });

        return redirect()->route('damaged-products.index')->with('success', __('damaged.saved'));
    }

    public function edit(DamagedProduct $damaged)
    {
        return view('damaged.form', $this->formData($damaged));
    }

    public function update(DamagedProductRequest $request, DamagedProduct $damaged)
    {
        $data    = Arr::except($request->validated(), ['photo', 'deduct_stock']);
        $product = Product::findOrFail($data['product_id']);

        $data['supplier_id'] = $product->supplier_id;
        $data['loss_value']  = filled($data['loss_value'] ?? null) ? $data['loss_value'] : $product->cost_price * $data['qty'];

        if ($request->hasFile('photo')) {
            if ($damaged->photo_path) {
                Storage::disk('public')->delete($damaged->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('damaged', 'public');
        }

        $damaged->update($data);

        return redirect()->route('damaged-products.index')->with('success', __('damaged.updated'));
    }

    public function destroy(DamagedProduct $damaged)
    {
        $damaged->delete(); // soft delete

        return redirect()->route('damaged-products.index')->with('success', __('damaged.deleted'));
    }

    private function formData(DamagedProduct $record): array
    {
        return [
            'record'   => $record,
            'products' => Product::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $record->product_id))
                            ->orderBy('name')->get(),
        ];
    }
}
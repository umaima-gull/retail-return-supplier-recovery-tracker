<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpiryRecordRequest;
use App\Models\ExpiryRecord;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExpiryRecordController extends Controller
{
    public function index(Request $request)
    {
        $urgent = ExpiryRecord::URGENT_DAYS;
        $soon   = ExpiryRecord::SOON_DAYS;

        $records = ExpiryRecord::with(['product', 'batch', 'claim'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->whereHas('product', fn ($p) => $p
                    ->where('name', 'like', $t)
                    ->orWhere('name_ur', 'like', $t)
                    ->orWhere('sku', 'like', $t));
            })
            ->when($request->state === 'expired', fn ($q) => $q->whereDate('expiry_date', '<', today()))
            ->when($request->state === 'urgent', fn ($q) => $q->whereBetween('expiry_date', [today()->toDateString(), today()->addDays($urgent)->toDateString()]))
            ->when($request->state === 'soon', fn ($q) => $q->whereBetween('expiry_date', [today()->addDays($urgent + 1)->toDateString(), today()->addDays($soon)->toDateString()]))
            ->when($request->state === 'ok', fn ($q) => $q->whereDate('expiry_date', '>', today()->addDays($soon)))
            ->when($request->filled('action'), fn ($q) => $q->where('action_status', $request->action))
            ->orderByRaw("action_status IN ('recovered','disposed') ASC") // kaam ho chuke wale neeche
            ->orderBy('expiry_date')
            ->paginate(10)
            ->withQueryString();

        $open = fn () => ExpiryRecord::whereNotIn('action_status', ExpiryRecord::RESOLVED);

        $totals = [
            'expired' => (int) $open()->whereDate('expiry_date', '<', today())->sum('qty'),
            'soon'    => (int) $open()->whereBetween('expiry_date', [today()->toDateString(), today()->addDays($soon)->toDateString()])->sum('qty'),
            'pending' => ExpiryRecord::where('action_status', 'pending')->count(),
            'at_risk' => (float) $open()->sum('loss_value'),
        ];

        return view('expiry.index', compact('records', 'totals'));
    }

    public function create()
    {
        $record = new ExpiryRecord([
            'qty' => 1, 'expiry_date' => today(), 'action_status' => 'pending',
        ]);

        return view('expiry.form', $this->formData($record));
    }

    public function store(ExpiryRecordRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data    = Arr::except($request->validated(), ['deduct_stock']);
            $product = Product::findOrFail($data['product_id']);

            $data['supplier_id'] = $product->supplier_id;
            $data['loss_value']  = filled($data['loss_value'] ?? null) ? $data['loss_value'] : $product->cost_price * $data['qty'];

            ExpiryRecord::create($data);

            // Naya record banate waqt chahein to ekspire maal stock se kam kar do
            if ($request->boolean('deduct_stock')) {
                $product->update(['stock_qty' => max(0, $product->stock_qty - $data['qty'])]);
            }
        });

        return redirect()->route('expiry.index')->with('success', __('expiry.saved'));
    }

    public function edit(ExpiryRecord $expiry)
    {
        return view('expiry.form', $this->formData($expiry));
    }

    public function update(ExpiryRecordRequest $request, ExpiryRecord $expiry)
    {
        $data    = Arr::except($request->validated(), ['deduct_stock']);
        $product = Product::findOrFail($data['product_id']);

        $data['supplier_id'] = $product->supplier_id;
        $data['loss_value']  = filled($data['loss_value'] ?? null) ? $data['loss_value'] : $product->cost_price * $data['qty'];

        $expiry->update($data);

        return redirect()->route('expiry.index')->with('success', __('expiry.updated'));
    }

    public function destroy(ExpiryRecord $expiry)
    {
        $expiry->delete();

        return redirect()->route('expiry.index')->with('success', __('expiry.deleted'));
    }

    private function formData(ExpiryRecord $record): array
    {
        return [
            'record'   => $record,
            'products' => Product::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $record->product_id))
                            ->orderBy('name')->get(),
        ];
    }
}
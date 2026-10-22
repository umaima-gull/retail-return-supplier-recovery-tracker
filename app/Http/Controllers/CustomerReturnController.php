<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerReturnRequest;
use App\Models\CustomerReturn;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    public function index(Request $request)
    {
        $records = CustomerReturn::with(['product', 'claim'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('return_no', 'like', $t)
                    ->orWhere('customer_name', 'like', $t)
                    ->orWhere('customer_phone', 'like', $t)
                    ->orWhere('invoice_no', 'like', $t)
                    ->orWhereHas('product', fn ($p) => $p
                        ->where('name', 'like', $t)
                        ->orWhere('name_ur', 'like', $t)
                        ->orWhere('sku', 'like', $t)));
            })
            ->when($request->filled('reason'), fn ($q) => $q->where('reason', $request->reason))
            ->when($request->filled('condition'), fn ($q) => $q->where('item_condition', $request->condition))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('returned_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totals = [
            'records'   => CustomerReturn::count(),
            'refunded'  => (float) CustomerReturn::sum('refund_amount'),
            'resalable' => (int) CustomerReturn::where('item_condition', 'resalable')->sum('qty'),
            'claimable' => CustomerReturn::where('item_condition', 'not_resalable')->where('status', 'received')->count(),
        ];

        return view('customer_returns.index', compact('records', 'totals'));
    }

    public function create()
    {
        $record = new CustomerReturn([
            'qty' => 1, 'returned_at' => today(), 'reason' => 'damaged', 'item_condition' => 'not_resalable',
            'refund_method' => 'cash', 'status' => 'received',
        ]);

        return view('customer_returns.form', $this->formData($record));
    }

    public function store(CustomerReturnRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data    = Arr::except($request->validated(), ['restock']);
            $product = Product::findOrFail($data['product_id']);

            $data['return_no']   = CustomerReturn::nextReturnNo();
            $data['supplier_id'] = $product->supplier_id;
            $data['refund_amount'] = filled($data['refund_amount'] ?? null) ? $data['refund_amount'] : $product->sale_price * $data['qty'];

            CustomerReturn::create($data);

            // Bikne ke qabil maal wapas stock mein
            if ($request->boolean('restock') && $data['item_condition'] === 'resalable') {
                $product->update(['stock_qty' => $product->stock_qty + $data['qty']]);
            }
        });

        return redirect()->route('customer-returns.index')->with('success', __('customer_returns.saved'));
    }

    public function edit(CustomerReturn $customer_return)
    {
        return view('customer_returns.form', $this->formData($customer_return));
    }

    public function update(CustomerReturnRequest $request, CustomerReturn $customer_return)
    {
        $data    = Arr::except($request->validated(), ['restock']);
        $product = Product::findOrFail($data['product_id']);

        $data['supplier_id']   = $product->supplier_id;
        $data['refund_amount'] = filled($data['refund_amount'] ?? null) ? $data['refund_amount'] : $product->sale_price * $data['qty'];

        $customer_return->update($data);

        return redirect()->route('customer-returns.index')->with('success', __('customer_returns.updated'));
    }

    public function destroy(CustomerReturn $customer_return)
    {
        $customer_return->delete(); // soft delete

        return redirect()->route('customer-returns.index')->with('success', __('customer_returns.deleted'));
    }

    private function formData(CustomerReturn $record): array
    {
        return [
            'record'   => $record,
            'products' => Product::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $record->product_id))
                            ->orderBy('name')->get(),
        ];
    }
}
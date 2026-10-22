<?php

namespace App\Http\Controllers;

use App\Models\ClaimStatusLog;
use App\Models\CustomerReturn;
use App\Models\DamagedProduct;
use App\Models\ExpiryRecord;
use App\Models\Recovery;
use App\Models\Supplier;
use App\Models\SupplierClaim;
use App\Models\SupplierClaimItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierClaimController extends Controller
{
    /* ------------------------------------------------------------------
     |  1. List
     * ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $claims = SupplierClaim::with('supplier')->withCount('items')
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('claim_no', 'like', $t)
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $t)->orWhere('name_ur', 'like', $t)));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_id', $request->supplier))
            ->when($request->filled('type'), fn ($q) => $q->where('claim_type', $request->type))
            ->orderByDesc('claim_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totals = [
            'open'      => SupplierClaim::whereIn('status', SupplierClaim::OPEN)->count(),
            'overdue'   => SupplierClaim::whereIn('status', SupplierClaim::OPEN)->whereDate('due_date', '<', today())->count(),
            'claimed'   => (float) SupplierClaim::whereNotIn('status', ['draft', 'rejected'])->sum('total_value'),
            'recovered' => (float) Recovery::sum('amount'),
        ];

        $suppliers = Supplier::orderBy('name')->get();

        return view('supplier_returns.index', compact('claims', 'totals', 'suppliers'));
    }

    /* ------------------------------------------------------------------
     |  2. Naya claim: pehle supplier chuno, phir uske items
     * ------------------------------------------------------------------ */
    public function create(Request $request)
    {
        $counts    = $this->eligibleCounts();
        $suppliers = Supplier::whereIn('id', $counts->keys())->orderBy('name')->get();
        $supplier  = $request->filled('supplier') ? $suppliers->firstWhere('id', (int) $request->supplier) : null;
        $eligible  = $supplier ? $this->eligibleItems($supplier->id) : null;

        return view('supplier_returns.create', compact('suppliers', 'counts', 'supplier', 'eligible'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'claim_date'  => ['required', 'date', 'before_or_equal:today'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:claim_date'],
            'notes'       => ['nullable', 'string', 'max:2000'],
            'damaged'     => ['nullable', 'array'],
            'damaged.*'   => ['integer'],
            'expiry'      => ['nullable', 'array'],
            'expiry.*'    => ['integer'],
            'returns'     => ['nullable', 'array'],
            'returns.*'   => ['integer'],
        ], $this->messages(), $this->attributes());

        $sid      = (int) $data['supplier_id'];
        $eligible = $this->eligibleItems($sid);

        // Sirf wohi items jo is supplier ke hain aur abhi claim mein nahi hain
        $damaged = $eligible['damaged']->whereIn('id', $data['damaged'] ?? []);
        $expiry  = $eligible['expiry']->whereIn('id', $data['expiry'] ?? []);
        $returns = $eligible['returns']->whereIn('id', $data['returns'] ?? []);

        if ($damaged->isEmpty() && $expiry->isEmpty() && $returns->isEmpty()) {
            return back()->withInput()->withErrors(['items' => __('supplier_returns.no_items')]);
        }

        $supplier = Supplier::findOrFail($sid);

        $claim = DB::transaction(function () use ($data, $supplier, $damaged, $expiry, $returns) {
            $types = collect(['damaged' => $damaged, 'expired' => $expiry, 'customer_return' => $returns])
                ->filter(fn ($rows) => $rows->isNotEmpty())->keys();

            $claimDate = \Illuminate\Support\Carbon::parse($data['claim_date']);

            $claim = SupplierClaim::create([
                'claim_no'    => SupplierClaim::nextClaimNo(),
                'supplier_id' => $supplier->id,
                'claim_type'  => $types->count() === 1 ? $types->first() : 'other',
                'status'      => 'pending',
                'claim_date'  => $claimDate,
                'due_date'    => $data['due_date'] ?? $claimDate->copy()->addDays($supplier->response_time_days),
                'notes'       => $data['notes'] ?? null,
                'total_value' => 0,
            ]);

            $total = 0;
            foreach ([$damaged, $expiry, $returns] as $rows) {
                foreach ($rows as $row) {
                    $unit  = (float) $row->product->cost_price;
                    $line  = $unit * $row->qty;
                    $total += $line;

                    SupplierClaimItem::create([
                        'claim_id'   => $claim->id,
                        'product_id' => $row->product_id,
                        'qty'        => $row->qty,
                        'unit_cost'  => $unit,
                        'line_total' => $line,
                    ]);
                }
            }
            $claim->update(['total_value' => $total]);

            // Items ko claim se jodo aur unki status "claim raised" kar do
            DamagedProduct::whereIn('id', $damaged->pluck('id'))->update(['claim_id' => $claim->id, 'status' => 'claim_raised']);
            ExpiryRecord::whereIn('id', $expiry->pluck('id'))->update(['claim_id' => $claim->id, 'action_status' => 'claim_raised']);
            CustomerReturn::whereIn('id', $returns->pluck('id'))->update(['claim_id' => $claim->id, 'status' => 'claim_raised']);

            ClaimStatusLog::create([
                'claim_id'  => $claim->id,
                'to_status' => 'pending',
                'comment'   => __('supplier_returns.log_created'),
            ]);

            return $claim;
        });

        return redirect()->route('supplier-returns.show', $claim)
            ->with('success', __('supplier_returns.created', ['no' => $claim->claim_no]));
    }

    /* ------------------------------------------------------------------
     |  3. Claim ka page
     * ------------------------------------------------------------------ */
    public function show(SupplierClaim $claim)
    {
        $claim->load(['supplier', 'items.product', 'logs', 'recoveries']);
        $recovered = (float) $claim->recoveries->sum('amount');

        return view('supplier_returns.show', compact('claim', 'recovered'));
    }

    /* ------------------------------------------------------------------
     |  4. Status badalna
     * ------------------------------------------------------------------ */
    public function updateStatus(Request $request, SupplierClaim $claim)
    {
        $needsResolution = in_array($request->status, ['approved', 'completed'], true);

        $data = $request->validate([
            'status'           => ['required', Rule::in(SupplierClaim::STATUSES)],
            'resolution_type'  => [Rule::requiredIf($needsResolution), 'nullable', Rule::in(SupplierClaim::RESOLUTIONS)],
            'approved_value'   => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'rejection_reason' => [Rule::requiredIf($request->status === 'rejected'), 'nullable', 'string', 'max:255'],
            'due_date'         => ['nullable', 'date'],
            'comment'          => ['nullable', 'string', 'max:255'],
        ], $this->messages(), $this->attributes());

        $new  = $data['status'];
        $from = $claim->status;

        DB::transaction(function () use ($claim, $data, $new, $from) {
            if ($new === 'rejected') {
                $approved = 0;
            } elseif (in_array($new, ['approved', 'completed'], true)) {
                $approved = filled($data['approved_value'] ?? null) ? $data['approved_value'] : $claim->total_value;
            } else {
                $approved = $claim->approved_value;
            }

            $claim->update([
                'status'           => $new,
                'resolution_type'  => $data['resolution_type'] ?? $claim->resolution_type,
                'approved_value'   => $approved,
                'rejection_reason' => $new === 'rejected' ? $data['rejection_reason'] : null,
                'due_date'         => $data['due_date'] ?? $claim->due_date,
            ]);

            if ($new !== $from || filled($data['comment'] ?? null)) {
                ClaimStatusLog::create([
                    'claim_id'    => $claim->id,
                    'from_status' => $from,
                    'to_status'   => $new,
                    'comment'     => $data['comment'] ?? null,
                ]);
            }

            $this->syncSources($claim, $new);

            // Claim mukammal hua to dashboard ke liye wasooli ka record bhi ban jaye
            if ($new === 'completed' && ! $claim->recoveries()->exists()) {
                Recovery::create([
                    'claim_id'      => $claim->id,
                    'recovery_type' => $claim->resolution_type ?? 'refund',
                    'amount'        => $claim->approved_value,
                    'received_date' => today(),
                    'notes'         => __('supplier_returns.auto_recovery_note'),
                ]);
            }
        });

        return redirect()->route('supplier-returns.show', $claim)->with('success', __('supplier_returns.status_updated'));
    }

    /* ------------------------------------------------------------------
     |  5. Delete (sirf jab tak claim approve / mukammal na ho)
     * ------------------------------------------------------------------ */
    public function destroy(SupplierClaim $claim)
    {
        if (in_array($claim->status, ['approved', 'completed'], true) || $claim->recoveries()->exists()) {
            return redirect()->route('supplier-returns.index')->with('error', __('supplier_returns.cannot_delete'));
        }

        DB::transaction(function () use ($claim) {
            // Items wapas "claim ke liye tayyar" ho jayen
            DamagedProduct::where('claim_id', $claim->id)->update(['claim_id' => null, 'status' => 'reported']);
            ExpiryRecord::where('claim_id', $claim->id)->update(['claim_id' => null, 'action_status' => 'pending']);
            CustomerReturn::where('claim_id', $claim->id)->update(['claim_id' => null, 'status' => 'received']);

            $claim->delete(); // soft delete
        });

        return redirect()->route('supplier-returns.index')->with('success', __('supplier_returns.deleted'));
    }

    /* ------------------------------------------------------------------
     |  Madadgar
     * ------------------------------------------------------------------ */

    /** Claim ki status ke hisaab se jude hue items ki status bhi badlo */
    private function syncSources(SupplierClaim $claim, string $status): void
    {
        [$damaged, $expiry, $returns] = match ($status) {
            'completed' => ['recovered', 'recovered', 'closed'],
            'rejected'  => ['written_off', 'disposed', 'closed'],
            default     => ['claim_raised', 'claim_raised', 'claim_raised'],
        };

        DamagedProduct::where('claim_id', $claim->id)->update(['status' => $damaged]);
        ExpiryRecord::where('claim_id', $claim->id)->update(['action_status' => $expiry]);
        CustomerReturn::where('claim_id', $claim->id)->update(['status' => $returns]);
    }

    /** Un items ka supplier-wise hisaab jin par abhi claim nahi bana */
    private function eligibleCounts()
    {
        $counts = [];

        $groups = [
            DamagedProduct::where('status', 'reported')->whereNull('claim_id')->whereNotNull('supplier_id')
                ->selectRaw('supplier_id, COUNT(*) as c')->groupBy('supplier_id')->pluck('c', 'supplier_id'),
            ExpiryRecord::where('action_status', 'pending')->whereNull('claim_id')->whereNotNull('supplier_id')
                ->selectRaw('supplier_id, COUNT(*) as c')->groupBy('supplier_id')->pluck('c', 'supplier_id'),
            CustomerReturn::where('item_condition', 'not_resalable')->where('status', 'received')->whereNull('claim_id')->whereNotNull('supplier_id')
                ->selectRaw('supplier_id, COUNT(*) as c')->groupBy('supplier_id')->pluck('c', 'supplier_id'),
        ];

        foreach ($groups as $plucked) {
            foreach ($plucked as $sid => $n) {
                $counts[$sid] = ($counts[$sid] ?? 0) + $n;
            }
        }

        return collect($counts);
    }

    private function eligibleItems(int $supplierId): array
    {
        return [
            'damaged' => DamagedProduct::with('product')->where('supplier_id', $supplierId)
                ->where('status', 'reported')->whereNull('claim_id')->orderBy('reported_at')->get(),
            'expiry'  => ExpiryRecord::with('product')->where('supplier_id', $supplierId)
                ->where('action_status', 'pending')->whereNull('claim_id')->orderBy('expiry_date')->get(),
            'returns' => CustomerReturn::with('product')->where('supplier_id', $supplierId)
                ->where('item_condition', 'not_resalable')->where('status', 'received')->whereNull('claim_id')->orderBy('returned_at')->get(),
        ];
    }

    private function messages(): array
    {
        return [
            'required'         => __('common.v_required'),
            'required_if'      => __('common.v_required'),
            'max'              => __('common.v_max'),
            'min'              => __('common.v_min'),
            'numeric'          => __('common.v_numeric'),
            'integer'          => __('common.v_integer'),
            'exists'           => __('common.v_exists'),
            'in'               => __('common.v_exists'),
            'date'             => __('common.v_date'),
            'before_or_equal'  => __('common.v_future'),
            'after_or_equal'   => __('common.v_after'),
        ];
    }

    private function attributes(): array
    {
        return collect(['supplier_id', 'claim_date', 'due_date', 'notes', 'status', 'resolution_type',
                        'approved_value', 'rejection_reason', 'comment'])
            ->mapWithKeys(fn ($f) => [$f => __('supplier_returns.f_' . $f)])
            ->all();
    }
}
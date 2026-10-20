<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        /* ---------- 1. Top 4 stat cards ---------- */
        $s = DB::table('v_dashboard_stats')->first();

        $newProducts = DB::table('products')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $recThis = (float) DB::table('recoveries')
            ->whereBetween('received_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');

        $lastStart = now()->startOfMonth()->subMonthNoOverflow();
        $recLast = (float) DB::table('recoveries')
            ->whereBetween('received_date', [$lastStart->toDateString(), $lastStart->copy()->endOfMonth()->toDateString()])
            ->sum('amount');

        if ($recLast > 0) {
            $pct   = round(($recThis - $recLast) / $recLast * 100, 1);
            $recTrend = __($pct >= 0 ? 'app.trend_up' : 'app.trend_down', ['pct' => abs($pct)]);
        } else {
            $recTrend = $recThis > 0 ? __('app.trend_new_rec') : __('app.trend_no_rec');
        }

        $stats = [
            [
                'icon' => '&#128230;', 'title' => __('app.stat_products'),
                'value' => number_format($s->total_products),
                'trend' => __('app.new_this_month', ['count' => $newProducts]),
                'accent' => '#7D5BA6', 'accent2' => '#B197FC',
            ],
            [
                'icon' => '&#8987;', 'title' => __('app.stat_expired'),
                'value' => __('app.units', ['count' => number_format($s->expired_units)]),
                'trend' => __('app.urgent', ['count' => $s->urgent_units]),
                'accent' => '#E0A458', 'accent2' => '#F5C67B',
            ],
            [
                'icon' => '&#8617;', 'title' => __('app.stat_pending'),
                'value' => number_format($s->pending_claims),
                'trend' => __('app.overdue', ['count' => $s->overdue_claims]),
                'accent' => '#D96C47', 'accent2' => '#F08C4B',
            ],
            [
                'icon' => '&#128176;', 'title' => __('app.stat_recovered'),
                'value' => 'Rs ' . number_format($s->amount_recovered),
                'trend' => $recTrend,
                'accent' => '#7FB069', 'accent2' => '#A9C46C',
            ],
        ];

        /* ---------- 2. Bar chart (last 6 months) ---------- */
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonthsNoOverflow($i));
        $from   = $months->first()->toDateString();

        $claimsByMonth = DB::table('supplier_claims')
            ->whereNull('deleted_at')
            ->where('claim_date', '>=', $from)
            ->selectRaw("DATE_FORMAT(claim_date,'%Y-%m') as ym, COUNT(*) as c")
            ->groupBy('ym')->pluck('c', 'ym');

        $recoveredByMonth = DB::table('recoveries')
            ->where('received_date', '>=', $from)
            ->selectRaw("DATE_FORMAT(received_date,'%Y-%m') as ym, COUNT(DISTINCT claim_id) as c")
            ->groupBy('ym')->pluck('c', 'ym');

        $claimBars = $months->map(fn ($m) => ['label' => __('app.months.' . $m->month), 'value' => (int) ($claimsByMonth[$m->format('Y-m')] ?? 0)]);
        $recBars   = $months->map(fn ($m) => ['label' => __('app.months.' . $m->month), 'value' => (int) ($recoveredByMonth[$m->format('Y-m')] ?? 0)]);
        $barMax    = max(1, $claimBars->max('value'), $recBars->max('value'));

        /* ---------- 3. Recovery summary (donut) ---------- */
        $refund = (float) DB::table('recoveries')->where('recovery_type', 'refund')->sum('amount');
        $replacement = (float) DB::table('recoveries')->whereIn('recovery_type', ['replacement', 'credit_note'])->sum('amount');

        $pending = (float) DB::table('supplier_claims as c')
            ->whereNull('c.deleted_at')
            ->whereIn('c.status', ['pending', 'in_progress', 'approved'])
            ->selectRaw('COALESCE(SUM(c.total_value - COALESCE((SELECT SUM(r.amount) FROM recoveries r WHERE r.claim_id = c.id),0)),0) as t')
            ->value('t');
        $pending = max(0, $pending);

        $rejected = (float) DB::table('supplier_claims')
            ->whereNull('deleted_at')->where('status', 'rejected')->sum('total_value');

        $total = $refund + $replacement + $pending + $rejected;
        $rate  = $total > 0 ? round(($refund + $replacement) / $total * 100) : 0;

        if ($total > 0) {
            $a = $refund / $total * 100;
            $b = $a + $replacement / $total * 100;
            $c = $b + $pending / $total * 100;
            $gradient = "conic-gradient(var(--matcha) 0 {$a}%, var(--plum) {$a}% {$b}%, var(--caramel) {$b}% {$c}%, var(--terracotta) {$c}% 100%)";
        } else {
            $gradient = 'conic-gradient(var(--line) 0 100%)';
        }

        $summary = [
            ['label' => __('app.refund'), 'color' => '#7FB069', 'value' => $this->short($refund)],
            ['label' => __('app.replacement'), 'color' => '#7D5BA6', 'value' => $this->short($replacement)],
            ['label' => __('app.pending'), 'color' => '#E0A458', 'value' => $this->short($pending)],
            ['label' => __('app.rejected'), 'color' => '#D96C47', 'value' => $this->short($rejected)],
        ];

        /* ---------- 4. Recent supplier claims ---------- */
        $statusMap = [
            'draft'       => 't-pending',
            'pending'     => 't-pending',
            'in_progress' => 't-progress',
            'approved'    => 't-approved',
            'completed'   => 't-approved',
            'rejected'    => 't-rejected',
        ];

        // Urdu mode mein Urdu naam (name_ur); na ho to English naam
        $ur = app()->getLocale() === 'ur'
            && Schema::hasColumn('products', 'name_ur')
            && Schema::hasColumn('suppliers', 'name_ur')
            && Schema::hasColumn('follow_up_reminders', 'title_ur');
        $supplierCol = $ur ? "COALESCE(NULLIF(s.name_ur,''), s.name)" : 's.name';
        $productCol  = $ur ? "COALESCE(NULLIF(p.name_ur,''), p.name)" : 'p.name';

        $claims = DB::table('supplier_claims as c')
            ->join('suppliers as s', 's.id', '=', 'c.supplier_id')
            ->whereNull('c.deleted_at')
            ->select(
                'c.claim_no', 'c.status', 'c.total_value',
                DB::raw("{$supplierCol} as supplier"),
                DB::raw("(SELECT {$productCol} FROM supplier_claim_items i JOIN products p ON p.id = i.product_id WHERE i.claim_id = c.id ORDER BY i.id LIMIT 1) as product"),
                DB::raw('(SELECT COUNT(*) FROM supplier_claim_items i WHERE i.claim_id = c.id) as items_count')
            )
            ->orderByDesc('c.claim_date')->orderByDesc('c.id')
            ->limit(5)->get()
            ->map(function ($r) use ($statusMap) {
                $r->product_label = ($r->product ?? '—') . ($r->items_count > 1 ? ' ' . __('app.more', ['count' => $r->items_count - 1]) : '');
                $r->tag_class = $statusMap[$r->status] ?? 't-pending';
                $r->tag_label = __('app.status.' . $r->status);
                return $r;
            });

        /* ---------- 5. Needs your attention ---------- */
        $alertCount = DB::table('follow_up_reminders')->where('status', 'pending')->count();

        $iconMap = [
            'expiry'            => ['a-amber',  '&#8987;'],
            'supplier_response' => ['a-plum',   '&#8617;'],
            'replacement'       => ['a-matcha', '&#10003;'],
            'payment'           => ['a-matcha', '&#10003;'],
            'general'           => ['a-terra',  '&#9888;'],
        ];

        $alerts = DB::table('follow_up_reminders')
            ->where('status', 'pending')
            ->orderBy('due_at')->limit(4)->get()
            ->map(function ($r) use ($iconMap, $ur) {
                [$r->dot_class, $r->icon] = $iconMap[$r->type] ?? ['a-terra', '&#9888;'];
                $r->title_show   = ($ur && ! empty($r->title_ur))   ? $r->title_ur   : $r->title;
                $r->message_show = ($ur && ! empty($r->message_ur)) ? $r->message_ur : $r->message;
                $days = (int) today()->diffInDays(Carbon::parse($r->due_at)->startOfDay(), false); // + = future, - = past
                $r->when = $days < 0 ? trans_choice('app.days_late', abs($days), ['count' => abs($days)])
                         : ($days === 0 ? __('app.today') : trans_choice('app.in_days', $days, ['count' => $days]));
                return $r;
            });

        return view('dashboard', compact(
            'stats', 'claimBars', 'recBars', 'barMax', 'rate', 'gradient',
            'summary', 'claims', 'alerts', 'alertCount'
        ));
    }

    /** 137000 => "Rs 137k" */
    private function short(float $n): string
    {
        return $n >= 1000 ? 'Rs ' . round($n / 1000) . 'k' : 'Rs ' . number_format($n);
    }
}
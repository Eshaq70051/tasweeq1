<?php

namespace App\Services;

use App\Models\CaptainLedgerEntry;
use App\Models\CaptainPayment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CaptainWalletService
{
    /**
     * احتساب الملخص المالي الشامل للكابتن
     */
    public function getCaptainFinancialSummary(User $captain): array
    {
        // 1. إجمالي المبالغ النقدية المحصلة من الطلبات المكتملة
        $cashCollected = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        // 2. إجمالي العمولات المستحقة للكابتن
        $earnedCommission = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->sum('captain_commission');

        // 3. إجمالي المبالغ المسددة والمعتمدة للإدارة
        $paidDebt = CaptainPayment::where('captain_id', $captain->id)
            ->where('status', 'approved')
            ->sum('amount');

        // سندات القبض اليدوية المسجلة لصالح الإدارة من الكابتن
        $extraReceipts = \App\Models\FinancialTransaction::where('party_type', 'captain')
            ->where('party_id', $captain->id)
            ->where('type', 'receipt')
            ->sum('amount');
        
        $totalPaidDebt = (float) $paidDebt + (float) $extraReceipts;

        // قيود وتعديلات يدوية أخرى (مدين / دائن)
        $manualDebit = \App\Models\FinancialTransaction::where('party_type', 'captain')
            ->where('party_id', $captain->id)
            ->where('direction', 'debit')
            ->where('type', '!=', 'payment')
            ->sum('amount');

        $manualCredit = \App\Models\FinancialTransaction::where('party_type', 'captain')
            ->where('party_id', $captain->id)
            ->where('direction', 'credit')
            ->where('type', '!=', 'receipt')
            ->sum('amount');

        // 4. إجمالي طلبات السداد المعلقة
        $pendingPayments = CaptainPayment::where('captain_id', $captain->id)
            ->where('status', 'pending')
            ->sum('amount');

        // 5. عدد الطلبات المسلمة بنجاح
        $completedOrdersCount = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->count();

        // 6. صافي الذمة / المديونية الحالية (المحصل نقدًا + مدين يدوي - المسدد للإدارة - دائن يدوي)
        $effectiveCollected = (float) $cashCollected + (float) $manualDebit;
        $effectiveSettled = (float) $totalPaidDebt + (float) $manualCredit;
        $netDifference = $effectiveCollected - $effectiveSettled;

        return [
            'cash_collected' => (float) $cashCollected,
            'earned_commission' => (float) $earnedCommission,
            'paid_debt' => (float) $totalPaidDebt,
            'pending_payments' => (float) $pendingPayments,
            'outstanding_debt' => max(0, $netDifference),
            'net_difference' => (float) $netDifference,
            'completed_orders_count' => (int) $completedOrdersCount,
            'is_debtor' => $netDifference > 0,
            'is_creditor' => $netDifference < 0,
        ];
    }

    /**
     * تسجيل قيد دفتر الأستاذ عند تسليم الطلب
     */
    public function recordOrderDelivered(Order $order): void
    {
        if (!$order->assigned_captain_id) {
            return;
        }

        $captain = User::find($order->assigned_captain_id);
        if (!$captain) {
            return;
        }

        DB::transaction(function () use ($order, $captain) {
            // أ) إذا كان الطلب نقدياً: قيد تحصيل كاش (على الكابتن)
            if ($order->payment_method === 'cash' && $order->total_amount > 0) {
                // التأكد من عدم تكرار القيد
                $exists = CaptainLedgerEntry::where('order_id', $order->id)
                    ->where('type', 'cash_collected')
                    ->exists();

                if (!$exists) {
                    $summary = $this->getCaptainFinancialSummary($captain);
                    CaptainLedgerEntry::create([
                        'captain_id' => $captain->id,
                        'order_id' => $order->id,
                        'type' => 'cash_collected',
                        'direction' => 'debit',
                        'amount' => $order->total_amount,
                        'balance_after' => $summary['outstanding_debt'],
                        'description' => "تحصيل نقدي للطلب رقم #{$order->id}",
                    ]);
                }
            }

            // ب) قيد استحقاق عمولة التوصيل (لصالح الكابتن)
            if ($order->captain_commission > 0) {
                $commissionExists = CaptainLedgerEntry::where('order_id', $order->id)
                    ->where('type', 'commission_earned')
                    ->exists();

                if (!$commissionExists) {
                    $summary = $this->getCaptainFinancialSummary($captain);
                    CaptainLedgerEntry::create([
                        'captain_id' => $captain->id,
                        'order_id' => $order->id,
                        'type' => 'commission_earned',
                        'direction' => 'credit',
                        'amount' => $order->captain_commission,
                        'balance_after' => $summary['outstanding_debt'],
                        'description' => "عمولة توصيل للطلب رقم #{$order->id}",
                    ]);
                }
            }
        });
    }

    /**
     * تسجيل قيد سداد دفعة نقدية بعد اعتماد الإدارة
     */
    public function recordPaymentApproved(CaptainPayment $payment): void
    {
        $captain = User::find($payment->captain_id);
        if (!$captain) {
            return;
        }

        $exists = CaptainLedgerEntry::where('captain_payment_id', $payment->id)->exists();
        if ($exists) {
            return;
        }

        $summary = $this->getCaptainFinancialSummary($captain);

        CaptainLedgerEntry::create([
            'captain_id' => $captain->id,
            'captain_payment_id' => $payment->id,
            'type' => 'payment_settlement',
            'direction' => 'credit',
            'amount' => $payment->amount,
            'balance_after' => $summary['outstanding_debt'],
            'description' => "تسديد دفعة نقدية معتمدة (#{$payment->id})",
        ]);
    }
}

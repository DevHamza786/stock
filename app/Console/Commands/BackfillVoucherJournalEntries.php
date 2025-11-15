<?php

namespace App\Console\Commands;

use App\Models\BankPaymentVoucher;
use App\Models\CashPaymentVoucher;
use App\Models\PurchaseVoucher;
use App\Models\AccountTransaction;
use Illuminate\Console\Command;

class BackfillVoucherJournalEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:backfill-voucher-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill journal entries for vouchers that do not have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to backfill journal entries for vouchers...');

        // Backfill bank payment vouchers
        $this->backfillBankPaymentVouchers();
        
        // Backfill cash payment vouchers
        $this->backfillCashPaymentVouchers();
        
        // Backfill purchase vouchers
        $this->backfillPurchaseVouchers();

        $this->info('Backfill completed!');
    }

    protected function backfillBankPaymentVouchers()
    {
        $vouchers = BankPaymentVoucher::with('lines.account')->get();
        $count = 0;

        foreach ($vouchers as $voucher) {
            // Check if journal entry already exists
            $existing = AccountTransaction::where('reference_type', 'bank_payment_voucher')
                ->where('reference_id', $voucher->id)
                ->first();

            if (!$existing) {
                $controller = new \App\Http\Controllers\BankPaymentVoucherController();
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('createJournalEntry');
                $method->setAccessible(true);
                $method->invoke($controller, $voucher, $voucher->created_by);
                $count++;
                $this->info("Created journal entry for Bank Payment Voucher: {$voucher->voucher_number}");
            }
        }

        $this->info("Backfilled {$count} bank payment voucher(s).");
    }

    protected function backfillCashPaymentVouchers()
    {
        $vouchers = CashPaymentVoucher::with('lines.account')->get();
        $count = 0;

        foreach ($vouchers as $voucher) {
            // Check if journal entry already exists
            $existing = AccountTransaction::where('reference_type', 'cash_payment_voucher')
                ->where('reference_id', $voucher->id)
                ->first();

            if (!$existing) {
                $controller = new \App\Http\Controllers\CashPaymentVoucherController();
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('createJournalEntry');
                $method->setAccessible(true);
                $method->invoke($controller, $voucher, $voucher->created_by);
                $count++;
                $this->info("Created journal entry for Cash Payment Voucher: {$voucher->voucher_number}");
            }
        }

        $this->info("Backfilled {$count} cash payment voucher(s).");
    }

    protected function backfillPurchaseVouchers()
    {
        $vouchers = PurchaseVoucher::with(['bill.account', 'stockAddition'])
            ->where('status', 'posted')
            ->get();
        $count = 0;

        foreach ($vouchers as $voucher) {
            // Check if journal entry already exists
            $existing = AccountTransaction::where('reference_type', 'purchase_voucher')
                ->where('reference_id', $voucher->id)
                ->first();

            if (!$existing && $voucher->bill) {
                $controller = new \App\Http\Controllers\PurchaseVoucherController();
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('createJournalEntry');
                $method->setAccessible(true);
                $method->invoke($controller, $voucher, $voucher->created_by);
                $count++;
                $this->info("Created journal entry for Purchase Voucher: {$voucher->voucher_number}");
            }
        }

        $this->info("Backfilled {$count} purchase voucher(s).");
    }
}


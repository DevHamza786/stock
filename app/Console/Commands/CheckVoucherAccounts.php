<?php

namespace App\Console\Commands;

use App\Models\BankPaymentVoucher;
use App\Models\CashPaymentVoucher;
use App\Models\PurchaseVoucher;
use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Console\Command;

class CheckVoucherAccounts extends Command
{
    protected $signature = 'accounting:check-voucher-accounts';
    protected $description = 'Check which accounts are used in voucher transactions';

    public function handle()
    {
        $this->info('Checking Bank Payment Vouchers...');
        $bankVouchers = BankPaymentVoucher::with('lines.account')->get();
        foreach ($bankVouchers as $voucher) {
            $this->info("Voucher: {$voucher->voucher_number} - Date: {$voucher->payment_date}");
            foreach ($voucher->lines as $line) {
                $this->line("  - Account: {$line->account->account_code} ({$line->account->account_name}) - Type: {$line->entry_type} - Amount: {$line->amount}");
            }
            
            // Check if journal entry exists
            $transactions = AccountTransaction::where('reference_type', 'bank_payment_voucher')
                ->where('reference_id', $voucher->id)
                ->with('account', 'journalEntry')
                ->get();
            
            if ($transactions->count() > 0) {
                $this->info("  Journal Entry Transactions:");
                foreach ($transactions as $trans) {
                    $this->line("    - Account: {$trans->account->account_code} ({$trans->account->account_name}) - Debit: {$trans->debit_amount} - Credit: {$trans->credit_amount}");
                    $this->line("      Journal Entry: {$trans->journalEntry->entry_number} - Date: {$trans->journalEntry->entry_date} - Status: {$trans->journalEntry->status}");
                }
            } else {
                $this->warn("  No journal entry transactions found!");
            }
            $this->line('');
        }

        $this->info('Checking Cash Payment Vouchers...');
        $cashVouchers = CashPaymentVoucher::with('lines.account')->get();
        foreach ($cashVouchers as $voucher) {
            $this->info("Voucher: {$voucher->voucher_number} - Date: {$voucher->payment_date}");
            foreach ($voucher->lines as $line) {
                $this->line("  - Account: {$line->account->account_code} ({$line->account->account_name}) - Type: {$line->entry_type} - Amount: {$line->amount}");
            }
            
            $transactions = AccountTransaction::where('reference_type', 'cash_payment_voucher')
                ->where('reference_id', $voucher->id)
                ->with('account', 'journalEntry')
                ->get();
            
            if ($transactions->count() > 0) {
                $this->info("  Journal Entry Transactions:");
                foreach ($transactions as $trans) {
                    $this->line("    - Account: {$trans->account->account_code} ({$trans->account->account_name}) - Debit: {$trans->debit_amount} - Credit: {$trans->credit_amount}");
                    $this->line("      Journal Entry: {$trans->journalEntry->entry_number} - Date: {$trans->journalEntry->entry_date} - Status: {$trans->journalEntry->status}");
                }
            } else {
                $this->warn("  No journal entry transactions found!");
            }
            $this->line('');
        }

        // Check account 1600
        $pettyCash = ChartOfAccount::where('account_code', '1600')->first();
        if ($pettyCash) {
            $this->info("Checking transactions for account 1600 (Petty Cash)...");
            $transactions = AccountTransaction::where('account_id', $pettyCash->id)
                ->with('journalEntry')
                ->get();
            
            $this->info("Found {$transactions->count()} transactions for account 1600");
            foreach ($transactions as $trans) {
                $this->line("  - Debit: {$trans->debit_amount} - Credit: {$trans->credit_amount}");
                $this->line("    Journal Entry: {$trans->journalEntry->entry_number} - Date: {$trans->journalEntry->entry_date} - Status: {$trans->journalEntry->status}");
            }
        } else {
            $this->warn("Account 1600 (Petty Cash) not found!");
        }
    }
}


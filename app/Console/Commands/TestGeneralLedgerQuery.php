<?php

namespace App\Console\Commands;

use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Console\Command;

class TestGeneralLedgerQuery extends Command
{
    protected $signature = 'accounting:test-ledger-query {account_code} {start_date} {end_date}';
    protected $description = 'Test the general ledger query';

    public function handle()
    {
        $accountCode = $this->argument('account_code');
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date');

        $account = ChartOfAccount::where('account_code', $accountCode)->first();
        
        if (!$account) {
            $this->error("Account {$accountCode} not found!");
            return;
        }

        $this->info("Testing query for account: {$account->account_code} ({$account->account_name})");
        $this->info("Account ID: {$account->id}");
        $this->info("Date range: {$startDate} to {$endDate}");
        $this->line('');

        // Test the exact query from the controller
        $transactions = AccountTransaction::with(['journalEntry', 'account'])
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                      ->where('status', 'POSTED');
            })
            ->get();

        $this->info("Found {$transactions->count()} transactions");
        $this->line('');

        if ($transactions->count() > 0) {
            foreach ($transactions as $trans) {
                $this->line("Transaction ID: {$trans->id}");
                $this->line("  Account: {$trans->account->account_code} ({$trans->account->account_name})");
                $this->line("  Journal Entry: {$trans->journalEntry->entry_number}");
                $this->line("  Entry Date: {$trans->journalEntry->entry_date->toDateString()}");
                $this->line("  Status: {$trans->journalEntry->status}");
                $this->line("  Debit: {$trans->debit_amount}");
                $this->line("  Credit: {$trans->credit_amount}");
                $this->line("  Description: {$trans->description}");
                $this->line('');
            }
        } else {
            $this->warn("No transactions found. Checking all transactions for this account...");
            
            $allTransactions = AccountTransaction::with(['journalEntry', 'account'])
                ->where('account_id', $account->id)
                ->get();
            
            $this->info("Total transactions for account {$accountCode}: {$allTransactions->count()}");
            
            foreach ($allTransactions as $trans) {
                $this->line("  Entry Date: {$trans->journalEntry->entry_date->toDateString()}, Status: {$trans->journalEntry->status}, Debit: {$trans->debit_amount}, Credit: {$trans->credit_amount}");
            }
        }
    }
}


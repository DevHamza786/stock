<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Models\BankPaymentVoucher;
use App\Models\CashPaymentVoucher;
use App\Models\PurchaseVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /**
     * Display the accounting dashboard.
     */
    public function dashboard()
    {
        // Get summary statistics
        $totalAccounts = ChartOfAccount::active()->count();
        $totalJournalEntries = JournalEntry::count();
        $postedEntries = JournalEntry::byStatus('POSTED')->count();
        $draftEntries = JournalEntry::byStatus('DRAFT')->count();

        // Get recent journal entries
        $recentEntries = JournalEntry::with(['creator', 'transactions.account'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get account balances by type
        $accountBalances = ChartOfAccount::active()
            ->select('account_type', DB::raw('SUM(current_balance) as total_balance'))
            ->groupBy('account_type')
            ->get()
            ->keyBy('account_type');

        return view('accounting.dashboard', compact(
            'totalAccounts',
            'totalJournalEntries',
            'postedEntries',
            'draftEntries',
            'recentEntries',
            'accountBalances'
        ));
    }

    /**
     * Display trial balance.
     */
    public function trialBalance(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $accounts = ChartOfAccount::active()
            ->with(['transactions' => function($query) use ($date) {
                $query->whereHas('journalEntry', function($q) use ($date) {
                    $q->where('entry_date', '<=', $date)
                      ->where('status', 'POSTED');
                });
            }])
            ->orderBy('account_code')
            ->get()
            ->map(function($account) {
                $debitTotal = $account->transactions->sum('debit_amount');
                $creditTotal = $account->transactions->sum('credit_amount');

                $balance = $account->opening_balance;
                if ($account->normal_balance === 'DEBIT') {
                    $balance += $debitTotal - $creditTotal;
                } else {
                    $balance += $creditTotal - $debitTotal;
                }

                return [
                    'account' => $account,
                    'debit_balance' => $account->normal_balance === 'DEBIT' ? abs($balance) : 0,
                    'credit_balance' => $account->normal_balance === 'CREDIT' ? abs($balance) : 0,
                ];
            });

        $totalDebits = $accounts->sum('debit_balance');
        $totalCredits = $accounts->sum('credit_balance');

        return view('accounting.trial-balance', compact('accounts', 'totalDebits', 'totalCredits', 'date'));
    }

    /**
     * Display balance sheet.
     */
    public function balanceSheet(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        // Assets
        $assets = ChartOfAccount::active()
            ->byType('ASSET')
            ->with(['transactions' => function($query) use ($date) {
                $query->whereHas('journalEntry', function($q) use ($date) {
                    $q->where('entry_date', '<=', $date)
                      ->where('status', 'POSTED');
                });
            }])
            ->get()
            ->map(function($account) {
                $debitTotal = $account->transactions->sum('debit_amount');
                $creditTotal = $account->transactions->sum('credit_amount');

                $balance = $account->opening_balance + $debitTotal - $creditTotal;
                return ['account' => $account, 'balance' => $balance];
            });

        // Liabilities
        $liabilities = ChartOfAccount::active()
            ->byType('LIABILITY')
            ->with(['transactions' => function($query) use ($date) {
                $query->whereHas('journalEntry', function($q) use ($date) {
                    $q->where('entry_date', '<=', $date)
                      ->where('status', 'POSTED');
                });
            }])
            ->get()
            ->map(function($account) {
                $debitTotal = $account->transactions->sum('debit_amount');
                $creditTotal = $account->transactions->sum('credit_amount');

                $balance = $account->opening_balance + $creditTotal - $debitTotal;
                return ['account' => $account, 'balance' => $balance];
            });

        // Equity
        $equity = ChartOfAccount::active()
            ->byType('EQUITY')
            ->with(['transactions' => function($query) use ($date) {
                $query->whereHas('journalEntry', function($q) use ($date) {
                    $q->where('entry_date', '<=', $date)
                      ->where('status', 'POSTED');
                });
            }])
            ->get()
            ->map(function($account) {
                $debitTotal = $account->transactions->sum('debit_amount');
                $creditTotal = $account->transactions->sum('credit_amount');

                $balance = $account->opening_balance + $creditTotal - $debitTotal;
                return ['account' => $account, 'balance' => $balance];
            });

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return view('accounting.balance-sheet', compact(
            'assets', 'liabilities', 'equity',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'date'
        ));
    }

    /**
     * Display income statement.
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        // Revenue
        $revenue = ChartOfAccount::active()
            ->byType('REVENUE')
            ->with(['transactions' => function($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate])
                      ->where('status', 'POSTED');
                });
            }])
            ->get()
            ->map(function($account) {
                $debitTotal = $account->transactions->sum('debit_amount');
                $creditTotal = $account->transactions->sum('credit_amount');

                $balance = $account->opening_balance + $creditTotal - $debitTotal;
                return ['account' => $account, 'balance' => $balance];
            });

        // Expenses
        $expenses = ChartOfAccount::active()
            ->byType('EXPENSE')
            ->with(['transactions' => function($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate])
                      ->where('status', 'POSTED');
                });
            }])
            ->get()
            ->map(function($account) {
                $debitTotal = $account->transactions->sum('debit_amount');
                $creditTotal = $account->transactions->sum('credit_amount');

                $balance = $account->opening_balance + $debitTotal - $creditTotal;
                return ['account' => $account, 'balance' => $balance];
            });

        $totalRevenue = $revenue->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;

        return view('accounting.income-statement', compact(
            'revenue', 'expenses',
            'totalRevenue', 'totalExpenses', 'netIncome',
            'startDate', 'endDate'
        ));
    }

    /**
     * Display general ledger.
     */
    public function generalLedger(Request $request)
    {
        $accountId = $request->get('account_id') ? (int) $request->get('account_id') : null;
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $accounts = ChartOfAccount::active()->orderBy('account_code')->get();

        $transactions = collect();
        $allTransactionsSummary = collect();
        
        if ($accountId) {
            // Debug: Log the query parameters
            \Log::info('General Ledger Query', [
                'account_id' => $accountId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $transactions = AccountTransaction::with(['journalEntry', 'account'])
                ->where('account_id', $accountId)
                ->whereHas('journalEntry', function($query) use ($startDate, $endDate) {
                    $query->whereBetween('entry_date', [$startDate, $endDate])
                          ->where('status', 'POSTED');
                })
                ->get();
            
            // Sort by journal entry date and time
            $transactions = $transactions->sortBy(function($transaction) {
                if (!$transaction->journalEntry) {
                    return '9999-99-99-99-99-99';
                }
                return $transaction->journalEntry->entry_date->format('Y-m-d') . '-' . 
                       ($transaction->journalEntry->created_at ? $transaction->journalEntry->created_at->format('H:i:s') : '00:00:00');
            })->values();

            // Debug: Log the results
            \Log::info('General Ledger Results', [
                'transaction_count' => $transactions->count(),
                'transactions' => $transactions->map(function($t) {
                    return [
                        'id' => $t->id,
                        'account_id' => $t->account_id,
                        'journal_entry_id' => $t->journal_entry_id,
                        'entry_date' => $t->journalEntry->entry_date->toDateString(),
                        'status' => $t->journalEntry->status,
                        'debit' => $t->debit_amount,
                        'credit' => $t->credit_amount
                    ];
                })->toArray()
            ]);
        } else {
            // Show summary of all transactions when no account is selected
            $allTransactionsSummary = AccountTransaction::whereHas('journalEntry', function($query) use ($startDate, $endDate) {
                    $query->whereBetween('entry_date', [$startDate, $endDate])
                          ->where('status', 'POSTED');
                })
                ->select('account_id', DB::raw('SUM(debit_amount) as total_debit'), DB::raw('SUM(credit_amount) as total_credit'), DB::raw('COUNT(*) as transaction_count'))
                ->groupBy('account_id')
                ->get();
        }

        return view('accounting.general-ledger', compact('accounts', 'transactions', 'accountId', 'startDate', 'endDate', 'allTransactionsSummary'));
    }

    /**
     * Display voucher postings page with tabs for different voucher types.
     */
    public function voucherPostings(Request $request)
    {
        $activeTab = $request->get('tab', 'payment-receipt');

        // Get bank payment/receipt vouchers
        $bankVouchers = BankPaymentVoucher::with(['bankAccount', 'creator'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'bank_page');

        // Get cash payment vouchers
        $cashVouchers = CashPaymentVoucher::with(['cashAccount', 'creator'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'cash_page');

        // Get purchase vouchers
        $purchaseVouchers = PurchaseVoucher::with(['bill.account', 'stockAddition', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'purchase_page');

        // Sales vouchers - placeholder for future implementation
        $salesVouchers = collect();

        return view('accounting.voucher-postings', compact(
            'activeTab',
            'bankVouchers',
            'cashVouchers',
            'purchaseVouchers',
            'salesVouchers'
        ));
    }
}

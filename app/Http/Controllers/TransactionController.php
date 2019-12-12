<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{

    /**
     * Make a deposit
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postDeposit(Request $request) {
        $this->validate($request, [
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric'
        ]);

        $customerId = $request->post('customer_id');
        $amount = $request->post('amount');

        DB::beginTransaction();

        try {
            $customer = Customer::findOrFail($customerId);
            $userDeposits = Transaction::where(['customer_id' => $customerId, 'transaction_type' => 'deposit'])->lockForUpdate()->count();
            $transaction = new Transaction();
            $transaction->customer_id = $customerId;
            $transaction->amount = $amount;
            if (($userDeposits + 1) % 3 == 0) {
                $transaction->bonus_amount = round($customer->bonus / 100 * $amount, 2);
            }
            $transaction->transaction_type = 'deposit';
            $transaction->save();

        } catch (\Exception $e) {
            DB::rollback();
            abort(500, $e->getMessage());
        }

        DB::commit();

        return response()->json(['transaction' => $transaction]);
    }

    /**
     * Make a withdrawal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postWithdraw(Request $request) {
        $this->validate($request, [
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric'
        ]);

        $customerId = $request->post('customer_id');
        $amount = $request->post('amount');

        DB::beginTransaction();

        try {
            $userDeposits = Transaction::where(['customer_id' => $customerId, 'transaction_type' => 'deposit'])->lockForUpdate()->sum('amount');
            $userWithdrawals = Transaction::where(['customer_id' => $customerId, 'transaction_type' => 'withdraw'])->lockForUpdate()->sum('amount');
            if ($amount <= ($userDeposits - $userWithdrawals)) {
                $transaction = new Transaction();
                $transaction->customer_id = $customerId;
                $transaction->amount = $amount;
                $transaction->transaction_type = 'withdraw';
                $transaction->save();
            } else {
                throw new \Exception('Not enough balance to withdraw that amount.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            abort(500, $e->getMessage());
        }

        DB::commit();

        return response()->json(['transaction' => $transaction]);
    }

    /**
     * Get a report
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReport(Request $request) {
        $fromDate = $request->query('fromDate', date('Y-m-d', strtotime('-7 days')));
        $toDate = $request->query('toDate', date('Y-m-d'));

        $result = DB::select("select DATE(t.created_at) as date, c.country, COUNT(DISTINCT c.id) as uniqueCustomers,
            COUNT(IF (t.transaction_type = 'deposit', 1, null)) as numberDeposits,
            SUM(IF (t.transaction_type = 'deposit', t.amount, 0)) as totalDeposits,
            COUNT(IF (t.transaction_type = 'withdraw', 1, null)) as numberWithdrawals,
            SUM(IF (t.transaction_type = 'withdraw', -t.amount, 0)) as totalWithdrawals
            from transactions t join customers c on t.customer_id = c.id
            where cast(t.created_at as date) between ? and ? GROUP BY DATE(t.created_at), c.country", [$fromDate, $toDate]);

        return response()->json(['result' => $result]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // get sent and received transactions
        $transactions = Transaction::query()->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json([
            'balance' => $user->balance,
            'transactions' => $transactions,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id|not_in:'.$user->id,
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $receiverId = (int) $request->input('receiver_id');
        $amount = number_format((float)$request->input('amount'), 2, '.', ''); // safe decimal string

        // commission 1.5%
        $commission = bcmul($amount, '0.015', 2); // returns string with 2 decimals
        $totalDebit = bcadd($amount, $commission, 2);

        // Atomic operation with row-level locks
        try {
            $result = DB::transaction(function() use($user, $receiverId, $amount, $commission, $totalDebit) {
                // reload sender and receiver with FOR UPDATE locking
                $sender = User::query()->where('id', $user->id)->lockForUpdate()->first();
                $receiver = User::query()->where('id', $receiverId)->lockForUpdate()->first();

                if (!$receiver) {
                    throw new \Exception('Receiver not found');
                }

                // ensure sufficient funds
                if (bccomp($sender->balance, $totalDebit, 2) < 0) {
                    throw new \Exception('Insufficient funds');
                }

                // Debit sender
                $sender->balance = bcsub($sender->balance, $totalDebit, 2);
                $sender->save();

                // Credit receiver
                $receiver->balance = bcadd($receiver->balance, $amount, 2);
                $receiver->save();

                // Create transaction record
                $transaction = Transaction::query()->create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'commission_fee' => $commission,
                    'status' => 'completed',
                ]);

                // Broadcast the event to both users
                broadcast(new TransactionCreated($transaction, $sender->id, $receiver->id))->toOthers();

                return $transaction;
            }, 5); // retry 5 times on deadlock

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['transaction' => $result], 201);
    }
}

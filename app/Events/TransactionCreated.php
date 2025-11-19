<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Transaction $transaction;
    public int $senderId;
    public int $receiverId;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, int $senderId, int $receiverId)
    {
        $this->transaction = $transaction;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // broadcast to both users' private channels
        return [
            new PrivateChannel('user.' . $this->senderId),
            new PrivateChannel('user.' . $this->receiverId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->transaction->id,
            'sender_id'      => $this->transaction->sender_id,
            'receiver_id'    => $this->transaction->receiver_id,
            'amount'         => (string)$this->transaction->amount,
            'commission_fee' => (string)$this->transaction->commission_fee,
            'status'         => $this->transaction->status,
            'created_at'     => $this->transaction->created_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.created';
    }
}

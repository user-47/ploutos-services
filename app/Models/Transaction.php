<?php

namespace App\Models;

use App\Traits\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use UuidModel, SoftDeletes;

    const TYPE_BUY = 'buy';
    const TYPE_SELL = 'sell';

    const STATUS_OPEN = 'open';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $uuidPrefix = 'txn';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'amount',
        'currency',
        'type',
    ];
}

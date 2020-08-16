<?php

namespace App\Models;

use App\Traits\UuidModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use UuidModel, SoftDeletes;

    const STATUS_DRAFT ='draft';
    const STATUS_PAID ='paid';
    const STATUS_FAILED ='failed';
    const STATUS_CANCELLED ='cancelled';
    const STATUS_REFUNDED ='refunded';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
    ];

    /**
     * Binds creating/saving events to create Reference Number if not passed (and also prevent them from being overwritten).
     *
     * @return void
     */
    public static function booted()
    {
        static::creating(function ($model) {
            if (is_null($model->reference_no)) {
                // ensure reference_no is unique
                $unique = false;
                do {
                    $reference_no = "PLEX-" . Str::random(5) . "-" . time();
                    $unique = !static::where('reference_no', $reference_no)->first();
                } while (!$unique);
                $model->reference_no = $reference_no;
            }
        });

        static::saving(function ($model) {
            // What's that, trying to change the Reference Number huh?  Nope, not gonna happen.
            $original_reference_no = $model->getOriginal('reference_no');

            if ($original_reference_no !== $model->reference_no) {
                $model->reference_no = $original_reference_no;
            }
        });
    }

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    //////////////
    // MUTATORS //
    //////////////

    ////////////
    // SCOPES //
    ////////////

    /////////////
    // METHODS //
    /////////////

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid()
    {
        if ($this->status != self::STATUS_DRAFT) {
            throw new Exception("Can not mark a non draft invoice as paid");
        }
        $this->status = self::STATUS_PAID;
        $this->save();
    }
}

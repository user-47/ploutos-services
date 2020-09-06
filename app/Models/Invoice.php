<?php

namespace App\Models;

use App\Traits\UuidModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use UuidModel, SoftDeletes;

    const STATUS_DRAFT ='draft';
    const STATUS_PAID ='paid';
    const STATUS_FAILED ='failed';
    const STATUS_CANCELLED ='cancelled';
    const STATUS_PARTIAL_REFUND ='partial_refund';
    const STATUS_REFUNDED ='refunded';
    const STATUS_PAST_DUE = 'past_due';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'due_date',
        'paid_at',
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

    /**
     * Get the transaction for this invoice.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    /**
     * Get the invoice for the reference transaction of the transaction of this invoice.
     */
    public function getreferenceInvoiceAttribute(): ?Invoice
    {
        if ($referenceTransaction = $this->transaction->referenceTransaction) {
            return $referenceTransaction->invoices()->latest()->first();
        }
        return null;
    }

    ////////////
    // SCOPES //
    ////////////

    /**
     * Scope a query to only include draft invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /////////////
    // METHODS //
    /////////////

    /**
     * Mark an invoice as failed.
     */
    public function markAsFailed()
    {
        if ($this->status != self::STATUS_DRAFT) {
            throw new Exception("Can not mark a non draft invoice as failed");
        }
        $this->status = self::STATUS_FAILED;
        $this->save();
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid()
    {
        if ($this->status != self::STATUS_DRAFT) {
            throw new Exception("Can not mark a non draft invoice as paid");
        }
        $this->status = self::STATUS_PAID;
        $this->paid_at = Carbon::now();
        $this->save();

        $this->referenceInvoice->setDueDate();
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPastDue()
    {
        if ($this->status != self::STATUS_DRAFT) {
            throw new Exception("Can not mark a non draft invoice as past due");
        }
        $this->status = self::STATUS_PAST_DUE;
        $this->save();

        // Todo:: raise past due event
        // listener to notifier both buyer and seller
        // set transaction as cancelled
        // listener to refund the reference invoice paid
        // set paid transaction status as refunded
    }

    /**
     * Set the due date of the invoice
     */
    public function setDueDate()
    {
        // Todo:: determin business rule for this. Can be made configurable.
        $this->due_date = $this->referenceInvoice->paid_at->addHour();
        $this->save();
    }
}

<?php

namespace App\Models;

use App\Traits\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use UuidModel, SoftDeletes;

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the user that owns this card
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //////////////
    // ACCESORS //
    //////////////

    //////////////
    // MUTATORS //
    //////////////

    ////////////
    // SCOPES //
    ////////////

    /**
     * Scope query to only include invoice with payment id = $identifier
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIdentifier($query, $identifier)
    {
        return $query->where('provider_id', $identifier);
    }

    /////////////
    // METHODS //
    /////////////
}

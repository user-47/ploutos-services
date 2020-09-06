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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    ////////////
    // SCOPES //
    ////////////

    public function scopeIdentifier($query, $identifier)
    {
        return $query->where('provider_id', $identifier);
    }

    /////////////
    // METHODS //
    /////////////
}

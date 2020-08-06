<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    const AVAILABLE_CURRENCIES = [
        'cad',
        'ngn',
        'usd'
    ];
}

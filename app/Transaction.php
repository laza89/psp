<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * Get the customer that owns the transaction.
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }
}

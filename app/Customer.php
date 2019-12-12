<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * Get the transactions for the customer.
     */
    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}

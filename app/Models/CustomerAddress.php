<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerAddress extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'postal_code',
        'address',
        'locality_name',
        'federal_entity',
        'neighborhood',
        'municipality',
        'between_streets',
        'interior_number',
        'exterior_number',
    ];

    public function customer() : HasOne
    {
        return $this->hasOne (Customer::class, 'address_id');
    }
}

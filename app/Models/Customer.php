<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use softDeletes;
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'address_id',
        'business_name',
        'representative_name',
        'rfc',
        'phone_number',
    ];

    protected $hidden = [
        'user_id',
        'address_id',
        'deleted_at',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customerAddress() : BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'address_id');
    }

}

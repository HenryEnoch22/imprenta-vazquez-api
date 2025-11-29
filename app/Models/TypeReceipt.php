<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeReceipt extends Model
{
    use SoftDeletes;
    protected $table = 'type_receipts';
    protected $fillable  = [
        'type_receipt_category_id',
        'name',
        'description',
        'created_at',
        'updated_at',
    ];

    public static $categories = [
        1 => 'Impresión',
        2 => 'Varios',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeReceipt extends Model
{
    use SoftDeletes, HasFactory;
    protected $table = 'type_receipts';
    protected $fillable  = [
        'name',
        'description',
        'receipt_category',
        'created_at',
        'updated_at',
    ];

    public static $categories = [
        'Impresión',
        'Varios',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintJobRequest extends Model
{
    use SoftDeletes;
    protected $table = 'print_job_requests';
    protected $fillable = [
        'customer_id',
        'type_receipt_id',
        'name',
        'category_id',
        'file_path',
        'description',
        'folio',
        'paper_size',
        'copies_number',
        'copies_colors',
        'tint_colors',
        'paper_type',
        'quantity',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'copies_colors' => 'array',
        'tint_colors' => 'array',
    ];

    public static $copiesColors = [
        1 => 'rosa',
        2 => 'azul',
        3 => 'amarillo',
        4 => 'verde',
    ];

    public static $tintColors = [
        1 => 'negro',
        2 => 'azul reflex',
        3 => 'azul process',
        4 => 'verde',
        5 => 'rojo',
        6 => 'sepia',
        7 => 'otro (especificar en descripcion)',
    ];

    public static $paperSizes = [
        1 => '1/8 de carta',
        2 => '1/6 de carta',
        3 => '1/4 de carta',
        4 => '1/4 de oficio',
        5 => '1/2 de carta',
        6 => '1/2 de oficio',
        7 => 'Tamaño carta',
        8 => 'Tamaño oficio',
        9 => 'Tamaño especial',
    ];

    public static $paperTypes = [
        1 => 'Papel bond',
        2 => 'Papel autocopiante',
        3 => 'cartulina',
    ];

    public static $status = [
        1 => 'Solicitada',
        2 => 'Esperando aceptacion',
        3 => 'En proceso',
        4 => 'Terminada',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function typeReceipt()
    {
        return $this->hasOne(TypeReceipt::class, 'id', 'type_receipt_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintJobRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'category_id',
        'type_receipt_id',
        'name',
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
        'price',
        'estimated_date',
        'reason_rejection',
    ];

    protected $casts = [
        'copies_colors' => 'array',
        'tint_colors' => 'array',
        'price' => 'decimal:2',
        'estimated_date' => 'date',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING_ACCEPTANCE = 'waiting_acceptance';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_DECLINED = 'declined';

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

    public static $statusLabels = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_WAITING_ACCEPTANCE => 'Esperando aceptación',
        self::STATUS_ACCEPTED => 'Aceptada',
        self::STATUS_IN_PROGRESS => 'En proceso',
        self::STATUS_COMPLETED => 'Completada',
        self::STATUS_REJECTED => 'Rechazada',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function typeReceipt()
    {
        return $this->hasOne(TypeReceipt::class, 'id', 'type_receipt_id');
    }

    /**
     * Verifica si el cliente puede editar esta solicitud
     */
    public function canBeEditedByClient(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_WAITING_ACCEPTANCE,
            self::STATUS_REJECTED
        ]);
    }

    /**
     * Verifica si el admin puede cambiar el estado de esta solicitud
     */
    public function canBeEditedByAdmin(): bool
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED
        ]);
    }
}

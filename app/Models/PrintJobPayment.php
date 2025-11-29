<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintJobPayment extends Model
{
    use SoftDeletes;
    protected $table = 'print_job_payments';
    protected $fillable = [
        'print_job_request_id',
        'payment_method',
        'amount',
        'paid_at',
        'file_path',
        'created_at',
        'updated_at',
    ];

    public static $paymentMethods = [
        1 => 'Pago parcial por transferencia',
        2 => 'Pago anticipado por transferencia',
        3 => 'Pago en efectivo (en sucursal)',
    ];

    public function printJobRequest()
    {
        $this->belongsTo(PrintJobRequest::class, 'print_job_request_id');
    }
}

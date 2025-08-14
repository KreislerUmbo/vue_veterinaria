<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRecibo extends Model
{
    protected $fillable = [
        'recibo_id',
        'concepto_id', 
        'monto_usd', 
        'monto_soles', 
        'description_detail'
    ];
    

    public function recibo() {
        return $this->belongsTo(Recibo::class);
    }
    
    public function concepto() {
        return $this->belongsTo(Concepto::class);
    }
    
}

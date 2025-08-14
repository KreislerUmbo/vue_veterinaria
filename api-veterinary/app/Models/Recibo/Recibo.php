<?php

namespace App\Models;

use App\Models\Cliente\Cliente;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    
    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }
    
    public function detalles() {
        return $this->hasMany(DetalleRecibo::class);
    }
    
}

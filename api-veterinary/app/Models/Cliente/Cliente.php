<?php

namespace App\Models\Cliente;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
   use HasFactory;
   use SoftDeletes;

    protected $fillable = [
        'id_tipodoc_identidad',
        'nombres',
        'apellidos',
        'razon_social',
        'nombre_comercial',
        'codigo',
        'num_doc',
        'direccion_fiscal',
        'email',
        'celular',
        'telefono',
        'estado',
        'num_cuenta_detraccion',
        'fecha_registro',
    ];

    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set('America/Lima');
        $this->attributes["created_at"] = Carbon::now();
    }
    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }    
}

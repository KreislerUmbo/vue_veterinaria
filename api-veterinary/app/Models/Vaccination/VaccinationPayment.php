<?php

namespace App\Models\Vaccination;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccinationPayment extends Model
{
    use HasFactory; 
    use SoftDeletes;// habilitar eliminacion logica

    // campos asignables
    protected $fillable = [
        "vaccination_id",
        "method_payment", // metodo de pago
        "amount", // monto pagado
        //   "payment_date",
        //   "note",
        // "user_id",
    ];

    // relacion con la tabla de vacunaciones
    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class, "vaccination_id"); // un pago de vacunacion pertenece a una vacunacion
    }

    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }
    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
}

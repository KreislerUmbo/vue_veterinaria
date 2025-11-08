<?php

namespace App\Models\Vaccination;

use App\Models\MedicalRecord;
use App\Models\Pets\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Vaccination extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "pet_id",
        "veterinarie_id",
        "vaccine_names",// nombres de las vacunas aplicadas
        "day",// dia de la vacunacion en formato texto
        "vaccionation_date",// fecha de vacunacion
        "nex_due_date",// proxima fecha de vacunacion
        "reason",// motivo de la vacunacion
        "state",// 1=pendiente, 2=cancelado, 3=atendido
        "outside",// si la vacunacion fue fuera de la clinica
        "reprogramar",// si la vacunacion fue reprogramada
        "amount",// monto total o precio de la vacunacion
        "state_pay",// estado del pago: 1=pendiente,  2=parcial , 3=pagado/completo
        "user_id",
    ];
    //
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
    
    // relacion con la tabla de mascotas
    public function pet()
    {
        return $this->belongsTo(Pet::class, "pet_id");// una vacunacion pertenece a una mascota
    }

    // relacion con la tabla de usuarios (usuarios que registran la vacunacion)
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    // relacion con la tabla de usuarios (veterinarios que aplican la vacunacion)
    public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }

    // relacion con los pagos de las vacunaciones
    public function payments()
    {
        return $this->hasMany(VaccinationPayment::class);
    }
    // relacion con los horarios de las vacunaciones
    public function schedules()
    {
        return $this->hasMany(VaccinationSchedule::class); // relacion 1 a muchos
    }
    // relacion con el registro medico
    public function medical_record() 
    {
        return $this->hasOne(MedicalRecord::class, "vaccination_id"); // relacion con medical_records 1 a 1
    }

    
    public function scopeFilterMultiple($query, $type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)
    {
        if ($type_date && $start_date && $end_date) {
            if ($type_date == 1) { // Fecha de Cita
                $query->whereBetween("vaccionation_date", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
            } elseif ($type_date == 2) { // Fecha de Registro
                $query->whereBetween("created_at", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
            }
        }
        if ($state_pay) {
            $query->where('state_pay', $state_pay);
        }
        if ($state) {
            $query->where('state', $state);
        }
        if ($specie) {
            $query->whereHas('pet', function ($q) use ($specie) {
                $q->where('specie', 'LIKE', '%' . $specie . '%');
            });
        }
        if ($search_pets) {
            $query->whereHas('pet', function ($q) use ($search_pets) {
                $q->where('name', 'ILIKE', '%' . $search_pets . '%');
            });
        }
        if ($search_vets) {
            $query->whereHas('veterinarie', function ($q) use ($search_vets) {
                $q->where(DB::raw("users.name || ' ' || COALESCE(users.surname,'') || ' ' ||  COALESCE(users.phone,'') || ' ' ||  COALESCE(users.n_document,'')"), 'ILIKE', '%' . $search_vets . '%');
            });
        }

        return $query;
    }

}

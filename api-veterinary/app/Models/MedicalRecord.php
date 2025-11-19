<?php

namespace App\Models;

use App\Models\Appointment\Appointment;
use App\Models\Pets\Pet;
use App\Models\Surgerie\Surgerie;
use App\Models\Vaccination\Vaccination;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MedicalRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "pet_id",
        "veterinarie_id",
        "event_type",
        "appointment_id",
        "vaccination_id",
        "surgerie_id",
        "event_date",
        "notes",
    ];
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
    public function pet()
    {
        return $this->belongsTo(Pet::class, "pet_id");
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, "appointment_id");
    }
      public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }
    public function vaccination() // relation con vacunacion con registro medico
    {
        return $this->belongsTo(Vaccination::class, "vaccination_id");
    }
    public function surgerie() // relacion con cirugia con registro medico
    {
        return $this->belongsTo(Surgerie::class, "surgerie_id");
    }

    public function scopeFilterMultiple($query, $type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets, $type_service)
    {

  // 🔥 FILTRO TYPE SERVICE (este es el nuevo)
    if ($type_service) {
        switch ($type_service) {
            case 1: // appointment
                $query->whereNotNull('appointment_id')
                      ->whereNull('vaccination_id')
                      ->whereNull('surgerie_id');
                break;

            case 2: // vaccination
                $query->whereNotNull('vaccination_id')
                      ->whereNull('appointment_id')
                      ->whereNull('surgerie_id');
                break;

            case 3: // surgerie
                $query->whereNotNull('surgerie_id')
                      ->whereNull('appointment_id')
                      ->whereNull('vaccination_id');
                break;
        }
    }

        // Filtro por rango de fechas
        if ($start_date && $end_date) {
            if ($type_date == 1) { // Fecha del evento
                $query->whereBetween("event_date", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
            } elseif ($type_date == 2) { // Fecha de Registro
                $query->whereBetween("created_at", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
            }
        }
        // Filtro por estado de pago
        if ($state_pay) {
            if ($type_service == 1) { // citas medicas
                $query->whereHas('appointment', function ($subquery) use ($state_pay) {
                    $subquery->where('state_pay', $state_pay);
                });
            }
            if ($type_service == 2) { // vacunaciones
                $query->whereHas('vaccination', function ($subquery) use ($state_pay) {
                    $subquery->where('state_pay', $state_pay);
                });
            }
            if ($type_service == 3) { // cirugias 

                $query->whereHas('surgerie', function ($subquery) use ($state_pay) {
                    $subquery->where('state_pay', $state_pay);
                });
            }
        }
        // Filtro por estado del servicio
        if ($state) {
            if ($type_service == 1) { // citas medicas
                $query->whereHas('appointment', function ($q) use ($state) {
                    $q->where('state', $state);
                });
            }
            if ($type_service == 2) { // vacunaciones
                $query->whereHas('vaccination', function ($q) use ($state) {
                    $q->where('state', $state);
                });
            }
            if ($type_service == 3) { // cirugias

                $query->whereHas('surgerie', function ($q) use ($state) {
                    $q->where('state', $state);
                });
            }
        }
        // Filtro por especie
        if ($specie) {
            $query->whereHas('pet', function ($q) use ($specie) {
                $q->where('specie', $specie);
            });
        }

        // Filtro por nombre de mascota
        if ($search_pets) {
            $query->whereHas('pet', function ($q) use ($search_pets) {
                $q->where('name', 'like', '%' . $search_pets . '%');
            });
        }

        // Filtro por nombre de veterinario
        if ($search_vets) {
            $query->whereHas('veterinarie', function ($q) use ($search_vets) {
                $q->where(DB::raw("users.name || ' ' || COALESCE(users.surname,'') || ' ' ||  COALESCE(users.phone,'') || ' ' ||  COALESCE(users.n_document,'')"), 'ILIKE', '%' . $search_vets . '%');
            });
        }

        return $query;
    }
}

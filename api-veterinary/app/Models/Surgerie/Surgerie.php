<?php

namespace App\Models\Surgerie;

use App\Models\MedicalRecord;
use App\Models\Pets\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\Concerns\Has;

class Surgerie extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "pet_id",
        "veterinarie_id",
        "day",
        "surgerie_date",
        "surgerie_type",
        "medical_notes",
        "outcome",
        "state",
        "outside",
        "reprogramar",
        "amount",
        "state_pay",
        "user_id",

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

    public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, "pet_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function payments()// nombre de la relacion entre cirugias y pagos de cirugias 
    {
        return $this->hasMany(SurgeriePayment::class);
    }
    public function schedules() // nombre de la relacion entre cirugias y horarios de cirugias
    {
        return $this->hasMany(SurgerieSchedule::class);
    }

    public function medical_record() 
    {
        return $this->hasOne(MedicalRecord::class, "surgerie_id"); // relacion uno a uno entre cirugia y registro medico
    }

    public function scopeFilterMultiple($query, $type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)
    {
        if ($type_date && $start_date && $end_date) {
            if ($type_date == 1) { // Fecha de Cita
                $query->whereBetween("surgerie_date", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
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

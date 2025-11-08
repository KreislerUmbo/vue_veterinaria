<?php

namespace App\Models\Vaccination;

use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        "vaccination_id",
        "veterinarie_schedule_hour_id",
        "hour",
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

    // relacion con la tabla de vacunaciones
    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class, "vaccination_id"); // una programacion de vacunacion pertenece a una vacunacion
    }

    // relacion con la tabla de horas de agenda de veterinarios
    public function schedule_hour()
    {
        return $this->belongsTo(VeterinarieScheduleHour::class, "veterinarie_schedule_hour_id"); // una programacion de vacunacion pertenece a una hora de agenda de veterinario
    }
}

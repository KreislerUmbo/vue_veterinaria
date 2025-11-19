<?php

namespace App\Models\Surgerie;

use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgerieSchedule extends Model
{
   use HasFactory;
   
    protected $fillable = [
        "surgerie_id",
        "veterinarie_shedule_hour_id",
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
    public function surgerie()
    {
        return $this->belongsTo(Surgerie::class, "surgerie_id");
    }
    public function schedule_hour()// relacion  la tabla de horarios con la tabla de horarios de cirugias
    {
        return $this->belongsTo(VeterinarieScheduleHour::class, "veterinarie_shedule_hour_id");
    }
}

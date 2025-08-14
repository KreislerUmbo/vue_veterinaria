<?php

namespace App\Models\Appointment;

use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        "appointment_id",
        "veterinarie_schedule_hour_id",
    ];
    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_get("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, "appointment_id");
    }
    public function schedule_hour()
    {
        return $this->belongsTo(VeterinarieScheduleHour::class, "veterinarie_schedule_hour_id");
    }
}

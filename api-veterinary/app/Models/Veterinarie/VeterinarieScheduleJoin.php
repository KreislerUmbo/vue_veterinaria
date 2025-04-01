<?php

namespace App\Models\Veterinarie;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinarieScheduleJoin extends Model
{
    use HasFactory;
    protected $fillable = [
        "veterinarie_schedule_day_id",
        "veterinarie_schedule_hour_id",
    ];
    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set('America/Lima');
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttributse($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
    public function veterinarie_schedule_day(){
        return $this->belongsTo(VeterinarieScheduleDay::class,"veterinarie_schedule_day_id");
    }
    public function veterinarie_schedule_hour(){
        return $this->belongsTo(VeterinarieScheduleHour::class,"veterinarie_schedule_hour_id");
    }
}

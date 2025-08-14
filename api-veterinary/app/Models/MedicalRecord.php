<?php

namespace App\Models;

use App\Models\Appointment\Appointment;
use App\Models\Pets\Pet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        date_default_timezone_get("America/Lima");
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
  /*  public function veterinarie()
    {
        return $this->belongsTo(User::class, "veterinarie_id");
    }
    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class, "vaccination_id");
    }*/
}

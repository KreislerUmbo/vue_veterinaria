<?php

namespace App\Models\Appointment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "method_payment",
        "amount",
        "date_payment",
        "appointment_id",
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
}

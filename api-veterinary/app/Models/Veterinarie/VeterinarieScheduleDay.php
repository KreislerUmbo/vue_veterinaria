<?php

namespace App\Models\Veterinarie;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinarieScheduleDay extends Model
{
    use HasFactory;
    protected $fillable = [
        "veterinarie_id",
        "day"
    ];
    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set('America/Lima');
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }
    public function veterinarie(){
        return $this->belongsTo(User::class,"veterinarie_id");
    }
}

<?php

namespace App\Models\Surgerie;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurgeriePayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "surgerie_id",
        "method_payment",
        "date_payment",
        "amount",
        "notes",
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
    public function surgerie()
    {
        return $this->belongsTo(Surgerie::class, "surgerie_id");
    }
}

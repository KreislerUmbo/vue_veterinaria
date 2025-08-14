<?php

namespace App\Models\Pets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'specie',
        'breed',
        'dirth_date',
        'gender',
        'color',
        'weight',
        'photo',
        'medical_notes',
        'owner_id',

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

    public function owner() // mascota relacionado con un  dueño, 
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}

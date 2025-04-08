<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'city_id'];
    public function cities()
    {
        return $this->belongsTo(City::class);
    }
}

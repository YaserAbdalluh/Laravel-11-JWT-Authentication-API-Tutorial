<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categroy extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image'];

    public function profession()
    {
        return $this->hasMany(Profession::class);
    }
}

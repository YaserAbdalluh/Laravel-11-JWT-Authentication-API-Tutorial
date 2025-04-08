<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profession extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'category_id', 'description'];
    public function categroys()
    {
        return $this->belongsTo(Categroy::class);
    }
    public function workers()
    {
        return $this->belongsTo(Worker::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}
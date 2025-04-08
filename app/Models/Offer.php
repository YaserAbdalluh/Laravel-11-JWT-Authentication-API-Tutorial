<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'profession_id',
        'worker_id',
        'order_date',
        'price',
        'status'
    ];

    public function profession()
    {
        return $this->belongsTo(Profession::class, 'profession_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'offer_id');
    }
}
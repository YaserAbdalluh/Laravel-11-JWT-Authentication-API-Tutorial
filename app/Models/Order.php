<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'worker_id',
        'order_date',
        'price',
        'status'
    ];
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }


    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

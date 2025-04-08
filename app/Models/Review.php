<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'worker_id',
        'order_id',
        'rating',
        'comment'
    ];
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

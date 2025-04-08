<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;
    protected $fillable = ['admin_id', 'can_manage_users', 'can_manage_offers', 'can_manage_orders', 'can_manage_reviews', 'can_manage_notifications'];
    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'role_id', 'description'];
    public function roles()
    {
        return $this->hasMany(Permission::class, 'role_id');
    }
}

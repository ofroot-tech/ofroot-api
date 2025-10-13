<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'is_system'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('tenant_id');
    }
}

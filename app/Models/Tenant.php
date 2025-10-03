<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'plan',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * A tenant has many users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Placeholder for other relationships
     * e.g., leads(), projects(), etc.
     */
    // public function leads() { return $this->hasMany(Lead::class); }
}

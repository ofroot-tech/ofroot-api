<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'zip',
        'service',
        'name',
        'phone',
        'email',
        'source',
        'status',
        'meta',
    ];

    /**
     * Attribute casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Relationships
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Query scopes
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeZip($query, string $zip)
    {
        return $query->where('zip', $zip);
    }
}

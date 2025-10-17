<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanityRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'source', 'destination', 'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}

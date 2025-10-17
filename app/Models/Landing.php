<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landing extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title', 'subheadline', 'features', 'theme', 'cta_label', 'cta_href', 'variants', 'seo', 'canonical'
    ];

    protected $casts = [
        'features' => 'array',
        'theme' => 'array',
        'variants' => 'array',
        'seo' => 'array',
    ];
}

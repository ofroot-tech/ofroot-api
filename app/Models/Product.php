<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'kind', 'name', 'hero_title', 'hero_subtitle', 'anchor_price', 'includes', 'default_plan'
    ];

    protected $casts = [
        'includes' => 'array',
    ];

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\Product;

class PublicConfigController extends Controller
{
    public function landings()
    {
        return response()->json(Landing::orderBy('slug')->get());
    }

    public function products()
    {
        return response()->json(Product::with('pricingRules')->orderBy('slug')->get());
    }
}

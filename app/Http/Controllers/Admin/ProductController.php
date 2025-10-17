<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::with('pricingRules')->orderBy('slug')->get());
    }

    public function show(Product $product)
    {
        return response()->json($product->load('pricingRules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string|unique:products,slug',
            'kind' => 'required|string|in:product,service',
            'name' => 'required|string',
            'hero_title' => 'nullable|string',
            'hero_subtitle' => 'nullable|string',
            'anchor_price' => 'nullable|string',
            'includes' => 'nullable|array',
            'default_plan' => 'nullable|string|in:pro,business',
        ]);
        $product = Product::create($data);
        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'kind' => 'sometimes|required|string|in:product,service',
            'name' => 'sometimes|required|string',
            'hero_title' => 'nullable|string',
            'hero_subtitle' => 'nullable|string',
            'anchor_price' => 'nullable|string',
            'includes' => 'nullable|array',
            'default_plan' => 'nullable|string|in:pro,business',
        ]);
        $product->update($data);
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['ok' => true]);
    }
}

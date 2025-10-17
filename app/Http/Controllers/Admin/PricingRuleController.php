<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{
    public function index()
    {
        return response()->json(PricingRule::orderBy('priority')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'plan' => 'required|string|in:pro,business',
            'monthly' => 'nullable|string',
            'yearly' => 'nullable|string',
            'segment' => 'nullable|string',
            'priority' => 'nullable|integer',
        ]);
        $rule = PricingRule::create($data);
        return response()->json($rule, 201);
    }

    public function show(PricingRule $pricingRule)
    {
        return response()->json($pricingRule);
    }

    public function update(Request $request, PricingRule $pricingRule)
    {
        $data = $request->validate([
            'plan' => 'sometimes|required|string|in:pro,business',
            'monthly' => 'nullable|string',
            'yearly' => 'nullable|string',
            'segment' => 'nullable|string',
            'priority' => 'nullable|integer',
        ]);
        $pricingRule->update($data);
        return response()->json($pricingRule);
    }

    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();
        return response()->json(['ok' => true]);
    }
}

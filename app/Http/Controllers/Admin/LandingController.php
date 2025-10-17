<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Landing;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return response()->json(Landing::orderBy('slug')->get());
    }

    public function show(Landing $landing)
    {
        return response()->json($landing);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string|unique:landings,slug',
            'title' => 'required|string',
            'subheadline' => 'nullable|string',
            'features' => 'nullable|array',
            'theme' => 'nullable|array',
            'cta_label' => 'nullable|string',
            'cta_href' => 'nullable|string',
            'variants' => 'nullable|array',
            'seo' => 'nullable|array',
            'canonical' => 'nullable|string',
        ]);
        $landing = Landing::create($data);
        return response()->json($landing, 201);
    }

    public function update(Request $request, Landing $landing)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'subheadline' => 'nullable|string',
            'features' => 'nullable|array',
            'theme' => 'nullable|array',
            'cta_label' => 'nullable|string',
            'cta_href' => 'nullable|string',
            'variants' => 'nullable|array',
            'seo' => 'nullable|array',
            'canonical' => 'nullable|string',
        ]);
        $landing->update($data);
        return response()->json($landing);
    }

    public function destroy(Landing $landing)
    {
        $landing->delete();
        return response()->json(['ok' => true]);
    }
}

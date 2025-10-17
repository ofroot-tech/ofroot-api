<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VanityRoute;
use Illuminate\Http\Request;

class VanityRouteController extends Controller
{
    public function index()
    {
        return response()->json(VanityRoute::orderBy('source')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source' => 'required|string|unique:vanity_routes,source',
            'destination' => 'required|string',
            'active' => 'nullable|boolean',
        ]);
        $route = VanityRoute::create($data);
        return response()->json($route, 201);
    }

    public function show(VanityRoute $vanityRoute)
    {
        return response()->json($vanityRoute);
    }

    public function update(Request $request, VanityRoute $vanityRoute)
    {
        $data = $request->validate([
            'destination' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);
        $vanityRoute->update($data);
        return response()->json($vanityRoute);
    }

    public function destroy(VanityRoute $vanityRoute)
    {
        $vanityRoute->delete();
        return response()->json(['ok' => true]);
    }
}

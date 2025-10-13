<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use Illuminate\Http\Request;

class DocController extends Controller
{
    // List docs
    public function index()
    {
        $docs = Doc::query()->select(['slug','title','updated_at'])->orderBy('slug')->get();
        return response()->json(['ok' => true, 'data' => ['items' => $docs]]);
    }

    // Show one
    public function show(string $slug)
    {
        $doc = Doc::where('slug',$slug)->first();
        if (!$doc) return response()->json(['ok'=>false,'error'=>['message'=>'Not found']],404);
        return response()->json(['ok'=>true,'data'=>$doc]);
    }

    // Create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required','regex:/^[a-z0-9-_]+$/i','unique:docs,slug'],
            'title' => ['required','string','max:255'],
            'body' => ['required','string'],
        ]);
        $doc = Doc::create($validated);
        return response()->json(['ok'=>true,'data'=>['slug'=>$doc->slug]],201);
    }

    // Update
    public function update(Request $request, string $slug)
    {
        $doc = Doc::where('slug',$slug)->first();
        if (!$doc) return response()->json(['ok'=>false,'error'=>['message'=>'Not found']],404);
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'body' => ['required','string'],
        ]);
        $doc->update($validated);
        return response()->json(['ok'=>true,'data'=>['slug'=>$doc->slug]]);
    }

    // Delete
    public function destroy(string $slug)
    {
        $doc = Doc::where('slug',$slug)->first();
        if (!$doc) return response()->json(['ok'=>false,'error'=>['message'=>'Not found']],404);
        $doc->delete();
        return response()->json(['ok'=>true,'data'=>['slug'=>$slug]]);
    }
}

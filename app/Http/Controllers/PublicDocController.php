<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use Illuminate\Http\Request;

class PublicDocController extends Controller
{
    // Public index of docs (read-only)
    public function index()
    {
        $items = Doc::query()->select(['slug','title','updated_at'])->orderBy('slug')->get();
        return response()->json(['ok'=>true,'data'=>['items'=>$items]]);
    }

    // Public fetch of a single doc
    public function show(string $slug)
    {
        $doc = Doc::where('slug',$slug)->first();
        if (!$doc) return response()->json(['ok'=>false,'error'=>['message'=>'Not found']],404);
        return response()->json(['ok'=>true,'data'=>['slug'=>$doc->slug,'title'=>$doc->title,'body'=>$doc->body]]);
    }
}

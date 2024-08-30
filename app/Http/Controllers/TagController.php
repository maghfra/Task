<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        if ($tags->isEmpty()){
            return response()->json(['message'=>'No tags found'],404);
        }
        return response()->json(['message'=>'All tags retrieved successfully','tags'=>$tags],200);
    }

    public function store(Request $request)
    {
        $request->validate(['name'=>'required|string|unique:tags,name|max:255']);
        $tag = Tag::create($request->all());

        return response()->json(['message'=>'Tag created successfully','data'=>$tag],201);
    }

    public function show($id)
    {
        $tag = Tag::find($id);

        if(!$tag){
            return response()->json(['message'=>'tag not found'],404);
        }

        return response()->json(['message'=>'tag retrived successfully','data'=>$tag], 200);
    }

    public function update(Request $request, $id)

    {
        $request->validate(['name'=>'required|string|unique:tags,name|max:255']);

        $tag = Tag::find($id);

        if(!$tag){
            return response()->json(['message'=>'tag not found'],404);
        }

        $tag->update($request->all());
        return response()->json(['message'=>'Tag updated successfully','data'=>$tag],201);
    }

    public function destroy($id)
    {
        $tag = Tag::find($id);

        if(!$tag){
            return response()->json(['message'=>'tag not found'],404);
        }

        $tag->delete();
        
        return response()->json(['message'=>'Tag deleted successfully'],200);
    }
}

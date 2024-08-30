<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('user_id',Auth::id())
                      ->with('tags')
                      ->orderBy('pinned', 'desc')
                      ->get();

        if ($posts->isEmpty()){
            return response()->json(['message'=>'No posts found'],404);
        }   

        return response()->json(['message' => 'User posts retrieved successfully', 'posts' => $posts], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'required|image',
            'pinned' => 'required|boolean',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $validated['user_id'] = Auth::id();
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('cover_images');
        }

        $post = Post::create($validated);
        $post->tags()->attach($validated['tags']);
        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }

    public function show($id)
    {
        $post = Post::find($id);

        if(!$post){
            return response()->json(['message' => 'post not found'], 404);
        }

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['message' => 'Post retrieved successfully', 'post' => $post], 200);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if(!$post){
            return response()->json(['message' => 'post not found'], 404);
        }

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validation logic remains the same
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'nullable|image',
            'pinned' => 'required|boolean',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('cover_images');
        }

        $post->update($validated);
        $post->tags()->sync($validated['tags']);

        return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if(!$post){
            return response()->json(['message' => 'post not found'], 404);
        }
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();
        return response()->json(['message' => 'Post softly deleted'], 200);
    }

    public function deleted()
    {
        $deleted_posts = Post::where('user_id',Auth::id())
                            ->onlyTrashed()->with('tags')->get();
        return response()->json(['message' => 'Deleted posts retrieved successfully', 'deleted_posts' => $deleted_posts], 200);

    }

    public function restore($id)
    {
        $post = Post::where('user_id',Auth::id())
                    ->onlyTrashed()->with('tags')->findOrFail($id);
        $post->restore();
        return response()->json(['message' => 'Post restored successfully', 'post' => $post], 200);
    }
}

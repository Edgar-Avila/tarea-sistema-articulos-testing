<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Comment::class);
        return Comment::all();
    }

    public function myComments()
    {
        return request()->user()->comments;
    }

    public function byPost(Post $post)
    {
        return $post->comments;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request)
    {
        $data = $request->validated();
        $post = Post::findOrFail($data['post_id']);
        $data['user_id'] = $request->user()->id;
        $comment = $post->comments()->create($data);
        return $comment;
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        Gate::authorize('view', $comment);
        return $comment;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $data = $request->validated();
        $comment->update($data);
        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);
        $ok = $comment->delete();
        if ($ok) {
            return response(['success' => true], 200);
        }
        return response(['success' => false], 500);
    }
}

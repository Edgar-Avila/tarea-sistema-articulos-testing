<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    // Posts
    Route::get('/posts/my-posts', [PostController::class, 'myPosts'])->name('posts.my-posts');
    Route::resource('posts', PostController::class)->except(['create', 'edit']);
    // Comments
    Route::get('/comments/my-comments', [CommentController::class, 'myComments'])->name('comments.my-comments');
    Route::get('/comments/by-post/{post}', [CommentController::class, 'byPost'])->name('comments.by-post');
    Route::resource('comments', CommentController::class)->except(['create', 'edit']);
});

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Events\PostUpdated;
use App\Http\Resources\MessageResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found!']);
        }
        if ($post->user_id != Auth::user()->id) {
            $message = $post->messages()->create([
                'text' => $request->text,
                'user_id' => Auth::user()->id,
            ]);
        } else {
            $message = $post->messages()->create([
                'text' => $request->text,
                'admin_id' => Auth::user()->id,
            ]);
        }
        broadcast(new MessageCreated($message))->toOthers();
        return new MessageResource($message);
    }

    public function getMessages(Post $post)
    {
        if (!$post) {
            return response()->json(['message' => 'Post not found!']);
        }
        return MessageResource::collection($post->messages);
    }
}

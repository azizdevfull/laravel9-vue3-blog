<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
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
        $message = $post->messages()->create([
            'text' => $request->text,
        ]);

        broadcast(new NewMessage($message))->toOthers();

        return new MessageResource($message);
    }




    public function getMessages(Post $post)
    {
        // dd($post);
        if (!$post) {
            return response()->json(['message' => 'Post not found!']);
        }
        return MessageResource::collection($post->messages);
    }
}

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

        $recipientId = $post->user_id;

        // Check if the authenticated user is the post author
        if ($post->user_id === Auth::id()) {
            // If the post author is sending the message, set the recipient ID to the recipient of the message
            $recipientId = $post->user_id;
        }

        $message = $post->messages()->create([
            'text' => $request->text,
            'user_id' => Auth::id(), // Sender ID
            'to' => $recipientId, // Recipient ID
            'from' => Auth::id(), // Sender ID
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

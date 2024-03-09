<?php

namespace App\Services;

use App\Interfaces\MessageServiceInterface;
use App\Events\NewMessage;
use App\Http\Resources\MessageResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageService implements MessageServiceInterface
{
    public function createMessage($postId, $text)
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found!'], 404);
        }

        $userId = Auth::id();

        $message = $post->messages()->create([
            'text' => $text,
            'user_id' => $userId,
        ]);

        broadcast(new NewMessage($message))->toOthers();

        return new MessageResource($message);
    }

    public function getMessages(Post $post)
    {
        if (!$post) {
            return response()->json(['message' => 'Post not found!'], 404);
        }

        return MessageResource::collection($post->messages);
    }
}

<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Http\Resources\MessageResource;
use App\Models\Post;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct(protected MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function store(Request $request, $id)
    {
        return $this->messageService->createMessage($id, $request->text);
    }

    public function getMessages(Post $post)
    {
        return $this->messageService->getMessages($post);
    }
}

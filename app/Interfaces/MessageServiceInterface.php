<?php

namespace App\Interfaces;

use App\Models\Post;


interface MessageServiceInterface
{
    public function createMessage($postId, $text);
    public function getMessages(Post $post);
}

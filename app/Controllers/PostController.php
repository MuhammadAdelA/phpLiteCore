<?php

namespace App\Controllers;

use PhpLiteCore\Http\Response;
use PhpLiteCore\View\View;

class PostController extends BaseController
{
    /**
     * Show a single post by its ID.
     *
     * @param string|int $id The ID from the URL.
     * @return void
     */
    public function show($id): void
    {
        // Find the post in the database using the provided ID.
        $post = $this->app->db->table('posts')->where('id', '=', $id)->first();

        // If no post is found, return a 404 error.
        if (!$post) {
            Response::notFound("Post with ID {$id} not found.");
            return;
        }

        // We need a view file for this: views/themes/default/post.php
        $this->view('post', [
            'pageTitle' => $post['title'],
            'post' => $post
        ]);
    }
}
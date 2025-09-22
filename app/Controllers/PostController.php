<?php

namespace App\Controllers;

// Make sure to import Paginator and Bootstrap5Renderer
use Exception;
use PhpLiteCore\Pagination\Paginator;
use PhpLiteCore\Pagination\Renderers\Bootstrap5Renderer;
use PhpLiteCore\Http\Response;
use PhpLiteCore\View\View;

class PostController extends BaseController
{
    /**
     * Display a list of all posts with pagination.
     * @throws Exception
     */
    public function index(): void
    {
        $itemsPerPage = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // A single, fluent line to get paginated results!
        $paginationData = $this->app->db->table('posts')
            ->orderBy('created_at', 'DESC')
            ->paginate($itemsPerPage, $currentPage);

        $renderer = new Bootstrap5Renderer();
        $paginationLinks = $renderer->render($paginationData['paginator']);

        $this->view('posts', [
            'pageTitle'       => 'All Posts',
            'posts'           => $paginationData['items'],
            'paginationLinks' => $paginationLinks
        ]);
    }
    /**
     * Show a single post by its ID.
     *
     * @param int|string $id The ID from the URL.
     * @return void
     * @throws Exception
     */
    public function show(int|string $id): void
    {
        // Find the post in the database using the provided ID.
        $post = $this->app->db->table('posts')->where('id', '=', $id)->first();

        // If no post is found, return a 404 error.
        if (!$post) {
            Response::notFound("Post with ID {$id} not found.");
        }

        // We need a view file for this: views/themes/default/post.php
        $this->view('post', [
            'pageTitle' => $post['title'],
            'post' => $post
        ]);
    }
}
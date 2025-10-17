<?php

namespace App\Controllers;

use Exception;
use PhpLiteCore\Pagination\Renderers\Bootstrap5Renderer;
use PhpLiteCore\Http\Response;
use PhpLiteCore\Validation\Exceptions\ValidationException;
use PhpLiteCore\Validation\Validator;

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
            'pageTitle' => $post->title,
            'post' => $post
        ]);
    }

    /**
     * Store a new post in the database.
     */
    public function store(): void
    {
        try {
            // 1. Define the validation rules.
            $rules = [
                'title' => 'required|min:5',
                'body'  => 'required|min:10',
            ];

            // 2. Run the validator. It will throw an exception on failure.
            //    We assume the data comes from a POST request, e.g., $_POST.
            $validatedData = Validator::validate($_POST, $rules);

            // 3. If validation passes, create the post.
            //    For this example, let's assume user_id is 1.
            $this->app->db->table('posts')->insert([
                'title'   => $validatedData['title'],
                'body'    => $validatedData['body'],
                'user_id' => 1,
            ]);

            // 4. Redirect to the posts list (or the new post's page).
            Response::redirect('/posts');

        } catch (ValidationException $e) {
            // 5. If validation fails, handle the errors.
            //    For an API, you might return JSON.
            //    For a web page, you would typically redirect back with errors.
            //    For now, we'll just dump the errors.
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['errors' => $e->getErrors()]);
        }
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): void
    {
        $this->view('create-post', [
            'pageTitle' => 'Create New Post'
        ]);
    }

}
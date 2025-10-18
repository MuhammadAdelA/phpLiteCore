<?php

namespace App\Controllers;

use App\Models\Post; // Import the Post model
use Exception;
use PhpLiteCore\Pagination\Renderers\Bootstrap5Renderer;
use PhpLiteCore\Http\Response;
use PhpLiteCore\Validation\Exceptions\ValidationException;
use PhpLiteCore\Validation\Validator;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;

class PostController extends BaseController
{
    /**
     * Display a list of all posts with pagination.
     * (Compliant with Active Record and Translation)
     *
     * @return void
     * @throws ViewNotFoundException
     * @throws Exception
     */
    public function index(): void
    {
        $itemsPerPage = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // 1. Use Active Record (Constitution Sec 2)
        $paginationData = Post::orderBy('created_at', 'DESC')
            ->paginate($itemsPerPage, $currentPage);

        $renderer = new Bootstrap5Renderer();
        $paginationLinks = $renderer->render($paginationData['paginator']);

        // 2. Prepare translated variables (Constitution Sec 1.5)
        $pageTitle = $this->app->translator->get('messages.posts.index_title');
        $noPostsText = $this->app->translator->get('messages.posts.no_posts');
        $publishedOnText = $this->app->translator->get('messages.posts.published_on');

        // 3. Render the view with translated data
        $this->view('posts', [
            'pageTitle'       => $pageTitle,
            'posts'           => $paginationData['items'],
            'paginationLinks' => $paginationLinks,
            'noPostsText'     => $noPostsText,
            'publishedOnText' => $publishedOnText,
        ]);
    }

    /**
     * Show a single post by its ID.
     * (Compliant with Active Record, Translation, URL Decoding)
     *
     * @param int|string $id The ID from the URL (URL-encoded).
     * @return void
     * @throws ViewNotFoundException
     * @throws Exception
     */
    public function show(int|string $id): void
    {
        // 1. Decode ID (Constitution Sec 1.3 - Logic)
        $decodedId = urldecode($id);

        // 2. Find post using Active Record (Constitution Sec 2)
        $post = Post::find($decodedId);

        // 3. Handle not found (Constitution Sec 1.5 & Response logic)
        if (!$post) {
            $notFoundMessage = $this->app->translator->get('messages.posts.not_found', ['id' => $decodedId]);
            Response::notFound($notFoundMessage);
            return;
        }

        // 4. Prepare translated variables (Constitution Sec 1.5)
        $publishedOnText = $this->app->translator->get('messages.posts.published_on');
        $backLinkText = $this->app->translator->get('messages.posts.back_link');
        $editButtonText = $this->app->translator->get('messages.posts.edit_button'); // Added

        // 5. Render the view (Constitution Sec 2 - MVC & Sec 1.6.1)
        $this->view('post', [
            'pageTitle'       => $post->title,
            'post'            => $post,
            'publishedOnText' => $publishedOnText,
            'backLinkText'    => $backLinkText,
            'editButtonText'  => $editButtonText, // Passed
        ]);
    }

    /**
     * Show the form for editing an existing post.
     * (Compliant with Active Record, Translation, URL Decoding)
     * THIS METHOD WAS MISSING IN THE PREVIOUS INTERNAL STATE UPDATE
     *
     * @param int|string $id The ID from the URL (URL-encoded).
     * @return void
     * @throws ViewNotFoundException
     * @throws Exception
     */
    public function edit(int|string $id): void // <<< THIS IS THE METHOD
    {
        // 1. Decode ID
        $decodedId = urldecode($id);

        // 2. Find post
        $post = Post::find($decodedId);

        // 3. Handle not found
        if (!$post) {
            $notFoundMessage = $this->app->translator->get('messages.posts.not_found', ['id' => $decodedId]);
            Response::notFound($notFoundMessage);
            return;
        }

        // 4. Prepare translated variables
        $pageTitle = $this->app->translator->get('messages.posts.edit_title', ['title' => $post->title]);
        $formTitle = $this->app->translator->get('messages.posts.form_title');
        $formContent = $this->app->translator->get('messages.posts.form_content');
        $updateButton = $this->app->translator->get('messages.posts.update_button');
        $cancelButton = $this->app->translator->get('messages.posts.cancel_button');

        // 5. Render the edit view
        $this->view('edit-post', [
            'pageTitle'    => $pageTitle,
            'post'         => $post,
            'formTitle'    => $formTitle,
            'formContent'  => $formContent,
            'updateButton' => $updateButton,
            'cancelButton' => $cancelButton,
        ]);
    }

    /**
     * Update an existing post in the database.
     * (Compliant with Active Record, Validation, Translation, URL Decoding)
     *
     * @param int|string $id The ID from the URL (URL-encoded).
     * @return void
     */
    public function update(int|string $id): void
    {
        // 1. Decode ID
        $decodedId = urldecode($id);

        // 2. Find the post
        $post = Post::find($decodedId);

        // 3. Handle not found
        if (!$post) {
            $notFoundMessage = $this->app->translator->get('messages.posts.not_found', ['id' => $decodedId]);
            Response::notFound($notFoundMessage);
            return;
        }

        try {
            // 4. Define validation rules
            $rules = [
                'title' => 'required|min:5',
                'body'  => 'required|min:10',
            ];

            // 5. Validate input
            $validatedData = Validator::validate($_POST, $rules);

            // 6. Update the Post object properties
            $post->title = $validatedData['title'];
            $post->body = $validatedData['body'];

            // 7. Save the changes (executes UPDATE)
            $post->save();

            // 8. Redirect back to the post's show page
            Response::redirect('/posts/' . $post->id);

        } catch (ValidationException $e) {
            // 9. Handle validation errors (return JSON for now)
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['errors' => $e->getErrors()]);
            // Ideally: return $this->view('edit-post', [ ... pass old input and errors ... ]);
        } catch (\Exception $e) {
            // Handle other potential errors during save
            error_log("Error updating post {$decodedId}: " . $e->getMessage());
            $errorTitle = $this->app->translator->get('messages.error_500_title');
            $errorMessage = $this->app->translator->get('messages.error_500_message');
            $homeLinkText = $this->app->translator->get('messages.home_link_text');
            render_http_error_page(500, $errorTitle, $errorMessage, $homeLinkText);
        }
    }

    /**
     * Store a new post in the database.
     *
     * @return void
     */
    public function store(): void
    {
        try {
            // 1. Define the validation rules.
            $rules = [
                'title' => 'required|min:5',
                'body'  => 'required|min:10',
            ];

            // 2. Run the validator.
            $validatedData = Validator::validate($_POST, $rules);

            // 3. Create the post using Active Record
            $post = new Post([
                'title'   => $validatedData['title'],
                'body'    => $validatedData['body'],
                'user_id' => 1, // Assuming user ID 1
            ]);
            $post->save();

            // 4. Redirect to the posts list.
            Response::redirect('/posts');

        } catch (ValidationException $e) {
            // 5. Handle validation errors
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['errors' => $e->getErrors()]);
        }
    }

    /**
     * Show the form for creating a new post.
     *
     * @return void
     * @throws ViewNotFoundException
     */
    public function create(): void
    {
        // 1. Prepare translated variables
        $pageTitle = $this->app->translator->get('messages.posts.create_title');
        $formTitle = $this->app->translator->get('messages.posts.form_title');
        $formContent = $this->app->translator->get('messages.posts.form_content');
        $createButton = $this->app->translator->get('messages.posts.create_button');
        $cancelButton = $this->app->translator->get('messages.posts.cancel_button');

        // 2. Render the view
        $this->view('create-post', [
            'pageTitle'    => $pageTitle,
            'formTitle'    => $formTitle,
            'formContent'  => $formContent,
            'createButton' => $createButton,
            'cancelButton' => $cancelButton,
        ]);
    }
}
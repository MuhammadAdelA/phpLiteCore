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
     * (Compliant with Active Record and Translation)
     *
     * @param int|string $id The ID from the URL.
     * @return void
     * @throws Exception
     */
    public function show(int|string $id): void
    {
        // 1. Use Active Record (Constitution Sec 2)
        $post = Post::find($id);

        // 2. Handle not found with translated message (Constitution Sec 1.5)
        if (!$post) {
            $notFoundMessage = $this->app->translator->get('messages.posts.not_found', ['id' => $id]);
            Response::notFound($notFoundMessage);
        }

        // 3. Prepare translated variables
        $publishedOnText = $this->app->translator->get('messages.posts.published_on');
        $backLinkText = $this->app->translator->get('messages.posts.back_link');

        // 4. Render the view
        $this->view('post', [
            'pageTitle'       => $post->title, // Page title is data, not UI text
            'post'            => $post,
            'publishedOnText' => $publishedOnText,
            'backLinkText'    => $backLinkText,
        ]);
    }

    /**
     * Store a new post in the database.
     * (This implementation was already compliant as Validator handles errors)
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
            // (This now uses the fixed Validator, so errors will be translated)
            $validatedData = Validator::validate($_POST, $rules);

            // 3. Create the post using Active Record (Constitution Sec 2)
            $post = new Post([
                'title'   => $validatedData['title'],
                'body'    => $validatedData['body'],
                'user_id' => 1, // Assuming user ID 1 for now
            ]);
            $post->save();

            // 4. Redirect to the posts list.
            Response::redirect('/posts');

        } catch (ValidationException $e) {
            // 5. If validation fails, handle the errors.
            http_response_code(422);
            header('Content-Type: application/json');
            // Errors are already translated by the Validator
            echo json_encode(['errors' => $e->getErrors()]);
        }
    }

    /**
     * Show the form for creating a new post.
     * (Compliant with Translation)
     * @throws ViewNotFoundException
     */
    public function create(): void
    {
        // 1. Prepare translated variables (Constitution Sec 1.5)
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
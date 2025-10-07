<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\User;
use PhpLiteCore\Http\Response;
use PhpLiteCore\Pagination\Paginator;
use PhpLiteCore\Pagination\Renderers\Bootstrap5Renderer;

class TestController extends BaseController
{
    /**
     * Run a series of tests on the database layer and Active Record implementation.
     */
    public function runDbTests(): void
    {
        // Start output with a clear header
        echo "<pre>";
        echo "<h1>phpLiteCore Database Layer Test</h1>";
        echo "<hr>";

        // --- Run the Tests ---

        // Test 1: Fetch all users using all()
        echo "<h2>1. Testing `User::all()`</h2>";
        $users = User::all();
        echo "Found " . count($users) . " users.\n";
        print_r($users);
        echo "<hr>";

        // Test 2: Find a specific user using find()
        echo "<h2>2. Testing `User::find(1)`</h2>";
        $user = User::find(1);
        if ($user) {
            echo "Found user: " . $user->name . " (" . $user->email . ")\n";
        } else {
            echo "User with ID 1 not found.\n";
        }
        echo "<hr>";

        // Test 3: Find a user with a where() clause
        echo "<h2>3. Testing `User::where('email', '=', 'ahmed@example.com')->first()`</h2>";
        $ahmed = User::where('email', '=', 'ahmed@example.com')->first();
        if ($ahmed) {
            echo "Found user via where clause: " . $ahmed->name . "\n";
        } else {
            echo "User with email ahmed@example.com not found.\n";
        }
        echo "<hr>";

        // Test 4: Create a new post (INSERT)
        echo "<h2>4. Testing Create (new Post)->save()</h2>";
        $newPost = new Post([
            'title'   => 'A New Post from Test Controller',
            'body'    => 'This post was created automatically by TestController.',
            'user_id' => 1
        ]);
        $success = $newPost->save();
        if ($success && $newPost->id) {
            echo "Successfully created a new post with ID: " . $newPost->id . "\n";
        } else {
            echo "Failed to create a new post.\n";
        }
        echo "<hr>";

        // Test 5: Update a post (UPDATE)
        echo "<h2>5. Testing Update (Post->save())</h2>";
        $idToUpdate = $newPost->id ?? null;
        if ($idToUpdate) {
            $postToUpdate = Post::find($idToUpdate);
            if ($postToUpdate) {
                $postToUpdate->title = 'Updated Post Title!';
                $updateSuccess = $postToUpdate->save();
                if ($updateSuccess) {
                    echo "Successfully updated post with ID: " . $postToUpdate->id . "\n";
                    echo "New title: " . $postToUpdate->title . "\n";
                } else {
                    echo "Failed to update post or no changes were made.\n";
                }
            } else {
                echo "Could not find post with ID {$idToUpdate} to update.\n";
            }
        } else {
            echo "Skipping update test because new post ID is not available.\n";
        }
        echo "<hr>";

        // Test 6: Delete a post (DELETE)
        echo "<h2>6. Testing `Post::where('id', ...)->delete()`</h2>";
        $idToDelete = $newPost->id ?? null;
        if ($idToDelete) {
            $affectedRows = Post::where('id', '=', $idToDelete)->delete();
            if ($affectedRows > 0) {
                echo "Successfully deleted post with ID: " . $idToDelete . "\n";
                $deletedPost = Post::find($idToDelete);
                if (!$deletedPost) {
                    echo "Verification successful: Post is no longer in the database.\n";
                }
            } else {
                echo "Failed to delete post with ID: " . $idToDelete . "\n";
            }
        } else {
            echo "Skipping delete test because new post ID is not available.\n";
        }
        echo "<hr>";

        // Test 7: Paginate posts
        echo "<h2>7. Testing `Post::paginate()`</h2>";
        $perPage = 3;
        $currentPage = 2;
        $paginationData = Post::orderBy('id', 'ASC')->paginate($perPage, $currentPage);
        $paginator = $paginationData['paginator'];
        $paginatedPosts = $paginationData['items'];

        echo "Paginating posts, {$perPage} per page. Showing page {$currentPage} of {$paginator->getTotalPages()}.\n\n";
        echo "Total posts found: " . $paginator->getTotalItems() . "\n\n";
        echo "Posts on this page:\n";
        print_r($paginatedPosts);
        echo "<hr>";

        echo "<h1>Test Complete!</h1>";
        echo "</pre>";
    }
}
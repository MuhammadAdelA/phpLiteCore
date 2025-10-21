<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\User;

class TestController extends BaseController
{
    /**
     * Run a series of tests on the database layer and Active Record implementation.
     */
    public function runDbTests(): void
    {
        // ... (Database test code remains the same) ...

        echo "<h1>Test Complete!</h1>";
        echo "</pre>";
    }

    // --- NEW: Session Test Methods ---

    /**
     * Test setting a session variable.
     * @param string $key
     * @param string $value
     */
    public function testSessionSet(string $key, string $value): void
    {
        $decodedKey = urldecode($key);
        $decodedValue = urldecode($value);
        $this->app->session->set($decodedKey, $decodedValue); // Use the session service
        echo "<pre>";
        echo "<h1>Session Test: Set Variable</h1><hr>";
        echo "Session variable set successfully!<br>";
        echo "Key: <code>" . htmlspecialchars($decodedKey) . "</code><br>";
        echo "Value: <code>" . htmlspecialchars($decodedValue) . "</code><br><br>";
        echo "Current Session Data:<br>";
        print_r($_SESSION);
        echo "</pre>";
    }

    /**
     * Test getting a session variable.
     * @param string $key
     */
    public function testSessionGet(string $key): void
    {
        $decodedKey = urldecode($key);
        echo "<pre>";
        echo "<h1>Session Test: Get Variable</h1><hr>";
        echo "Attempting to get session variable with key: <code>" . htmlspecialchars($decodedKey) . "</code><br>";
        if ($this->app->session->has($decodedKey)) { // Check if key exists
            $value = $this->app->session->get($decodedKey, 'Default Value'); // Get the value
            echo "Value found: <code>" . htmlspecialchars(print_r($value, true)) . "</code><br>";
        } else {
            echo "Key <code>" . htmlspecialchars($decodedKey) . "</code> not found in session.<br>";
        }
        echo "<br>Current Session Data:<br>";
        print_r($_SESSION);
        echo "</pre>";
    }

    /**
     * Test setting a flash message.
     * @param string $key
     * @param string $message
     */
    public function testSessionFlashSet(string $key, string $message): void
    {
        $decodedKey = urldecode($key);
        $decodedMessage = urldecode($message);
        $this->app->session->setFlash($decodedKey, $decodedMessage); // Use setFlash
        echo "<pre>";
        echo "<h1>Session Test: Set Flash Message</h1><hr>";
        echo "Flash message set successfully!<br>";
        echo "Key: <code>" . htmlspecialchars($decodedKey) . "</code><br>";
        echo "Message: <code>" . htmlspecialchars($decodedMessage) . "</code><br><br>";
        echo "Flash messages will be available on the *next* request.<br><br>";
        echo "Current Session Data:<br>";
        print_r($_SESSION);
        echo "</pre>";
    }

    /**
     * Test getting a flash message (it should be removed after getting).
     * @param string $key
     */
    public function testSessionFlashGet(string $key): void
    {
        $decodedKey = urldecode($key);
        echo "<pre>";
        echo "<h1>Session Test: Get Flash Message</h1><hr>";
        echo "Attempting to get flash message with key: <code>" . htmlspecialchars($decodedKey) . "</code><br>";
        if ($this->app->session->hasFlash($decodedKey)) { // Check first (optional)
            $message = $this->app->session->flash($decodedKey, 'Default Flash'); // Get and remove
            echo "Flash message found: <code>" . htmlspecialchars($message) . "</code><br>";
            echo "The flash message should now be removed.<br>";
        } else {
            echo "Flash message with key <code>" . htmlspecialchars($decodedKey) . "</code> not found (or already retrieved).<br>";
        }
        echo "<br>Current Session Data (after flash retrieval):<br>";
        print_r($_SESSION);
        echo "</pre>";
    }

    /**
     * Test destroying the session.
     */
    public function testSessionDestroy(): void
    {
        $this->app->session->destroy(); // Destroy the session
        echo "<pre>";
        echo "<h1>Session Test: Destroy Session</h1><hr>";
        echo "Session destroyed successfully!<br>";
        echo "Session cookie should also be deleted.<br><br>";
        echo "Current Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive/Destroyed') . "<br>";
        echo "Session Data (should be empty):<br>";
        print_r($_SESSION ?? '$_SESSION is unset');
        echo "</pre>";
    }

    // --- END NEW Session Test Methods ---
}
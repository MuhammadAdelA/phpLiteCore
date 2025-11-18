<?php

namespace App\Controllers;

use PhpLiteCore\Http\Request;
use PhpLiteCore\Http\Response;

/**
 * Example controller demonstrating the new HTTP Request/Response API.
 * This controller showcases various patterns for working with the enhanced abstractions.
 */
class ApiExampleController extends BaseController
{
    /**
     * Example: GET request with query parameters
     * Route: GET /api/users?page=1&search=john
     */
    public function listUsers(Request $request): void
    {
        $page = $request->query('page', 1);
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        
        // Simulate data retrieval
        $users = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];
        
        // Check if client expects JSON
        if ($request->expectsJson() || $request->isAjax()) {
            Response::json([
                'data' => $users,
                'page' => $page,
                'search' => $search,
                'limit' => $limit,
            ]);
        } else {
            $this->view('api/users', compact('users', 'page', 'search'));
        }
    }
    
    /**
     * Example: POST request with JSON body
     * Route: POST /api/users (with JSON body)
     */
    public function createUser(Request $request): void
    {
        // Verify POST method
        if (!$request->isPost()) {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }
        
        // Check content type
        if ($request->isJson()) {
            $data = $request->json();
        } else {
            $data = [
                'name' => $request->post('name'),
                'email' => $request->post('email'),
            ];
        }
        
        // Validate data
        if (empty($data['name']) || empty($data['email'])) {
            Response::json(['error' => 'Name and email are required'], 422);
            return;
        }
        
        // Simulate user creation
        $userId = rand(100, 999);
        
        // Return response with cookie
        $response = new Response();
        $response
            ->setStatusCode(201)
            ->setCookie('last_created_user', (string)$userId, time() + 3600)
            ->withJson([
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => 'created',
            ]);
        
        $response->send();
    }
    
    /**
     * Example: File upload handling
     * Route: POST /api/users/{id}/avatar
     */
    public function uploadAvatar(Request $request, int $id): void
    {
        // Check if file was uploaded
        if (!$request->hasFile('avatar')) {
            Response::json(['error' => 'No file uploaded'], 400);
            return;
        }
        
        $file = $request->file('avatar');
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            Response::json(['error' => 'Invalid file type'], 422);
            return;
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            Response::json(['error' => 'File too large'], 422);
            return;
        }
        
        // Simulate file processing
        $filename = 'avatar_' . $id . '_' . time() . '.jpg';
        
        Response::json([
            'filename' => $filename,
            'size' => $file['size'],
            'type' => $file['type'],
            'status' => 'uploaded',
        ], 201);
    }
    
    /**
     * Example: Headers and authentication
     * Route: GET /api/profile
     */
    public function getProfile(Request $request): void
    {
        // Check Authorization header
        if (!$request->hasHeader('Authorization')) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        $authHeader = $request->header('Authorization');
        
        // Simple token validation (in real app, validate against database)
        if (!str_starts_with($authHeader, 'Bearer ')) {
            Response::json(['error' => 'Invalid authorization format'], 401);
            return;
        }
        
        $token = substr($authHeader, 7);
        
        // Simulate profile data
        $profile = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'token_used' => substr($token, 0, 10) . '...',
        ];
        
        // Return with cache headers
        $response = new Response();
        $response
            ->cache(300) // Cache for 5 minutes
            ->withJson($profile);
        
        $response->send();
    }
    
    /**
     * Example: Cookies and sessions
     * Route: GET /api/preferences
     */
    public function getPreferences(Request $request): void
    {
        // Read cookies
        $theme = $request->cookie('theme', 'light');
        $language = $request->cookie('language', 'en');
        $sessionId = $request->cookie('session_id');
        
        $preferences = [
            'theme' => $theme,
            'language' => $language,
            'has_session' => !empty($sessionId),
        ];
        
        Response::json($preferences);
    }
    
    /**
     * Example: Update preferences with cookies
     * Route: POST /api/preferences
     */
    public function updatePreferences(Request $request): void
    {
        $theme = $request->post('theme', 'light');
        $language = $request->post('language', 'en');
        
        // Validate values
        $validThemes = ['light', 'dark'];
        $validLanguages = ['en', 'ar'];
        
        if (!in_array($theme, $validThemes) || !in_array($language, $validLanguages)) {
            Response::json(['error' => 'Invalid preferences'], 422);
            return;
        }
        
        // Set cookies and return response
        $response = new Response();
        $response
            ->setCookie('theme', $theme, time() + (86400 * 365)) // 1 year
            ->setCookie('language', $language, time() + (86400 * 365))
            ->withJson([
                'theme' => $theme,
                'language' => $language,
                'status' => 'updated',
            ]);
        
        $response->send();
    }
    
    /**
     * Example: Request information
     * Route: GET /api/request-info
     */
    public function requestInfo(Request $request): void
    {
        $info = [
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'url' => $request->url(),
            'is_secure' => $request->isSecure(),
            'is_ajax' => $request->isAjax(),
            'is_json' => $request->isJson(),
            'expects_json' => $request->expectsJson(),
            'client_ip' => $request->getClientIp(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->referer(),
            'query_params' => $request->queryAll(),
            'headers' => $request->headers(),
            'cookies' => $request->cookies(),
        ];
        
        Response::json($info);
    }
    
    /**
     * Example: Download file
     * Route: GET /api/download/report
     */
    public function downloadReport(Request $request): void
    {
        // In a real application, generate or retrieve the file
        $tempFile = tempnam(sys_get_temp_dir(), 'report_');
        file_put_contents($tempFile, "Sample Report\n\nGenerated at: " . date('Y-m-d H:i:s'));
        
        $response = new Response();
        try {
            $response->download($tempFile, 'report.txt');
            $response->send();
        } finally {
            // Clean up temp file after sending
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
    
    /**
     * Example: Method-based routing
     * Route: Multiple methods on same path
     */
    public function handleResource(Request $request, int $id): void
    {
        if ($request->isGet()) {
            Response::json(['id' => $id, 'action' => 'retrieve']);
        } elseif ($request->isPost()) {
            Response::json(['id' => $id, 'action' => 'create']);
        } elseif ($request->isPut()) {
            Response::json(['id' => $id, 'action' => 'update']);
        } elseif ($request->isDelete()) {
            Response::json(['id' => $id, 'action' => 'delete']);
        } else {
            Response::json(['error' => 'Method not allowed'], 405);
        }
    }
}

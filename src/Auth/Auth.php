<?php

declare(strict_types=1);

namespace PhpLiteCore\Auth;

use App\Models\User; // Assuming your User model is in App\Models
use PhpLiteCore\Database\Database; // Need access to the DB service for direct queries
use PhpLiteCore\Session\Session;

/**
 * Handles user authentication (login, logout, checking status).
 * Allows login via verified email, verified phone, or username.
 * Requires separate user_emails and user_phones tables.
 * Relies on the Session service and the User model.
 */
class Auth
{
    /** @var Session */
    protected Session $session;
    /** @var Database */
    protected Database $db; // Inject Database service
    /** @var User|null|false */
    protected User|null|false $user = null;
    /** @const string */
    private const SESSION_KEY = 'user_id';

    /**
     * Auth constructor.
     * Injects Session and Database dependencies.
     *
     * @param Session $session The session management service.
     * @param Database $db The database service.
     */
    public function __construct(Session $session, Database $db) // Inject Database
    {
        $this->session = $session;
        $this->db = $db; // Store Database service
    }

    /**
     * Attempt to authenticate a user using an identifier (email, username, or phone) and password.
     * Determines the lookup method based on identifier format.
     *
     * @param string $identifier The user's email, username, or phone number.
     * @param string $password The user's plain-text password.
     * @return bool True if authentication is successful, false otherwise.
     */
    public function attempt(string $identifier, string $password): bool
    {
        $userId = null;
        $user = null;

        // 1. Determine lookup type and find user ID
        if (str_contains($identifier, '@')) {
            // Assume Email
            // Query user_emails table directly using QueryBuilder from Database service
            // We also check if the email is verified (verified_at IS NOT NULL)
            $emailRecord = $this->db->table('user_emails')
                ->where('email', '=', $identifier)
                ->where('verified_at', 'IS NOT', null) // Ensure email is verified
                ->first(); // Fetch as stdClass object
            if ($emailRecord) {
                $userId = $emailRecord->user_id;
            }
        } elseif (preg_match('/^[0-9\-\+\(\)\s]+$/', $identifier)) { // Basic phone number check
            // Assume Phone (perform basic normalization if needed)
            $cleanedPhone = preg_replace('/[\-\+\(\)\s]/', '', $identifier);
            // Query user_phones table
            $phoneRecord = $this->db->table('user_phones')
                ->where('phone', '=', $cleanedPhone) // Use cleaned phone
                ->where('verified_at', 'IS NOT', null) // Ensure phone is verified
                ->first();
            if ($phoneRecord) {
                $userId = $phoneRecord->user_id;
            }
        } else {
            // Assume Username
            // Query users table directly using the User Model (Active Record)
            $userRecord = User::where('username', '=', $identifier)->first();
            if ($userRecord) {
                // If found directly by username, we already have the user object
                $user = $userRecord;
                $userId = $user->id; // Get ID for consistency check later if needed
            }
        }

        // 2. If a user ID was found via email or phone, fetch the full User object
        if ($userId !== null && $user === null) {
            $user = User::find($userId);
        }

        // 3. If no user object could be determined, authentication fails
        if (! $user) {
            return false;
        }

        // 4. Verify the password against the found user's hash
        if (password_verify($password, $user->password)) {
            // 5. Password matches. Log the user in.
            $this->login($user);

            return true;
        }

        // 6. Password does not match.
        return false;
    }

    // --- login, logout, check, id, user methods remain the same ---
    // --- (Make sure id() returns int|null) ---

    /**
     * Log in a specific user.
     * @param User $user
     * @return void
     */
    public function login(User $user): void
    {
        $this->session->regenerate(true);
        $this->session->set(self::SESSION_KEY, $user->id);
        $this->user = $user;
    }

    /**
     * Log the current user out.
     * @return void
     */
    public function logout(): void
    {
        $this->session->remove(self::SESSION_KEY);
        $this->session->destroy();
        $this->user = false;
    }

    /**
     * Check if a user is currently logged in.
     * @return bool
     */
    public function check(): bool
    {
        return $this->session->has(self::SESSION_KEY);
    }

    /**
     * Get the ID of the currently logged-in user.
     * @return int|null
     */
    public function id(): ?int
    {
        $result = $this->session->get(self::SESSION_KEY);

        return $result ? (int)$result : null;
    }

    /**
     * Get the currently authenticated User object.
     * @return User|null
     */
    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user ?: null;
        }
        $id = $this->id();
        if (! $id) {
            $this->user = false;

            return null;
        }
        $user = User::find($id);
        if (! $user) {
            $this->session->remove(self::SESSION_KEY);
            $this->user = false;

            return null;
        }
        $this->user = $user;

        return $this->user;
    }
}

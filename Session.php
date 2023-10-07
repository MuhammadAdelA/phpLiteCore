<?php

namespace MyApp;

use JetBrains\PhpStorm\NoReturn;

class Session extends Member
{
    /**
     * The current instance
     * @var Object $instance
     */
    public static object $instance;

    /**
     * Current session id
     * @var string
     */
    public string $session_id;

    /**
     * Current logged in member ID
     * @var string
     */
    public string $logged_id;

    /**
     * Member is logged in or not
     * @var bool
     */
    public bool $logged_in = false;

    function __construct()
    {
        session_start();
        parent::__construct();
        $this->is_logged_in();
        $this->save_session_token();
    }

    /**
     * Returning the current instance
     * @return Session
     */
    public static function get_instance(): object
    {
        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Check current session set
     * @return bool Logged in or not
     */
    public function is_logged_in(): bool
    {
        // Let's check if there is any login cookie
        if (isset($_SESSION['user_id']) || isset($_COOKIE['user_id'])) {
            // Store the member ID
            $this->logged_id = $_SESSION['user_id'] ?? $_COOKIE['user_id'];
            // Store current session ID
            $this->session_id = session_id();

            // Make sure we are logged in
            $this->logged_in = true;
        } else {
            // Make sure we are logged out if no cookies
            unset($this->logged_id);
            $this->logged_in = false;
        }
        return ($this->logged_in);
    }

    /**
     * Make sure user is verified
     * @return bool
     */
    public function is_verified_member(): bool
    {
        global $sess;
        if (!$this->is_logged_in())
            return false;

        $member = new Member($sess->logged_id);
        return $sess->logged_in && $member->info['status'] == 1;
    }

    /**
     * Force member to login
     * @return int
     */
    public function login_required(): int
    {
        // Logged in or redirect to log in
        $return = urlencode(trim($_SERVER['REQUEST_URI'], "/"));
        $this->logged_in || redirect_to("/login" . ($return != "" && $return != "/ajax/module-crud.php"  ? "?return=" . $return : ""));
        return $this->logged_id;
    }

    /**
     * Logout action
     * @return void
     */
    #[NoReturn] public function logout(): void
    {
        // Unset the member ID
        unset($this->logged_id);
        // User session cookie
        if (isset($_SESSION['user_id'])) unset($_SESSION['user_id']);

        isset($_COOKIE['user_id']) && setcookie('user_id', '', time() - 3600, '/');
        // Remove session totally
        isset($_COOKIE[session_name()]) && setcookie(session_name(), '', time() - (10 * 365 * 24 * 60 * 60), '/');
        // Destroy the session
        session_destroy();
        // Logout
        $this->logged_in = false;
        redirect_to();
    }

    /**
     * Saves the session token
     * @return void
     */
    public function save_session_token():void
    {
        if(!isset($_COOKIE['session-token']))
            setcookie('session-token', gen_strong_pwd(233, '', 'lud'), time() + (1000 * 365 * 24 * 60 * 60), "/");
        /**
         * TODO FIX TABLE
         */
        if(isset($_GET['table-id'])){
            $_SESSION['table-id'] = $_GET['table-id'];
            $this->table_id = $_SESSION['table-id'];
        }
    }

    public function is_admin(): bool|string
    {
        if(!$this->logged_in)
            return false;
        return $this->mySQL->table('member')->where('id', $this->logged_id)->getThis('is_admin');
    }

    public function is_staff(): bool|string
    {
        if(!$this->logged_in)
            return false;
        return $this->mySQL->table('member')->where('id', $this->logged_id)->getThis('staff_member');
    }
}
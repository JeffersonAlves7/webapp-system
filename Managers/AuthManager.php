<?php
require_once "Models/User.php";

class AuthManager
{
    private $user;

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->user = new User();
    }

    public function login($email, $password)
    {
        $user = $this->user;
        return $user->login($email, $password);
    }

    public function register($name, $email, $password)
    {
        $user = $this->user;
        return $user->register($name, $email, $password);
    }

    public static function logout()
    {
        session_destroy();
        self::clearUser();
    }

    public static function isLoggedIn()
    {
        return isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])
            && isset($_COOKIE['user_email']) && !empty($_COOKIE['user_email'])
            && isset($_COOKIE['user_name']) && !empty($_COOKIE['user_name']);
    }

    public static function getUser()
    {
        if (AuthManager::isLoggedIn()) {
            return [
                'id' => $_COOKIE['user_id'],
                'email' => $_COOKIE['user_email'],
                'name' => $_COOKIE['user_name']
            ];
        }

        return null;
    }

    private static function getCookieExpireTime()
    {
        return time() + 60 * 60 * 24 * 30; // 30 days
    }

    public static function setUser($id, $email, $name)
    {
        setcookie('user_id', $id, AuthManager::getCookieExpireTime(), '/');
        setcookie('user_email', $email, AuthManager::getCookieExpireTime(), '/');
        setcookie('user_name', $name, AuthManager::getCookieExpireTime(), '/');
    }

    public static function clearUser()
    {
        setcookie('user_id', '', time() - 3600, '/');
        setcookie('user_email', '', time() - 3600, '/');
        setcookie('user_name', '', time() - 3600, '/');
    }

    public static function checkLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: /auth/login');
            exit();
        }
    }
}

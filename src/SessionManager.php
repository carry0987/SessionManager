<?php
declare(strict_types=1);

namespace carry0987\SessionManager;

class SessionManager
{
    const INITIATED = 'INITIATED';
    const LAST_ACTIVITY = 'LAST_ACTIVITY';

    public function __construct(string $sessionName = null, array $cookieParams = [])
    {
        if ($sessionName) {
            session_name($sessionName);
        }

        $cookieDefaults = [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        $cookieParams = array_merge($cookieDefaults, $cookieParams);
        session_set_cookie_params($cookieParams);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->preventSessionFixation();
        $this->preventSessionExpired();
        $this->generateCSRFToken();
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function exists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();

        // Delete session cookie
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    public function getCSRFToken(): string
    {
        return $this->get('csrf_token');
    }

    public function verifyCSRFToken(string $token): bool
    {
        return $this->exists('csrf_token') && hash_equals($this->get('csrf_token'), $token);
    }

    private function preventSessionFixation(): void
    {
        if (!$this->exists(self::INITIATED)) {
            session_regenerate_id(true);
            $this->set(self::INITIATED, true);
        }
    }

    private function preventSessionExpired(): void
    {
        if ($this->exists(self::LAST_ACTIVITY) && (time() - $this->get(self::LAST_ACTIVITY) > 1800)) {
            $this->destroy();
        } else {
            $this->set(self::LAST_ACTIVITY, time());
        }
    }

    private function generateCSRFToken(): void
    {
        if (!$this->exists('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
    }
}

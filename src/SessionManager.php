<?php
declare(strict_types=1);

namespace carry0987\SessionManager;

use carry0987\SessionManager\Exceptions\SessionException;

class SessionManager
{
    const INITIATED = 'INITIATED';
    const LAST_ACTIVITY = 'LAST_ACTIVITY';
    const EXPIRE_AFTER = 1800;

    public function __construct(string $sessionName = null, array $cookieParams = [])
    {
        $this->initSession($sessionName, $cookieParams);
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
        $this->unsetSession()->destroySession();

        // Delete session cookie
        $params = $this->getSessionCookieParams();
        $deleted = setcookie(
            $this->getSessionName(),
            '',
            time() - 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );

        if (!$deleted) {
            throw new SessionException('Unable to delete the session cookie.');
        }
    }

    public function renew(string $sessionName = null, array $cookieParams = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE || $this->checkSessionStarted() === true) {
            $this->destroy();
        }

        $this->initSession($sessionName, $cookieParams);
    }

    public function getCSRFToken(): string
    {
        return $this->get('csrf_token');
    }

    public function verifyCSRFToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        return $this->exists('csrf_token') && hash_equals($this->get('csrf_token'), $token);
    }

    public function refreshCSRFToken(): void
    {
        $this->remove('csrf_token');
        $this->generateCSRFToken();
    }

    private function initSession(string $sessionName, array $cookieParams): void
    {
        if (!empty($sessionName)) {
            $this->setSessionName($sessionName);
        }

        $cookieDefaults = [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        $cookieParams = array_merge($cookieDefaults, $cookieParams);
        $this->setSessionCookieParams($cookieParams);
        $this->startSession();

        if ($this->checkSessionStarted() === false) {
            $this->preventSessionFixation();
            $this->preventSessionExpired();
            $this->generateCSRFToken();
        }
    }

    private function preventSessionFixation(): void
    {
        if (session_status() === PHP_SESSION_NONE || $this->checkSessionStarted() === false) {
            return;
        }

        if (!$this->exists(self::INITIATED)) {
            $this->regenerateSession(true)->set(self::INITIATED, true);
        }
    }

    private function preventSessionExpired(): void
    {
        if ($this->exists(self::LAST_ACTIVITY) && (time() - $this->get(self::LAST_ACTIVITY) > self::EXPIRE_AFTER)) {
            $this->destroy();
        } else {
            $this->set(self::LAST_ACTIVITY, time());
        }
    }

    private function generateCSRFToken(): void
    {
        if (!$this->exists('csrf_token')) {
            try {
                $token = bin2hex(random_bytes(32));
            } catch (\Exception $e) {
                throw new SessionException('Unable to generate a CSRF token.', 0, $e);
            }

            // Set CSRF token
            $this->set('csrf_token', $token);
        }
    }

    private function unsetSession(): self
    {
        session_unset();

        return $this;
    }

    private function destroySession(): self
    {
        session_destroy();

        return $this;
    }

    private function regenerateSession(bool $delete_old_session = false): self
    {
        session_regenerate_id($delete_old_session);

        return $this;
    }

    private function setSessionName(string $sessionName): self
    {
        session_name($sessionName);

        return $this;
    }

    private function setSessionCookieParams(array $cookieParams): self
    {
        session_set_cookie_params($cookieParams);

        return $this;
    }

    private function startSession(array $sessionOptions = []): self
    {
        session_start($sessionOptions);

        return $this;
    }

    private function getSessionName(): string
    {
        return session_name();
    }

    private function checkSessionStarted(): bool
    {
        return !empty(session_id());
    }

    private function getSessionCookieParams(): array
    {
        return session_get_cookie_params();
    }
}

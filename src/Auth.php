<?php

declare(strict_types=1);

final class Auth
{
    private const SESSION_KEY = 'kotupanovklima_admin';
    private const CSRF_KEY = 'csrf_token';

    public function __construct(
        private readonly DataStore $dataStore,
        private readonly Logger $logger,
    ) {
    }

    public function getClientIp(): ?string
    {
        return detect_client_ip();
    }

    public function getClientIpContext(): array
    {
        return detect_client_ip_context();
    }

    public function isIpAllowedByEntries(?string $clientIp, array $allowedEntries): bool
    {
        return ip_matches_allowlist($clientIp, $allowedEntries);
    }

    public function csrfToken(): string
    {
        if (!isset($_SESSION[self::CSRF_KEY]) || !is_string($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(24));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public function verifyCsrf(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION[self::CSRF_KEY])
            && is_string($_SESSION[self::CSRF_KEY])
            && hash_equals($_SESSION[self::CSRF_KEY], $token);
    }

    public function isAllowedIp(array $settings): bool
    {
        if (($settings['accessMode'] ?? 'open') !== 'allowlist_only') {
            return true;
        }

        $clientIp = $this->getClientIp();
        $allowed = $settings['allowedIps'] ?? [];

        return $this->isIpAllowedByEntries($clientIp, is_array($allowed) ? $allowed : []);
    }

    public function isAuthenticated(): bool
    {
        $settings = $this->dataStore->getAdminSettings();

        if (!$this->isAllowedIp($settings)) {
            return false;
        }

        $session = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($session)) {
            return false;
        }

        return ($session['authenticated'] ?? false) === true
            && ($session['sessionVersion'] ?? null) === ($settings['adminSecurity']['sessionVersion'] ?? null);
    }

    public function requireAdmin(): void
    {
        $settings = $this->dataStore->getAdminSettings();
        $clientIp = $this->getClientIp();

        if (!$this->isAllowedIp($settings)) {
            $this->logger->security('admin_allowlist_denied', [
                'clientIp' => $clientIp,
                'clientIpContext' => $this->getClientIpContext(),
                'path' => request_path(),
                'allowedIpsCount' => count($settings['allowedIps'] ?? []),
            ], 'warn');

            flash_set('login', 'Този IP адрес няма достъп до администрацията.', 'error');
            redirect_to('/admin/login');
        }

        if (!$this->isAuthenticated()) {
            $this->logger->security('admin_session_invalid', [
                'clientIp' => $clientIp,
                'path' => request_path(),
            ], 'warn');

            flash_set('login', 'Сесията е невалидна или е изтекла.', 'error');
            redirect_to('/admin/login');
        }
    }

    public function attemptLogin(string $code): bool
    {
        $settings = $this->dataStore->getAdminSettings();
        $clientIp = $this->getClientIp();

        if (!$this->isAllowedIp($settings)) {
            $this->logger->security('admin_login_denied_by_ip', [
                'clientIp' => $clientIp,
                'clientIpContext' => $this->getClientIpContext(),
            ], 'warn');

            return false;
        }

        $hash = hash('sha256', $code);
        if (!hash_equals((string) ($settings['adminSecurity']['codeHash'] ?? ''), $hash)) {
            $this->logger->security('admin_login_failed', [
                'clientIp' => $clientIp,
            ], 'warn');

            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = [
            'authenticated' => true,
            'sessionVersion' => $settings['adminSecurity']['sessionVersion'] ?? 1,
        ];

        $this->logger->security('admin_login_succeeded', [
            'clientIp' => $clientIp,
        ]);

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        $this->logger->security('admin_logout', [
            'clientIp' => $this->getClientIp(),
        ]);
    }
}

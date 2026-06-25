<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $value = preg_replace('/[^\p{L}\p{N}]+/u', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'item';
}

function convert_bgn_to_eur(?float $priceBgn): ?float
{
    if ($priceBgn === null) {
        return null;
    }

    return round($priceBgn / 1.95583, 2);
}

function convert_eur_to_bgn(?float $priceEur): ?float
{
    if ($priceEur === null) {
        return null;
    }

    return round($priceEur * 1.95583, 2);
}

function format_price_bgn(?float $priceBgn): string
{
    return $priceBgn === null ? 'По запитване' : number_format($priceBgn, 2, ',', ' ') . ' лв.';
}

function format_price_eur(?float $priceEur): string
{
    return $priceEur === null ? 'По запитване' : number_format($priceEur, 2, ',', ' ') . ' EUR';
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    return is_string($path) && $path !== '' ? $path : '/';
}

function redirect_to(string $url, int $status = 302): never
{
    http_response_code($status);
    header('Location: ' . $url);
    exit;
}

function safe_href(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if (str_starts_with($value, '/') && !str_starts_with($value, '//')) {
        return $value;
    }

    $scheme = strtolower((string) (parse_url($value, PHP_URL_SCHEME) ?? ''));
    if (in_array($scheme, ['http', 'https', 'tel', 'mailto'], true)) {
        return $value;
    }

    return null;
}

function detect_client_ip(): ?string
{
    return detect_client_ip_context()['clientIp'];
}

function detect_client_ip_context(): array
{
    $remoteAddr = normalize_ip_address($_SERVER['REMOTE_ADDR'] ?? null);
    $realIp = normalize_ip_address($_SERVER['HTTP_X_REAL_IP'] ?? null);
    $forwardedChain = forwarded_ip_chain($_SERVER['HTTP_X_FORWARDED_FOR'] ?? null);
    $trustedProxy = $remoteAddr !== null && is_trusted_proxy_ip($remoteAddr);

    $clientIp = $remoteAddr;
    $source = $remoteAddr !== null ? 'remote_addr' : 'unknown';

    if ($trustedProxy && $forwardedChain !== []) {
        $clientIp = $forwardedChain[0];
        $source = 'x_forwarded_for';
    } elseif ($trustedProxy && $realIp !== null) {
        $clientIp = $realIp;
        $source = 'x_real_ip';
    }

    return [
        'clientIp' => $clientIp,
        'clientIpSource' => $source,
        'remoteAddr' => $remoteAddr,
        'trustedProxy' => $trustedProxy,
        'xForwardedFor' => is_string($_SERVER['HTTP_X_FORWARDED_FOR'] ?? null) ? trim((string) $_SERVER['HTTP_X_FORWARDED_FOR']) : null,
        'xForwardedChain' => $forwardedChain,
        'xRealIp' => $realIp,
    ];
}

function normalize_ip_address(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if (str_starts_with($value, '::ffff:')) {
        $value = substr($value, 7);
    }

    return filter_var($value, FILTER_VALIDATE_IP) !== false ? $value : null;
}

function forwarded_ip_chain(mixed $value): array
{
    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $items = [];
    foreach (explode(',', $value) as $part) {
        $ip = normalize_ip_address($part);
        if ($ip !== null) {
            $items[] = $ip;
        }
    }

    return array_values(array_unique($items));
}

function is_trusted_proxy_ip(string $ip): bool
{
    return ip_matches_allowlist($ip, trusted_proxy_allowlist());
}

function trusted_proxy_allowlist(): array
{
    $value = getenv('TRUSTED_PROXY_RANGES');
    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $items = preg_split('/[\s,]+/', trim($value)) ?: [];

    return array_values(array_filter(array_map('trim', $items), static fn (string $item): bool => $item !== ''));
}

function is_private_or_loopback_ip(string $ip): bool
{
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
}

function ip_matches_allowlist(?string $ip, array $entries): bool
{
    $normalizedIp = normalize_ip_address($ip);
    if ($normalizedIp === null) {
        return false;
    }

    foreach ($entries as $entry) {
        if (ip_matches_entry($normalizedIp, $entry)) {
            return true;
        }
    }

    return false;
}

function ip_matches_entry(string $ip, mixed $entry): bool
{
    if (!is_string($entry)) {
        return false;
    }

    $entry = trim($entry);
    if ($entry === '') {
        return false;
    }

    $entry = preg_replace('/\s+#.*$/', '', $entry) ?? $entry;
    $entry = trim($entry);
    if ($entry === '') {
        return false;
    }

    $normalizedEntry = normalize_ip_address($entry);
    if ($normalizedEntry !== null) {
        return $ip === $normalizedEntry;
    }

    if (!str_contains($entry, '/')) {
        return false;
    }

    [$network, $prefix] = array_pad(explode('/', $entry, 2), 2, null);
    $network = normalize_ip_address($network);

    if ($network === null || $prefix === null || !ctype_digit($prefix)) {
        return false;
    }

    $prefixLength = (int) $prefix;
    $ipBinary = @inet_pton($ip);
    $networkBinary = @inet_pton($network);

    if ($ipBinary === false || $networkBinary === false || strlen($ipBinary) !== strlen($networkBinary)) {
        return false;
    }

    $maxPrefix = strlen($ipBinary) * 8;
    if ($prefixLength < 0 || $prefixLength > $maxPrefix) {
        return false;
    }

    $fullBytes = intdiv($prefixLength, 8);
    $remainingBits = $prefixLength % 8;

    if ($fullBytes > 0 && substr($ipBinary, 0, $fullBytes) !== substr($networkBinary, 0, $fullBytes)) {
        return false;
    }

    if ($remainingBits === 0) {
        return true;
    }

    $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

    return (ord($ipBinary[$fullBytes]) & $mask) === (ord($networkBinary[$fullBytes]) & $mask);
}

function flash_set(string $key, string $message, string $type = 'info'): void
{
    $_SESSION['_flash'][$key] = [
        'message' => $message,
        'type' => $type,
    ];
}

function flash_get(string $key): ?array
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return is_array($value) ? $value : null;
}

function normalize_newlines(string $value): string
{
    return str_replace(["\r\n", "\r"], "\n", trim($value));
}

function array_value(array $source, string $key, mixed $default = null): mixed
{
    return array_key_exists($key, $source) ? $source[$key] : $default;
}

function starts_with(string $value, string $prefix): bool
{
    return str_starts_with($value, $prefix);
}

<?php
/**
 * Shared random-image helpers.
 * Local folders first; fall back to GitHub raw when only the .php files are deployed.
 *
 * Query flags (any endpoint):
 *   ?json   → JSON metadata instead of redirect
 *   ?serve  → stream the image bytes (local mode only; github-raw still redirects)
 */

declare(strict_types=1);

const RID_GITHUB_RAW_BASE = 'https://raw.githubusercontent.com/Lossalt/random-img/main';

const RID_DEVICES = [
    'pc' => [
        'dir' => 'pc',
        'ext' => 'webp',
        'fallback_count' => 197,
        'label' => 'PC wallpapers',
    ],
    'mobile' => [
        'dir' => 'mobile',
        'ext' => 'webp',
        'fallback_count' => 68,
        'label' => 'Mobile wallpapers',
    ],
];

const RID_MIME = [
    'webp' => 'image/webp',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'avif' => 'image/avif',
];

function rid_device_config(string $device): ?array
{
    return RID_DEVICES[$device] ?? null;
}

function rid_mime(string $ext): string
{
    return RID_MIME[strtolower($ext)] ?? 'application/octet-stream';
}

/**
 * @return list<string> basenames like "12.webp"
 */
function rid_local_files(string $dir, string $ext): array
{
    static $cache = [];

    $cacheKey = $dir . '|' . $ext;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $path = __DIR__ . DIRECTORY_SEPARATOR . $dir;
    $files = [];
    if (is_dir($path)) {
        foreach (glob($path . DIRECTORY_SEPARATOR . '*.' . $ext) ?: [] as $file) {
            $name = basename($file);
            if ($name !== '') {
                $files[] = $name;
            }
        }
        sort($files, SORT_NATURAL);
    }

    $cache[$cacheKey] = $files;
    return $files;
}

/**
 * Pick one image. Prefers a local file; otherwise GitHub raw.
 *
 * @return array{url:string,source:string,device:string,total:int,file?:string,local_path?:string}|null
 */
function rid_pick_image(string $device): ?array
{
    $cfg = rid_device_config($device);
    if ($cfg === null) {
        return null;
    }

    $local = rid_local_files($cfg['dir'], $cfg['ext']);
    if ($local !== []) {
        $name = $local[random_int(0, count($local) - 1)];
        return [
            'url' => $cfg['dir'] . '/' . $name,
            'source' => 'local',
            'device' => $device,
            'total' => count($local),
            'file' => $name,
            'local_path' => __DIR__ . DIRECTORY_SEPARATOR . $cfg['dir'] . DIRECTORY_SEPARATOR . $name,
        ];
    }

    $max = max(1, (int) $cfg['fallback_count']);
    $num = random_int(1, $max);
    $file = $num . '.' . $cfg['ext'];
    return [
        'url' => RID_GITHUB_RAW_BASE . '/' . $cfg['dir'] . '/' . $file,
        'source' => 'github-raw',
        'device' => $device,
        'total' => $max,
        'file' => $file,
    ];
}

function rid_wants_json(): bool
{
    if (isset($_GET['json'])) {
        return true;
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_contains($accept, 'application/json') && !str_contains($accept, 'image/');
}

function rid_wants_serve(): bool
{
    return isset($_GET['serve']);
}

function rid_is_head(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD';
}

function rid_fail(int $code, string $message): void
{
    http_response_code($code);
    if (rid_wants_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return;
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo $message . "\n";
}

function rid_redirect(string $device): void
{
    $pick = rid_pick_image($device);
    if ($pick === null) {
        rid_fail(404, 'Unknown device. Use: pc | mobile');
        return;
    }

    header('Cache-Control: no-store');
    header('X-Random-Img-Source: ' . $pick['source']);
    header('X-Random-Img-File: ' . $pick['file']);
    header('Location: ' . $pick['url'], true, 302);
    if (!rid_is_head()) {
        // Body kept empty; clients follow Location.
    }
    exit;
}

function rid_serve(string $device): void
{
    $pick = rid_pick_image($device);
    if ($pick === null) {
        rid_fail(404, 'Unknown device. Use: pc | mobile');
        return;
    }

    // github-raw cannot be proxied without extra deps — fall back to redirect.
    if ($pick['source'] !== 'local' || !is_file($pick['local_path'])) {
        rid_redirect($device);
        return;
    }

    $path = $pick['local_path'];
    $ext = strtolower(pathinfo($pick['file'], PATHINFO_EXTENSION));
    $size = (int) filesize($path);

    header('Content-Type: ' . rid_mime($ext));
    header('Content-Length: ' . $size);
    header('Cache-Control: no-store');
    header('X-Random-Img-Source: local');
    header('X-Random-Img-File: ' . $pick['file']);
    header('Content-Disposition: inline; filename="' . rawurlencode($pick['file']) . '"');

    if (rid_is_head()) {
        exit;
    }

    readfile($path);
    exit;
}

function rid_json(string $device): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('Access-Control-Allow-Origin: *');

    if ($device === 'index') {
        $out = [];
        foreach (RID_DEVICES as $key => $cfg) {
            $local = rid_local_files($cfg['dir'], $cfg['ext']);
            $out[$key] = [
                'label' => $cfg['label'],
                'mode' => $local !== [] ? 'local' : 'github-raw',
                'total' => $local !== [] ? count($local) : max(1, (int) $cfg['fallback_count']),
                'endpoint' => $key . '.php',
            ];
        }
        echo json_encode(['devices' => $out], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return;
    }

    $pick = rid_pick_image($device);
    if ($pick === null) {
        http_response_code(404);
        echo json_encode(['error' => 'unknown device'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return;
    }

    unset($pick['local_path']);
    echo json_encode($pick, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function rid_handle_endpoint(string $device): void
{
    if (rid_wants_json()) {
        rid_json($device);
        return;
    }
    if (rid_wants_serve()) {
        rid_serve($device);
        return;
    }
    rid_redirect($device);
}

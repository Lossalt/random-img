<?php
/**
 * Shared random-image helpers.
 * Local folders first; fall back to GitHub raw when only the .php files are deployed.
 */

declare(strict_types=1);

const RID_GITHUB_RAW_BASE = 'https://raw.githubusercontent.com/Lossalt/ramdom-img/main';

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

function rid_device_config(string $device): ?array
{
    return RID_DEVICES[$device] ?? null;
}

/**
 * @return list<string> basenames like "12.webp"
 */
function rid_local_files(string $dir, string $ext): array
{
    $root = __DIR__;
    $path = $root . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($path)) {
        return [];
    }

    $files = [];
    foreach (glob($path . DIRECTORY_SEPARATOR . '*.' . $ext) ?: [] as $file) {
        $name = basename($file);
        if ($name !== '') {
            $files[] = $name;
        }
    }
    sort($files, SORT_NATURAL);
    return $files;
}

/**
 * Pick one image URL for the device.
 * Prefers a local file (same-origin path); otherwise GitHub raw.
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
        ];
    }

    // Fallback: only .php deployed — jump to GitHub raw.
    $max = max(1, (int) $cfg['fallback_count']);
    $num = random_int(1, $max);
    $url = RID_GITHUB_RAW_BASE . '/' . $cfg['dir'] . '/' . $num . '.' . $cfg['ext'];
    return [
        'url' => $url,
        'source' => 'github-raw',
        'device' => $device,
        'total' => $max,
    ];
}

function rid_redirect(string $device): void
{
    $pick = rid_pick_image($device);
    if ($pick === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Unknown device. Use: pc | mobile\n";
        return;
    }

    header('Cache-Control: no-store');
    header('X-Random-Img-Source: ' . $pick['source']);
    header('Location: ' . $pick['url'], true, 302);
    exit;
}

function rid_json(string $device): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('Access-Control-Allow-Origin: *');

    $pick = rid_pick_image($device);
    if ($pick === null) {
        http_response_code(404);
        echo json_encode(['error' => 'unknown device'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return;
    }

    echo json_encode($pick, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function rid_handle_endpoint(string $device): void
{
    if (isset($_GET['json'])) {
        rid_json($device);
        return;
    }
    rid_redirect($device);
}

<?php
declare(strict_types=1);

require __DIR__ . '/random-image.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$devices = [];
foreach (RID_DEVICES as $key => $cfg) {
    $local = rid_local_files($cfg['dir'], $cfg['ext']);
    $devices[] = [
        'key' => $key,
        'label' => $cfg['label'],
        'local_count' => count($local),
        'fallback_count' => $cfg['fallback_count'],
        'mode' => $local !== [] ? 'local' : 'github-raw',
    ];
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>random-img · 随机图 API</title>
  <style>
    :root { color-scheme: light dark; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
    body { margin: 0; padding: 2rem 1.25rem 3rem; line-height: 1.6; }
    main { max-width: 44rem; margin: 0 auto; }
    h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
    p.lead { opacity: 0.8; margin-top: 0; }
    code, pre { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 0.92em; }
    pre { background: rgba(127,127,127,0.12); padding: 0.85rem 1rem; border-radius: 8px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin: 1rem 0 1.5rem; }
    th, td { text-align: left; padding: 0.5rem 0.4rem; border-bottom: 1px solid rgba(127,127,127,0.25); vertical-align: top; }
    .badge { display: inline-block; padding: 0.1rem 0.45rem; border-radius: 999px; font-size: 0.8rem; background: rgba(46,160,67,0.18); }
    .badge.raw { background: rgba(187,128,9,0.2); }
    a.button {
      display: inline-block; margin: 0.2rem 0.4rem 0.2rem 0;
      padding: 0.4rem 0.75rem; border-radius: 8px;
      background: rgba(88,166,255,0.18); text-decoration: none;
    }
  </style>
</head>
<body>
<main>
  <h1>random-img</h1>
  <p class="lead">随机壁纸接口 · Random wallpaper API</p>

  <table>
    <thead>
      <tr><th>接口</th><th>说明</th><th>模式</th></tr>
    </thead>
    <tbody>
      <?php foreach ($devices as $d): ?>
      <tr>
        <td><a href="/<?= htmlspecialchars($d['key']) ?>.php">/<?= htmlspecialchars($d['key']) ?>.php</a></td>
        <td><?= htmlspecialchars($d['label']) ?>（本地 <?= (int) $d['local_count'] ?> 张<?= $d['mode'] === 'github-raw' ? '，回退池 ' . (int) $d['fallback_count'] : '' ?>）</td>
        <td>
          <?php if ($d['mode'] === 'local'): ?>
            <span class="badge">local</span>
          <?php else: ?>
            <span class="badge raw">github-raw</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>用法 / Usage</h2>
  <pre>GET /pc.php          → 302 跳到随机 PC 图
GET /mobile.php      → 302 跳到随机手机图
GET /pc.php?serve    → 直接返回图片字节（本地模式）
GET /pc.php?json     → JSON 元数据
GET /api.php         → 设备列表 JSON
GET /pc.php          Accept: application/json → 同上 JSON</pre>

  <pre>{
  "url": "pc/12.webp",
  "source": "local",
  "device": "pc",
  "total": 197,
  "file": "12.webp"
}</pre>

  <h2>试一下 / Try</h2>
  <p>
    <a class="button" href="/pc.php" target="_blank" rel="noopener">随机 PC 图</a>
    <a class="button" href="/mobile.php" target="_blank" rel="noopener">随机手机图</a>
    <a class="button" href="/pc.php?serve" target="_blank" rel="noopener">PC 直出</a>
    <a class="button" href="/mobile.php?serve" target="_blank" rel="noopener">手机直出</a>
    <a class="button" href="/pc.php?json" target="_blank" rel="noopener">PC JSON</a>
    <a class="button" href="/api.php" target="_blank" rel="noopener">设备列表</a>
  </p>

  <h2>部署 / Deploy</h2>
  <p><strong>推荐（整仓部署）</strong>：把整个仓库放到 PHP 空间，自动扫描 <code>pc/</code>、<code>mobile/</code>，同域直出。</p>
  <p><strong>轻量（只放 PHP）</strong>：把 <code>pc.php</code>、<code>mobile.php</code>、<code>random-image.php</code>、<code>api.php</code> 放到服务器；没有本地图片目录时会跳转到 GitHub raw。</p>
</main>
</body>
</html>

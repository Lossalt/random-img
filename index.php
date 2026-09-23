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
  <title>ramdom-img · 随机图 API</title>
  <style>
    :root { color-scheme: light dark; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
    body { margin: 0; padding: 2rem 1.25rem 3rem; line-height: 1.6; }
    main { max-width: 42rem; margin: 0 auto; }
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
  <h1>ramdom-img</h1>
  <p class="lead">随机壁纸跳转接口 · Random wallpaper redirect API</p>

  <table>
    <thead>
      <tr><th>接口</th><th>说明</th><th>模式</th></tr>
    </thead>
    <tbody>
      <?php foreach ($devices as $d): ?>
      <tr>
        <td><a href="/pc.php">/<?= htmlspecialchars($d['key']) ?>.php</a></td>
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
  <p>浏览器打开即 302 跳到随机图：</p>
  <pre>https://你的域名/pc.php
https://你的域名/mobile.php</pre>

  <p>需要 JSON（给程序用）加 <code>?json</code>：</p>
  <pre>https://你的域名/pc.php?json</pre>
  <pre>{
  "url": "pc/12.webp",
  "source": "local",
  "device": "pc",
  "total": 197
}</pre>

  <h2>试一下 / Try</h2>
  <p>
    <a class="button" href="/pc.php" target="_blank" rel="noopener">随机 PC 图</a>
    <a class="button" href="/mobile.php" target="_blank" rel="noopener">随机手机图</a>
    <a class="button" href="/pc.php?json" target="_blank" rel="noopener">PC JSON</a>
    <a class="button" href="/mobile.php?json" target="_blank" rel="noopener">手机 JSON</a>
  </p>

  <h2>部署 / Deploy</h2>
  <p><strong>推荐（整仓部署）</strong>：把整个仓库放到 PHP 空间，脚本会自动扫描 <code>pc/</code>、<code>mobile/</code> 下的图片，同域直出。</p>
  <p><strong>轻量（只放 PHP）</strong>：把 <code>pc.php</code>、<code>mobile.php</code>、<code>random-image.php</code> 放到服务器；没有本地图片目录时会跳转到 GitHub raw。</p>
</main>
</body>
</html>

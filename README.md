# random-img

随机壁纸接口。打开 PHP 接口 → `302` 跳到一张随机 WebP 图（或直出图片字节 / JSON）。

Random wallpaper API. Hit a PHP endpoint → `302` to a random WebP image (or stream bytes / JSON).

## 接口 Endpoints

| 路径 | 说明 |
|------|------|
| `/pc.php` | 随机 PC / 桌面壁纸（302） |
| `/mobile.php` | 随机手机壁纸（302） |
| `/pc.php?serve` | 直接返回图片字节（本地模式） |
| `/pc.php?json` | JSON 元数据 |
| `/api.php` | 设备列表 JSON |
| `/index.php` | 简单说明页 |

也支持请求头 `Accept: application/json`，等价于 `?json`。

示例 Example：

```text
https://your-domain/pc.php
https://your-domain/mobile.php
https://your-domain/pc.php?serve
https://your-domain/pc.php?json
https://your-domain/api.php
```

JSON 返回示例：

```json
{
  "url": "pc/12.webp",
  "source": "local",
  "device": "pc",
  "total": 197,
  "file": "12.webp"
}
```

- `source: local` — 服务器上有图片目录，同域路径
- `source: github-raw` — 仅部署了 PHP，跳到 GitHub raw

响应头 `X-Random-Img-Source` / `X-Random-Img-File` 可查看来源与文件名。

## 部署 Deploy

### 方式 A：整仓部署（推荐）

```bash
git clone https://github.com/Lossalt/random-img.git
# 将目录配置到网站根目录即可
```

脚本会自动扫描 `pc/*.webp`、`mobile/*.webp`，加图、删图都不用改代码。

### 方式 B：只放 PHP 文件

```text
pc.php
mobile.php
random-image.php
api.php          # 可选
index.php        # 可选
```

没有本地图片目录时，自动跳转到 GitHub raw。回退数量在 `random-image.php` 的 `RID_DEVICES` 里配置（`fallback_count`）。

### Nginx 示例

```nginx
server {
    listen 80;
    server_name img.example.com;
    root /var/www/random-img;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }
}
```

## 本地加图 Add images

- 桌面图放进 `pc/`，手机图放进 `mobile/`
- 命名随意（建议 `1.webp`、`2.webp`…），脚本按目录扫描
- 目前使用 `.webp`；要改 jpg/png，改 `random-image.php` 里对应设备的 `ext`

## 说明 Notes

- 默认 `302` + `Cache-Control: no-store`，每次都是新的随机结果
- `HEAD` 请求会返回同样响应头、不带 body
- `?serve` 仅在本地有图时直出；github-raw 模式仍为跳转
- 仓库原名 `ramdom-img`，已更正为 `random-img`

## License

代码部分采用 MIT（见 `LICENSE`）；图片版权归原作者所有。

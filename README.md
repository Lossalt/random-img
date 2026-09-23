# random-img

随机壁纸跳转接口：打开 PHP → `302` 到 `pc/` 或 `mobile/` 池里的一张随机图（也可直出字节 / JSON）。

Random wallpaper redirect API for blogs and homepages.

**和 [article-image](https://github.com/Lossalt/article-image) 同一思路**：脚本轻量、图池可拆——图可以随仓库放，也可以只部署 PHP、图走 GitHub raw，不绑死存储位置。

## 快速开始 Quick start

```text
GET /pc.php          → 302 随机 PC / 桌面壁纸
GET /mobile.php      → 302 随机手机壁纸
GET /pc.php?serve    → 直接返回图片字节（本地有图时）
GET /pc.php?json     → JSON 元数据
GET /api.php         → 设备列表 JSON
```

也支持 `Accept: application/json`。

```json
{
  "url": "pc/12.webp",
  "source": "local",
  "device": "pc",
  "total": 197,
  "file": "12.webp"
}
```

| 响应头 | 含义 |
|--------|------|
| `X-Random-Img-Source` | `local` 或 `github-raw` |
| `X-Random-Img-File` | 选中的文件名 |

## 部署 Deploy

### 方式 A：整仓部署（推荐）

```bash
git clone https://github.com/Lossalt/random-img.git
# 配置到网站根目录
```

自动扫描 `pc/*.webp`、`mobile/*.webp`，加图删图不用改代码。同域直出，可开 `?serve`。

### 方式 B：只放 PHP（图不在本机）

```text
pc.php
mobile.php
random-image.php
api.php          # 可选
index.php        # 可选
```

无本地图片目录时自动跳到 GitHub raw。回退数量在 `random-image.php` 的 `RID_DEVICES` / `fallback_count`。

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

## 加图 Add images

| 目录 | 用途 | 数量（当前） |
|------|------|----------------|
| `pc/` | 桌面壁纸 | 197 |
| `mobile/` | 手机壁纸 | 68 |

- 命名随意（建议 `1.webp`、`2.webp`…），按目录扫描  
- 默认 `.webp`；要改格式，改 `random-image.php` 里设备配置的 `ext`

## 文件 Files

| 文件 | 作用 |
|------|------|
| `random-image.php` | 核心：扫图、随机、local / github-raw、JSON |
| `pc.php` / `mobile.php` | 接口入口 |
| `api.php` | 设备列表 |
| `index.php` | 说明页 |
| `LICENSE` | MIT（代码）；图片版权归原作者 |

## 说明 Notes

- 默认 `302` + `Cache-Control: no-store`，每次都是新图  
- `HEAD` 只回响应头  
- `?serve` 仅本地有图时直出；github-raw 模式仍为跳转  
- 仓库原名 `ramdom-img`，已更正为 `random-img`

## License

代码 MIT（见 `LICENSE`）；`pc/`、`mobile/` 图片版权归原作者所有。

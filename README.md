# ramdom-img

随机壁纸跳转接口。打开 PHP 接口 → `302` 跳到一张随机 WebP 图。

Random wallpaper redirect API. Hit a PHP endpoint → `302` to a random WebP image.

## 接口 Endpoints

| 路径 | 说明 |
|------|------|
| `/pc.php` | 随机 PC / 桌面壁纸 |
| `/mobile.php` | 随机手机壁纸 |
| `/pc.php?json` | 同上，返回 JSON（给程序用） |
| `/mobile.php?json` | 同上，返回 JSON |
| `/index.php` | 简单说明页 |

示例 Example：

```text
https://your-domain/pc.php
https://your-domain/mobile.php
https://your-domain/pc.php?json
```

JSON 返回示例：

```json
{
  "url": "pc/12.webp",
  "source": "local",
  "device": "pc",
  "total": 197
}
```

- `source: local` — 服务器上有图片目录，同域路径
- `source: github-raw` — 仅部署了 PHP，跳到 GitHub raw

## 部署 Deploy

### 方式 A：整仓部署（推荐）

把整个仓库放到支持 PHP 的空间（虚拟主机 / VPS + Nginx / Apache 等）：

```bash
git clone https://github.com/Lossalt/ramdom-img.git
# 将目录配置到网站根目录即可
```

脚本会自动扫描 `pc/*.webp`、`mobile/*.webp`，加图、删图都不用改代码。

### 方式 B：只放 PHP 文件

只需这三个文件：

```text
pc.php
mobile.php
random-image.php
```

放到任意 PHP 空间。没有本地图片目录时，自动跳转到 GitHub raw 上的图片。

> 回退数量在 `random-image.php` 的 `RID_DEVICES` 里配置（`fallback_count`）。
> 若用方式 A，可以忽略该数量。

## 本地加图 Add images

- 桌面图放进 `pc/`，命名建议 `1.webp`、`2.webp`…（也支持其它文件名，脚本按目录扫描）
- 手机图放进 `mobile/`
- 目前仓库使用 `.webp`；要改成 jpg/png，改 `random-image.php` 里对应设备的 `ext`

## 说明 Notes

- 跳转状态码：`302`，带 `Cache-Control: no-store`，每次都是新的随机结果
- 响应头 `X-Random-Img-Source` 可查看当前是 `local` 还是 `github-raw`
- 仓库名里的 `ramdom` 是历史拼写，实际是 random

## License

图片版权归原作者所有；代码部分可自由使用。

import json
import os
import sys
import time
import urllib.request
import urllib.error
from urllib.parse import urlparse
from concurrent.futures import ThreadPoolExecutor, as_completed

sys.stdout.reconfigure(encoding='utf-8')

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
BASE_STORAGE = os.path.join(BASE_DIR, 'storage', 'app', 'public')
JSON_FILE = os.path.join(BASE_DIR, 'storage', 'eshaq_gifts_and_stationery.json')
SOURCE_BASE_URL = os.environ.get('ESHAQ_BASE_URL', '').rstrip('/')
if not SOURCE_BASE_URL:
    raise RuntimeError('Set ESHAQ_BASE_URL to the Eshaq base URL before running this script.')

CATEGORY_IMAGE_URL = f'{SOURCE_BASE_URL}/storage/sections/1791193540.jpg'

def source_image_url(value, directory):
    filename = os.path.basename(urlparse(value).path)
    if not filename:
        raise ValueError(f'Cannot resolve an image filename from path: {value!r}')

    return filename, f'{SOURCE_BASE_URL}/storage/{directory}/{filename}'

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.9,ar;q=0.8',
}

# 1. Load JSON
with open(JSON_FILE, 'r', encoding='utf-8') as f:
    stores_data = json.load(f)

download_tasks = {}

# Category image
cat_filename = os.path.basename(urlparse(CATEGORY_IMAGE_URL).path)
cat_full_path = os.path.join(BASE_STORAGE, 'categories', cat_filename)
download_tasks[('cat', CATEGORY_IMAGE_URL)] = {
    'url': CATEGORY_IMAGE_URL,
    'full_path': cat_full_path,
    'rel_path': f'categories/{cat_filename}'
}

# Stores & products
for s in stores_data:
    logo_url = s.get('logo')
    if logo_url:
        fn, source_url = source_image_url(logo_url, 'markets')
        fp = os.path.join(BASE_STORAGE, 'stores', 'logos', fn)
        download_tasks[('logo', source_url)] = {
            'url': source_url,
            'full_path': fp,
            'rel_path': f'stores/logos/{fn}'
        }

    banner_url = s.get('banner')
    if banner_url:
        fn, source_url = source_image_url(banner_url, 'markets')
        fp = os.path.join(BASE_STORAGE, 'stores', 'banners', fn)
        download_tasks[('banner', source_url)] = {
            'url': source_url,
            'full_path': fp,
            'rel_path': f'stores/banners/{fn}'
        }

    for p in s.get('products', []):
        img_url = p.get('image')
        if img_url:
            fn, source_url = source_image_url(img_url, 'products')
            fp = os.path.join(BASE_STORAGE, 'products', fn)
            download_tasks[('product', source_url)] = {
                'url': source_url,
                'full_path': fp,
                'rel_path': f'products/{fn}'
            }

tasks_list = list(download_tasks.values())
print(f"Total unique images to download/verify: {len(tasks_list)}", flush=True)

def download_item(item):
    url = item['url']
    full_path = item['full_path']

    if os.path.exists(full_path) and os.path.getsize(full_path) > 0:
        return {'status': 'skipped', 'url': url, 'bytes': os.path.getsize(full_path)}

    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    tmp_path = full_path + '.part'
    last_error = None

    for attempt in range(3):
        try:
            req = urllib.request.Request(url, headers=HEADERS)
            with urllib.request.urlopen(req, timeout=15) as resp:
                if resp.status == 200:
                    data = resp.read()
                    if len(data) > 0:
                        with open(tmp_path, 'wb') as f:
                            f.write(data)
                        if os.path.exists(full_path):
                            os.remove(full_path)
                        os.rename(tmp_path, full_path)
                        return {'status': 'downloaded', 'url': url, 'bytes': len(data)}
                    else:
                        last_error = "Empty response"
                else:
                    last_error = f"HTTP {resp.status}"
        except urllib.error.HTTPError as e:
            last_error = f"HTTP {e.code}: {e.reason}"
            if e.code in [404, 410]:
                break
            time.sleep(0.5 * (attempt + 1))
        except Exception as e:
            last_error = str(e)
            time.sleep(0.5 * (attempt + 1))
        finally:
            if os.path.exists(tmp_path):
                try:
                    os.remove(tmp_path)
                except Exception:
                    pass

    return {'status': 'failed', 'url': url, 'error': last_error}

MAX_WORKERS = 30
start_time = time.time()
downloaded = 0
skipped = 0
failed = 0
total_bytes = 0

print(f"Starting download with {MAX_WORKERS} workers...", flush=True)
with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
    futures = {executor.submit(download_item, item): item for item in tasks_list}
    for idx, future in enumerate(as_completed(futures), 1):
        res = future.result()
        st = res.get('status')
        if st == 'downloaded':
            downloaded += 1
            total_bytes += res.get('bytes', 0)
        elif st == 'skipped':
            skipped += 1
            total_bytes += res.get('bytes', 0)
        else:
            failed += 1

        if idx % 100 == 0 or idx == len(tasks_list):
            elapsed = time.time() - start_time
            speed = idx / elapsed if elapsed > 0 else 0
            mb = total_bytes / (1024 * 1024)
            pct = (idx / len(tasks_list)) * 100
            print(f"[{pct:5.1f}%] {idx}/{len(tasks_list)} | Down: {downloaded} | Skip: {skipped} | Fail: {failed} | {mb:.1f} MB | {speed:.1f} f/s", flush=True)

duration = time.time() - start_time
print("=" * 60, flush=True)
print(f"Images download complete in {duration:.1f}s!")
print(f"Downloaded: {downloaded}, Skipped: {skipped}, Failed: {failed}", flush=True)
print("=" * 60, flush=True)

# Now update the JSON file to use relative paths
print("Updating JSON paths to local relative paths...", flush=True)
def clean_path(url, prefix):
    if not url:
        return url
    if url.startswith('http'):
        fn = os.path.basename(urlparse(url).path)
        return f"{prefix}/{fn}"
    return url

for s in stores_data:
    if s.get('logo'):
        s['logo'] = clean_path(s['logo'], 'stores/logos')
    if s.get('banner'):
        s['banner'] = clean_path(s['banner'], 'stores/banners')
    for p in s.get('products', []):
        if p.get('image'):
            p['image'] = clean_path(p['image'], 'products')

with open(JSON_FILE, 'w', encoding='utf-8') as f:
    json.dump(stores_data, f, ensure_ascii=False, indent=2)

print(f"Successfully updated {JSON_FILE} with local image paths!", flush=True)

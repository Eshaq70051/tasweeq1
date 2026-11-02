import json
import os
import sys
import time
import urllib.request
import urllib.error
from concurrent.futures import ThreadPoolExecutor, as_completed

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
STORAGE_PUBLIC = os.path.join(BASE_DIR, 'storage', 'app', 'public')
MANIFEST_PATH = os.path.join(BASE_DIR, 'download_manifest.json')
SOURCE_BASE_URL = os.environ.get('ESHAQ_BASE_URL', '').rstrip('/')
MAX_WORKERS = 30
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
}

def source_url(value, section):
    if value.startswith(('http://', 'https://')):
        return value
    if not SOURCE_BASE_URL:
        raise RuntimeError('Set ESHAQ_BASE_URL to the Eshaq base URL before downloading remote images.')
    return f"{SOURCE_BASE_URL}/storage/{section}/{os.path.basename(value)}"

def generate_manifest():
    tasks = {}
    json_files = [
        'eshaq_restaurants.json',
        'eshaq_meats_and_fish.json',
        'eshaq_bakeries.json',
    ]

    for jf in json_files:
        full_p = os.path.join(BASE_DIR, 'storage', jf)
        if not os.path.exists(full_p):
            continue
        try:
            with open(full_p, 'r', encoding='utf-8') as f:
                data = json.load(f)
            for item in data:
                # Store logo
                logo = item.get('logo', '')
                if logo:
                    fn = os.path.basename(logo)
                    url = source_url(logo, 'markets')
                    dest = os.path.join(STORAGE_PUBLIC, 'stores', 'logos', fn)
                    tasks[dest] = {'url': url, 'full_path': dest}

                # Store banner
                banner = item.get('banner', '')
                if banner:
                    fn = os.path.basename(banner)
                    url = source_url(banner, 'markets')
                    dest = os.path.join(STORAGE_PUBLIC, 'stores', 'banners', fn)
                    tasks[dest] = {'url': url, 'full_path': dest}

                # Products
                prods = item.get('products', [])
                for p in prods:
                    img = p.get('image', '')
                    if img:
                        fn = os.path.basename(img)
                        url = source_url(img, 'products')
                        dest = os.path.join(STORAGE_PUBLIC, 'products', fn)
                        tasks[dest] = {'url': url, 'full_path': dest}

                # Sections products
                for sec in item.get('sections', []):
                    for p in sec.get('products', []):
                        img = p.get('image', '')
                        if img:
                            fn = os.path.basename(img)
                            url = source_url(img, 'products')
                            dest = os.path.join(STORAGE_PUBLIC, 'products', fn)
                            tasks[dest] = {'url': url, 'full_path': dest}
        except Exception as e:
            print(f"Warning reading {jf}: {e}")

    # Category images
    cat_images = [
        '1791195622.jpg',
        '1791192941.jpg',
    ]
    for ci in cat_images:
        dest = os.path.join(STORAGE_PUBLIC, 'categories', ci)
        if not SOURCE_BASE_URL:
            raise RuntimeError('Set ESHAQ_BASE_URL to the Eshaq base URL before downloading remote images.')
        tasks[dest] = {
            'url': f"{SOURCE_BASE_URL}/storage/sections/{ci}",
            'full_path': dest
        }

    return list(tasks.values())

def download_item(item):
    url = item['url']
    full_path = item['full_path']

    if os.path.exists(full_path) and os.path.getsize(full_path) > 0:
        return {'status': 'skipped', 'url': url, 'path': full_path, 'bytes': os.path.getsize(full_path)}

    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    tmp_path = full_path + '.part'

    max_retries = 3
    last_error = None

    for attempt in range(max_retries):
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
                        return {'status': 'downloaded', 'url': url, 'path': full_path, 'bytes': len(data)}
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

    return {'status': 'failed', 'url': url, 'path': full_path, 'error': last_error}

def main():
    tasks = generate_manifest()
    total_tasks = len(tasks)
    print(f"Total files in dataset: {total_tasks}")

    start_time = time.time()
    last_report_time = start_time

    downloaded = 0
    skipped = 0
    failed = 0
    total_bytes = 0

    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        futures = {executor.submit(download_item, item): item for item in tasks}

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

            current_time = time.time()
            if current_time - last_report_time >= 3.0 or idx == total_tasks:
                elapsed = current_time - start_time
                pct = (idx / total_tasks) * 100
                mb = total_bytes / (1024 * 1024)
                print(f"[{pct:5.1f}%] {idx}/{total_tasks} | Down: {downloaded} | Exists: {skipped} | Fail: {failed} | {mb:.1f} MB", flush=True)
                last_report_time = current_time

    print("\n" + "=" * 50)
    print("DOWNLOAD VERIFICATION COMPLETE!")
    print(f"Total:      {total_tasks}")
    print(f"Downloaded: {downloaded}")
    print(f"Exists:     {skipped}")
    print(f"Failed:     {failed}")
    print("=" * 50)

if __name__ == '__main__':
    main()

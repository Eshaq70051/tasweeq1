import requests
import json
import sys
import os
import time
import re
from urllib.parse import urlparse
from concurrent.futures import ThreadPoolExecutor, as_completed

sys.stdout.reconfigure(encoding='utf-8')

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
API_BASE_URL = os.environ.get('ESHAQ_BASE_URL', '').rstrip('/')
if not API_BASE_URL:
    raise RuntimeError('Set ESHAQ_BASE_URL to the Eshaq API base URL before running this script.')

TOKEN_URL = f'{API_BASE_URL}/api/token/token'
OUTPUT_FILE = os.path.join(BASE_DIR, 'storage', 'eshaq_gifts_and_stationery.json')

session = requests.Session()
session.headers.update({
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json'
})

def get_token(retries=5):
    for attempt in range(retries):
        try:
            r = session.post(TOKEN_URL, data={'xxx': ''}, timeout=15)
            if r.status_code == 200:
                data = r.json()
                return data['access_token']
        except Exception as e:
            print(f"[get_token] Attempt {attempt+1} failed: {e}. Retrying in 2s...", flush=True)
            time.sleep(2)
    raise RuntimeError("Failed to obtain token from Eshaq API.")

token = get_token()
print("Successfully obtained Eshaq API token.", flush=True)
session.headers.update({'Authorization': f'Bearer {token}'})

def post_api(url, payload, retries=5):
    global token
    for attempt in range(retries):
        try:
            r = session.post(url, json=payload, timeout=20)
            if r.status_code == 401:
                print("Token expired. Refreshing...", flush=True)
                token = get_token()
                session.headers.update({'Authorization': f'Bearer {token}'})
                continue
            if r.status_code == 200:
                return r.json()
            time.sleep(1)
        except Exception as e:
            time.sleep(1.5)
    return {}

def local_image_path(value, directory):
    if not value:
        return value

    filename = os.path.basename(urlparse(value).path)
    return f'{directory}/{filename}' if filename else None

print("Step 1: Fetching all markets from Section 5 (التحف والهدايا والمكتبات)...", flush=True)
all_markets_dict = {}

offset = 0
while True:
    res = post_api(f'{API_BASE_URL}/api/v3/client/market/get', {'section': 5, 'from': offset, 'search': ''})
    markets = res.get('data', {}).get('markets', [])
    if not markets:
        break
    for m in markets:
        all_markets_dict[m['id']] = m
    offset += len(markets)
    print(f"  Got {len(all_markets_dict)} stores from Section 5 so far...", flush=True)
    if len(markets) < 20:
        break

# Also search keywords to ensure no gift or stationery store was placed in another section
keywords = ['مكتبة', 'هدايا', 'تحف', 'قرطاسية']
for kw in keywords:
    search_res = post_api(f'{API_BASE_URL}/api/v3/client/market/get', {'from': 0, 'search': kw})
    s_markets = search_res.get('data', {}).get('markets', [])
    for m in s_markets:
        if m['id'] not in all_markets_dict:
            # Let's inspect the name to make sure it's really gift/stationery (not e.g. a restaurant with 'هدايا')
            m_name = (m.get('name') or '').strip()
            print(f"  Found extra store via keyword '{kw}': [{m['id']}] {m_name}", flush=True)
            all_markets_dict[m['id']] = m

all_markets = list(all_markets_dict.values())
print(f"Total unique stores to process: {len(all_markets)}", flush=True)

def fetch_store_details(m):
    market_id = m['id']
    name = str(m.get('name') or '').strip()

    # 1. Fetch menu sections / categories
    prod_page = post_api(f'{API_BASE_URL}/api/v3/client/productPage', {'market_id': market_id})
    categories = prod_page.get('data', {}).get('categories', [])

    # 2. Fetch all products
    products = []
    p_offset = 0
    while True:
        p_res = post_api(f'{API_BASE_URL}/api/v3/client/product/get', {'market_id': market_id, 'from': p_offset})
        prods = p_res.get('data', {}).get('products', [])
        if not prods:
            break
        products.extend(prods)
        p_offset += len(prods)
        if len(prods) < 20:
            break

    # Extract English and Arabic name if separated
    name_en = ''
    if ' - ' in name:
        parts = [p.strip() for p in name.split(' - ')]
        for p in parts:
            if any('a' <= c.lower() <= 'z' for c in p):
                name_en = p
                break
    elif any('a' <= c.lower() <= 'z' for c in name):
        en_matches = re.findall(r'[a-zA-Z0-9\s]+', name)
        if en_matches:
            name_en = ' '.join(en_matches).strip()

    delivery = m.get('delivery_time') or '30 - 45'
    if not any(unit in str(delivery) for unit in ['دقيقة', 'ساعة', 'min']):
        delivery = f"{delivery} دقيقة"

    rate = float(m.get('rate') or 4.5)
    if rate <= 0:
        rate = 4.5

    banner = local_image_path(m.get('image'), 'stores/banners')
    logo = local_image_path(m.get('image'), 'stores/logos')

    return {
        'id': market_id,
        'name': name,
        'name_en': name_en if name_en else None,
        'logo': logo,
        'banner': banner,
        'rating': rate,
        'distance': f"{m.get('distance', 1.5)} كم" if m.get('distance') else "1.5 كم",
        'delivery_time': delivery,
        'is_open': m.get('status') == 0 or m.get('status') == 1,
        'is_featured': rate >= 4.3,
        'address': m.get('address') or 'صنعاء',
        'phone': m.get('phone'),
        'latitude': m.get('latitude'),
        'longitude': m.get('longitude'),
        'sections': [{'id': c['id'], 'name': str(c.get('name') or '').strip()} for c in categories if 'id' in c and 'name' in c],
        'products': [
            {
                'id': p['id'],
                'name': str(p.get('name') or '').strip(),
                'price': float(p.get('price') or 0),
                'old_price': float(p['special_price']) if (p.get('special_price') and float(p['special_price']) > 0 and float(p['special_price']) != float(p.get('price') or 0)) else None,
                'details': str(p.get('meal_type')) if p.get('meal_type') and str(p.get('meal_type')) != '.' else (str(p.get('option_name')) if p.get('option_name') else None),
                'image': local_image_path(p.get('image'), 'products'),
                'category_id': p.get('category_id'),
                'is_available': p.get('status', 1) == 1,
            }
            for p in products if str(p.get('name') or '').strip()
        ]
    }

print("\nStep 2: Fetching full menus and products for all stores concurrently...", flush=True)
completed_stores = []
total_count = len(all_markets)
done = 0

with ThreadPoolExecutor(max_workers=5) as executor:
    futures = {executor.submit(fetch_store_details, m): m for m in all_markets}
    for future in as_completed(futures):
        store_data = future.result()
        completed_stores.append(store_data)
        done += 1
        p_count = len(store_data['products'])
        s_count = len(store_data['sections'])
        print(f"[{done}/{total_count}] {store_data['name']}: {s_count} sections, {p_count} products", flush=True)

completed_stores.sort(key=lambda x: x['id'])

total_sections = sum(len(b['sections']) for b in completed_stores)
total_products = sum(len(b['products']) for b in completed_stores)

print("\nStep 3: Saving data to JSON...", flush=True)
os.makedirs(os.path.dirname(OUTPUT_FILE), exist_ok=True)
with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
    json.dump(completed_stores, f, ensure_ascii=False, indent=2)

print("=" * 60, flush=True)
print("SUCCESSFULLY COMPLETED SCRAPING!", flush=True)
print(f"Saved {len(completed_stores)} stores, {total_sections} menu sections, and {total_products} products to:")
print(f"  {OUTPUT_FILE}", flush=True)
print("=" * 60, flush=True)

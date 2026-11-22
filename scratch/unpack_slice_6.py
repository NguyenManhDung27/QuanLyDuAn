import json, base64, gzip, os
from bs4 import BeautifulSoup

bundle_path = 'soulwine-docs/docs/man-hinh/nhanvienkho.html'
with open(bundle_path, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
manifest = json.loads(soup.find('script', {'type': '__bundler/manifest'}).string)
page_order = json.loads(soup.find('script', {'type': '__bundler/page_order'}).string)

print("NHAN VIEN KHO SCREENS:")
for idx, pid in enumerate(page_order):
    entry = manifest.get(pid, {})
    title = entry.get('title', '')
    url = entry.get('url', '')
    print(f"[{idx}] pid: {pid} | url: {url} | title: {title}")

def unpack(idx, name):
    pid = page_order[idx]
    entry = manifest[pid]
    raw = base64.b64decode(entry['data'])
    if entry.get('compressed'):
        raw = gzip.decompress(raw)
    page_html = raw.decode('utf-8', errors='ignore')
    psoup = BeautifulSoup(page_html, 'html.parser')
    tmpl = psoup.find('script', {'type': '__bundler/template'})
    if tmpl:
        tmpl_str = json.loads(tmpl.string)
        with open(f"scratch/{name}_template.html", 'w', encoding='utf-8') as out:
            out.write(tmpl_str)
        print(f"Extracted template: scratch/{name}_template.html")
    else:
        with open(f"scratch/{name}.html", 'w', encoding='utf-8') as out:
            out.write(page_html)
        print(f"Extracted raw: scratch/{name}.html")

for idx, pid in enumerate(page_order):
    entry = manifest.get(pid, {})
    url = entry.get('url', f'screen_{idx}').replace('.dc.html', '').replace('.html', '')
    unpack(idx, f"NVK_{idx}_{url}")


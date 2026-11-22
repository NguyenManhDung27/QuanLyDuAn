import json
import base64
import gzip
from bs4 import BeautifulSoup

def inspect_file(path):
    print("=" * 60)
    print("INSPECTING:", path)
    with open(path, 'r', encoding='utf-8') as f:
        html = f.read()
    soup = BeautifulSoup(html, 'html.parser')
    manifest_script = soup.find('script', {'type': '__bundler/manifest'})
    page_order_script = soup.find('script', {'type': '__bundler/page_order'})
    if not manifest_script:
        print("No manifest found!")
        return
    manifest = json.loads(manifest_script.string)
    page_order = json.loads(page_order_script.string) if page_order_script else list(manifest.keys())
    
    for idx, pid in enumerate(page_order):
        entry = manifest.get(pid, {})
        title = entry.get('title', pid)
        url = entry.get('url', '')
        print(f"[{idx}] pid: {pid} | url: {url} | title: {title}")

inspect_file('soulwine-docs/docs/man-hinh/quanlykho.html')
inspect_file('soulwine-docs/docs/man-hinh/Ketoan.html')


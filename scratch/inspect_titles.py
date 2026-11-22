import json, base64, gzip, re
from bs4 import BeautifulSoup

def inspect_entries(path):
    print("=" * 60)
    print("INSPECTING:", path)
    with open(path, 'r', encoding='utf-8') as f:
        html = f.read()
    soup = BeautifulSoup(html, 'html.parser')
    manifest = json.loads(soup.find('script', {'type': '__bundler/manifest'}).string)
    page_order = json.loads(soup.find('script', {'type': '__bundler/page_order'}).string)
    
    for idx, pid in enumerate(page_order):
        entry = manifest.get(pid, {})
        raw_data = base64.b64decode(entry['data'])
        if entry.get('compressed'):
            raw_data = gzip.decompress(raw_data)
        page_html = raw_data.decode('utf-8', errors='ignore')
        
        # Check title / h1 / breadcrumb / template
        psoup = BeautifulSoup(page_html, 'html.parser')
        h1 = psoup.find('h1')
        h1_text = h1.get_text(strip=True) if h1 else 'No H1'
        
        # Look for template script if any
        tmpl_script = psoup.find('script', {'type': '__bundler/template'})
        if tmpl_script:
            try:
                tmpl_data = json.loads(tmpl_script.string)
                tsoup = BeautifulSoup(tmpl_data, 'html.parser')
                th1 = tsoup.find('h1')
                if th1:
                    h1_text += f" | Template H1: {th1.get_text(strip=True)}"
            except Exception:
                pass
        
        # Look for breadcrumb or main title in text
        match = re.search(r'<h1[^>]*>(.*?)</h1>', page_html)
        raw_h1 = match.group(1) if match else ''
        print(f"[{idx}] pid: {pid} | H1: {h1_text} | raw_h1: {raw_h1}")

inspect_entries('soulwine-docs/docs/man-hinh/quanlykho.html')
inspect_entries('soulwine-docs/docs/man-hinh/Ketoan.html')


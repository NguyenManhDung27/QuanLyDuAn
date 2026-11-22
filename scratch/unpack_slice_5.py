import json, base64, gzip, os
from bs4 import BeautifulSoup

def unpack_screen(bundle_path, index, out_name):
    with open(bundle_path, 'r', encoding='utf-8') as f:
        html = f.read()
    soup = BeautifulSoup(html, 'html.parser')
    manifest = json.loads(soup.find('script', {'type': '__bundler/manifest'}).string)
    page_order = json.loads(soup.find('script', {'type': '__bundler/page_order'}).string)
    
    pid = page_order[index]
    entry = manifest[pid]
    raw = base64.b64decode(entry['data'])
    if entry.get('compressed'):
        raw = gzip.decompress(raw)
    page_html = raw.decode('utf-8', errors='ignore')
    
    # Also extract template script
    psoup = BeautifulSoup(page_html, 'html.parser')
    tmpl = psoup.find('script', {'type': '__bundler/template'})
    if tmpl:
        tmpl_str = json.loads(tmpl.string)
        with open(f"scratch/{out_name}_template.html", 'w', encoding='utf-8') as out:
            out.write(tmpl_str)
        print(f"Extracted template: scratch/{out_name}_template.html")
    else:
        with open(f"scratch/{out_name}.html", 'w', encoding='utf-8') as out:
            out.write(page_html)
        print(f"Extracted raw: scratch/{out_name}.html")

# QLK
unpack_screen('soulwine-docs/docs/man-hinh/quanlykho.html', 2, 'QLK_03_DeXuat')
unpack_screen('soulwine-docs/docs/man-hinh/quanlykho.html', 3, 'QLK_05_DonDatHang')
unpack_screen('soulwine-docs/docs/man-hinh/quanlykho.html', 5, 'QLK_04_ChiTiet')

# KT
unpack_screen('soulwine-docs/docs/man-hinh/Ketoan.html', 1, 'KT_02_DeXuat')
unpack_screen('soulwine-docs/docs/man-hinh/Ketoan.html', 2, 'KT_03_Duyet')


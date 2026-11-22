import base64, gzip, json, re

for filename in ['soulwine-docs/docs/man-hinh/quanlykho.html', 'soulwine-docs/docs/man-hinh/Ketoan.html']:
    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()
    m = re.search(r'data-sc-manifest="([^"]+)"', content)
    if m:
        raw = gzip.decompress(base64.b64decode(m.group(1))).decode('utf-8')
        data = json.loads(raw)
        print('FILE:', filename)
        for s in data.get('screens', []):
            print(f"  - id: {s.get('id')} | title: {s.get('title')} | file: {s.get('filename')}")


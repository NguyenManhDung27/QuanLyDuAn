from bs4 import BeautifulSoup
import glob

for f in sorted(glob.glob('scratch/NVK_*_template.html'), key=lambda x: int(x.split('_')[1])):
    with open(f, 'r', encoding='utf-8') as fp:
        s = BeautifulSoup(fp.read(), 'html.parser')
        t = s.find('title')
        h1 = s.find('h1')
        title_text = t.text.strip() if t else ''
        h1_text = h1.text.strip() if h1 else ''
        print(f"{f}: Title = '{title_text}' | H1 = '{h1_text}'")


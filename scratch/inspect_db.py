import sqlite3

con = sqlite3.connect('database/database.sqlite')
cur = con.cursor()
print('LOS:')
for r in cur.execute("SELECT * FROM Lo WHERE Ma_lo_ncc LIKE '%TEST%'").fetchall():
    print(r)
print('PN:')
for r in cur.execute("SELECT Ma_phieu_nhap, Ma_kho, Trang_thai FROM Phieu_nhap_kho WHERE Ma_phieu_nhap LIKE '%TEST%'").fetchall():
    print(r)
print('TON_KHO:')
for r in cur.execute("SELECT * FROM Ton_kho").fetchall():
    print(r)


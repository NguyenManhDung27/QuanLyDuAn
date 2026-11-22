import sqlite3

con = sqlite3.connect('database/database.sqlite')
con.execute("DELETE FROM Chi_tiet_phieu_nhap WHERE Ma_phieu_nhap NOT IN (SELECT Ma_phieu_nhap FROM Phieu_nhap_kho)")
con.execute("DELETE FROM Chi_tiet_phieu_xuat WHERE Ma_phieu_xuat NOT IN (SELECT Ma_phieu_xuat FROM Phieu_xuat_kho)")
con.execute("DELETE FROM Chi_tiet_don_dat_hang WHERE Ma_don_dat_hang NOT IN (SELECT Ma_don_dat_hang FROM Don_dat_hang)")
con.execute("DELETE FROM Chi_tiet_don_ban WHERE Ma_don_hang_ban NOT IN (SELECT Ma_don_hang_ban FROM Don_hang_ban)")
con.commit()
print("Cleaned all orphaned detail rows.")


# 07. Nguồn dữ liệu hiển thị

Tài liệu này quy định mỗi ô trên màn hình lấy số từ đâu. Agent **không được tự thêm cột
để lưu các giá trị thuộc mục 2** — chúng được tính khi hiển thị.

Tên bảng và cột viết đúng như chương 3: tiếng Việt không dấu, viết hoa chữ cái đầu.

---

## 1. Công thức dùng chung

### F1. Lô thực xuất

```sql
Lo_xuat = COALESCE(Chi_tiet_phieu_xuat.Ma_lo_thuc_xuat,
                   Chi_tiet_phieu_xuat.Ma_lo_phan_bo)
```

`Ma_lo_thuc_xuat` chỉ có giá trị khi nhân viên kho đã đổi lô. Mọi tính toán tồn kho
liên quan phiếu xuất đều dùng `Lo_xuat`, không dùng thẳng `Ma_lo_phan_bo`.

### F2. Tồn thực (kho K, sản phẩm P)

```sql
SELECT COALESCE(SUM(tk.Ton_thuc), 0)
FROM Ton_kho tk
JOIN Lo l ON l.Ma_lo = tk.Ma_lo
WHERE tk.Ma_kho = :K AND l.Ma_san_pham = :P
```

### F3. Đang giữ cho đơn bán (kho K, sản phẩm P)

```sql
SELECT COALESCE(SUM(ct.So_luong), 0)
FROM Chi_tiet_phieu_xuat ct
JOIN Phieu_xuat_kho px ON px.Ma_phieu_xuat = ct.Ma_phieu_xuat
JOIN Don_hang_ban db ON db.Ma_don_hang_ban = px.Ma_don_hang_ban
JOIN Lo l ON l.Ma_lo = COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)
WHERE px.Trang_thai_giao_hang = 'Đang chuẩn bị hàng'
  AND db.Ma_kho = :K AND l.Ma_san_pham = :P
```

Hệ thống **không có bảng giữ hàng riêng**. Hàng được coi là đang giữ khi tồn tại dòng
phiếu xuất ở trạng thái `Đang chuẩn bị hàng`.

### F4. Tồn khả dụng

```
Ton_kha_dung = F2 − F3
```

### F5. Số còn lại của một dòng đơn đặt hàng

```sql
ct.So_luong_dat - COALESCE((
    SELECT SUM(ctn.So_luong_nhap + ctn.So_luong_hao_hut)
    FROM Chi_tiet_phieu_nhap ctn
    JOIN Phieu_nhap_kho pn ON pn.Ma_phieu_nhap = ctn.Ma_phieu_nhap
    JOIN Lo l ON l.Ma_lo = ctn.Ma_lo
    WHERE pn.Ma_don_dat_hang = ct.Ma_don_dat_hang
      AND pn.Trang_thai = 'Đã nhập kho'
      AND l.Ma_san_pham = ct.Ma_san_pham
), 0)
```

Hao hụt tính vào phần đã nhận, vì nhà cung cấp đã giao số đó rồi.

### F6. Tồn dự kiến (kho K, sản phẩm P)

```
Ton_du_kien = F2 + tổng F5 của các dòng đơn đặt hàng thuộc kho K, sản phẩm P,
              có Don_dat_hang.Trang_thai IN
              ('Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần')
```

### F7. Giá vốn bình quân gia quyền liên hoàn

Tính **chung toàn công ty**, lưu ở `San_pham.Gia_von_binh_quan`. Cập nhật từng dòng,
ngay khi xác nhận phiếu nhập, theo đúng thứ tự dòng trong phiếu.

```
Ton_truoc    = tổng Ton_kho.Ton_thuc của sản phẩm trên CẢ HAI kho, trước khi cộng dòng này
Gia_von_moi  = (Ton_truoc × Gia_von_binh_quan + So_luong_nhap × Don_gia_nhap)
               / (Ton_truoc + So_luong_nhap)
```

`Don_gia_nhap` tùy `Phieu_nhap_kho.Loai_nhap`:

| Loại nhập | `Don_gia_nhap` |
|---|---|
| Nhập từ NCC | `Chi_tiet_don_dat_hang.Don_gia_mua` của (đơn đặt hàng, sản phẩm) |
| Khách trả hàng | `Chi_tiet_phieu_xuat.Gia_von_xuat` của dòng phiếu xuất tương ứng |
| Hoàn giao thất bại | `Chi_tiet_phieu_xuat.Gia_von_xuat` của dòng phiếu xuất tương ứng |

Nếu `Ton_truoc + So_luong_nhap = 0` thì giữ nguyên giá vốn cũ.
Số lượng hao hụt **không** tham gia công thức, vì hàng đó không vào kho.

### F8. Chênh lệch kiểm kê

```
Chenh_lech = Chi_tiet_kiem_ke.So_luong_thuc_te − Chi_tiet_kiem_ke.So_luong_he_thong
```

Âm là thiếu, dương là thừa. Không lưu cột.

### F9. Nhãn "Cần xem xét" trên đơn đặt hàng

```
Don_dat_hang.Trang_thai IN ('Đã gửi NCC', 'Nhận một phần')
AND (
     Ngay_du_kien_nhan_du < CURDATE()
  OR EXISTS (phiếu nhập của đơn, Trang_thai = 'Đã nhập kho', Dot_giao_cuoi = 1)
)
```

### F10. Nhãn "Thiếu tồn" trên đơn bán và phiếu xuất

```
Phieu_xuat_kho.Trang_thai_giao_hang = 'Đang chuẩn bị hàng'
AND EXISTS dòng chi tiết có Ton_kho.Ton_thuc (của Lo_xuat, tại kho của đơn)
           < Chi_tiet_phieu_xuat.So_luong
```

### F11. Nhãn "Ưu tiên kiểm" trên lô

```
Moc = Thoi_diem_duyet của phiếu kiểm kê Hoàn tất gần nhất của kho đó
      (chưa có phiếu nào thì lấy '1900-01-01')

Lô được đánh dấu khi tồn tại dòng Chi_tiet_phieu_xuat với:
  Lo_xuat = lô đang xét
  AND Ly_do_doi_lo IN ('Không tìm thấy lô gốc', 'Hỏng hoặc vỡ')
  AND Thoi_diem_doi_lo > Moc
  AND Thoi_diem_doi_lo < Thoi_diem_tao của phiếu kiểm kê đang xem
```

Điều kiện cuối giúp mở lại phiếu cũ vẫn ra đúng tập lô ưu tiên lúc tạo phiếu.

### F12. Thứ tự FIFO

```sql
ORDER BY Ton_kho.Ngay_nhap_dau_tien ASC, Ton_kho.Ma_lo ASC
```

Chỉ xét lô cùng kho, cùng sản phẩm, còn tồn khả dụng > 0.

### F13. Đơn giá bán

```
Don_gia_ban = ROUND(San_pham.Gia_niem_yet × (1 − Chiet_khau / 100))
```

### F14. Thuế GTGT (chỉ hiển thị)

```
Thue       = Tong_tien_hang × 0.08
Tong_thanh_toan = Tong_tien_hang × 1.08
```

Không lưu vào cơ sở dữ liệu. Chỉ hiện trên màn lập đơn và chi tiết đơn bán, kèm chữ
"(tham khảo)".

---

## 2. Trường chụp và trường tính

| Giá trị | Cách xử lý | Thời điểm |
|---|---|---|
| `Chi_tiet_don_ban.Don_gia_ban`, `Chiet_khau` | **Chụp** | Khi xác nhận đơn bán |
| `Don_hang_ban.Tong_tien_hang` | **Chụp** | Khi xác nhận đơn bán |
| `Don_hang_ban.Ten_nguoi_nhan`, `Sdt_nguoi_nhan`, `Dia_chi_giao_hang` | **Chụp**, sửa được khi Đang chuẩn bị hàng | Khi xác nhận đơn bán |
| `Chi_tiet_don_dat_hang.Don_gia_mua` | **Chụp** | Khi lưu đề xuất |
| `Don_dat_hang.Tong_gia_tri` | **Chụp** | Khi lưu đề xuất |
| `Chi_tiet_phieu_xuat.Ma_lo_phan_bo` | **Chụp** | Khi xác nhận đơn bán (FIFO) |
| `Chi_tiet_phieu_xuat.Gia_von_xuat` | **Chụp** | Khi xác nhận xuất kho |
| `Chi_tiet_phieu_xuat.Gia_von_hang_hong` | **Chụp** | Khi đổi lô có hàng hỏng |
| `Chi_tiet_kiem_ke.So_luong_he_thong` | **Chụp** | Khi tạo phiếu kiểm kê |
| `Chi_tiet_kiem_ke.Gia_von_dieu_chinh` | **Chụp** | Khi duyệt phiếu kiểm kê |
| `San_pham.Gia_von_binh_quan` | **Ghi đè** | Sau mỗi dòng nhập kho được xác nhận |
| `Ton_kho.Ton_thuc` | **Ghi đè** | Nhập kho, xuất kho, đổi lô có hàng hỏng, duyệt kiểm kê |
| `Ton_kho.Ngay_nhap_dau_tien` | **Chụp một lần** | Lần đầu lô vào kho đó, không cập nhật ở các lần nhập sau |
| Tồn khả dụng, tồn dự kiến | **Tính** (F4, F6) | Khi hiển thị |
| Chênh lệch kiểm kê, giá trị chênh lệch | **Tính** (F8) | Khi hiển thị |
| Nhãn Cần xem xét, Thiếu tồn, Ưu tiên kiểm | **Tính** (F9–F11) | Khi hiển thị |
| Đã nhận / Còn lại của đơn đặt hàng | **Tính** (F5) | Khi hiển thị |
| Thuế GTGT, tổng thanh toán | **Tính** (F14) | Khi hiển thị |
| Số liệu mọi báo cáo và ô "việc cần xử lý" | **Tính** | Khi hiển thị |

Quy tắc chung: **giá trị nào dùng để đối chiếu về sau thì chụp, giá trị nào phản ánh
hiện trạng thì tính**. Giá bán đã chụp nên đổi giá niêm yết không ảnh hưởng đơn cũ.

---

## 3. Nguồn dữ liệu theo màn hình

### QLK-02. Theo dõi tồn kho

Một dòng là một sản phẩm. `:K` = `Nguoi_dung.Ma_kho` của người đăng nhập.

| Cột | Nguồn |
|---|---|
| Mã SP, Sản phẩm, Niên vụ, Dung tích | `San_pham` |
| Tồn thực | F2(:K, sản phẩm) |
| Khả dụng | F4(:K, sản phẩm) |
| Dự kiến | F6(:K, sản phẩm) |
| Tồn thực toàn công ty | F2 cộng cả hai kho |

Bảng lô khi mở rộng một dòng:

| Cột | Nguồn |
|---|---|
| Thứ tự xuất | Vị trí theo F12 |
| Số lô | `Lo.Ma_lo_ncc` |
| Ngày nhập đầu tiên | `Ton_kho.Ngay_nhap_dau_tien` |
| Ngày sản xuất, Hạn sử dụng | `Lo.Ngay_san_xuat`, `Lo.Han_su_dung` |
| Tồn thực | `Ton_kho.Ton_thuc` |
| Đang giữ | F3 giới hạn theo lô |
| Khả dụng | Tồn thực − Đang giữ |

Lọc: chỉ lô có `Ton_thuc > 0`. Không hiển thị cột tiền trên màn này.

### QLK-03. Đề xuất nhập kho (danh sách)

Lọc: `Ma_kho = :K` **và** `Trang_thai IN ('Đang cập nhật','Chờ phê duyệt','Từ chối','Đề xuất đã hủy')`.

| Cột | Nguồn |
|---|---|
| Mã đề xuất | `Don_dat_hang.Ma_don_dat_hang` |
| Nhà cung cấp | `Don_dat_hang.Ma_ncc` → `Nha_cung_cap.Ten_ncc` |
| Ngày lập | `Don_dat_hang.Ngay_lap` |
| Ngày dự kiến nhận đủ | `Don_dat_hang.Ngay_du_kien_nhan_du` |
| Tổng giá trị | `Don_dat_hang.Tong_gia_tri` |
| Trạng thái | `Don_dat_hang.Trang_thai` |
| Ghi chú | `Ly_do_tu_choi` nếu Từ chối; `Ly_do_huy` nếu Đề xuất đã hủy |

### QLK-05. Đơn đặt hàng (danh sách)

Lọc: `Ma_kho = :K` **và** `Trang_thai IN ('Chưa gửi NCC','Đã gửi NCC','Nhận một phần','Đã nhận đủ','Đóng thiếu','Đơn đã hủy')`.

| Cột | Nguồn |
|---|---|
| Mã đơn | `Ma_don_dat_hang` (giữ nguyên mã của đề xuất) |
| Ngày gửi NCC | `Ngay_gui_ncc`, hiển thị "—" nếu NULL |
| Dự kiến nhận đủ | `Ngay_du_kien_nhan_du` |
| Tiến độ nhận | Đã nhận = Σ(F5 nghịch đảo) trên mọi dòng; Tổng đặt = Σ`So_luong_dat` |
| Nhãn Cần xem xét | F9 |

### QLK-04. Chi tiết đề xuất / đơn đặt hàng

| Vùng | Nguồn |
|---|---|
| Thông tin chung | `Don_dat_hang` + `Nha_cung_cap.Ten_ncc` + `Kho.Ten_kho` + `Nguoi_dung.Ho_ten` của `Ma_nguoi_lap`, `Ma_nguoi_duyet`, `Ma_nguoi_gui_ncc` |
| Dòng sản phẩm | `Chi_tiet_don_dat_hang` + `San_pham.Ten_san_pham`, `Nien_vu`, `Dung_tich` |
| Thành tiền từng dòng | `So_luong_dat × Don_gia_mua` |
| Đã nhận / Hao hụt / Còn lại | Σ`So_luong_nhap`, Σ`So_luong_hao_hut` của phiếu nhập Đã nhập kho; Còn lại theo F5 |
| Các lần nhận hàng | `Phieu_nhap_kho` của đơn, `Trang_thai = 'Đã nhập kho'`, kèm `Nguoi_dung.Ho_ten` |
| Banner từ chối | `Ly_do_tu_choi` |

### QLK-06 / NVK-09. Phiếu kiểm kê (danh sách)

| Cột | Nguồn |
|---|---|
| Mã phiếu, Thời điểm tạo, Trạng thái | `Phieu_kiem_ke` |
| Người kiểm | `Ma_nguoi_kiem` → `Nguoi_dung.Ho_ten`; NULL hiển thị "Chưa giao" |
| Số lô kiểm | `COUNT(Chi_tiet_kiem_ke)` |
| Đã đếm | `COUNT(So_luong_thuc_te IS NOT NULL)` / tổng số lô |
| Dòng chênh lệch | `COUNT` dòng có F8 ≠ 0; phiếu chưa đếm xong hiển thị "—" |

QLK-06 lọc theo `Ma_kho = :K`.
NVK-09 lọc thêm: `Ma_nguoi_kiem = người đăng nhập` **hoặc** `Ma_nguoi_kiem IS NULL`.

### QLK-07. Tạo phiếu kiểm kê

| Cột | Nguồn |
|---|---|
| Danh sách lô | `Ton_kho` của `:K`, `Ton_thuc > 0`, join `Lo`, `San_pham` |
| Tồn hệ thống | `Ton_kho.Ton_thuc` tại thời điểm xem |
| Ưu tiên kiểm | F11 |
| Người kiểm | `Nguoi_dung` có vai trò Nhân viên kho và `Ma_kho = :K` |

Sắp xếp: lô ưu tiên lên đầu, sau đó theo F12. Lô ưu tiên được tick sẵn.

### QLK-08 / NVK-10. Chi tiết và đếm kiểm kê

| Cột | Nguồn |
|---|---|
| Sản phẩm, Lô, Hạn sử dụng | `Chi_tiet_kiem_ke.Ma_lo` → `Lo` → `San_pham` |
| Tồn hệ thống | `Chi_tiet_kiem_ke.So_luong_he_thong` (đã chụp, **không đọc lại `Ton_kho`**) |
| Thực tế | `Chi_tiet_kiem_ke.So_luong_thuc_te` |
| Chênh lệch | F8 |
| Lý do chênh lệch | `Chi_tiet_kiem_ke.Ly_do_chenh_lech` |

### QLK-09 / KD-06 / KT-05. Sản phẩm

| Cột | Nguồn | Màn nào thấy |
|---|---|---|
| Thông tin sản phẩm | `San_pham` | Cả ba |
| Giá niêm yết | `San_pham.Gia_niem_yet`; chưa có thì hiện "Chưa có giá" | QLK-09 chỉ đọc, KD-06 sửa được, KT-05 chỉ đọc |
| Tồn khả dụng HN / HCM | F4 theo từng kho | KD-06, KT-05 |
| Giá vốn bình quân | `San_pham.Gia_von_binh_quan` | **Chỉ KT-05** |
| Giá trị tồn | F2 toàn công ty × `Gia_von_binh_quan` | **Chỉ KT-05** |

### QLK-10 / KT-06. Nhà cung cấp — trực tiếp từ `Nha_cung_cap`
### KD-05 / KT-07. Khách hàng — trực tiếp từ `Khach_hang`

### QLK-11. Báo cáo của Quản lý kho

Chỉ số lượng, giới hạn `Ma_kho = :K`, không có cột tiền.

| Tab | Nguồn |
|---|---|
| Nhập – xuất – tồn | Tồn đầu kỳ suy ngược từ tồn hiện tại; Nhập = Σ`Chi_tiet_phieu_nhap.So_luong_nhap`; Xuất = Σ`Chi_tiet_phieu_xuat.So_luong` của phiếu đã xuất kho + Σ`So_luong_hong`; Điều chỉnh = Σ F8 của phiếu kiểm kê Hoàn tất |
| Giao hàng theo NCC | `Don_dat_hang` + `Phieu_nhap_kho` nhóm theo `Ma_ncc`. Đúng hạn: phiếu nhập cuối có `Thoi_diem_xac_nhan` ≤ `Ngay_du_kien_nhan_du` |
| Hàng hỏng | `Chi_tiet_phieu_xuat` có `So_luong_hong > 0` |
| Chênh lệch kiểm kê | `Chi_tiet_kiem_ke` của phiếu Hoàn tất, chỉ dòng F8 ≠ 0 |

### NVK-02. Phiếu nhập kho (danh sách)

| Cột | Nguồn |
|---|---|
| Loại nhập | `Phieu_nhap_kho.Loai_nhap` |
| Chứng từ gốc | `Ma_don_dat_hang` nếu Nhập từ NCC; `Ma_phieu_xuat` + `Ma_don_hang_ban` nếu loại khác |
| SL nhập | Σ`Chi_tiet_phieu_nhap.So_luong_nhap` |
| Nhãn "Chờ kiểm kê xong" | Phiếu Đang cập nhật có ≥1 lô thuộc phiếu kiểm kê chưa Hoàn tất |

### NVK-03. Nhập kho – chọn đơn đặt hàng

Lọc: `Ma_kho = :K`, `Trang_thai IN ('Đã gửi NCC','Nhận một phần')`.

| Cột | Nguồn |
|---|---|
| Đã nhận / Đặt | Như QLK-05 |
| Số lần đã giao | `COUNT(Phieu_nhap_kho)` của đơn, `Trang_thai = 'Đã nhập kho'` |
| Nhãn Cần xem xét | F9 |

### NVK-04. Nhập hàng từ NCC

| Cột | Nguồn |
|---|---|
| Danh sách sản phẩm | `Chi_tiet_don_dat_hang` của đơn |
| Còn lại | F5 |
| Mã lô | Người dùng gõ. Khớp `Lo.Ma_lo_ncc` + `Ma_san_pham` → tự điền `Ngay_san_xuat`, `Han_su_dung` và khóa hai ô đó. Không khớp thì tạo `Lo` mới khi xác nhận |
| SL nhận đạt → `So_luong_nhap`; Hao hụt → `So_luong_hao_hut`; Lý do → `Ly_do_hao_hut` | `Chi_tiet_phieu_nhap` |

Kiểm tra khi xác nhận: với mỗi sản phẩm, Σ(`So_luong_nhap` + `So_luong_hao_hut`) của
phiếu này ≤ F5.

### NVK-05 / NVK-06. Nhập kho hàng hoàn

| Màn | Nguồn danh sách phiếu xuất |
|---|---|
| NVK-05 Khách trả hàng | `Trang_thai_giao_hang = 'Giao thành công'` và `Trang_thai_hoan_hang IS NULL`. Tìm theo `Ma_don_hang_ban` hoặc tên, SĐT khách |
| NVK-06 Hoàn giao thất bại | `Trang_thai_giao_hang = 'Giao hàng thất bại'` và `Trang_thai_hoan_hang = 'Chờ xử lý'` |

| Cột bảng dòng hàng | Nguồn |
|---|---|
| Sản phẩm, Lô | `Chi_tiet_phieu_xuat` → `Lo_xuat` (F1) |
| Đã xuất | `Chi_tiet_phieu_xuat.So_luong` |
| SL thực nhận | Người dùng nhập, ≤ Đã xuất → `Chi_tiet_phieu_nhap.So_luong_nhap` |

Hàng trả nhập **đúng lô đã xuất**, không tạo lô mới.

### NVK-07. Phiếu xuất kho (danh sách)

Lọc: `Don_hang_ban.Ma_kho = :K`.

| Cột | Nguồn |
|---|---|
| Đơn bán, Người nhận, Địa chỉ giao | `Don_hang_ban` |
| Vận đơn | `Don_vi_van_chuyen` + `Ma_van_don`, NULL hiển thị "—" |
| Nhãn Thiếu tồn | F10 |
| Nhãn Chờ xử lý hoàn | `Trang_thai_hoan_hang = 'Chờ xử lý'` |

### NVK-08. Chi tiết phiếu xuất

| Cột | Nguồn |
|---|---|
| Lô phân bổ | `Ma_lo_phan_bo` → `Lo.Ma_lo_ncc`, `Han_su_dung` |
| Lô thực xuất | `Ma_lo_thuc_xuat`; NULL hiển thị "Như lô phân bổ" |
| Số lượng | `Chi_tiet_phieu_xuat.So_luong` |
| Ghi chú | `Ly_do_doi_lo`; dòng thiếu tồn hiện cảnh báo theo F10 |

Cửa sổ Đổi lô:

| Thành phần | Nguồn |
|---|---|
| Lô thay thế | Lô cùng sản phẩm, cùng kho, khả dụng > 0, sắp theo F12, loại trừ lô gốc |
| Tồn khả dụng của lô | F4 giới hạn theo lô |
| Cảnh báo "Không còn lô nào đủ hàng" | Tổng khả dụng các lô thay thế < số cần đổi |

### KD-02. Đơn hàng bán (danh sách)

| Cột | Nguồn |
|---|---|
| Kho xuất | `Don_hang_ban.Ma_kho` → `Kho.Ten_kho` |
| Ngày xác nhận | `Thoi_diem_xac_nhan` |
| Tiền hàng | `Tong_tien_hang` |
| Trạng thái | `Don_hang_ban.Trang_thai` |
| Nhãn Thiếu tồn | F10 trên phiếu xuất của đơn |

### KD-03. Lập đơn bán

| Thành phần | Nguồn |
|---|---|
| Kho xuất | `Kho` có `Trang_thai = 'Hoạt động'` |
| Khách hàng | `Khach_hang` có `Trang_thai = 'Đang giao dịch'` |
| Người nhận, địa chỉ | Điền sẵn từ `Khach_hang`, sửa được |
| Danh sách sản phẩm | `San_pham` có `Trang_thai = 'Đang kinh doanh'`; sản phẩm `Gia_niem_yet IS NULL` hiện mờ, không chọn được |
| Tồn khả dụng | F4 theo kho xuất đang chọn, đổi lại khi đổi kho |
| Giá niêm yết | `San_pham.Gia_niem_yet` |
| Đơn giá bán | F13 |
| Thành tiền | `So_luong × Don_gia_ban` |
| Tổng tiền hàng | Σ thành tiền → `Tong_tien_hang` |
| Thuế, tổng thanh toán | F14, chỉ hiển thị |

Chặn xác nhận khi có dòng vượt F4.

### KD-04. Chi tiết đơn bán

| Vùng | Nguồn |
|---|---|
| Thông tin chung | `Don_hang_ban` + `Khach_hang` + `Kho` + `Nguoi_dung.Ho_ten` |
| Dòng sản phẩm | `Chi_tiet_don_ban` — **đọc giá đã chụp**, không tính lại từ `San_pham.Gia_niem_yet` |
| Giao hàng | `Phieu_xuat_kho`: `Ma_phieu_xuat`, `Don_vi_van_chuyen`, `Ma_van_don`, `Thoi_diem_xuat_kho`, `Trang_thai_giao_hang`, `Thoi_diem_ket_qua_giao` |
| Banner giao thất bại | `Ly_do_giao_that_bai` |

### KT-02. Đề xuất nhập kho (Kế toán)

Thấy cả hai kho. Nhãn trạng thái hiển thị theo ánh xạ:

| `Trang_thai` trong DB | Nhãn hiển thị |
|---|---|
| Chờ phê duyệt | Chờ phê duyệt |
| Từ chối | Từ chối |
| Chưa gửi NCC, Đã gửi NCC, Nhận một phần, Đã nhận đủ, Đóng thiếu, Đơn đã hủy | **Đã duyệt** |
| Đang cập nhật, Đề xuất đã hủy | **Không hiển thị** — Kế toán không thấy đề xuất chưa từng gửi duyệt |

### KT-03. Duyệt đề xuất

Ngoài dữ liệu như QLK-04, có thêm 3 cột tham khảo, tính khi hiển thị, không lưu:

| Cột | Nguồn |
|---|---|
| Giá vốn bình quân hiện tại | `San_pham.Gia_von_binh_quan` |
| Cảnh báo cao hơn giá vốn | Hiện khi `Don_gia_mua > Gia_von_binh_quan`; phần trăm = `(Don_gia_mua − Gia_von_binh_quan) / Gia_von_binh_quan × 100` |
| Tồn khả dụng kho nhận | F4(`Don_dat_hang.Ma_kho`, sản phẩm) |
| Bán 90 ngày qua | Σ`Chi_tiet_phieu_xuat.So_luong` của phiếu `Trang_thai_giao_hang = 'Giao thành công'`, `Thoi_diem_ket_qua_giao ≥ CURDATE() − 90`, cùng kho, trừ đi số đã nhập lại qua phiếu nhập `Khách trả hàng` |

### KT-04. Báo cáo của Kế toán

Thấy cả hai kho, có cột tiền.

| Tab | Nguồn chính |
|---|---|
| Tồn kho | `Ton_kho` × `San_pham.Gia_von_binh_quan`; Đang giữ theo F3 |
| Nhập – xuất – tồn | Như QLK-11 nhưng nhân giá vốn |
| Doanh thu – lãi gộp | Doanh thu = Σ(`Chi_tiet_don_ban.So_luong × Don_gia_ban`) của đơn `Hoàn thành`; Giá vốn = Σ(`Chi_tiet_phieu_xuat.So_luong × Gia_von_xuat`); trừ phần khách trả đã nhập lại |
| Mua hàng | `Don_dat_hang` + `Chi_tiet_don_dat_hang` + `Chi_tiet_phieu_nhap`. Hao hụt GT = Σ`So_luong_hao_hut × Don_gia_mua` |
| Hàng trả và giao thất bại | `Phieu_xuat_kho` có `Trang_thai_hoan_hang` khác NULL. GT không nhập = số không nhập × `Gia_von_xuat` |
| Điều chỉnh tồn | `Chi_tiet_kiem_ke` (phiếu Hoàn tất) × `Gia_von_dieu_chinh`, và `Chi_tiet_phieu_xuat.So_luong_hong × Gia_von_hang_hong` |

### ADM-02 / ADM-04. Người dùng

| Cột | Nguồn |
|---|---|
| Vai trò | `Ma_vai_tro` → `Vai_tro.Ten_vai_tro` |
| Kho | `Ma_kho` → `Kho.Ten_kho`; NULL hiển thị "—" |
| Trạng thái | `Nguoi_dung.Trang_thai` |

Ô Kho chỉ hiện khi vai trò là Nhân viên kho hoặc Quản lý kho, và khi đó bắt buộc.

### ADM-06. Nhật ký hệ thống

Lấy từ bảng kỹ thuật `activity_log` của Laravel, không thuộc ERD nghiệp vụ.

| Cột | Nguồn |
|---|---|
| Thời điểm, Người thực hiện, Vai trò, Thao tác | `activity_log` |
| Chứng từ | Mã chứng từ dạng chữ, không gắn liên kết |

---

## 4. Ô "Việc cần xử lý" trên trang chủ

Chuông trên header = tổng các ô của vai trò đó.

| Vai trò | Ô | Điều kiện đếm |
|---|---|---|
| Quản lý kho | Đề xuất bị từ chối | `Trang_thai = 'Từ chối'`, kho mình |
| | Đơn chưa gửi NCC | `Trang_thai = 'Chưa gửi NCC'` |
| | Đơn cần xem xét | F9 |
| | Phiếu kiểm kê chờ duyệt | `Phieu_kiem_ke.Trang_thai = 'Chờ duyệt'` |
| Nhân viên kho | Phiếu xuất cần chuẩn bị | `Trang_thai_giao_hang = 'Đang chuẩn bị hàng'` |
| | Đang giao chờ kết quả | `Trang_thai_giao_hang = 'Đang giao'` |
| | Giao thất bại chờ xử lý hoàn | `Trang_thai_hoan_hang = 'Chờ xử lý'` |
| | Phiếu nhập đang cập nhật | `Phieu_nhap_kho.Trang_thai = 'Đang cập nhật'` |
| | Phiếu kiểm kê cần đếm | `Trang_thai = 'Đang kiểm'` và (giao cho mình hoặc chưa giao) |
| Kế toán | Đề xuất chờ duyệt | `Trang_thai = 'Chờ phê duyệt'`, cả hai kho |
| Kinh doanh | Đơn thiếu tồn | F10 |
| | Đơn giao thất bại | `Don_hang_ban.Trang_thai = 'Giao thất bại'` |
| | Đơn đang chuẩn bị hàng | `Don_hang_ban.Trang_thai = 'Đang chuẩn bị hàng'` |
| Quản trị | Tài khoản bị khóa | `Nguoi_dung.Trang_thai = 'Khóa'` |

Sáu ô số liệu tháng của KT-01: giá trị tồn kho, doanh thu, giá vốn hàng bán, lãi gộp,
giá trị nhập mua, hàng mất hỏng — lấy cùng công thức với các tab tương ứng ở KT-04.

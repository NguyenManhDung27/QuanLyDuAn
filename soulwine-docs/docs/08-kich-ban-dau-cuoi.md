# 08. Kịch bản đầu cuối

Tám kịch bản dưới đây chạy nối tiếp nhau trên **cùng một bộ dữ liệu**, theo thứ tự thời
gian. Agent dùng tài liệu này để hiểu một chứng từ đi qua những màn hình nào, bảng nào
thay đổi và màn hình của vai trò khác hiển thị khác đi ra sao.

Số liệu ở đây cũng dùng làm **dữ liệu seed và dữ liệu kiểm thử**.

---

## 0. Dữ liệu nền

**Kho**

| Ma_kho | Ten_kho | Trang_thai |
|---|---|---|
| HN | Kho Hà Nội | Hoạt động |
| HCM | Kho TP.HCM | Hoạt động |

**Người dùng**

| Ten_dang_nhap | Ho_ten | Vai trò | Ma_kho |
|---|---|---|---|
| quantri01 | Phạm Minh Khôi | Quản trị | NULL |
| qlkho.hn | Nguyễn Đức Anh | Quản lý kho | HN |
| nvkho.hn | Lê Văn Bình | Nhân viên kho | HN |
| ketoan01 | Nguyễn Vân Anh | Kế toán | NULL |
| kinhdoanh01 | Lê Thị Huyền Trang | Kinh doanh | NULL |

**Sản phẩm**

| Ma_san_pham | Ten_san_pham | Nien_vu | Dung_tich | Gia_niem_yet | Gia_von_binh_quan |
|---|---|---|---|---|---|
| SP001 | Château Dupont Rouge | 2018 | 750 | 1.200.000 | 800.000,00 |
| SP002 | Chablis Blanc | NULL | 750 | 650.000 | 0,00 |

**Nhà cung cấp**: `NCC01` – Vignobles Dupont (Pháp)
**Khách hàng**: `KH01` – Nhà hàng Sen Tây Hồ

**Tồn kho đầu kỳ**

| Ma_kho | Ma_lo | Ma_san_pham | Ton_thuc | Ngay_nhap_dau_tien |
|---|---|---|---|---|
| HN | L2201 | SP001 | 10 | 15/01/2026 |

---

## KB1. Mua hàng và nhập kho nhiều đợt, kết thúc bằng Đóng thiếu

### B1.1 — QLK lập đề xuất · màn QLK-04 · `qlkho.hn`

Chọn NCC01, kho HN, ngày dự kiến nhận đủ 30/08/2026. Thêm 2 dòng:

| Sản phẩm | So_luong_dat | Don_gia_mua | Thành tiền |
|---|---|---|---|
| SP001 | 100 | 900.000 | 90.000.000 |
| SP002 | 60 | 500.000 | 30.000.000 |

Bấm **Lưu**.

```
Don_dat_hang: Ma='DDH-2026-0001', Ma_ncc='NCC01', Ma_kho='HN',
  Ma_nguoi_lap='qlkho.hn', Ngay_lap=18/08/2026,
  Ngay_du_kien_nhan_du=30/08/2026, Tong_gia_tri=120.000.000,
  Trang_thai='Đang cập nhật'
Chi_tiet_don_dat_hang: 2 dòng như bảng trên
Tồn kho: không đổi
```

### B1.2 — Gửi duyệt · màn QLK-04

`Trang_thai = 'Chờ phê duyệt'`.

Màn khác đổi: **KT-01** ô "Đề xuất nhập kho chờ duyệt" tăng 1. **KT-02** có thêm dòng.
**QLK-03** dòng đổi nhãn sang Chờ phê duyệt.

### B1.3 — Kế toán duyệt · màn KT-03 · `ketoan01`

Màn hiển thị 3 cột tham khảo:

| Sản phẩm | Đơn giá mua | Giá vốn BQ hiện tại | Cảnh báo | Tồn khả dụng HN | Bán 90 ngày |
|---|---|---|---|---|---|
| SP001 | 900.000 | 800.000,00 | Cao hơn giá vốn bình quân hiện tại 12,5% | 10 | 0 |
| SP002 | 500.000 | 0,00 | — | 0 | 0 |

Bấm **Duyệt**, ghi chú để trống.

```
Don_dat_hang: Trang_thai='Chưa gửi NCC',
  Ma_nguoi_duyet='ketoan01', Thoi_diem_duyet=19/08/2026 09:15
```

Màn khác đổi: **QLK-03** mất dòng này. **QLK-05** có thêm dòng. **QLK-01** ô "Đơn chưa
gửi NCC" tăng 1. **KT-02** nhãn chuyển sang Đã duyệt.

### B1.4 — Xác nhận đã gửi NCC · màn QLK-04

```
Don_dat_hang: Trang_thai='Đã gửi NCC',
  Ma_nguoi_gui_ncc='qlkho.hn', Ngay_gui_ncc=19/08/2026
```

Màn khác đổi: **NVK-03** đơn xuất hiện trong danh sách chờ nhận.

### B1.5 — Nhận đợt 1 · màn NVK-03 → NVK-04 · `nvkho.hn` · 28/08/2026

NCC giao 60 chai SP001 (mã lô in trên thùng `LOT-FR-2301`, NSX 10/03/2023, HSD
10/03/2033), trong đó 2 chai vỡ khi mở thùng. SP002 giao đủ 60 chai (`LOT-FR-M301`).

Nhập vào bảng:

| Sản phẩm | Mã lô | SL nhận đạt | Hao hụt | Lý do hao hụt |
|---|---|---|---|---|
| SP001 | LOT-FR-2301 | 58 | 2 | 2 chai vỡ trong thùng |
| SP002 | LOT-FR-M301 | 60 | 0 | — |

Bấm **Lưu** → `Phieu_nhap_kho PN-2026-0001`, `Trang_thai='Đang cập nhật'`, tồn chưa đổi.
Bấm **Xác nhận nhập kho**, trong **một transaction**:

```
Lo: tạo L2301 (SP001, Ma_lo_ncc='LOT-FR-2301', NSX, HSD)
    tạo M2301 (SP002, Ma_lo_ncc='LOT-FR-M301')
Ton_kho: (HN, L2301) Ton_thuc=58, Ngay_nhap_dau_tien=28/08/2026 14:20
         (HN, M2301) Ton_thuc=60, Ngay_nhap_dau_tien=28/08/2026 14:20
San_pham SP001.Gia_von_binh_quan:
    (10 × 800.000 + 58 × 900.000) / 68 = 885.294,12
San_pham SP002.Gia_von_binh_quan:
    (0 + 60 × 500.000) / 60 = 500.000,00
Phieu_nhap_kho: Trang_thai='Đã nhập kho', Thoi_diem_xac_nhan=28/08/2026 14:20
Don_dat_hang: Trang_thai='Nhận một phần'
```

Tiến độ đơn: SP001 đã nhận 60/100 (58 đạt + 2 hao hụt), còn 40. SP002 60/60, còn 0.

Màn khác đổi: **QLK-02** tồn thực SP001 HN = 68. **QLK-05** tiến độ 120/160.
**KD-03** tồn khả dụng SP001 HN = 68.

### B1.6 — Nhận đợt 2, đợt giao cuối · màn NVK-04 · 05/09/2026

Đã quá `Ngay_du_kien_nhan_du` 30/08 → từ 31/08 đơn đã mang nhãn **Cần xem xét** (F9).

NCC giao thêm 30 chai SP001, lô `LOT-FR-2305`, báo không giao tiếp. Nhân viên kho tick
**Đợt giao cuối**.

```
Lo: tạo L2305 (SP001, Ma_lo_ncc='LOT-FR-2305')
Ton_kho: (HN, L2305) Ton_thuc=30, Ngay_nhap_dau_tien=05/09/2026
San_pham SP001.Gia_von_binh_quan:
    (68 × 885.294,12 + 30 × 900.000) / 98 = 889.795,92
Phieu_nhap_kho PN-2026-0002: Loai_nhap='Nhập từ NCC', Dot_giao_cuoi=1,
    Trang_thai='Đã nhập kho'
Don_dat_hang: vẫn 'Nhận một phần' (SP001 mới 90/100)
```

Tồn SP001 tại HN: 10 + 58 + 30 = **98**.

### B1.7 — Đóng thiếu · màn QLK-04 · `qlkho.hn`

Nút Đóng thiếu dùng được vì không còn phiếu nhập Đang cập nhật của đơn.
Lý do: "NCC báo hết hàng niên vụ 2018, không giao tiếp."

```
Don_dat_hang: Trang_thai='Đóng thiếu',
  Ma_nguoi_dong_thieu='qlkho.hn', Thoi_diem_dong_thieu=06/09/2026 08:30,
  Ly_do_dong_thieu='NCC báo hết hàng niên vụ 2018, không giao tiếp'
```

Màn khác đổi: **NVK-03** bớt dòng. **QLK-01** ô "Đơn cần xem xét" giảm 1.

---

## KB2. Bán hàng, xuất kho và giao thành công

Trạng thái trước: SP001 tại HN có 3 lô — L2201: 10 (15/01), L2301: 58 (28/08),
L2305: 30 (05/09). Giá vốn bình quân 889.795,92.

### B2.1 — Lập đơn bán · màn KD-03 · `kinhdoanh01` · 10/09/2026

Kho xuất HN, khách KH01, SP001 số lượng 24, chiết khấu 5%.

```
Giá niêm yết 1.200.000 → Đơn giá bán = 1.200.000 × 0,95 = 1.140.000
Thành tiền = 24 × 1.140.000 = 27.360.000
Thuế GTGT 8% = 2.188.800 (chỉ hiển thị)
Tổng thanh toán = 29.548.800 (chỉ hiển thị)
Tồn khả dụng HN = 98 ≥ 24 → nút Xác nhận bật
```

Bấm **Xác nhận đơn hàng**, một transaction:

```
Don_hang_ban: Ma='DB-2026-0001', Ma_khach_hang='KH01', Ma_kho='HN',
  Ma_nguoi_lap='kinhdoanh01', Thoi_diem_xac_nhan=10/09/2026 10:05,
  Tong_tien_hang=27.360.000, Trang_thai='Đang chuẩn bị hàng',
  Ten_nguoi_nhan / Sdt_nguoi_nhan / Dia_chi_giao_hang chép từ KH01
Chi_tiet_don_ban: SP001, So_luong=24, Don_gia_ban=1.140.000, Chiet_khau=5.00
Phieu_xuat_kho: Ma='PX-2026-0001', Ma_don_hang_ban='DB-2026-0001',
  Ngay_tao=10/09/2026, Trang_thai_giao_hang='Đang chuẩn bị hàng'
Chi_tiet_phieu_xuat (phân bổ FIFO theo Ngay_nhap_dau_tien):
  dòng 1: Ma_lo_phan_bo='L2201', So_luong=10
  dòng 2: Ma_lo_phan_bo='L2301', So_luong=14
Ton_kho: KHÔNG đổi
```

Tồn khả dụng SP001 HN = 98 − 24 = **74**. Tồn thực vẫn 98.

Màn khác đổi: **NVK-01** ô "Phiếu xuất cần chuẩn bị" tăng 1. **NVK-07** có thêm dòng.
**QLK-02** cột Khả dụng giảm còn 74, cột Tồn thực giữ 98.

### B2.2 — Xuất kho · màn NVK-08 · `nvkho.hn` · 11/09/2026

Lấy đủ hàng theo đúng lô phân bổ. Nhập ĐVVC "GHN", mã vận đơn "GHN123456789".
Bấm **Xuất kho**:

```
Chi_tiet_phieu_xuat: Gia_von_xuat=889.795,92 cho cả 2 dòng (chụp)
Ton_kho: (HN,L2201) 10 → 0
         (HN,L2301) 58 → 44
Phieu_xuat_kho: Trang_thai_giao_hang='Đang giao',
  Ma_nguoi_xuat='nvkho.hn', Thoi_diem_xuat_kho=11/09/2026 09:40,
  Don_vi_van_chuyen='GHN', Ma_van_don='GHN123456789'
Don_hang_ban: Trang_thai='Đang giao'
San_pham.Gia_von_binh_quan: KHÔNG đổi (chỉ đổi khi nhập kho)
```

Tồn thực SP001 HN = **74**.

### B2.3 — Giao thành công · màn NVK-08 · 13/09/2026

```
Phieu_xuat_kho: Trang_thai_giao_hang='Giao thành công',
  Thoi_diem_ket_qua_giao=13/09/2026 16:20
Don_hang_ban: Trang_thai='Hoàn thành'
```

Ghi nhận cho báo cáo KT-04 tab Doanh thu – lãi gộp:

```
Doanh thu = 27.360.000
Giá vốn   = 24 × 889.795,92 = 21.355.102,08
Lãi gộp   = 6.004.897,92     → tỷ lệ 21,95%
```

---

## KB3. Đổi lô do hàng hỏng

Trạng thái trước: L2201: 0, L2301: 44, L2305: 30. Tổng 74.

### B3.1 — Lập đơn · màn KD-03 · 15/09/2026

SP001 × 20, chiết khấu 0%, kho HN → `DB-2026-0002`, `Tong_tien_hang = 24.000.000`.
FIFO phân bổ toàn bộ 20 chai vào L2301 (nhập 28/08, sớm hơn L2305).

### B3.2 — Đổi lô · màn NVK-08 · 16/09/2026

Xuống kho thấy chỉ còn 14 chai L2301 dùng được, 6 chai bị vỡ.
Mở cửa sổ **Đổi lô** trên dòng L2301: số lượng cần đổi 6, lý do "Hỏng hoặc vỡ",
số lượng hỏng 6, chọn lô thay thế L2305 lấy 6.

```
Chi_tiet_phieu_xuat:
  dòng cũ:  Ma_lo_phan_bo='L2301', So_luong=14   (giữ nguyên)
  dòng mới: Ma_lo_phan_bo='L2301', Ma_lo_thuc_xuat='L2305', So_luong=6,
            Ly_do_doi_lo='Hỏng hoặc vỡ', So_luong_hong=6,
            Gia_von_hang_hong=889.795,92,
            Ma_nguoi_doi_lo='nvkho.hn', Thoi_diem_doi_lo=16/09/2026 08:50
Ton_kho: (HN,L2301) 44 → 38   ← trừ ngay 6 chai hỏng, không chờ xuất kho
```

Hệ quả về sau: lô L2301 sẽ mang nhãn **Ưu tiên kiểm** ở phiếu kiểm kê kế tiếp (F11).

Màn khác đổi: **QLK-11** tab Hàng hỏng có thêm dòng 6 chai.

### B3.3 — Xuất kho

```
Ton_kho: (HN,L2301) 38 → 24
         (HN,L2305) 30 → 24
```

Tồn thực SP001 HN = 24 + 24 = **48**. Kiểm chứng: 74 − 6 hỏng − 20 xuất = 48.

---

## KB4. Kiểm kê và điều chỉnh tồn

### B4.1 — Đơn bán đang chờ lấy hàng · 25/09/2026

KD lập `DB-2026-0003`: SP001 × 48. Tồn khả dụng đúng 48 → xác nhận được.
FIFO phân bổ L2301 24 + L2305 24. Tồn khả dụng SP001 HN = **0**, tồn thực vẫn 48.

### B4.2 — Tạo phiếu kiểm kê · màn QLK-07 · `qlkho.hn` · 30/09/2026

Chọn 3 lô. L2301 hiện nhãn **Ưu tiên · Hỏng, vỡ** và được xếp lên đầu, tick sẵn.
Người kiểm: để trống (không giao).

```
Phieu_kiem_ke: Ma='KK-2026-0001', Ma_kho='HN', Ma_nguoi_tao='qlkho.hn',
  Ma_nguoi_kiem=NULL, Thoi_diem_tao=30/09/2026 08:00, Trang_thai='Đang kiểm'
Chi_tiet_kiem_ke (chụp So_luong_he_thong từ Ton_kho.Ton_thuc):
  L2301 → 24      L2305 → 24      M2301 → 60
```

Từ lúc này, 3 lô bị **khóa**: không xác nhận được phiếu nhập có chứa chúng, không đổi
lô sang hoặc từ chúng. Lô khác vẫn nhập, xuất bình thường.

Màn khác đổi: **NVK-01** ô "Phiếu kiểm kê cần đếm" tăng 1. **NVK-09** có thêm dòng,
cột Người kiểm hiện "Chưa giao", nút ghi "Nhận và đếm".

### B4.3 — Đếm · màn NVK-10 · `nvkho.hn`

| Lô | Tồn hệ thống | Thực tế | Chênh lệch | Lý do |
|---|---|---|---|---|
| L2301 | 24 | 19 | **−5** | Không tìm thấy, nghi thất lạc khi dồn khu vang đỏ |
| L2305 | 24 | 24 | 0 | — |
| M2301 | 60 | 60 | 0 | — |

Bấm **Hoàn thành đếm**:

```
Chi_tiet_kiem_ke: ghi So_luong_thuc_te, Ly_do_chenh_lech
Phieu_kiem_ke: Trang_thai='Chờ duyệt', Thoi_diem_dem_xong=30/09/2026 11:30,
  Ma_nguoi_kiem='nvkho.hn'   ← ghi người bấm, vì lúc tạo để trống
Ton_kho: CHƯA đổi
```

### B4.4 — Không duyệt · màn QLK-08 · `qlkho.hn`

Lý do: "Đếm lại khu vang đỏ Pháp, kiểm tra cả kệ hàng trả."

```
Phieu_kiem_ke: Trang_thai='Đang kiểm', Ly_do_khong_duyet=...,
  Thoi_diem_dem_xong=NULL
```

Màn khác đổi: **NVK-10** hiện banner đỏ "Đếm lại theo yêu cầu" kèm lý do.

### B4.5 — Đếm lại và duyệt

NVK đếm lại vẫn ra 19, bấm Hoàn thành đếm → Chờ duyệt.
QLK bấm **Duyệt và điều chỉnh tồn**:

```
Chi_tiet_kiem_ke: Gia_von_dieu_chinh=889.795,92 (chụp từ San_pham)
Ton_kho: (HN,L2301) 24 → 19      ← ghi đè bằng So_luong_thuc_te
Phieu_kiem_ke: Trang_thai='Hoàn tất', Ma_nguoi_duyet='qlkho.hn',
  Thoi_diem_duyet=01/10/2026 09:00
San_pham.Gia_von_binh_quan: KHÔNG đổi
Khóa 3 lô: được gỡ
```

Báo cáo KT-04 tab Điều chỉnh tồn: −5 chai, giá trị −4.448.979,60 đ.
Tồn thực SP001 HN = 19 + 24 = **43**.

---

## KB5. Thiếu tồn sau kiểm kê và hủy đơn

Tiếp nối KB4. Phiếu xuất `PX-2026-0003` của đơn `DB-2026-0003` cần L2301 24 chai nhưng
lô chỉ còn 19.

### B5.1 — Hệ thống gắn nhãn

F10 đúng → **NVK-07** và **NVK-08** hiện nhãn đỏ "Thiếu tồn"; **KD-02** và **KD-04**
cũng hiện nhãn này; **KD-01** ô "Đơn thiếu tồn" tăng 1. Không ai phải bấm gì để báo.

### B5.2 — Thử đổi lô · màn NVK-08 · `nvkho.hn`

Mở cửa sổ Đổi lô, số cần đổi 5. Các lô thay thế:

| Lô | Tồn thực | Đang giữ | Khả dụng |
|---|---|---|---|
| L2305 | 24 | 24 (cho chính đơn này) | 0 |
| L2201 | 0 | 0 | 0 |

Tổng khả dụng 0 < 5 → hiện cảnh báo **"Không còn lô nào đủ hàng"**, nút Xác nhận đổi lô
chuyển xám. Phiếu xuất không xuất kho được.

### B5.3 — Kinh doanh hủy đơn · màn KD-04 · `kinhdoanh01`

Sau khi trao đổi với khách. Lý do hủy: "Kho không đủ hàng sau kiểm kê, khách đồng ý
giảm số lượng và đặt lại."

```
Don_hang_ban: Trang_thai='Đã hủy', Thoi_diem_huy=..., Ly_do_huy=...
Phieu_xuat_kho: Trang_thai_giao_hang='Đã hủy'
Ton_kho: KHÔNG đổi (hàng chưa từng bị trừ)
```

Tồn khả dụng SP001 HN trở lại **43**. Khách đặt lại đơn mới 43 chai bằng KD-03.

---

## KB6. Khách trả hàng

Đơn `DB-2026-0001` (KB2) đã Hoàn thành, đã giao 24 chai: L2201 10 + L2301 14.
Ngày 05/10/2026 khách trả lại 6 chai thuộc lô L2301.

### B6.1 — Màn NVK-05 · `nvkho.hn`

Tìm theo mã đơn `DB-2026-0001` → chọn phiếu xuất `PX-2026-0001`.
Chọn **Nhập lại kho**.

| Sản phẩm | Lô | Đã xuất | SL thực nhận |
|---|---|---|---|
| SP001 | L2201 | 10 | 0 |
| SP001 | L2301 | 14 | **6** |

Lý do khách trả hàng: "Khách đặt nhầm niên vụ, hàng còn nguyên thùng."
Bấm **Xác nhận nhập kho**:

```
Phieu_nhap_kho: Ma='PN-2026-0003', Loai_nhap='Khách trả hàng',
  Ma_phieu_xuat='PX-2026-0001', Ly_do_tra_hang=...,
  Trang_thai='Đã nhập kho'
Chi_tiet_phieu_nhap: Ma_lo='L2301', So_luong_nhap=6, So_luong_hao_hut=0
Ton_kho: (HN,L2301) 19 → 25
  Ngay_nhap_dau_tien GIỮ NGUYÊN 28/08/2026 — hàng trả không làm lô trẻ lại
San_pham.Gia_von_binh_quan:
  Don_gia_nhap = Chi_tiet_phieu_xuat.Gia_von_xuat = 889.795,92
  (43 × 889.795,92 + 6 × 889.795,92) / 49 = 889.795,92  → không đổi
Phieu_xuat_kho: Trang_thai_hoan_hang='Đã nhập lại kho',
  Thoi_diem_xu_ly_hoan=05/10/2026 15:00
Don_hang_ban: Trang_thai GIỮ NGUYÊN 'Hoàn thành'
```

Tồn thực SP001 HN = **49**.

Báo cáo KT-04 tab Doanh thu trừ phần trả: −6 × 1.140.000 = −6.840.000 doanh thu,
−6 × 889.795,92 = −5.338.775,52 giá vốn.

Màn khác đổi: **NVK-05** không còn hiện `PX-2026-0001` trong danh sách chọn nữa.

---

## KB7. Giao thất bại và xử lý hàng hoàn

### B7.1 — Lập đơn và xuất kho · 08/10/2026

`DB-2026-0005`: SP001 × 12, kho HN. FIFO phân bổ L2301 (còn 25, nhập 28/08) 12 chai.
Xuất kho → `Ton_kho (HN,L2301) 25 → 13`, `Gia_von_xuat = 889.795,92`, đơn Đang giao.

### B7.2 — Giao thất bại · màn NVK-08 · 10/10/2026

Lý do: "Khách từ chối nhận, báo đặt nhầm số lượng."

```
Phieu_xuat_kho: Trang_thai_giao_hang='Giao hàng thất bại',
  Thoi_diem_ket_qua_giao=10/10/2026 11:00, Ly_do_giao_that_bai=...,
  Trang_thai_hoan_hang='Chờ xử lý'
Don_hang_ban: Trang_thai='Giao thất bại'
Ton_kho: CHƯA đổi — hàng đang trên đường về
```

Màn khác đổi: **KD-01** ô "Đơn giao thất bại" tăng 1. **NVK-01** ô "Giao thất bại chờ
xử lý hoàn" tăng 1. **NVK-06** có thêm dòng.

### B7.3a — Nhánh thường gặp: nhập lại một phần · màn NVK-06 · 12/10/2026

Hàng về 12 chai, kiểm tra thấy 10 chai nguyên, 2 chai vỡ trên đường.
Chọn **Nhập lại kho**, SL thực nhận 10. Ghi chú hoàn hàng: "2 chai vỡ khi vận chuyển về."

```
Phieu_nhap_kho PN-2026-0004: Loai_nhap='Hoàn giao thất bại',
  Ma_phieu_xuat='PX-2026-0005', Ly_do_tra_hang='2 chai vỡ khi vận chuyển về'
Chi_tiet_phieu_nhap: Ma_lo='L2301', So_luong_nhap=10
Ton_kho: (HN,L2301) 13 → 23
San_pham.Gia_von_binh_quan: không đổi (nhập lại theo Gia_von_xuat)
Phieu_xuat_kho: Trang_thai_hoan_hang='Đã nhập lại kho',
  Thoi_diem_xu_ly_hoan=12/10/2026,
  Ghi_chu_xu_ly_hoan='2 chai vỡ khi vận chuyển về'
```

Báo cáo KT-04 tab Hàng trả và giao thất bại:
SL không nhập = 12 − 10 = 2 → giá trị hàng mất = 2 × 889.795,92 = 1.779.591,84 đ.

Phần chênh giữa số đã xuất và số nhập lại được tính là hàng mất, không lưu thành cột
riêng. Lý do ghi ở `Ghi_chu_xu_ly_hoan`, ô này không bắt buộc ở nhánh nhập lại kho.

### B7.3b — Nhánh thay thế: không nhập chai nào

Nếu cả 12 chai đều vỡ, chọn **Không nhập kho**, lý do "Hỏng hoặc vỡ".

```
KHÔNG tạo Phieu_nhap_kho
Ton_kho: KHÔNG đổi
Phieu_xuat_kho: Trang_thai_hoan_hang='Đã xử lý – không nhập kho',
  Thoi_diem_xu_ly_hoan=..., Ghi_chu_xu_ly_hoan='Hỏng hoặc vỡ'
```

Giá trị hàng mất = 12 × 889.795,92 = 10.677.551,04 đ, chỉ hiện ở báo cáo Kế toán,
không hiện ở báo cáo Quản lý kho.

Cả hai nhánh: **NVK-06** bớt dòng, **NVK-01** ô "Giao thất bại chờ xử lý hoàn" giảm 1.

---

## KB8. Tạo tài khoản và đăng nhập lần đầu

### B8.1 — Quản trị tạo tài khoản · màn ADM-03 · `quantri01`

Họ tên "Trần Thị Mai", tên đăng nhập `nvkho.hn2`, email, vai trò **Nhân viên kho**.
Chọn vai trò này thì ô **Kho** mới hiện ra và bắt buộc → chọn Kho Hà Nội.

```
Nguoi_dung: Ma='ND0007', Mat_khau=hash(mật khẩu tạm ngẫu nhiên),
  Ma_vai_tro='NV_KHO', Ma_kho='HN', Trang_thai='Hoạt động',
  Phai_doi_mat_khau=1, So_lan_sai_mat_khau=0
Gửi email chứa mật khẩu tạm
```

### B8.2 — Đăng nhập lần đầu · AUTH-01 → AUTH-02

Đăng nhập đúng nhưng `Phai_doi_mat_khau = 1` → mọi route chuyển về AUTH-02.
Nhập mật khẩu tạm, mật khẩu mới, nhập lại. Lưu:

```
Nguoi_dung: Mat_khau=hash(mới), Phai_doi_mat_khau=0
```

Vào NVK-01. Menu chỉ có 4 mục của Nhân viên kho. Mọi danh sách lọc theo `Ma_kho='HN'`.

### B8.3 — Khóa và mở khóa

Nhập sai 5 lần → `So_lan_sai_mat_khau=5`, `Trang_thai='Khóa'`.
**ADM-01** ô tài khoản bị khóa tăng 1.

Hai cách mở:

| Cách | Màn | Kết quả |
|---|---|---|
| Quản trị bấm Mở khóa | ADM-04 | `Trang_thai='Hoạt động'`, `So_lan_sai_mat_khau=0` |
| Người dùng tự đặt lại mật khẩu qua email | AUTH-03 → AUTH-04 | Như trên, thêm `Phai_doi_mat_khau=0` |

---

## 9. Bảng đối chiếu tồn kho cuối các kịch bản

| Thời điểm | L2201 | L2301 | L2305 | Tổng SP001 HN | Giá vốn BQ SP001 |
|---|---|---|---|---|---|
| Đầu kỳ | 10 | — | — | 10 | 800.000,00 |
| Sau KB1 đợt 1 | 10 | 58 | — | 68 | 885.294,12 |
| Sau KB1 đợt 2 | 10 | 58 | 30 | 98 | 889.795,92 |
| Sau KB2 xuất kho | 0 | 44 | 30 | 74 | 889.795,92 |
| Sau KB3 đổi lô + xuất | 0 | 24 | 24 | 48 | 889.795,92 |
| Sau KB4 duyệt kiểm kê | 0 | 19 | 24 | 43 | 889.795,92 |
| Sau KB6 khách trả | 0 | 25 | 24 | 49 | 889.795,92 |
| Sau KB7 xuất 12 | 0 | 13 | 24 | 37 | 889.795,92 |
| Sau KB7 nhập lại 10 | 0 | 23 | 24 | 47 | 889.795,92 |

Dùng bảng này làm **test tồn kho đầu cuối**: chạy lần lượt 8 kịch bản rồi so
`Ton_kho.Ton_thuc` và `San_pham.Gia_von_binh_quan` với từng dòng.

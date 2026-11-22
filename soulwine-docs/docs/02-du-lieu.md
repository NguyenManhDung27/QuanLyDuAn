# 02. Dữ liệu

Trích từ thiết kế vật lý ở chương 3. MySQL 8, InnoDB, `utf8mb4_unicode_ci`.

**Agent không được thêm, bớt hay đổi tên bảng và cột trong tài liệu này.**
Cần thay đổi thì dừng lại và hỏi người dùng.

Quy ước: tên bảng và cột tiếng Việt không dấu, viết hoa chữ cái đầu. Khóa chính kiểu
`VARCHAR`, sinh theo mã nghiệp vụ, **không dùng AUTO_INCREMENT** (quy tắc sinh mã ở
`03-quy-tac-nghiep-vu.md` mục 3).

---

## 1. Danh sách bảng

| # | Bảng | Tên nghiệp vụ | Loại |
|---|---|---|---|
| 1 | `Vai_tro` | Vai trò | Thực thể |
| 2 | `Kho` | Kho | Thực thể |
| 3 | `Nguoi_dung` | Người dùng | Thực thể |
| 4 | `San_pham` | Sản phẩm | Thực thể |
| 5 | `Lo` | Lô | Thực thể |
| 6 | `Nha_cung_cap` | Nhà cung cấp | Thực thể |
| 7 | `Khach_hang` | Khách hàng | Thực thể |
| 8 | `Don_dat_hang` | Đơn đặt hàng | Thực thể |
| 9 | `Phieu_nhap_kho` | Phiếu nhập kho | Thực thể |
| 10 | `Don_hang_ban` | Đơn hàng bán | Thực thể |
| 11 | `Phieu_xuat_kho` | Phiếu xuất kho | Thực thể |
| 12 | `Phieu_kiem_ke` | Phiếu kiểm kê | Thực thể |
| 13 | `Ton_kho` | Tồn kho | Bảng liên kết |
| 14 | `Chi_tiet_don_dat_hang` | Chi tiết đơn đặt hàng | Bảng liên kết |
| 15 | `Chi_tiet_phieu_nhap` | Chi tiết phiếu nhập | Bảng liên kết |
| 16 | `Chi_tiet_don_ban` | Chi tiết đơn bán | Bảng liên kết |
| 17 | `Chi_tiet_phieu_xuat` | Chi tiết phiếu xuất | Bảng liên kết |
| 18 | `Chi_tiet_kiem_ke` | Chi tiết kiểm kê | Bảng liên kết |

Ngoài ra Laravel tự tạo các bảng kỹ thuật `password_reset_tokens`, `sessions`,
`jobs`, `activity_log`. Chúng không thuộc ERD nghiệp vụ.

---

## 2. Quan hệ

| Bảng A | Quan hệ | Bảng B | Bản số |
|---|---|---|---|
| `Nguoi_dung` | Có | `Vai_tro` | n:1 |
| `Nguoi_dung` | Thuộc | `Kho` | n:1 |
| `Lo` | Thuộc | `San_pham` | n:1 |
| `Kho` | Chứa | `Lo` | n:n |
| `Don_dat_hang` | Đặt từ | `Nha_cung_cap` | n:1 |
| `Don_dat_hang` | Nhập về | `Kho` | n:1 |
| `Nguoi_dung` | Lập / Duyệt | `Don_dat_hang` | 1:n |
| `Don_dat_hang` | Có | `San_pham` | n:n |
| `Don_dat_hang` | Được nhận theo | `Phieu_nhap_kho` | 1:n |
| `Phieu_nhap_kho` | Thuộc | `Kho` | n:1 |
| `Nguoi_dung` | Lập | `Phieu_nhap_kho` | 1:n |
| `Phieu_nhap_kho` | Nhập vào | `Lo` | n:n |
| `Phieu_xuat_kho` | Được hoàn theo | `Phieu_nhap_kho` | 1:n |
| `Khach_hang` | Đặt | `Don_hang_ban` | 1:n |
| `Don_hang_ban` | Xuất từ | `Kho` | n:1 |
| `Nguoi_dung` | Lập | `Don_hang_ban` | 1:n |
| `Don_hang_ban` | Có | `San_pham` | n:n |
| `Don_hang_ban` | Có | `Phieu_xuat_kho` | 1:1 |
| `Nguoi_dung` | Xuất | `Phieu_xuat_kho` | 1:n |
| `Phieu_xuat_kho` | Lấy từ | `Lo` | n:n |
| `Phieu_kiem_ke` | Thuộc | `Kho` | n:1 |
| `Nguoi_dung` | Lập / Kiểm / Duyệt | `Phieu_kiem_ke` | 1:n |
| `Phieu_kiem_ke` | Đối soát | `Lo` | n:n |

Quan hệ n:n đã được tách thành bảng liên kết, khóa chính là tổ hợp hai khóa ngoại.

---

## 3. Chi tiết từng bảng

### `Vai_tro` — Vai trò

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_vai_tro` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ten_vai_tro` | VARCHAR(100) | NOT NULL UNIQUE | X |

### `Kho` — Kho

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_kho` **[PK]** | VARCHAR(10) | PRIMARY KEY |  |
| `Ten_kho` | VARCHAR(100) | NOT NULL UNIQUE | X |
| `Dia_chi` | VARCHAR(255) | NOT NULL |  |
| `Trang_thai` | VARCHAR(20) | NOT NULL, DEFAULT 'Hoạt động' (Hoạt động / Ngừng; kho Ngừng không được chọn khi lập chứng từ mới hoặc gán người dùng) |  |

### `Nguoi_dung` — Người dùng

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_nguoi_dung` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ten_dang_nhap` | VARCHAR(50) | NOT NULL UNIQUE | X |
| `Mat_khau` | VARCHAR(255) | NOT NULL (lưu dạng mã hóa) |  |
| `Ho_ten` | VARCHAR(100) | NOT NULL |  |
| `Email` | VARCHAR(100) | NOT NULL UNIQUE (nhận đường dẫn đặt lại mật khẩu) | X |
| `So_dien_thoai` | VARCHAR(20) | NULL |  |
| `Ma_vai_tro` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Vai_tro(Ma_vai_tro) | X |
| `Ma_kho` *[FK]* | VARCHAR(10) | NULL, FOREIGN KEY REFERENCES Kho(Ma_kho); bắt buộc với Nhân viên kho, Quản lý kho | X |
| `Trang_thai` | VARCHAR(20) | NOT NULL, DEFAULT 'Hoạt động' (Hoạt động / Khóa) |  |
| `Phai_doi_mat_khau` | TINYINT(1) | NOT NULL, DEFAULT 1 (bằng 1 khi tài khoản mới tạo với mật khẩu tạm do hệ thống sinh và gửi qua Email; về 0 sau khi người dùng đổi mật khẩu lần đầu) |  |
| `So_lan_sai_mat_khau` | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 (sai 5 lần liên tiếp thì tự chuyển Trang_thai sang Khóa; về 0 khi đăng nhập đúng hoặc khi Quản trị mở khóa) |  |

### `San_pham` — Sản phẩm

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_san_pham` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ten_san_pham` | VARCHAR(255) | NOT NULL | X |
| `Nien_vu` | SMALLINT UNSIGNED | NULL (trống với rượu NV) |  |
| `Loai_vang` | VARCHAR(20) | NOT NULL (Đỏ, Trắng, Hồng, Vang nổ, Khác) | X |
| `Quoc_gia` | VARCHAR(100) | NOT NULL |  |
| `Vung` | VARCHAR(100) | NULL |  |
| `Nha_san_xuat` | VARCHAR(255) | NOT NULL |  |
| `Dung_tich` | INT UNSIGNED | NOT NULL, CHECK (Dung_tich > 0) |  |
| `Nong_do_con` | DECIMAL(4,1) | NULL |  |
| `Don_vi_tinh` | VARCHAR(20) | NOT NULL, DEFAULT 'Chai' |  |
| `Quy_cach_thung` | SMALLINT UNSIGNED | NULL |  |
| `Gia_niem_yet` | DECIMAL(18,0) | NULL (chưa thuế GTGT) |  |
| `Gia_von_binh_quan` | DECIMAL(18,2) | NOT NULL, DEFAULT 0 (hệ thống tính) |  |
| `Trang_thai` | VARCHAR(20) | NOT NULL, DEFAULT 'Đang kinh doanh' (Đang kinh doanh / Ngừng) |  |

### `Lo` — Lô

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_lo` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ma_san_pham` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES San_pham(Ma_san_pham) | X |
| `Ma_lo_ncc` | VARCHAR(50) | NOT NULL, UNIQUE (Ma_san_pham, Ma_lo_ncc) | X |
| `Ngay_san_xuat` | DATE | NULL |  |
| `Han_su_dung` | DATE | NULL |  |

### `Nha_cung_cap` — Nhà cung cấp

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_ncc` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ten_ncc` | VARCHAR(255) | NOT NULL | X |
| `Quoc_gia` | VARCHAR(100) | NOT NULL |  |
| `Dia_chi` | VARCHAR(255) | NULL |  |
| `Ma_so_thue` | VARCHAR(20) | NULL |  |
| `Nguoi_lien_he` | VARCHAR(100) | NULL |  |
| `So_dien_thoai` | VARCHAR(20) | NULL |  |
| `Email` | VARCHAR(100) | NULL |  |
| `Trang_thai` | VARCHAR(20) | NOT NULL, DEFAULT 'Đang giao dịch' (Đang giao dịch / Ngừng giao dịch) |  |

### `Khach_hang` — Khách hàng

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_khach_hang` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ten_khach_hang` | VARCHAR(255) | NOT NULL | X |
| `Loai_khach_hang` | VARCHAR(20) | NOT NULL (Cá nhân / Doanh nghiệp) |  |
| `Ma_so_thue` | VARCHAR(20) | NULL |  |
| `So_dien_thoai` | VARCHAR(20) | NOT NULL | X |
| `Email` | VARCHAR(100) | NULL |  |
| `Dia_chi` | VARCHAR(255) | NULL |  |
| `Trang_thai` | VARCHAR(20) | NOT NULL, DEFAULT 'Đang giao dịch' (Đang giao dịch / Ngừng giao dịch) |  |

### `Don_dat_hang` — Đơn đặt hàng

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_don_dat_hang` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ma_ncc` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Nha_cung_cap(Ma_ncc) | X |
| `Ma_kho` *[FK]* | VARCHAR(10) | NOT NULL, FOREIGN KEY REFERENCES Kho(Ma_kho) | X |
| `Ma_nguoi_lap` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung) | X |
| `Ngay_lap` | DATE | NOT NULL |  |
| `Ngay_du_kien_nhan_du` | DATE | NULL; bắt buộc từ khi gửi duyệt |  |
| `Trang_thai` | VARCHAR(30) | NOT NULL, DEFAULT 'Đang cập nhật' (Đang cập nhật, Chờ phê duyệt, Từ chối, Chưa gửi NCC, Đã gửi NCC, Nhận một phần, Đã nhận đủ, Đóng thiếu, Đề xuất đã hủy, Đơn đã hủy) | X |
| `Tong_gia_tri` | DECIMAL(18,0) | NOT NULL, DEFAULT 0 (hệ thống tính) |  |
| `Ma_nguoi_duyet` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc khi Từ chối và từ Chưa gửi NCC trở đi | X |
| `Thoi_diem_duyet` | DATETIME | NULL; bắt buộc cùng Ma_nguoi_duyet |  |
| `Ghi_chu_phe_duyet` | VARCHAR(1000) | NULL |  |
| `Ly_do_tu_choi` | VARCHAR(1000) | NULL; bắt buộc khi Từ chối |  |
| `Ma_nguoi_gui_ncc` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc từ Đã gửi NCC trở đi | X |
| `Ngay_gui_ncc` | DATE | NULL; bắt buộc từ Đã gửi NCC trở đi |  |
| `Ma_nguoi_huy` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc khi Đề xuất đã hủy hoặc Đơn đã hủy | X |
| `Thoi_diem_huy` | DATETIME | NULL; bắt buộc khi Đề xuất đã hủy hoặc Đơn đã hủy |  |
| `Ly_do_huy` | VARCHAR(1000) | NULL; bắt buộc khi Đề xuất đã hủy hoặc Đơn đã hủy |  |
| `Ma_nguoi_dong_thieu` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc khi Đóng thiếu | X |
| `Thoi_diem_dong_thieu` | DATETIME | NULL; bắt buộc khi Đóng thiếu |  |
| `Ly_do_dong_thieu` | VARCHAR(1000) | NULL; bắt buộc khi Đóng thiếu |  |

### `Phieu_nhap_kho` — Phiếu nhập kho

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_phieu_nhap` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ma_kho` *[FK]* | VARCHAR(10) | NOT NULL, FOREIGN KEY REFERENCES Kho(Ma_kho) | X |
| `Loai_nhap` | VARCHAR(30) | NOT NULL (Nhập từ NCC / Khách trả hàng / Hoàn giao thất bại) | X |
| `Ma_don_dat_hang` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Don_dat_hang(Ma_don_dat_hang); bắt buộc khi Nhập từ NCC | X |
| `Ma_phieu_xuat` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Phieu_xuat_kho(Ma_phieu_xuat); bắt buộc khi Khách trả hàng / Hoàn giao thất bại | X |
| `Ma_nguoi_lap` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung) | X |
| `Ngay_lap` | DATE | NOT NULL |  |
| `Thoi_diem_xac_nhan` | DATETIME | NULL; bắt buộc khi Đã nhập kho |  |
| `So_phieu_giao_ncc` | VARCHAR(50) | NULL |  |
| `Dot_giao_cuoi` | TINYINT(1) | NOT NULL, DEFAULT 0 |  |
| `Ly_do_tra_hang` | VARCHAR(1000) | NULL; bắt buộc khi Khách trả hàng / Hoàn giao thất bại |  |
| `Trang_thai` | VARCHAR(30) | NOT NULL, DEFAULT 'Đang cập nhật' (Đang cập nhật / Đã nhập kho) | X |

### `Don_hang_ban` — Đơn hàng bán

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_don_hang_ban` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ma_khach_hang` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Khach_hang(Ma_khach_hang) | X |
| `Ma_kho` *[FK]* | VARCHAR(10) | NOT NULL, FOREIGN KEY REFERENCES Kho(Ma_kho) | X |
| `Ma_nguoi_lap` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung) | X |
| `Thoi_diem_xac_nhan` | DATETIME | NOT NULL | X |
| `Ten_nguoi_nhan` | VARCHAR(100) | NOT NULL |  |
| `Sdt_nguoi_nhan` | VARCHAR(20) | NOT NULL |  |
| `Dia_chi_giao_hang` | VARCHAR(255) | NOT NULL |  |
| `Tong_tien_hang` | DECIMAL(18,0) | NOT NULL (chưa thuế GTGT, hệ thống tính) |  |
| `Trang_thai` | VARCHAR(30) | NOT NULL, DEFAULT 'Đang chuẩn bị hàng' (Đang chuẩn bị hàng, Đang giao, Hoàn thành, Giao thất bại, Đã hủy) | X |
| `Thoi_diem_huy` | DATETIME | NULL; bắt buộc khi Đã hủy |  |
| `Ly_do_huy` | VARCHAR(1000) | NULL; bắt buộc khi Đã hủy |  |

### `Phieu_xuat_kho` — Phiếu xuất kho

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_phieu_xuat` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ma_don_hang_ban` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Don_hang_ban(Ma_don_hang_ban), UNIQUE | X |
| `Ngay_tao` | DATE | NOT NULL |  |
| `Trang_thai_giao_hang` | VARCHAR(30) | NOT NULL, DEFAULT 'Đang chuẩn bị hàng' (Đang chuẩn bị hàng, Đang giao, Giao thành công, Giao hàng thất bại, Đã hủy) | X |
| `Trang_thai_hoan_hang` | VARCHAR(30) | NOT NULL, DEFAULT 'Chưa xử lý' (Chưa xử lý, Đã hoàn kho, Đã xử lý - không nhập kho) | X |
| `Don_vi_van_chuyen` | VARCHAR(100) | NULL; bắt buộc từ Đang giao |  |
| `Ma_van_don` | VARCHAR(50) | NULL; bắt buộc từ Đang giao | X |
| `Ma_nguoi_xuat` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc từ Đang giao | X |
| `Thoi_diem_xuat_kho` | DATETIME | NULL; bắt buộc từ Đang giao |  |
| `Thoi_diem_ket_qua_giao` | DATETIME | NULL; bắt buộc khi Giao thành công / Giao hàng thất bại | X |
| `Ly_do_giao_that_bai` | VARCHAR(1000) | NULL; bắt buộc khi Giao hàng thất bại |  |
| `Thoi_diem_xu_ly_hoan` | DATETIME | NULL; bắt buộc khi trạng thái hoàn hàng khác Chưa xử lý |  |
| `Ghi_chu_xu_ly_hoan` | VARCHAR(1000) | NULL; bắt buộc khi Trang_thai_hoan_hang = Đã xử lý – không nhập kho; tùy chọn khi Đã nhập lại kho mà số nhập lại ít hơn số đã xuất |  |

### `Phieu_kiem_ke` — Phiếu kiểm kê

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_phieu_kiem_ke` **[PK]** | VARCHAR(20) | PRIMARY KEY |  |
| `Ma_kho` *[FK]* | VARCHAR(10) | NOT NULL, FOREIGN KEY REFERENCES Kho(Ma_kho) | X |
| `Ma_nguoi_tao` *[FK]* | VARCHAR(20) | NOT NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung) | X |
| `Ma_nguoi_kiem` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc khi Chờ duyệt / Hoàn tất; để trống khi tạo thì nhân viên kho của kho tự nhận, người bấm Hoàn thành đếm được ghi vào | X |
| `Thoi_diem_tao` | DATETIME | NOT NULL |  |
| `Trang_thai` | VARCHAR(30) | NOT NULL, DEFAULT 'Đang kiểm' (Đang kiểm, Chờ duyệt, Hoàn tất, Đã hủy) | X |
| `Thoi_diem_dem_xong` | DATETIME | NULL; bắt buộc khi Chờ duyệt / Hoàn tất |  |
| `Ma_nguoi_duyet` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc khi Hoàn tất sau khi duyệt | X |
| `Thoi_diem_duyet` | DATETIME | NULL; bắt buộc cùng Ma_nguoi_duyet |  |
| `Ly_do_khong_duyet` | VARCHAR(1000) | NULL |  |
| `Thoi_diem_huy` | DATETIME | NULL; bắt buộc khi Đã hủy |  |
| `Ly_do_huy` | VARCHAR(1000) | NULL; bắt buộc khi Đã hủy |  |
| `Ghi_chu` | VARCHAR(1000) | NULL |  |

### `Ton_kho` — Tồn kho

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_kho` **[PK]** *[FK]* | VARCHAR(10) | PRIMARY KEY, FOREIGN KEY REFERENCES Kho(Ma_kho) |  |
| `Ma_lo` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Lo(Ma_lo) | X |
| `Ton_thuc` | INT UNSIGNED | NOT NULL, DEFAULT 0 |  |
| `Ngay_nhap_dau_tien` | DATETIME | NOT NULL (dùng cho FIFO) | X |

### `Chi_tiet_don_dat_hang` — Chi tiết đơn đặt hàng

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_don_dat_hang` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Don_dat_hang(Ma_don_dat_hang) |  |
| `Ma_san_pham` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES San_pham(Ma_san_pham) | X |
| `So_luong_dat` | INT UNSIGNED | NOT NULL, CHECK (So_luong_dat > 0) |  |
| `Don_gia_mua` | DECIMAL(18,0) | NOT NULL (giá NCC) |  |

### `Chi_tiet_phieu_nhap` — Chi tiết phiếu nhập

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_phieu_nhap` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Phieu_nhap_kho(Ma_phieu_nhap) |  |
| `Ma_lo` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Lo(Ma_lo) | X |
| `So_luong_nhap` | INT UNSIGNED | NOT NULL |  |
| `So_luong_hao_hut` | INT UNSIGNED | NOT NULL, DEFAULT 0 |  |
| `Ly_do_hao_hut` | VARCHAR(1000) | NULL; bắt buộc khi So_luong_hao_hut > 0 |  |

### `Chi_tiet_don_ban` — Chi tiết đơn bán

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_don_hang_ban` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Don_hang_ban(Ma_don_hang_ban) |  |
| `Ma_san_pham` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES San_pham(Ma_san_pham) | X |
| `So_luong` | INT UNSIGNED | NOT NULL, CHECK (So_luong > 0) |  |
| `Don_gia_ban` | DECIMAL(18,0) | NOT NULL (chưa thuế GTGT, đã trừ chiết khấu) |  |
| `Chiet_khau` | DECIMAL(5,2) | NOT NULL, DEFAULT 0, CHECK (Chiet_khau BETWEEN 0 AND 100) |  |

### `Chi_tiet_phieu_xuat` — Chi tiết phiếu xuất

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_phieu_xuat` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Phieu_xuat_kho(Ma_phieu_xuat) |  |
| `Ma_lo_phan_bo` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Lo(Ma_lo) | X |
| `Ma_lo_thuc_xuat` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Lo(Ma_lo) | X |
| `So_luong` | INT UNSIGNED | NOT NULL, CHECK (So_luong > 0) |  |
| `Ly_do_doi_lo` | VARCHAR(30) | NULL; bắt buộc khi lô thực xuất khác lô phân bổ (Lấy lô khác cho tiện / Không tìm thấy lô gốc / Hỏng hoặc vỡ / Khác) |  |
| `So_luong_hong` | INT UNSIGNED | NULL; bắt buộc khi lý do là Hỏng hoặc vỡ |  |
| `Gia_von_hang_hong` | DECIMAL(18,2) | NULL; bắt buộc khi có So_luong_hong |  |
| `Ma_nguoi_doi_lo` *[FK]* | VARCHAR(20) | NULL, FOREIGN KEY REFERENCES Nguoi_dung(Ma_nguoi_dung); bắt buộc khi có đổi lô | X |
| `Thoi_diem_doi_lo` | DATETIME | NULL; bắt buộc khi có đổi lô |  |
| `Gia_von_xuat` | DECIMAL(18,2) | NULL; bắt buộc từ khi phiếu chuyển Đang giao |  |

### `Chi_tiet_kiem_ke` — Chi tiết kiểm kê

| Tên trường | Kiểu dữ liệu | Ràng buộc | Index |
|---|---|---|---|
| `Ma_phieu_kiem_ke` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Phieu_kiem_ke(Ma_phieu_kiem_ke) |  |
| `Ma_lo` **[PK]** *[FK]* | VARCHAR(20) | PRIMARY KEY, FOREIGN KEY REFERENCES Lo(Ma_lo) | X |
| `So_luong_he_thong` | INT UNSIGNED | NOT NULL |  |
| `So_luong_thuc_te` | INT UNSIGNED | NULL; bắt buộc khi hoàn thành đếm |  |
| `Ly_do_chenh_lech` | VARCHAR(1000) | NULL; bắt buộc khi số thực tế khác số hệ thống |  |
| `Gia_von_dieu_chinh` | DECIMAL(18,2) | NULL; bắt buộc khi phiếu được duyệt và dòng có chênh lệch |  |

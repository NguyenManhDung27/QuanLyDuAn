# Hệ thống quản lý kho Soul Wine — quy ước cho agent

File này áp dụng cho **mọi** việc trong repo. Đọc trước khi viết dòng mã đầu tiên.

---

## 1. Bối cảnh

Hệ thống web quản lý mua hàng, nhập kho, tồn kho, bán hàng, xuất kho, giao hàng và kiểm
kê cho Công ty Cổ phần Soul Wine — doanh nghiệp nhập khẩu và phân phối rượu vang, có
hai kho tại Hà Nội và TP.HCM. Năm nhóm người dùng nội bộ. Kế toán dùng phần mềm kế toán
riêng, hệ thống này chỉ cung cấp dữ liệu để đối chiếu.

Toàn bộ thiết kế đã được duyệt. Việc của agent là **hiện thực hóa đúng thiết kế**, không
phải thiết kế lại.

---

## 2. Công nghệ

| Hạng mục | Quy định |
|---|---|
| Ngôn ngữ | PHP 8.2 |
| Framework | Laravel 11 |
| Giao diện | Blade + CSS thuần. **Không dùng SPA**, không React, không Vue |
| Cơ sở dữ liệu | MySQL 8, InnoDB, `utf8mb4_unicode_ci` |
| Môi trường phát triển | XAMPP |
| Quản lý mã nguồn | Git |

Không thêm thư viện ngoài nếu Laravel đã có sẵn chức năng tương đương.

---

## 3. Tài liệu và thứ tự đọc

| File | Nội dung | Đọc khi nào |
|---|---|---|
| `AGENTS.md` | File này | Luôn luôn |
| `01-tong-quan.md` | Vai trò, phân quyền, danh sách chức năng, ngoài phạm vi | Luôn luôn |
| `02-du-lieu.md` | 18 bảng, 157 cột, kiểu dữ liệu, ràng buộc | Khi làm migration, model |
| `03-quy-tac-nghiep-vu.md` | BR01–BR17, transaction, sinh mã, thông báo lỗi | Khi làm nghiệp vụ |
| `04-trang-thai.md` | Máy trạng thái 6 loại chứng từ | Khi làm màn chi tiết, nút bấm |
| `06-luong-man-hinh.md` | 40 màn hình, route, điều hướng | Khi làm controller, view |
| `07-nguon-du-lieu.md` | Công thức F1–F14, nguồn từng cột trên màn | Khi hiển thị số liệu |
| `08-kich-ban-dau-cuoi.md` | 8 kịch bản có số cụ thể | Khi làm seeder và test |
| `man-hinh/*.html` | Giao diện mẫu đã duyệt | Khi làm Blade |

Mỗi lần giao việc chỉ cần đọc các file liên quan, không cần đọc hết.

---

## 4. Quy ước đặt tên

### Cơ sở dữ liệu

- Tên bảng và cột: **tiếng Việt không dấu, viết hoa chữ cái đầu**, các từ nối bằng gạch
  dưới. Ví dụ `Don_dat_hang`, `Ma_nguoi_lap`, `So_luong_dat`.
- Lấy **đúng** tên trong `02-du-lieu.md`. Không viết lại thành tiếng Anh, không đổi sang
  số nhiều, không đổi sang snake_case thường.
- Khóa chính kiểu `VARCHAR`, sinh theo mã nghiệp vụ. **Không dùng AUTO_INCREMENT**, không
  dùng UUID.
- Không dùng soft delete. Bản ghi đã phát sinh chứng từ thì chuyển `Trang_thai`, không xóa.
- Không tự thêm cột `created_at`, `updated_at`. Thời điểm nào cần lưu thì đã có cột
  nghiệp vụ riêng trong `02-du-lieu.md`.

### Mã nguồn

| Thành phần | Quy ước | Ví dụ |
|---|---|---|
| Model | PascalCase tiếng Việt không dấu, số ít | `DonDatHang`, `ChiTietPhieuXuat` |
| Controller | Tên chức năng + `Controller` | `DonDatHangController` |
| Thư mục Blade | theo vai trò | `resources/views/kho/don-dat-hang/index.blade.php` |
| Route name | kebab-case | `kho.don-dat-hang.index` |
| Policy | Model + `Policy` | `DonDatHangPolicy` |
| Biến, hàm | tiếng Anh thông thường | `$totalAmount`, `calculateAvailableStock()` |

Model phải khai báo `$table`, `$primaryKey`, `$keyType = 'string'`,
`$incrementing = false`.

---

## 5. Giao diện

- **Toàn bộ chữ trên màn hình bằng tiếng Việt có dấu**, kể cả thông báo lỗi và nhãn nút.
- Dựng Blade từ file trong `man-hinh/`. Giữ nguyên bố cục, màu, khoảng cách. Chỉ thay dữ
  liệu giả `[số]`, `[Tên sản phẩm]`, `[dd/mm/yyyy]` bằng dữ liệu thật.
- Các màu dùng lại từ file mẫu, không tự chọn màu mới:
  `#8B1E2D` nhấn · `#6E1423` đậm · `#4A0E1A` menu · `#FAF5F3` nền · `#EADFDC` viền ·
  `#241417` chữ · `#6B5559` chữ phụ.
- Font: Be Vietnam Pro cho nội dung, Playfair Display cho logo.
- Tách phần dùng chung thành component: layout có header và menu, bảng, nhãn trạng thái,
  hộp xác nhận, phân trang.

### Định dạng hiển thị

| Loại | Định dạng | Ví dụ |
|---|---|---|
| Tiền | Dấu chấm ngăn nghìn, không ghi ",00" | `1.234.567` |
| Số lượng | Dấu chấm ngăn nghìn, đơn vị là chai | `1.250` |
| Ngày | `dd/mm/yyyy` | `28/08/2026` |
| Ngày giờ | `dd/mm/yyyy hh:mm` | `28/08/2026 14:20` |
| Phần trăm | Một chữ số thập phân, dấu phẩy | `12,5%` |

---

## 6. Những việc không được làm

1. **Không thêm, bớt, đổi tên bảng hoặc cột** ngoài `02-du-lieu.md`. Thấy cần thì dừng
   lại và hỏi.
2. **Không thêm chức năng ngoài phạm vi.** Danh sách ngoài phạm vi ở `01-tong-quan.md`
   mục 5. Đáng chú ý: không quản lý vị trí kệ tầng, không chuyển kho, không giao thiếu,
   không tồn kho tối thiểu, không gửi email thông báo nghiệp vụ, không cấu hình tham số.
3. **Không tự đặt thêm trạng thái.** Danh sách trạng thái đầy đủ ở `04-trang-thai.md`.
4. **Không lưu giá trị tính được.** Xem bảng "trường chụp và trường tính" ở
   `07-nguon-du-lieu.md` mục 2.
5. **Không sửa trực tiếp `Ton_kho.Ton_thuc`** ngoài 4 nghiệp vụ: xác nhận nhập kho, xác
   nhận xuất kho, đổi lô có hàng hỏng, duyệt phiếu kiểm kê.
6. **Không chỉ ẩn nút để phân quyền.** Phải chặn ở server bằng Policy hoặc middleware.
7. **Không dùng dữ liệu cá nhân thật** trong seeder và test.

---

## 7. Cách làm việc

### Giao việc theo lát cắt dọc

Mỗi lần làm trọn một nhóm chức năng, từ database lên giao diện. Thứ tự xây dựng ở
`01-tong-quan.md` mục 6. Không làm hết migration rồi mới làm hết controller.

### Hoàn thành khi

Một lát cắt được coi là xong khi đủ các điều kiện sau:

- [ ] Migration chạy được cả `migrate` và `rollback`
- [ ] Không có cột nào ngoài `02-du-lieu.md`
- [ ] Mọi route đều qua Policy, đã thử gọi thẳng URL bằng vai trò khác và bị chặn
- [ ] Màn hình khớp file mẫu trong `man-hinh/`
- [ ] Nút hiển thị đúng theo `04-trang-thai.md`
- [ ] Mọi nút không hoàn tác được đều có hộp xác nhận
- [ ] Các quy tắc BR liên quan đều có test
- [ ] Seeder tạo được dữ liệu của kịch bản tương ứng trong `08-kich-ban-dau-cuoi.md`
- [ ] Số liệu sau khi chạy khớp bảng đối chiếu tồn kho ở cuối file kịch bản

### Báo cáo sau mỗi lát cắt

Liệt kê file đã tạo hoặc sửa, các quy tắc BR đã hiện thực, và những điểm phải quyết định
mà tài liệu chưa nói rõ.

---

## 8. Khi tài liệu chưa rõ

Dừng lại và hỏi. **Không tự suy diễn** rồi code tiếp, đặc biệt với:

- công thức tính tiền và tồn kho
- điều kiện chuyển trạng thái
- phân quyền
- ràng buộc dữ liệu

Nếu bắt buộc phải chọn để chạy tiếp, chọn phương án đơn giản nhất, đánh dấu `// TODO:
cần xác nhận` ngay tại chỗ, và ghi vào phần báo cáo.

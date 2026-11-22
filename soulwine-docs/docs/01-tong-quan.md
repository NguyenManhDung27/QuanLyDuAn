# 01. Tổng quan hệ thống

---

## 1. Nghiệp vụ hệ thống phục vụ

Năm luồng chính, chạy trên dữ liệu dùng chung cho hai kho:

1. **Mua hàng** — Quản lý kho lập đề xuất nhập kho, Kế toán duyệt, đề xuất thành đơn đặt
   hàng, gửi nhà cung cấp, theo dõi đến khi nhận đủ hoặc đóng thiếu.
2. **Nhập kho** — Nhân viên kho ghi nhận hàng thực nhận theo lô, có hao hụt. Ba nguồn
   nhập: từ nhà cung cấp, khách trả hàng, hàng hoàn do giao thất bại.
3. **Bán hàng** — Kinh doanh lập đơn, hệ thống kiểm tra tồn khả dụng, giữ hàng và phân bổ
   lô theo FIFO ngay khi xác nhận.
4. **Xuất kho và giao hàng** — Nhân viên kho lấy hàng, đổi lô khi cần, xuất kho, cập nhật
   kết quả giao.
5. **Kiểm kê** — Quản lý kho tạo phiếu theo lô, Nhân viên kho đếm, Quản lý kho duyệt
   chênh lệch để điều chỉnh tồn.

Mọi biến động tồn kho đều phải có chứng từ. Không có đường nào sửa thẳng số tồn.

---

## 2. Vai trò người dùng

| Mã vai trò | Tên | Phạm vi kho | Việc chính |
|---|---|---|---|
| `QUAN_TRI` | Quản trị | Không gắn kho | Tài khoản, phân quyền, danh mục kho, nhật ký. **Không truy cập dữ liệu nghiệp vụ** |
| `QL_KHO` | Quản lý kho | Một kho | Đề xuất nhập kho, theo dõi tồn, tạo và duyệt kiểm kê, danh mục sản phẩm và nhà cung cấp, báo cáo kho |
| `NV_KHO` | Nhân viên kho | Một kho | Nhập kho, lấy hàng, đổi lô, xuất kho, cập nhật giao hàng, đếm kiểm kê |
| `KE_TOAN` | Kế toán | Cả hai kho | Duyệt đề xuất nhập kho, báo cáo có giá trị tiền, xuất Excel, xem danh mục |
| `KINH_DOANH` | Kinh doanh | Cả hai kho | Lập đơn bán, theo dõi đơn, khách hàng, cập nhật giá niêm yết |

`Nguoi_dung.Ma_kho` bắt buộc với `QL_KHO` và `NV_KHO`, để trống với ba vai trò còn lại.

---

## 3. Ma trận phân quyền

X = được phép. Ô trống = không truy cập được, kể cả khi gõ thẳng URL.

| Chức năng | Quản trị | QL kho | NV kho | Kế toán | Kinh doanh |
|---|:--:|:--:|:--:|:--:|:--:|
| Quản lý tài khoản, phân quyền | X | | | | |
| Danh mục kho | X | | | | |
| Nhật ký hệ thống | X | | | | |
| Danh mục sản phẩm — thêm, sửa | | X | | | |
| Giá niêm yết — sửa | | | | | X |
| Giá vốn bình quân — xem | | | | X | |
| Danh mục nhà cung cấp — thêm, sửa | | X | | | |
| Danh mục khách hàng — thêm, sửa | | | | | X |
| Danh mục — chỉ xem | | | | X | |
| Theo dõi tồn kho (số lượng) | | X | X | X | X |
| Giá trị tồn kho | | | | X | |
| Lập, sửa, gửi duyệt đề xuất nhập kho | | X | | | |
| Duyệt, từ chối đề xuất | | | | X | |
| Gửi NCC, đóng thiếu, hủy đơn đặt hàng | | X | | | |
| Lập, xác nhận phiếu nhập kho | | | X | | |
| Lập đơn bán, hủy đơn bán | | | | | X |
| Đổi lô, xuất kho, cập nhật giao hàng | | | X | | |
| Xử lý hàng hoàn | | | X | | |
| Tạo phiếu kiểm kê, duyệt chênh lệch | | X | | | |
| Đếm kiểm kê | | | X | | |
| Báo cáo theo số lượng, một kho | | X | | | |
| Báo cáo có giá trị tiền, hai kho | | | | X | |
| Xuất Excel | | | | X | |

Quy tắc phạm vi kho: `QL_KHO` và `NV_KHO` chỉ thao tác trên bản ghi có `Ma_kho` trùng với
`Nguoi_dung.Ma_kho`. Truy cập bản ghi kho khác trả về **403**.

---

## 4. Danh sách chức năng

| Mã | Chức năng | Vai trò chính |
|---|---|---|
| FR01 | Quản lý sản phẩm | QL kho |
| FR02 | Quản lý kho | Quản trị |
| FR03 | Quản lý khách hàng | Kinh doanh |
| FR04 | Quản lý nhà cung cấp | QL kho |
| FR05 | Quản lý người dùng và phân quyền | Quản trị |
| FR06 | Quản lý đơn đặt hàng mua | QL kho, Kế toán |
| FR07 | Quản lý nhập kho | NV kho |
| FR08 | Quản lý tồn kho và lô | QL kho, Kinh doanh |
| FR09 | Đơn hàng bán, giữ hàng tự động | Kinh doanh |
| FR10 | Phân bổ lô FIFO và đổi lô | NV kho |
| FR11 | Xuất kho | NV kho |
| FR12 | Theo dõi giao hàng | NV kho, Kinh doanh |
| FR13 | Kiểm kê và điều chỉnh tồn | QL kho, NV kho |
| FR14 | Báo cáo tồn kho và giá vốn | Kế toán |
| FR15 | Báo cáo bán hàng, mua hàng, trả hàng, điều chỉnh | Kế toán |
| FR16 | Nhật ký hệ thống | Quản trị |
| FR17 | Việc cần xử lý theo vai trò | Mọi vai trò |

---

## 5. Ngoài phạm vi — không được làm

| Nội dung | Lý do |
|---|---|
| Quản lý vị trí lưu trữ trong kho (kệ, tầng) | Chỉ quản lý theo kho và lô |
| Chuyển hàng giữa hai kho | Hai kho hoạt động độc lập |
| Giao thiếu, đơn thay thế | Thiếu hàng thì hủy đơn và lập đơn mới |
| Tồn kho tối thiểu, cảnh báo đặt hàng tự động | Quản lý kho tự quyết dựa trên lịch sử |
| Gửi email hoặc thông báo đẩy cho nghiệp vụ | Chỉ có "việc cần xử lý" trên giao diện. Email chỉ dùng cho mật khẩu |
| Cấu hình tham số hệ thống, ngưỡng nhắc việc | Không có màn cấu hình |
| Tùy biến vai trò, tạo vai trò mới | Năm vai trò cố định |
| Hóa đơn, công nợ, thanh toán, hạch toán | Kế toán dùng phần mềm riêng |
| Thuế nhập khẩu, thuế tiêu thụ đặc biệt, tỷ giá | Đơn giá mua chỉ là giá nhà cung cấp |
| Giá vốn theo lô | Dùng bình quân gia quyền toàn công ty |
| CRM, marketing, bán lẻ trực tuyến | |
| Ứng dụng di động riêng | Chỉ web, dùng được trên máy tính bảng |
| Quản lý nội bộ đơn vị vận chuyển | Chỉ lưu tên đơn vị và mã vận đơn |

Thuế GTGT 8% có hiện trên màn lập đơn bán nhưng **chỉ để báo khách**, không lưu vào cơ
sở dữ liệu.

---

## 6. Thứ tự xây dựng

Làm trọn từng lát cắt, chạy được rồi mới sang lát tiếp theo.

| # | Lát cắt | Gồm | Màn hình |
|---|---|---|---|
| 1 | Nền tảng và xác thực | Migration toàn bộ 18 bảng, model, layout, đăng nhập, đổi mật khẩu, quên mật khẩu, khóa tài khoản | AUTH-01…05 |
| 2 | Quản trị | Người dùng, vai trò, kho, nhật ký | ADM-01…06 |
| 3 | Danh mục | Sản phẩm, nhà cung cấp, khách hàng, giá niêm yết | QLK-09, QLK-10, KD-05, KD-06, KT-05…07 |
| 4 | Tồn kho | Bảng `Ton_kho`, `Lo`, công thức F2–F6, màn theo dõi tồn | QLK-02 |
| 5 | Mua hàng | Đề xuất, duyệt, đơn đặt hàng, đóng thiếu | QLK-03…05, KT-02, KT-03 |
| 6 | Nhập kho | Ba loại nhập, lô, hao hụt, giá vốn bình quân | NVK-02…06 |
| 7 | Bán hàng | Lập đơn, giữ hàng, phân bổ FIFO, hủy đơn | KD-02…04 |
| 8 | Xuất kho và giao hàng | Đổi lô, xuất kho, kết quả giao, xử lý hàng hoàn | NVK-07, NVK-08 |
| 9 | Kiểm kê | Tạo phiếu, đếm, duyệt, điều chỉnh tồn, khóa lô | QLK-06…08, NVK-09, NVK-10 |
| 10 | Báo cáo và việc cần xử lý | Báo cáo hai vai trò, xuất Excel, trang chủ, chuông | QLK-11, KT-04, các trang chủ |

Lát cắt 4 phải xong trước 6 và 7, vì cả hai đều ghi vào `Ton_kho`.
Lát cắt 7 phải xong trước 8, vì phiếu xuất sinh ra từ đơn bán.

---

## 7. Ràng buộc kỹ thuật

- Giao diện web, ưu tiên máy tính, dùng được trên máy tính bảng. Không có ứng dụng riêng.
- Chạy trên hạ tầng sẵn có của Soul Wine, không yêu cầu phần cứng chuyên biệt.
- Dữ liệu được sao lưu định kỳ và phục hồi được.
- Dữ liệu cá nhân của khách hàng và người nhận hàng chỉ truy cập theo vai trò, mọi thao
  tác quan trọng đều ghi nhật ký.

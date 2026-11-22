# 03. Quy tắc nghiệp vụ

Công thức tính nằm ở `07-nguon-du-lieu.md` mục 1, ký hiệu F1–F14.
Chuyển trạng thái nằm ở `04-trang-thai.md`. File này không lặp lại hai phần đó.

---

## 1. Quy tắc BR01–BR17

### Mua hàng và nhập kho

**BR01.** Đơn đặt hàng chỉ được lập phiếu nhập khi đã qua phê duyệt, tức
`Trang_thai IN ('Đã gửi NCC', 'Nhận một phần')`. Đơn ở trạng thái khác không hiện trong
danh sách chọn ở màn Nhập kho.

**BR02.** Với mỗi sản phẩm trên một đơn, tổng `So_luong_nhap + So_luong_hao_hut` cộng dồn
qua tất cả phiếu nhập đã xác nhận **không được vượt** `So_luong_dat`. Kiểm tra lại tại
thời điểm xác nhận, không chỉ lúc nhập liệu.

**BR03.** Hao hụt bắt buộc có `Ly_do_hao_hut` khi `So_luong_hao_hut > 0`. Hàng hao hụt
không vào kho, không tính vào giá vốn bình quân, nhưng tính vào phần đã nhận của đơn.

**BR04.** Không có "Phiếu hoàn hàng" riêng. Hàng giao thất bại và hàng khách trả đều nhập
lại bằng `Phieu_nhap_kho` với `Loai_nhap` tương ứng, bắt buộc liên kết `Ma_phieu_xuat`.

**BR05.** Chỉ số lượng được chấp nhận nhập lại mới làm tăng `Ton_kho.Ton_thuc`. Phần
chênh giữa số đã xuất và số nhập lại được tính là hàng mất, không tạo chứng từ riêng.

**BR06.** Lô đang thuộc phiếu kiểm kê chưa `Hoàn tất` bị chặn ở hai thao tác: xác nhận
phiếu nhập và đổi lô. Lô ngoài phạm vi phiếu vẫn nhập, xuất bình thường.

### Tồn kho và kiểm kê

**BR07.** `Ton_kho.Ton_thuc` chỉ thay đổi qua đúng 4 nghiệp vụ:

| Nghiệp vụ | Chiều |
|---|---|
| Xác nhận phiếu nhập kho | Tăng |
| Xác nhận xuất kho | Giảm |
| Đổi lô có ghi nhận hàng hỏng | Giảm, trừ ngay lô gốc |
| Duyệt phiếu kiểm kê có chênh lệch | Ghi đè bằng số thực đếm |

Không có màn nào cho phép sửa trực tiếp số tồn.

**BR08.** Chênh lệch kiểm kê bắt buộc có `Ly_do_chenh_lech` và phải được Quản lý kho
duyệt. **Người đếm không được tự duyệt.** Hàng hỏng phát hiện khi lấy hàng được ghi nhận
qua thao tác đổi lô, bắt buộc có lý do và số lượng hỏng.

**BR09.** Mỗi giao dịch hàng hóa xác định rõ kho, sản phẩm, lô và số lượng. Không có giao
dịch nào chỉ ghi ở mức sản phẩm.

### Bán hàng và xuất kho

**BR10.** Đơn bán chỉ được tạo khi bấm Xác nhận. Không có trạng thái nháp. Khi xác nhận,
hệ thống kiểm tra mọi dòng `So_luong ≤` tồn khả dụng (F4) của kho xuất, rồi tạo luôn
phiếu xuất và phân bổ lô theo FIFO (F12).

**BR11.** Giữ hàng không có bảng riêng. Hàng được coi là đang giữ khi tồn tại dòng phiếu
xuất ở `Đang chuẩn bị hàng` (F3). Giữ hàng và hủy đơn **chỉ làm đổi tồn khả dụng, không
đổi tồn thực**.

**BR12.** Phân bổ lô theo FIFO. Khi lô đã phân bổ không đủ, nhân viên kho đổi sang lô
khác còn khả dụng, được lấy từ nhiều lô. Nếu tổng khả dụng các lô thay thế vẫn không đủ,
hệ thống chặn và Kinh doanh hủy đơn.

**BR13.** Đơn bán chỉ hủy được khi phiếu xuất còn `Đang chuẩn bị hàng`, bắt buộc ghi lý
do. Khi hủy, phiếu xuất chuyển `Đã hủy` và hàng giữ được giải phóng.

**BR14.** Sau khi xác nhận, không sửa được sản phẩm, số lượng, giá, kho xuất của đơn bán.
Chỉ sửa được người nhận và địa chỉ giao, và chỉ khi còn `Đang chuẩn bị hàng`.

**BR15.** Số lượng nhập lại không vượt `Chi_tiet_phieu_xuat.So_luong` của dòng tương ứng.
Hàng trả nhập vào **đúng lô đã xuất**, không tạo lô mới.

### Giá và báo cáo

**BR16.** Giá vốn tính theo **bình quân gia quyền liên hoàn, chung toàn công ty** (F7),
lưu ở `San_pham.Gia_von_binh_quan`. Cập nhật sau mỗi dòng nhập kho được xác nhận. Không
đổi khi xuất kho và khi duyệt kiểm kê. Giá vốn được chụp vào `Gia_von_xuat` lúc xuất kho,
vào `Gia_von_hang_hong` lúc đổi lô có hàng hỏng, vào `Gia_von_dieu_chinh` lúc duyệt kiểm
kê.

**BR17.** Hàng giao thất bại hoặc khách trả mà không nhập lại kho thì ghi
`Ghi_chu_xu_ly_hoan` trên phiếu xuất, không tạo phiếu nhập, tồn không đổi.

**BR18.** Chỉ vai trò Kế toán được xuất dữ liệu ra Excel. Các vai trò khác chỉ xem báo
cáo trên màn hình.

**BR19.** Đơn giá bán được chụp vào `Chi_tiet_don_ban` lúc xác nhận đơn. Đổi giá niêm yết
chỉ ảnh hưởng đơn lập sau đó. Sản phẩm chưa có `Gia_niem_yet` không chọn được khi lập đơn.

---

## 2. Giao dịch phải nguyên tử

Mỗi nhóm dưới đây chạy trong **một transaction**. Lỗi bất kỳ bước nào thì rollback toàn
bộ, không được ghi một phần.

| Nghiệp vụ | Các bước trong cùng transaction |
|---|---|
| Lưu đề xuất | Ghi `Don_dat_hang` + toàn bộ `Chi_tiet_don_dat_hang` + tính `Tong_gia_tri` |
| Xác nhận nhập kho | Tạo `Lo` mới nếu có → cộng `Ton_kho.Ton_thuc` → đặt `Ngay_nhap_dau_tien` nếu lần đầu → cập nhật `San_pham.Gia_von_binh_quan` từng dòng → đổi `Phieu_nhap_kho.Trang_thai` → tính lại trạng thái `Don_dat_hang` hoặc `Phieu_xuat_kho.Trang_thai_hoan_hang` |
| Xác nhận đơn bán | Kiểm tra tồn khả dụng → ghi `Don_hang_ban` + `Chi_tiet_don_ban` → ghi `Phieu_xuat_kho` + `Chi_tiet_phieu_xuat` đã phân bổ FIFO |
| Hủy đơn bán | Đổi `Don_hang_ban.Trang_thai` → đổi `Phieu_xuat_kho.Trang_thai_giao_hang` |
| Đổi lô | Tách dòng `Chi_tiet_phieu_xuat` → ghi lý do và người đổi → trừ `Ton_kho` phần hàng hỏng |
| Xác nhận xuất kho | Chụp `Gia_von_xuat` mọi dòng → trừ `Ton_kho.Ton_thuc` → đổi `Phieu_xuat_kho` → đổi `Don_hang_ban.Trang_thai` |
| Cập nhật kết quả giao | Đổi `Phieu_xuat_kho` → đổi `Don_hang_ban.Trang_thai` → đặt `Trang_thai_hoan_hang` nếu thất bại |
| Tạo phiếu kiểm kê | Ghi `Phieu_kiem_ke` → chụp `So_luong_he_thong` toàn bộ lô vào `Chi_tiet_kiem_ke` |
| Duyệt kiểm kê | Chụp `Gia_von_dieu_chinh` → ghi đè `Ton_kho.Ton_thuc` từng lô → đổi `Phieu_kiem_ke` |

### Khóa tranh chấp

Các thao tác đọc rồi ghi `Ton_kho` phải khóa bản ghi trước khi đọc
(`SELECT ... FOR UPDATE` hoặc `lockForUpdate()`), để hai người cùng thao tác không làm
tồn âm. Áp dụng cho: xác nhận đơn bán, xác nhận nhập kho, xác nhận xuất kho, đổi lô,
duyệt kiểm kê.

Kiểm tra điều kiện nghiệp vụ phải **thực hiện lại bên trong transaction**, không tin vào
kết quả kiểm tra lúc hiển thị màn hình.

---

## 3. Quy tắc sinh mã

| Bảng | Dạng mã | Ví dụ | Cách sinh |
|---|---|---|---|
| `Vai_tro` | Chữ hoa cố định | `QL_KHO` | Dữ liệu gốc, 5 bản ghi |
| `Kho` | Chữ viết tắt | `HN`, `HCM` | Quản trị nhập tay, không sửa sau khi tạo |
| `Nguoi_dung` | `ND` + 4 số | `ND0007` | Tự tăng theo số lớn nhất hiện có |
| `San_pham` | `SP` + 4 số | `SP0012` | Tự tăng |
| `Nha_cung_cap` | `NCC` + 3 số | `NCC001` | Tự tăng |
| `Khach_hang` | `KH` + 4 số | `KH0031` | Tự tăng |
| `Lo` | `L` + 2 số năm + 4 số | `L260008` | Hệ thống sinh khi nhập kho. Mã in trên thùng lưu ở `Ma_lo_ncc` |
| `Don_dat_hang` | `DDH-YYYY-NNNN` | `DDH-2026-0001` | Tự tăng theo năm. **Giữ nguyên khi đề xuất thành đơn đặt hàng** |
| `Phieu_nhap_kho` | `PN-YYYY-NNNN` | `PN-2026-0004` | Tự tăng theo năm |
| `Don_hang_ban` | `DB-YYYY-NNNN` | `DB-2026-0005` | Tự tăng theo năm |
| `Phieu_xuat_kho` | `PX-YYYY-NNNN` | `PX-2026-0005` | Sinh cùng lúc với đơn bán |
| `Phieu_kiem_ke` | `KK-YYYY-NNNN` | `KK-2026-0001` | Tự tăng theo năm |

Mã sinh **bên trong transaction** và có khóa, để hai người lập chứng từ cùng lúc không
trùng mã. Mã đã sinh thì không đổi, không dùng lại kể cả khi bản ghi bị xóa.

---

## 4. Kiểm tra đầu vào

### Quy tắc chung

| Loại | Quy tắc |
|---|---|
| Số lượng | Số nguyên, > 0. Riêng số thực đếm khi kiểm kê và số thực nhận khi nhập hàng trả thì ≥ 0 |
| Tiền | Số nguyên không âm, đơn vị đồng, không có phần lẻ |
| Chiết khấu | 0 đến 100, tối đa 2 chữ số thập phân |
| Ngày dự kiến nhận đủ | Không nhỏ hơn ngày lập |
| Email | Đúng định dạng, không trùng trong `Nguoi_dung` |
| Tên đăng nhập | Không dấu, không khoảng trắng, không trùng, không sửa sau khi tạo |
| Mật khẩu | Tối thiểu 8 ký tự, có cả chữ và số, khác mật khẩu hiện tại |
| Trường bắt buộc | Theo cột "Ràng buộc" trong `02-du-lieu.md` |

### Quy tắc theo nghiệp vụ

- Đề xuất phải có nhà cung cấp, ngày dự kiến nhận đủ và ít nhất một dòng sản phẩm.
- Một sản phẩm không xuất hiện hai lần trên cùng một đề xuất hoặc cùng một đơn bán.
- Một sản phẩm **được** xuất hiện nhiều lần trên phiếu nhập, mỗi dòng một lô.
- Đổi lô: tổng số lấy từ các lô thay thế phải **bằng đúng** số lượng cần đổi.
- Xuất kho: bắt buộc có đơn vị vận chuyển và mã vận đơn.
- Chỉ xóa được bản ghi danh mục khi chưa phát sinh chứng từ nào tham chiếu tới.

---

## 5. Thông báo chuẩn

Dùng đúng các câu dưới đây, không tự viết lại.

### Thành công

| Tình huống | Thông báo |
|---|---|
| Lưu bản ghi | `Đã lưu [tên chứng từ] [mã].` |
| Gửi duyệt | `Đã gửi đề xuất [mã] cho Kế toán.` |
| Duyệt đề xuất | `Đã duyệt đề xuất [mã]. Đơn chuyển sang trạng thái Chưa gửi NCC.` |
| Xác nhận nhập kho | `Đã nhập kho [số] chai theo phiếu [mã].` |
| Xác nhận đơn bán | `Đã xác nhận đơn [mã] và giữ hàng.` |
| Xuất kho | `Đã xuất kho phiếu [mã]. Đơn chuyển sang Đang giao.` |
| Duyệt kiểm kê | `Đã duyệt phiếu [mã] và điều chỉnh tồn kho.` |

### Lỗi nghiệp vụ

| Tình huống | Thông báo |
|---|---|
| Nhập vượt số đặt | `Số lượng nhập của [tên sản phẩm] vượt số còn lại của đơn ([số] chai).` |
| Hao hụt thiếu lý do | `Phải ghi lý do hao hụt cho [tên sản phẩm].` |
| Lô đang kiểm kê | `Lô [mã lô] đang thuộc phiếu kiểm kê [mã phiếu] chưa hoàn tất. Xác nhận lại sau khi kiểm kê xong.` |
| Vượt tồn khả dụng | `[Tên sản phẩm] chỉ còn [số] chai khả dụng tại [tên kho].` |
| Sản phẩm chưa có giá | `[Tên sản phẩm] chưa có giá niêm yết, không lập đơn được.` |
| Đổi lô không khớp số | `Tổng số lấy từ các lô thay thế phải bằng [số] chai.` |
| Không còn lô thay thế | `Không còn lô nào đủ hàng. Liên hệ Kinh doanh để hủy đơn.` |
| Xuất kho thiếu vận đơn | `Phải nhập đơn vị vận chuyển và mã vận đơn trước khi xuất kho.` |
| Đóng thiếu khi còn phiếu nháp | `Còn phiếu nhập [mã] đang cập nhật. Xử lý xong mới đóng thiếu được.` |
| Xóa bản ghi đã dùng | `Không xóa được vì [tên] đã phát sinh dữ liệu. Chuyển trạng thái sang Ngừng.` |
| Chênh lệch thiếu lý do | `Phải ghi lý do cho [số] dòng có chênh lệch.` |
| Người đếm tự duyệt | `Người đếm không được duyệt phiếu kiểm kê.` |
| Thiếu quyền | `Bạn không có quyền thực hiện thao tác này.` |
| Sai kho | `Chứng từ này thuộc kho khác.` |
| Trạng thái đã đổi | `Chứng từ đã được người khác xử lý. Tải lại để xem trạng thái mới nhất.` |

### Xác nhận trước khi làm

Mọi thao tác không hoàn tác được đều phải hỏi lại, nêu rõ hệ quả:

| Nút | Câu hỏi |
|---|---|
| Gửi duyệt | `Gửi đề xuất cho Kế toán duyệt? Sau khi gửi sẽ không sửa được trừ khi thu hồi.` |
| Duyệt | `Duyệt đề xuất? Đề xuất chuyển thành đơn đặt hàng trạng thái Chưa gửi NCC.` |
| Xác nhận đã gửi NCC | `Xác nhận đã gửi đơn cho nhà cung cấp? Từ lúc này đơn không sửa hay hủy được nữa.` |
| Xác nhận nhập kho | `Xác nhận nhập kho? Tồn kho sẽ tăng và giá vốn được tính lại. Không hoàn tác được.` |
| Xuất kho | `Xác nhận xuất kho? Tồn thực bị trừ theo lô thực xuất, phiếu chuyển Đang giao.` |
| Hủy đơn bán | `Hủy đơn bán này? Hàng đang giữ được trả lại tồn khả dụng, phiếu xuất bị hủy.` |
| Đóng thiếu | `Đóng thiếu đơn này? Phần còn lại sẽ không nhận nữa.` |
| Duyệt kiểm kê | `Duyệt phiếu? Tồn kho các lô chênh lệch sẽ được điều chỉnh theo số thực tế. Không hoàn tác được.` |
| Xóa | `Xóa [tên chứng từ] này? Không khôi phục được.` |

---

## 6. Nhật ký hệ thống

Ghi vào `activity_log` các thao tác sau, kèm người thực hiện, thời điểm, mã chứng từ và
nội dung thay đổi:

- Đăng nhập, đăng xuất, đăng nhập sai, khóa và mở khóa tài khoản
- Tạo, sửa, khóa tài khoản; sửa danh mục kho
- Mọi lần chuyển trạng thái chứng từ ở `04-trang-thai.md`
- Mọi lần `Ton_kho.Ton_thuc` thay đổi, kèm giá trị trước và sau
- Sửa giá niêm yết

Nhật ký chỉ đọc. Không có chức năng sửa hay xóa cho bất kỳ vai trò nào.

# 04. Trạng thái chứng từ

Tài liệu này định nghĩa toàn bộ trạng thái và phép chuyển trạng thái của hệ thống.
Mọi màn hình chỉ được hiển thị nút nào mà bảng chuyển trạng thái cho phép.

Quy ước: trạng thái được lưu bằng đúng chuỗi tiếng Việt trong cột `Trang_thai`.
Không dùng mã số. So sánh phân biệt dấu.

---

## 1. Đơn đặt hàng — `Don_dat_hang.Trang_thai`

Một bản ghi đi qua hai giai đoạn: **đề xuất** (trước khi Kế toán duyệt) và **đơn đặt
hàng** (sau khi duyệt). Mã không đổi khi chuyển giai đoạn.

| Trạng thái | Giai đoạn | Ý nghĩa |
|---|---|---|
| Đang cập nhật | Đề xuất | Quản lý kho đang soạn, chưa gửi duyệt |
| Chờ phê duyệt | Đề xuất | Đã gửi, chờ Kế toán xử lý |
| Từ chối | Đề xuất | Kế toán từ chối, chờ sửa và gửi lại |
| Đề xuất đã hủy | Đề xuất | Kết thúc, không dùng lại |
| Chưa gửi NCC | Đơn | Đã duyệt, chưa gửi nhà cung cấp |
| Đã gửi NCC | Đơn | Đã gửi, chưa nhận đợt hàng nào |
| Nhận một phần | Đơn | Đã nhận ít nhất một đợt, chưa đủ |
| Đã nhận đủ | Đơn | Kết thúc bình thường |
| Đóng thiếu | Đơn | Kết thúc, phần còn lại không nhận nữa |
| Đơn đã hủy | Đơn | Kết thúc, hủy trước khi gửi NCC |

### Bảng chuyển trạng thái

| Từ | Hành động | Vai trò | Màn hình | Điều kiện | Sang | Ghi vào DB |
|---|---|---|---|---|---|---|
| (mới) | Lưu | Quản lý kho | QLK-04 | Có NCC, ngày dự kiến, ≥1 dòng SP | Đang cập nhật | Sinh `Ma_don_dat_hang`, `Ngay_lap`, `Ma_nguoi_lap`, `Tong_gia_tri` |
| Đang cập nhật | Gửi duyệt | Quản lý kho | QLK-04 | Như trên | Chờ phê duyệt | — |
| Đang cập nhật | Xóa | Quản lý kho | QLK-04 | — | (xóa bản ghi) | Xóa cả chi tiết |
| Chờ phê duyệt | Thu hồi | Quản lý kho | QLK-04 | Kế toán chưa xử lý | Đang cập nhật | — |
| Chờ phê duyệt | Duyệt | Kế toán | KT-03 | — | Chưa gửi NCC | `Ma_nguoi_duyet`, `Thoi_diem_duyet`, `Ghi_chu_phe_duyet` |
| Chờ phê duyệt | Từ chối | Kế toán | KT-03 | Bắt buộc lý do | Từ chối | `Ma_nguoi_duyet`, `Thoi_diem_duyet`, `Ly_do_tu_choi` |
| Từ chối | Gửi lại | Quản lý kho | QLK-04 | — | Chờ phê duyệt | Xóa `Ly_do_tu_choi` |
| Từ chối | Hủy đề xuất | Quản lý kho | QLK-04 | Bắt buộc lý do | Đề xuất đã hủy | `Ma_nguoi_huy`, `Thoi_diem_huy`, `Ly_do_huy` |
| Chưa gửi NCC | Xác nhận đã gửi NCC | Quản lý kho | QLK-04 | — | Đã gửi NCC | `Ma_nguoi_gui_ncc`, `Ngay_gui_ncc` |
| Chưa gửi NCC | Hủy đơn | Quản lý kho | QLK-04 | Bắt buộc lý do | Đơn đã hủy | `Ma_nguoi_huy`, `Thoi_diem_huy`, `Ly_do_huy` |
| Đã gửi NCC | (hệ thống) | — | NVK-04 | Xác nhận phiếu nhập, đơn chưa đủ | Nhận một phần | — |
| Đã gửi NCC / Nhận một phần | (hệ thống) | — | NVK-04 | Xác nhận phiếu nhập, đã nhận đủ | Đã nhận đủ | — |
| Đã gửi NCC / Nhận một phần | Đóng thiếu | Quản lý kho | QLK-04 | Không có phiếu nhập Đang cập nhật của đơn. Bắt buộc lý do | Đóng thiếu | `Ma_nguoi_dong_thieu`, `Thoi_diem_dong_thieu`, `Ly_do_dong_thieu` |

**Không sửa được nội dung** (nhà cung cấp, dòng sản phẩm, số lượng, đơn giá) khi trạng
thái khác `Đang cập nhật` và `Từ chối`. Riêng `Ngay_du_kien_nhan_du` sửa được thêm ở
`Đã gửi NCC` và `Nhận một phần`.

---

## 2. Phiếu nhập kho — `Phieu_nhap_kho.Trang_thai`

| Trạng thái | Ý nghĩa |
|---|---|
| Đang cập nhật | Đã lưu, tồn kho **chưa** thay đổi |
| Đã nhập kho | Đã xác nhận, tồn kho đã cộng |

`Loai_nhap` có 3 giá trị: `Nhập từ NCC`, `Khách trả hàng`, `Hoàn giao thất bại`.

| Từ | Hành động | Vai trò | Điều kiện | Sang | Ghi vào DB |
|---|---|---|---|---|---|
| (mới) | Lưu | Nhân viên kho | ≥1 dòng có số lượng > 0 | Đang cập nhật | Sinh `Ma_phieu_nhap`, `Ngay_lap`, `Ma_nguoi_lap` |
| Đang cập nhật | Xóa phiếu | Nhân viên kho | — | (xóa bản ghi) | — |
| Đang cập nhật | Xác nhận nhập kho | Nhân viên kho | Không có lô nào trong phiếu thuộc phiếu kiểm kê chưa Hoàn tất | Đã nhập kho | `Thoi_diem_xac_nhan`; cộng `Ton_kho.Ton_thuc`; cập nhật `San_pham.Gia_von_binh_quan`; cập nhật trạng thái đơn đặt hàng hoặc phiếu xuất liên quan |
| Đã nhập kho | — | — | — | — | Không sửa, không xóa |

**Chặn xác nhận**: nếu có lô đang kiểm kê, hiện thông báo
`"Lô [mã lô] đang thuộc phiếu kiểm kê [mã phiếu] chưa hoàn tất. Xác nhận lại sau khi kiểm kê xong."`
Phiếu vẫn giữ `Đang cập nhật`.

---

## 3. Đơn hàng bán — `Don_hang_ban.Trang_thai`

Đơn chỉ được tạo khi bấm **Xác nhận đơn hàng**. Không có trạng thái nháp.

| Trạng thái | Ý nghĩa |
|---|---|
| Đang chuẩn bị hàng | Đã xác nhận, hàng đã giữ, kho chưa xuất |
| Đang giao | Đã xuất kho, đang vận chuyển |
| Hoàn thành | Giao thành công |
| Giao thất bại | Giao không thành công |
| Đã hủy | Hủy trước khi xuất kho |

| Từ | Hành động | Vai trò | Màn hình | Điều kiện | Sang | Ghi vào DB |
|---|---|---|---|---|---|---|
| (mới) | Xác nhận đơn hàng | Kinh doanh | KD-03 | Mọi dòng ≤ tồn khả dụng của kho xuất; sản phẩm phải có `Gia_niem_yet` | Đang chuẩn bị hàng | Tạo `Don_hang_ban` + `Chi_tiet_don_ban` (chụp `Don_gia_ban`, `Chiet_khau`) + `Phieu_xuat_kho` + `Chi_tiet_phieu_xuat` (phân bổ lô FIFO). **Một transaction** |
| Đang chuẩn bị hàng | Lưu thông tin giao hàng | Kinh doanh | KD-04 | — | Không đổi | `Ten_nguoi_nhan`, `Sdt_nguoi_nhan`, `Dia_chi_giao_hang` |
| Đang chuẩn bị hàng | Hủy đơn | Kinh doanh | KD-04 | Bắt buộc lý do | Đã hủy | `Thoi_diem_huy`, `Ly_do_huy`; phiếu xuất → `Đã hủy`; hàng giữ được giải phóng |
| Đang chuẩn bị hàng | (theo phiếu xuất) | — | NVK-08 | Nhân viên kho xuất kho | Đang giao | — |
| Đang giao | (theo phiếu xuất) | — | NVK-08 | Giao thành công | Hoàn thành | — |
| Đang giao | (theo phiếu xuất) | — | NVK-08 | Giao thất bại | Giao thất bại | — |

**Không sửa được** sản phẩm, số lượng, giá, kho xuất sau khi xác nhận. Muốn đổi thì hủy
đơn và lập đơn mới.

---

## 4. Phiếu xuất kho

Phiếu xuất có **hai cột trạng thái độc lập**.

### 4.1. `Trang_thai_giao_hang`

| Trạng thái | Ý nghĩa |
|---|---|
| Đang chuẩn bị hàng | Chờ nhân viên kho lấy hàng |
| Đang giao | Đã trừ tồn thực, đã bàn giao vận chuyển |
| Giao thành công | Khách đã nhận |
| Giao hàng thất bại | Không giao được |
| Đã hủy | Đơn bán bị hủy |

| Từ | Hành động | Vai trò | Điều kiện | Sang | Ghi vào DB |
|---|---|---|---|---|---|
| Đang chuẩn bị hàng | Đổi lô | Nhân viên kho | Lô gốc không thuộc phiếu kiểm kê chưa Hoàn tất; tổng số lấy = số cần đổi | Không đổi | `Ma_lo_thuc_xuat`, `Ly_do_doi_lo`, `So_luong_hong`, `Gia_von_hang_hong`, `Ma_nguoi_doi_lo`, `Thoi_diem_doi_lo`; nếu hỏng thì trừ `Ton_kho.Ton_thuc` của lô gốc ngay |
| Đang chuẩn bị hàng | Xuất kho | Nhân viên kho | Bắt buộc `Don_vi_van_chuyen`, `Ma_van_don`; không dòng nào thiếu tồn | Đang giao | `Ma_nguoi_xuat`, `Thoi_diem_xuat_kho`, `Gia_von_xuat` (chụp); trừ `Ton_kho.Ton_thuc`; đơn bán → Đang giao |
| Đang giao | Sửa thông tin vận chuyển | Nhân viên kho | — | Không đổi | `Don_vi_van_chuyen`, `Ma_van_don` |
| Đang giao | Giao thành công | Nhân viên kho | — | Giao thành công | `Thoi_diem_ket_qua_giao`; đơn bán → Hoàn thành |
| Đang giao | Giao thất bại | Nhân viên kho | Bắt buộc lý do | Giao hàng thất bại | `Thoi_diem_ket_qua_giao`, `Ly_do_giao_that_bai`; đơn bán → Giao thất bại; `Trang_thai_hoan_hang` = Chờ xử lý |
| Đang chuẩn bị hàng | (theo đơn bán) | Kinh doanh | Hủy đơn | Đã hủy | — |

### 4.2. `Trang_thai_hoan_hang`

Áp dụng cho hàng quay về kho. NULL nghĩa là chưa phát sinh.

| Trạng thái | Phát sinh khi |
|---|---|
| NULL | Bình thường |
| Chờ xử lý | Giao thất bại, chờ nhân viên kho xử lý hàng hoàn |
| Đã nhập lại kho | Đã lập phiếu nhập `Hoàn giao thất bại` hoặc `Khách trả hàng` |
| Đã xử lý – không nhập kho | Nhân viên kho xác nhận không nhập, bắt buộc `Ghi_chu_xu_ly_hoan` |

| Từ | Hành động | Vai trò | Sang | Ghi vào DB |
|---|---|---|---|---|
| Chờ xử lý | Nhập lại kho | Nhân viên kho | Đã nhập lại kho | Tạo phiếu nhập `Hoàn giao thất bại`; `Thoi_diem_xu_ly_hoan`; `Ghi_chu_xu_ly_hoan` khi nhận lại ít hơn số đã xuất |
| Chờ xử lý | Không nhập kho | Nhân viên kho | Đã xử lý – không nhập kho | `Thoi_diem_xu_ly_hoan`, `Ghi_chu_xu_ly_hoan`. **Không tạo phiếu nhập, tồn không đổi** |
| NULL (Giao thành công) | Khách trả hàng → Nhập lại kho | Nhân viên kho | Đã nhập lại kho | Tạo phiếu nhập `Khách trả hàng`; `Thoi_diem_xu_ly_hoan`; `Ghi_chu_xu_ly_hoan` khi nhận lại ít hơn số đã xuất |
| NULL (Giao thành công) | Khách trả hàng → Không nhập kho | Nhân viên kho | Đã xử lý – không nhập kho | `Thoi_diem_xu_ly_hoan`, `Ghi_chu_xu_ly_hoan` |

`Ghi_chu_xu_ly_hoan` dùng chung cho cả hai nhánh: bắt buộc khi không nhập kho, tùy chọn
khi nhập lại thiếu so với số đã xuất. Số lượng không nhập luôn tính được bằng
*số đã xuất − số đã nhập lại*, không lưu cột riêng.

Phiếu xuất đã có `Trang_thai_hoan_hang` khác NULL và khác `Chờ xử lý` thì **không hiện
trong danh sách chọn** ở màn Nhập kho nữa.

---

## 5. Phiếu kiểm kê — `Phieu_kiem_ke.Trang_thai`

| Trạng thái | Ý nghĩa |
|---|---|
| Đang kiểm | Chờ nhân viên kho đếm |
| Chờ duyệt | Đã đếm xong, có chênh lệch, chờ Quản lý kho |
| Hoàn tất | Kết thúc, tồn đã được điều chỉnh (nếu có chênh lệch) |
| Đã hủy | Kết thúc, số đã đếm không dùng |

| Từ | Hành động | Vai trò | Điều kiện | Sang | Ghi vào DB |
|---|---|---|---|---|---|
| (mới) | Tạo phiếu | Quản lý kho | ≥1 lô được chọn | Đang kiểm | Sinh `Ma_phieu_kiem_ke`, `Thoi_diem_tao`, `Ma_nguoi_tao`, `Ma_nguoi_kiem` (có thể NULL); chụp `So_luong_he_thong` của từng lô |
| Đang kiểm | Lưu tạm | Nhân viên kho | — | Không đổi | `So_luong_thuc_te`, `Ly_do_chenh_lech` |
| Đang kiểm | Hoàn thành đếm | Nhân viên kho | Mọi dòng có `So_luong_thuc_te`; dòng chênh lệch có lý do | **Hoàn tất** nếu không dòng nào lệch | `Thoi_diem_dem_xong`, `Ma_nguoi_kiem` = người bấm |
| Đang kiểm | Hoàn thành đếm | Nhân viên kho | Như trên, có ≥1 dòng lệch | **Chờ duyệt** | Như trên |
| Đang kiểm | Hủy phiếu | Quản lý kho | Bắt buộc lý do | Đã hủy | `Thoi_diem_huy`, `Ly_do_huy` |
| Chờ duyệt | Duyệt và điều chỉnh tồn | Quản lý kho | — | Hoàn tất | `Ma_nguoi_duyet`, `Thoi_diem_duyet`; chụp `Gia_von_dieu_chinh`; ghi đè `Ton_kho.Ton_thuc` = `So_luong_thuc_te` |
| Chờ duyệt | Không duyệt | Quản lý kho | Bắt buộc lý do | Đang kiểm | `Ly_do_khong_duyet`; xóa `Thoi_diem_dem_xong` |

**Khóa theo lô**: trong khi phiếu ở `Đang kiểm` hoặc `Chờ duyệt`, các lô thuộc phiếu bị
chặn ở hai thao tác: xác nhận phiếu nhập và đổi lô. Lô ngoài phạm vi phiếu vẫn nhập,
xuất bình thường.

---

## 6. Người dùng — `Nguoi_dung.Trang_thai`

| Trạng thái | Ý nghĩa |
|---|---|
| Hoạt động | Đăng nhập được |
| Khóa | Không đăng nhập được |

| Sự kiện | Kết quả |
|---|---|
| Quản trị tạo tài khoản | `Trang_thai` = Hoạt động, `Phai_doi_mat_khau` = 1, gửi mật khẩu tạm qua email |
| Đăng nhập đúng khi `Phai_doi_mat_khau` = 1 | Bắt buộc chuyển màn Đổi mật khẩu lần đầu |
| Đăng nhập sai | `So_lan_sai_mat_khau` + 1 |
| `So_lan_sai_mat_khau` đạt 5 | `Trang_thai` = Khóa |
| Đăng nhập đúng | `So_lan_sai_mat_khau` = 0 |
| Quản trị bấm Mở khóa | `Trang_thai` = Hoạt động, `So_lan_sai_mat_khau` = 0 |
| Đặt lại mật khẩu qua email thành công | `Trang_thai` = Hoạt động, `So_lan_sai_mat_khau` = 0, `Phai_doi_mat_khau` = 0 |

## 7. Trạng thái danh mục

| Bảng | Cột | Giá trị | Ảnh hưởng |
|---|---|---|---|
| `Kho` | `Trang_thai` | Hoạt động / Ngừng | Kho Ngừng không chọn được khi lập chứng từ mới và gán người dùng |
| `San_pham` | `Trang_thai` | Đang kinh doanh / Ngừng | Không chọn được khi lập đề xuất và đơn bán mới |
| `Nha_cung_cap` | `Trang_thai` | Đang giao dịch / Ngừng giao dịch | Không chọn được khi lập đề xuất mới |
| `Khach_hang` | `Trang_thai` | Đang giao dịch / Ngừng giao dịch | Không chọn được khi lập đơn bán mới |

Bản ghi đã phát sinh chứng từ thì **không xóa được**, chỉ chuyển trạng thái.

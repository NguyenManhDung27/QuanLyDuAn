<?php

namespace App\Services;

use App\Models\NguoiDung;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Tính tổng số ô "Việc cần xử lý" trên trang chủ theo vai trò (07-nguon-du-lieu.md mục 4)
     * Dùng cho chuông thông báo trên header
     */
    public static function getCountForUser($user): int
    {
        if (!$user) {
            return 0;
        }

        try {
            $data = self::getRoleActionItems($user);
            return (int) array_sum($data['counts'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Lấy chi tiết các mục việc cần xử lý theo vai trò
     */
    public static function getRoleActionItems($user): array
    {
        if (!$user) {
            return ['counts' => [], 'cards' => []];
        }

        $maKho = $user->Ma_kho;
        $today = date('Y-m-d');

        return match ($user->Ma_vai_tro) {
            // Quản trị: Ô "Tài khoản bị khóa" (Nguoi_dung.Trang_thai = 'Khóa')
            'QUAN_TRI' => [
                'counts' => [
                    'tai_khoan_khoa' => NguoiDung::where('Trang_thai', 'Khóa')->count(),
                ],
                'cards' => [
                    [
                        'id' => 'tai_khoan_khoa',
                        'label' => 'Tài khoản bị khóa',
                        'sub' => 'Người dùng nhập sai mật khẩu quá 5 lần hoặc bị khóa',
                        'count' => NguoiDung::where('Trang_thai', 'Khóa')->count(),
                        'dot' => '#B42318',
                        'href' => route('quantri.nguoi-dung.index', ['trang_thai' => 'Khóa']),
                    ],
                ],
            ],

            // Quản lý kho: 4 ô
            'QL_KHO' => (function () use ($maKho, $today) {
                // 1. Đề xuất bị từ chối
                $cTuChoi = DB::table('Don_dat_hang')
                    ->where('Ma_kho', $maKho)
                    ->where('Trang_thai', 'Từ chối')
                    ->count();

                // 2. Đơn chưa gửi NCC
                $cChuaGui = DB::table('Don_dat_hang')
                    ->where('Ma_kho', $maKho)
                    ->where('Trang_thai', 'Chưa gửi NCC')
                    ->count();

                // 3. Đơn cần xem xét (F9: Đã gửi NCC / Nhận một phần và quá ngày dự kiến nhận đủ)
                $cCanXemXet = DB::table('Don_dat_hang')
                    ->where('Ma_kho', $maKho)
                    ->whereIn('Trang_thai', ['Đã gửi NCC', 'Nhận một phần'])
                    ->whereNotNull('Ngay_du_kien_nhan_du')
                    ->where('Ngay_du_kien_nhan_du', '<', $today)
                    ->count();

                // 4. Phiếu kiểm kê chờ duyệt
                $cKiemKe = DB::table('Phieu_kiem_ke')
                    ->where('Ma_kho', $maKho)
                    ->where('Trang_thai', 'Chờ duyệt')
                    ->count();

                return [
                    'counts' => [
                        'tu_choi' => $cTuChoi,
                        'chua_gui' => $cChuaGui,
                        'can_xem_xet' => $cCanXemXet,
                        'kiem_ke_cho_duyet' => $cKiemKe,
                    ],
                    'cards' => [
                        [
                            'id' => 'tu_choi',
                            'label' => 'Đề xuất bị từ chối',
                            'sub' => 'Kế toán từ chối đề xuất, cần chỉnh sửa hoặc gửi lại',
                            'count' => $cTuChoi,
                            'dot' => '#B42318',
                            'href' => route('kho.de-xuat.index', ['trang_thai' => 'Từ chối']),
                        ],
                        [
                            'id' => 'chua_gui',
                            'label' => 'Đơn chưa gửi NCC',
                            'sub' => 'Đã được duyệt mua, cần xác nhận gửi đơn cho nhà cung cấp',
                            'count' => $cChuaGui,
                            'dot' => '#B45309',
                            'href' => route('kho.don-dat-hang.index', ['trang_thai' => 'Chưa gửi NCC']),
                        ],
                        [
                            'id' => 'can_xem_xet',
                            'label' => 'Đơn cần xem xét',
                            'sub' => 'Đã quá ngày dự kiến nhận hàng nhưng chưa nhận đủ',
                            'count' => $cCanXemXet,
                            'dot' => '#B42318',
                            'href' => route('kho.don-dat-hang.index', ['tab' => 'can-xem-xet']),
                        ],
                        [
                            'id' => 'kiem_ke_cho_duyet',
                            'label' => 'Phiếu kiểm kê chờ duyệt',
                            'sub' => 'Kiểm đếm có chênh lệch, chờ duyệt điều chỉnh tồn',
                            'count' => $cKiemKe,
                            'dot' => '#1D4ED8',
                            'href' => route('kho.kiem-ke.index', ['trang_thai' => 'Chờ duyệt']),
                        ],
                    ],
                ];
            })(),

            // Nhân viên kho: 5 ô
            'NV_KHO' => (function () use ($maKho, $user) {
                // 1. Phiếu xuất cần chuẩn bị
                $cChuanBi = DB::table('Phieu_xuat_kho')
                    ->join('Don_hang_ban', 'Don_hang_ban.Ma_don_hang_ban', '=', 'Phieu_xuat_kho.Ma_don_hang_ban')
                    ->where('Don_hang_ban.Ma_kho', $maKho)
                    ->where('Phieu_xuat_kho.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
                    ->count();

                // 2. Đang giao chờ kết quả
                $cDangGiao = DB::table('Phieu_xuat_kho')
                    ->join('Don_hang_ban', 'Don_hang_ban.Ma_don_hang_ban', '=', 'Phieu_xuat_kho.Ma_don_hang_ban')
                    ->where('Don_hang_ban.Ma_kho', $maKho)
                    ->where('Phieu_xuat_kho.Trang_thai_giao_hang', 'Đang giao')
                    ->count();

                // 3. Giao thất bại chờ xử lý hoàn
                $cHoanHang = DB::table('Phieu_xuat_kho')
                    ->join('Don_hang_ban', 'Don_hang_ban.Ma_don_hang_ban', '=', 'Phieu_xuat_kho.Ma_don_hang_ban')
                    ->where('Don_hang_ban.Ma_kho', $maKho)
                    ->where('Phieu_xuat_kho.Trang_thai_hoan_hang', 'Chờ xử lý')
                    ->count();

                // 4. Phiếu nhập đang cập nhật
                $cNhapCapNhat = DB::table('Phieu_nhap_kho')
                    ->where('Ma_kho', $maKho)
                    ->where('Trang_thai', 'Đang cập nhật')
                    ->count();

                // 5. Phiếu kiểm kê cần đếm
                $cKiemKe = DB::table('Phieu_kiem_ke')
                    ->where('Ma_kho', $maKho)
                    ->where('Trang_thai', 'Đang kiểm')
                    ->where(function ($q) use ($user) {
                        $q->where('Ma_nguoi_kiem', $user->Ma_nguoi_dung)
                          ->orWhereNull('Ma_nguoi_kiem');
                    })
                    ->count();

                return [
                    'counts' => [
                        'chuan_bi' => $cChuanBi,
                        'dang_giao' => $cDangGiao,
                        'hoan_hang' => $cHoanHang,
                        'nhap_cap_nhat' => $cNhapCapNhat,
                        'kiem_ke' => $cKiemKe,
                    ],
                    'cards' => [
                        [
                            'id' => 'chuan_bi',
                            'label' => 'Phiếu xuất cần chuẩn bị',
                            'sub' => 'Đơn bán đã xác nhận, chờ lấy hàng và xuất kho',
                            'count' => $cChuanBi,
                            'dot' => '#B45309',
                            'href' => route('nv-kho.phieu-xuat.index', ['tab' => 'chuan-bi']),
                        ],
                        [
                            'id' => 'dang_giao',
                            'label' => 'Đang giao chờ kết quả',
                            'sub' => 'Đã xuất kho, chờ cập nhật giao thành công hoặc thất bại',
                            'count' => $cDangGiao,
                            'dot' => '#1D4ED8',
                            'href' => route('nv-kho.phieu-xuat.index', ['tab' => 'dang-giao']),
                        ],
                        [
                            'id' => 'hoan_hang',
                            'label' => 'Giao thất bại chờ xử lý hoàn',
                            'sub' => 'Hàng hoàn về: nhập lại kho hoặc ghi nhận không nhập',
                            'count' => $cHoanHang,
                            'dot' => '#B42318',
                            'href' => route('nv-kho.nhap-kho.hoan-giao'),
                        ],
                        [
                            'id' => 'nhap_cap_nhat',
                            'label' => 'Phiếu nhập đang cập nhật',
                            'sub' => 'Đã lưu nháp, chưa xác nhận nhập kho',
                            'count' => $cNhapCapNhat,
                            'dot' => '#6B5559',
                            'href' => route('nv-kho.phieu-nhap.index', ['tab' => 'dang_cap_nhat']),
                        ],
                        [
                            'id' => 'kiem_ke',
                            'label' => 'Phiếu kiểm kê cần đếm',
                            'sub' => 'Được giao cho bạn hoặc chưa giao ai',
                            'count' => $cKiemKe,
                            'dot' => '#1D4ED8',
                            'href' => route('nv-kho.kiem-ke.index', ['tab' => 'can_dem']),
                        ],
                    ],
                ];
            })(),

            // Kế toán: 1 ô việc cần xử lý
            'KE_TOAN' => [
                'counts' => [
                    'cho_duyet' => DB::table('Don_dat_hang')->where('Trang_thai', 'Chờ phê duyệt')->count(),
                ],
                'cards' => [
                    [
                        'id' => 'cho_duyet',
                        'label' => 'Đề xuất chờ duyệt',
                        'sub' => 'Quản lý kho đã gửi, chờ kế toán phê duyệt ngân sách',
                        'count' => DB::table('Don_dat_hang')->where('Trang_thai', 'Chờ phê duyệt')->count(),
                        'dot' => '#B45309',
                        'href' => route('ke-toan.de-xuat.index', ['tab' => 'cho-duyet']),
                    ],
                ],
            ],

            // Kinh doanh: 3 ô
            'KINH_DOANH' => (function () {
                // 1. Đơn thiếu tồn (F10: Phieu_xuat_kho đang chuẩn bị có dòng thiếu tồn thực)
                $cThieuTon = DB::table('Phieu_xuat_kho as px')
                    ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
                    ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('Chi_tiet_phieu_xuat as ct')
                            ->join('Ton_kho as tk', function ($join) {
                                $join->on('tk.Ma_lo', '=', DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'))
                                     ->on('tk.Ma_kho', '=', 'dhb.Ma_kho');
                            })
                            ->whereColumn('ct.Ma_phieu_xuat', 'px.Ma_phieu_xuat')
                            ->whereColumn('tk.Ton_thuc', '<', 'ct.So_luong');
                    })
                    ->count();

                // 2. Đơn giao thất bại
                $cGiaoThatBai = DB::table('Don_hang_ban')
                    ->where('Trang_thai', 'Giao thất bại')
                    ->count();

                // 3. Đơn đang chuẩn bị hàng
                $cDangChuanBi = DB::table('Don_hang_ban')
                    ->where('Trang_thai', 'Đang chuẩn bị hàng')
                    ->count();

                return [
                    'counts' => [
                        'thieu_ton' => $cThieuTon,
                        'giao_that_bai' => $cGiaoThatBai,
                        'dang_chuan_bi' => $cDangChuanBi,
                    ],
                    'cards' => [
                        [
                            'id' => 'thieu_ton',
                            'label' => 'Đơn thiếu tồn',
                            'sub' => 'Lô phân bổ không đủ tồn thực, cần đổi lô hoặc phối hợp kho',
                            'count' => $cThieuTon,
                            'dot' => '#B42318',
                            'href' => route('kinh-doanh.don-ban.index', ['trang_thai' => 'Đang chuẩn bị hàng']),
                        ],
                        [
                            'id' => 'giao_that_bai',
                            'label' => 'Đơn giao thất bại',
                            'sub' => 'Giao không thành công, cần liên hệ khách hàng để xử lý',
                            'count' => $cGiaoThatBai,
                            'dot' => '#B42318',
                            'href' => route('kinh-doanh.don-ban.index', ['trang_thai' => 'Giao thất bại']),
                        ],
                        [
                            'id' => 'dang_chuan_bi',
                            'label' => 'Đơn đang chuẩn bị hàng',
                            'sub' => 'Đã xác nhận, đang được kho phân bổ và đóng gói',
                            'count' => $cDangChuanBi,
                            'dot' => '#B45309',
                            'href' => route('kinh-doanh.don-ban.index', ['trang_thai' => 'Đang chuẩn bị hàng']),
                        ],
                    ],
                ];
            })(),

            default => ['counts' => [], 'cards' => []],
        };
    }
}

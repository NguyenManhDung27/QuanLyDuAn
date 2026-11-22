<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogger
{
    /**
     * Ghi nhật ký hệ thống
     *
     * @param string $action Thao tác: Tạo, Sửa, Duyệt, Từ chối, Hủy, Xác nhận, Đổi lô, Khóa / Mở khóa, Đăng nhập, Đăng xuất
     * @param string $logName Loại chứng từ: Người dùng, Kho, Đơn đặt hàng, Phiếu nhập kho, Đơn hàng bán, Phiếu xuất kho, Phiếu kiểm kê, Danh mục, Hệ thống
     * @param string $description Mô tả chi tiết nội dung
     * @param mixed $subject Đối tượng bị tác động (Model hoặc null)
     * @param array $properties Thuộc tính/thay đổi
     */
    public static function log(
        string $action,
        string $logName,
        string $description,
        $subject = null,
        array $properties = []
    ): void {
        $user = Auth::user();

        $activity = activity($logName)
            ->event($action)
            ->withProperties(array_merge([
                'action' => $action,
                'user_name' => $user ? $user->Ho_ten : 'Hệ thống',
                'user_role' => $user && $user->vaiTro ? $user->vaiTro->Ten_vai_tro : 'Hệ thống',
            ], $properties));

        if ($user) {
            $activity->causedBy($user);
        }

        if ($subject) {
            $activity->performedOn($subject);
        }

        $activity->log($description);
    }
}


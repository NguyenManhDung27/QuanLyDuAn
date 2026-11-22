@php
    $user = auth()->user();
    $role = $user->Ma_vai_tro ?? '';
    $currentRoute = Route::currentRouteName() ?? '';
@endphp

<nav aria-label="Menu chính" class="sidebar-nav">
  {{-- ============================================================== --}}
  {{-- 1. VAI TRÒ: QUẢN TRỊ HỆ THỐNG (QUAN_TRI)                       --}}
  {{-- ============================================================== --}}
  @if($role === 'QUAN_TRI')
    <div class="menu-group">
      <a href="{{ route('quantri.trang-chu') }}" class="menu-item {{ str_starts_with($currentRoute, 'quantri.trang-chu') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        <span>Trang chủ</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">QUẢN TRỊ HỆ THỐNG</span>
      <a href="{{ route('quantri.nguoi-dung.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'quantri.nguoi-dung') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Người dùng</span>
      </a>
      <a href="{{ route('quantri.kho.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'quantri.kho') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
        <span>Kho</span>
      </a>
      <a href="{{ route('quantri.nhat-ky.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'quantri.nhat-ky') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Nhật ký hệ thống</span>
      </a>
    </div>

  {{-- ============================================================== --}}
  {{-- 2. VAI TRÒ: QUẢN LÝ KHO (QL_KHO)                               --}}
  {{-- ============================================================== --}}
  @elseif($role === 'QL_KHO')
    <div class="menu-group">
      <a href="{{ route('kho.trang-chu') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.trang-chu') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        <span>Trang chủ</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">MUA HÀNG & TỒN KHO</span>
      <a href="{{ route('kho.ton-kho.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.ton-kho') ? 'active' : '' }}">
        <span>Theo dõi tồn kho</span>
      </a>
      <a href="{{ route('kho.de-xuat.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.de-xuat') ? 'active' : '' }}">
        <span>Đề xuất nhập kho</span>
      </a>
      <a href="{{ route('kho.don-dat-hang.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.don-dat-hang') ? 'active' : '' }}">
        <span>Đơn đặt hàng</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">BÁO CÁO</span>
      <a href="{{ route('kho.bao-cao.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.bao-cao') ? 'active' : '' }}">
        <span>Báo cáo</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">KIỂM KÊ KHO</span>
      <a href="{{ route('kho.kiem-ke.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.kiem-ke') && !str_starts_with($currentRoute, 'kho.kiem-ke.create') ? 'active' : '' }}">
        <span>Phiếu kiểm kê</span>
      </a>
      <a href="{{ route('kho.kiem-ke.create') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.kiem-ke.create') ? 'active' : '' }}">
        <span>Tạo phiếu kiểm kê</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">DANH MỤC</span>
      <a href="{{ route('kho.san-pham.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.san-pham') ? 'active' : '' }}">
        <span>Sản phẩm</span>
      </a>
      <a href="{{ route('kho.nha-cung-cap.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kho.nha-cung-cap') ? 'active' : '' }}">
        <span>Nhà cung cấp</span>
      </a>
    </div>

  {{-- ============================================================== --}}
  {{-- 3. VAI TRÒ: NHÂN VIÊN KHO (NV_KHO)                             --}}
  {{-- ============================================================== --}}
  @elseif($role === 'NV_KHO')
    <div class="menu-group">
      <a href="{{ route('nv-kho.trang-chu') }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.trang-chu') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        <span>Trang chủ</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">NHẬP KHO</span>
      <a href="{{ route('nv-kho.phieu-nhap.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.phieu-nhap') ? 'active' : '' }}">
        <span>Phiếu nhập kho</span>
      </a>
      <a href="{{ route('nv-kho.nhap-kho.index', ['loai' => 'Nhập từ NCC']) }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.nhap-kho') && request('loai') === 'Nhập từ NCC' ? 'active' : '' }}">
        <span>Nhập từ NCC</span>
      </a>
      <a href="{{ route('nv-kho.nhap-kho.index', ['loai' => 'Khách trả hàng']) }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.nhap-kho') && request('loai') === 'Khách trả hàng' ? 'active' : '' }}">
        <span>Khách trả hàng</span>
      </a>
      <a href="{{ route('nv-kho.nhap-kho.index', ['loai' => 'Hoàn giao thất bại']) }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.nhap-kho') && request('loai') === 'Hoàn giao thất bại' ? 'active' : '' }}">
        <span>Hoàn giao thất bại</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">XUẤT KHO</span>
      <a href="{{ route('nv-kho.phieu-xuat.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.phieu-xuat') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Phiếu xuất kho</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">KIỂM KÊ</span>
      <a href="{{ route('nv-kho.kiem-ke.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'nv-kho.kiem-ke') ? 'active' : '' }}">
        <span>Phiếu kiểm kê</span>
      </a>
    </div>

  {{-- ============================================================== --}}
  {{-- 4. VAI TRÒ: KẾ TOÁN (KE_TOAN)                                  --}}
  {{-- ============================================================== --}}
  @elseif($role === 'KE_TOAN')
    <div class="menu-group">
      <a href="{{ route('ke-toan.trang-chu') }}" class="menu-item {{ str_starts_with($currentRoute, 'ke-toan.trang-chu') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        <span>Trang chủ</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">DUYỆT MUA HÀNG</span>
      <a href="{{ route('ke-toan.de-xuat.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'ke-toan.de-xuat') ? 'active' : '' }}">
        <span>Đề xuất nhập kho</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">BÁO CÁO</span>
      <a href="{{ route('ke-toan.bao-cao.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'ke-toan.bao-cao') ? 'active' : '' }}">
        <span>Báo cáo</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">DANH MỤC (CHỈ XEM)</span>
      <a href="{{ route('ke-toan.san-pham.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'ke-toan.san-pham') ? 'active' : '' }}">
        <span>Sản phẩm</span>
      </a>
      <a href="{{ route('ke-toan.nha-cung-cap.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'ke-toan.nha-cung-cap') ? 'active' : '' }}">
        <span>Nhà cung cấp</span>
      </a>
      <a href="{{ route('ke-toan.khach-hang.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'ke-toan.khach-hang') ? 'active' : '' }}">
        <span>Khách hàng</span>
      </a>
    </div>

  {{-- ============================================================== --}}
  {{-- 5. VAI TRÒ: KINH DOANH (KINH_DOANH)                            --}}
  {{-- ============================================================== --}}
  @elseif($role === 'KINH_DOANH')
    <div class="menu-group">
      <a href="{{ route('kinh-doanh.trang-chu') }}" class="menu-item {{ str_starts_with($currentRoute, 'kinh-doanh.trang-chu') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        <span>Trang chủ</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">QUẢN LÝ BÁN HÀNG</span>
      <a href="{{ route('kinh-doanh.don-ban.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kinh-doanh.don-ban') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Đơn hàng bán</span>
      </a>
      <a href="{{ route('kinh-doanh.don-ban.create') }}" class="menu-item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Lập đơn bán mới</span>
      </a>
    </div>

    <div class="menu-group">
      <span class="group-title">KHÁCH HÀNG & GIÁ</span>
      <a href="{{ route('kinh-doanh.khach-hang.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kinh-doanh.khach-hang') ? 'active' : '' }}">
        <span>Khách hàng</span>
      </a>
      <a href="{{ route('kinh-doanh.san-pham.index') }}" class="menu-item {{ str_starts_with($currentRoute, 'kinh-doanh.san-pham') ? 'active' : '' }}">
        <span>Sản phẩm & Giá niêm yết</span>
      </a>
    </div>

  @endif
</nav>

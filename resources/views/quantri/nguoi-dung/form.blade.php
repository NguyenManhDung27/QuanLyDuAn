@extends('layouts.app')

@section('title', ($mode === 'Thêm' ? 'Thêm người dùng' : 'Sửa người dùng: ' . $user->Ho_ten) . ' – Soul Wine')

@section('content')
<div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Quản trị hệ thống</span> &nbsp;/&nbsp; 
        <a href="{{ route('quantri.nguoi-dung.index') }}" style="color: #6B5559; text-decoration: none;">Người dùng</a> &nbsp;/&nbsp;
        <strong style="color: #241417;">{{ $mode === 'Thêm' ? 'Thêm mới' : 'Chi tiết người dùng' }}</strong>
      </div>
      <h1 style="font-size: 24px; font-weight: 700; color: #241417;">
        {{ $mode === 'Thêm' ? 'Thêm người dùng mới' : 'Sửa thông tin người dùng' }}
      </h1>
    </div>

    {{-- Các nút thao tác đặc biệt khi ở mode Sửa: Khóa/Mở khóa, Xóa --}}
    @if($mode === 'Sửa')
      <div style="display: flex; align-items: center; gap: 10px;">
        {{-- Nút Khóa / Mở khóa --}}
        <form method="POST" action="{{ route('quantri.nguoi-dung.toggle-lock', $user->Ma_nguoi_dung) }}" onsubmit="return confirm('{{ $user->Trang_thai === 'Khóa' ? 'Mở khóa tài khoản này? Người dùng sẽ có thể đăng nhập lại.' : 'Khóa tài khoản này? Người dùng sẽ không thể đăng nhập vào hệ thống.' }}');">
          @csrf
          @if($user->Trang_thai === 'Khóa')
            <button type="submit" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 8px; border: 1px solid #0E9F6E; background: #DEF7EC; color: #03543F; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>
              <span>Mở khóa tài khoản</span>
            </button>
          @else
            <button type="submit" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 8px; border: 1px solid #E02424; background: #FDE8E8; color: #9B1C1C; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <span>Khóa tài khoản</span>
            </button>
          @endif
        </form>

        {{-- Nút Xóa (chỉ xóa được khi chưa phát sinh chứng từ) --}}
        <form method="POST" action="{{ route('quantri.nguoi-dung.destroy', $user->Ma_nguoi_dung) }}" onsubmit="return confirm('Xóa người dùng này? Thao tác này không thể khôi phục.');">
          @csrf
          @method('DELETE')
          <button type="submit" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 8px; border: 1px solid #EADFDC; background: #FFFFFF; color: #C81E1E; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;" onmouseover="this.style.background='#FDE8E8';" onmouseout="this.style.background='#FFFFFF';">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            <span>Xóa</span>
          </button>
        </form>
      </div>
    @endif
  </div>

  {{-- Thông báo Alerts --}}
  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px;">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div style="padding: 14px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px;">
      <div style="font-weight: 600; margin-bottom: 4px;">Vui lòng kiểm tra lại thông tin:</div>
      <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form Card --}}
  <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; padding: 28px 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
    <form method="POST" action="{{ $mode === 'Thêm' ? route('quantri.nguoi-dung.store') : route('quantri.nguoi-dung.update', $user->Ma_nguoi_dung) }}" style="display: flex; flex-direction: column; gap: 20px;">
      @csrf

      {{-- Dòng 1: Mã người dùng & Tên đăng nhập --}}
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
            Mã người dùng
          </label>
          <input type="text" value="{{ $mode === 'Thêm' ? $suggestedMa . ' (tự động)' : $user->Ma_nguoi_dung }}" readonly style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; background: #F8F5F4; color: #6B5559; font: inherit; font-size: 14px; font-weight: 600; font-family: monospace;">
          <span style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">Hệ thống tự động sinh theo định dạng NDxxxx.</span>
        </div>

        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
            Tên đăng nhập <span style="color: #C81E1E;">*</span>
          </label>
          @if($mode === 'Thêm')
            <input type="text" name="Ten_dang_nhap" value="{{ old('Ten_dang_nhap') }}" placeholder="Viết liền, không dấu (ví dụ: nguyenvanan)" required style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; outline: none;">
            <span style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">Không dấu, không khoảng trắng, không sửa sau khi tạo.</span>
          @else
            <input type="text" value="{{ $user->Ten_dang_nhap }}" readonly style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; background: #F8F5F4; color: #6B5559; font: inherit; font-size: 14px;">
            <span style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">Tên đăng nhập không được sửa sau khi tạo.</span>
          @endif
        </div>
      </div>

      {{-- Dòng 2: Họ tên & Email --}}
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
            Họ và tên <span style="color: #C81E1E;">*</span>
          </label>
          <input type="text" name="Ho_ten" value="{{ old('Ho_ten', $user->Ho_ten) }}" placeholder="Nhập họ và tên đầy đủ" required style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; outline: none;">
        </div>

        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
            Email <span style="color: #C81E1E;">*</span>
          </label>
          <input type="email" name="Email" value="{{ old('Email', $user->Email) }}" placeholder="ten@soulwine.vn" required style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; outline: none;">
          <span style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">Dùng để nhận mật khẩu tạm và khôi phục tài khoản.</span>
        </div>
      </div>

      {{-- Dòng 3: Số điện thoại --}}
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
          Số điện thoại
        </label>
        <input type="tel" name="So_dien_thoai" value="{{ old('So_dien_thoai', $user->So_dien_thoai) }}" placeholder="Nhập số điện thoại liên hệ" style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; outline: none;">
      </div>

      {{-- Dòng 4: Vai trò --}}
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 8px;">
          Vai trò người dùng <span style="color: #C81E1E;">*</span>
        </label>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
          @foreach($danhSachVaiTro as $vt)
            @php
              $selected = old('Ma_vai_tro', $user->Ma_vai_tro ?? 'NV_KHO') == $vt->Ma_vai_tro;
            @endphp
            <label style="display: flex; align-items: center; gap: 8px; padding: 12px 14px; border-radius: 8px; border: 1px solid {{ $selected ? '#8B1E2D' : '#EADFDC' }}; background: {{ $selected ? '#FAF5F3' : '#FFFFFF' }}; cursor: pointer; transition: all 0.15s ease;">
              <input type="radio" name="Ma_vai_tro" value="{{ $vt->Ma_vai_tro }}" {{ $selected ? 'checked' : '' }} onchange="toggleKhoRequirement(this.value);" style="accent-color: #8B1E2D;">
              <span style="font-size: 13px; font-weight: 600; color: #241417;">{{ $vt->Ten_vai_tro }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Dòng 5: Kho làm việc --}}
      <div id="kho-container">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
          Kho phụ trách <span id="kho-required-star" style="color: #C81E1E;">*</span>
        </label>
        <select name="Ma_kho" id="select-kho" style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; background: #FFFFFF;">
          <option value="">-- Không gắn kho (Toàn hệ thống) --</option>
          @foreach($danhSachKho as $k)
            <option value="{{ $k->Ma_kho }}" {{ old('Ma_kho', $user->Ma_kho) == $k->Ma_kho ? 'selected' : '' }}>
              {{ $k->Ten_kho }} ({{ $k->Ma_kho }})
            </option>
          @endforeach
        </select>
        <span id="kho-note" style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">
          Bắt buộc chọn với vai trò <strong>Quản lý kho</strong> và <strong>Nhân viên kho</strong>. Quản trị, Kế toán và Kinh doanh xem cả hai kho nên để trống.
        </span>
      </div>

      {{-- Trạng thái hiện tại nếu mode = Sửa --}}
      @if($mode === 'Sửa')
        <div style="padding: 14px; border-radius: 8px; background: #FAF5F3; border: 1px solid #EADFDC; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <span style="font-size: 13px; font-weight: 600; color: #241417;">Trạng thái tài khoản:</span>
            <span style="display: inline-block; margin-left: 8px; font-size: 13px; font-weight: 600; color: {{ $user->Trang_thai === 'Hoạt động' ? '#03543F' : '#9B1C1C' }};">
              {{ $user->Trang_thai }}
            </span>
            @if($user->So_lan_sai_mat_khau > 0)
              <span style="font-size: 12px; color: #C81E1E; margin-left: 10px;">(Đã nhập sai {{ $user->So_lan_sai_mat_khau }}/5 lần)</span>
            @endif
          </div>
          <div>
            @if($user->Phai_doi_mat_khau)
              <span style="font-size: 12px; padding: 2px 8px; border-radius: 4px; background: #FEECDC; color: #B43403; font-weight: 500;">Chưa đổi mật khẩu lần đầu</span>
            @endif
          </div>
        </div>
      @endif

      {{-- Nút bấm Lưu & Hủy --}}
      <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 10px; padding-top: 20px; border-top: 1px solid #EADFDC;">
        <a href="{{ route('quantri.nguoi-dung.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 20px; border-radius: 8px; border: 1px solid #EADFDC; background: #FFFFFF; color: #6B5559; font-size: 14px; font-weight: 600; text-decoration: none; transition: background 0.15s ease;" onmouseover="this.style.background='#FAF5F3';" onmouseout="this.style.background='#FFFFFF';">
          Hủy bỏ
        </a>

        <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 24px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
          <span>Lưu</span>
        </button>
      </div>

    </form>
  </div>
</div>

<script>
  function toggleKhoRequirement(role) {
    const star = document.getElementById('kho-required-star');
    const select = document.getElementById('select-kho');
    const note = document.getElementById('kho-note');
    
    if (role === 'QL_KHO' || role === 'NV_KHO') {
      star.style.display = 'inline';
      note.innerHTML = 'Bắt buộc chọn với vai trò <strong>' + (role === 'QL_KHO' ? 'Quản lý kho' : 'Nhân viên kho') + '</strong>.';
    } else {
      star.style.display = 'none';
      note.innerHTML = 'Vai trò này quản lý trên toàn hệ thống (không gán vào kho cụ thể).';
    }
  }

  // Khởi tạo trạng thái ban đầu khi tải trang
  document.addEventListener('DOMContentLoaded', function() {
    const checkedRole = document.querySelector('input[name="Ma_vai_tro"]:checked');
    if (checkedRole) {
      toggleKhoRequirement(checkedRole.value);
    }
  });
</script>
@endsection


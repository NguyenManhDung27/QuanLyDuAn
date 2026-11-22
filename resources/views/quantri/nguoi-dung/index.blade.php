@extends('layouts.app')

@section('title', 'Danh sách người dùng – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title & Thêm mới --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Quản trị hệ thống</span> &nbsp;/&nbsp; <strong style="color: #241417;">Người dùng</strong>
      </div>
      <h1 style="font-size: 24px; font-weight: 700; color: #241417;">Người dùng</h1>
    </div>

    <a href="{{ route('quantri.nguoi-dung.create') }}" style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 8px; background: #8B1E2D; color: #FFFFFF; font-size: 14px; font-weight: 600; text-decoration: none; transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Thêm mới</span>
    </a>
  </div>

  {{-- Thông báo Alert --}}
  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px; display: flex; align-items: center; gap: 8px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px;">
      @foreach($errors->all() as $err)
        <div>• {{ $err }}</div>
      @endforeach
    </div>
  @endif

  {{-- Bộ lọc (Filters) --}}
  <form method="GET" action="{{ route('quantri.nguoi-dung.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; padding: 18px; background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC;">
    {{-- Ô tìm kiếm --}}
    <div style="flex: 2 1 200px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Tìm kiếm</label>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Họ tên, tên đăng nhập, email" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; outline: none;">
    </div>

    {{-- Vai trò --}}
    <div style="flex: 1 1 160px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Vai trò</label>
      <select name="vai_tro" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; background: #FFFFFF;">
        <option value="">Tất cả</option>
        @foreach($danhSachVaiTro as $vt)
          <option value="{{ $vt->Ma_vai_tro }}" {{ request('vai_tro') == $vt->Ma_vai_tro ? 'selected' : '' }}>
            {{ $vt->Ten_vai_tro }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Kho --}}
    <div style="flex: 1 1 160px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Kho</label>
      <select name="kho" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; background: #FFFFFF;">
        <option value="">Tất cả</option>
        @foreach($danhSachKho as $k)
          <option value="{{ $k->Ma_kho }}" {{ request('kho') == $k->Ma_kho ? 'selected' : '' }}>
            {{ $k->Ten_kho }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Trạng thái --}}
    <div style="flex: 1 1 140px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Trạng thái</label>
      <select name="trang_thai" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; background: #FFFFFF;">
        <option value="">Tất cả</option>
        <option value="Hoạt động" {{ request('trang_thai') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
        <option value="Khóa" {{ request('trang_thai') == 'Khóa' ? 'selected' : '' }}>Khóa</option>
      </select>
    </div>

    {{-- Nút Lọc --}}
    <div>
      <button type="submit" style="height: 40px; padding: 0 20px; border-radius: 8px; border: 1px solid #8B1E2D; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
        Lọc
      </button>
      @if(request()->hasAny(['search', 'vai_tro', 'kho', 'trang_thai']))
        <a href="{{ route('quantri.nguoi-dung.index') }}" style="display: inline-flex; align-items: center; height: 40px; padding: 0 14px; margin-left: 8px; border-radius: 8px; border: 1px solid #EADFDC; color: #6B5559; font-size: 13px; text-decoration: none;">
          Đặt lại
        </a>
      @endif
    </div>
  </form>

  {{-- Bảng dữ liệu người dùng --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
        <thead>
          <tr style="background: #FAF5F3; color: #6B5559; border-bottom: 1px solid #EADFDC; font-size: 13px;">
            <th style="padding: 12px 20px; font-weight: 600;">Mã</th>
            <th style="padding: 12px 20px; font-weight: 600;">Họ tên</th>
            <th style="padding: 12px 20px; font-weight: 600;">Tên đăng nhập</th>
            <th style="padding: 12px 20px; font-weight: 600;">Email</th>
            <th style="padding: 12px 20px; font-weight: 600;">Vai trò</th>
            <th style="padding: 12px 20px; font-weight: 600;">Kho</th>
            <th style="padding: 12px 20px; font-weight: 600;">Trạng thái</th>
            <th style="padding: 12px 20px; font-weight: 600; text-align: right;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $u)
            <tr style="border-bottom: 1px solid #F3EAE8; transition: background 0.1s ease;" onmouseover="this.style.background='#FAF7F6';" onmouseout="this.style.background='transparent';">
              <td style="padding: 14px 20px; font-weight: 600; color: #6E1423; font-family: monospace; font-size: 13px;">
                {{ $u->Ma_nguoi_dung }}
              </td>
              <td style="padding: 14px 20px; font-weight: 600; color: #241417;">
                {{ $u->Ho_ten }}
              </td>
              <td style="padding: 14px 20px; color: #6B5559;">
                {{ $u->Ten_dang_nhap }}
              </td>
              <td style="padding: 14px 20px; color: #6B5559;">
                {{ $u->Email }}
              </td>
              <td style="padding: 14px 20px; color: #241417;">
                {{ $u->vaiTro ? $u->vaiTro->Ten_vai_tro : $u->Ma_vai_tro }}
              </td>
              <td style="padding: 14px 20px; color: #6B5559;">
                {{ $u->kho ? $u->kho->Ten_kho : '—' }}
              </td>
              <td style="padding: 14px 20px;">
                @if($u->Trang_thai === 'Hoạt động')
                  <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #DEF7EC; color: #03543F;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #0E9F6E;"></span>
                    Hoạt động
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #FDE8E8; color: #9B1C1C;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #E02424;"></span>
                    Khóa
                  </span>
                @endif
              </td>
              <td style="padding: 14px 20px; text-align: right;">
                <a href="{{ route('quantri.nguoi-dung.edit', $u->Ma_nguoi_dung) }}" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; border-radius: 6px; border: 1px solid #EADFDC; background: #FFFFFF; color: #8B1E2D; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s ease;" onmouseover="this.style.background='#8B1E2D';this.style.color='#FFFFFF';this.style.borderColor='#8B1E2D';" onmouseout="this.style.background='#FFFFFF';this.style.color='#8B1E2D';this.style.borderColor='#EADFDC';">
                  Xem
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="padding: 32px 20px; text-align: center; color: #6B5559;">
                Không tìm thấy người dùng nào phù hợp với bộ lọc.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer: Tổng số & Phân trang --}}
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; border-top: 1px solid #EADFDC; background: #FFFFFF; font-size: 13px; color: #6B5559;">
      <div>
        Hiển thị <strong>{{ $users->firstItem() ?? 0 }}</strong> – <strong>{{ $users->lastItem() ?? 0 }}</strong> trên tổng số <strong>{{ $users->total() }}</strong> người dùng
      </div>
      <div>
        {{ $users->links() }}
      </div>
    </div>
  </div>
</div>
@endsection


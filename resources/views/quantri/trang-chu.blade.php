@extends('layouts.app')

@section('title', 'Trang chủ Quản trị – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">
  {{-- Tiêu đề trang --}}
  <div>
    <h1 style="font-size: 24px; font-weight: 700; color: #241417; margin-bottom: 4px;">Trang chủ</h1>
    <p style="font-size: 14px; color: #6B5559;">Quản trị tài khoản người dùng, kho và nhật ký hệ thống.</p>
  </div>

  {{-- Lối tắt / Thẻ tóm tắt --}}
  <div>
    <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 12px;">Lối tắt & Tình trạng</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
      
      {{-- Card 1: Thêm người dùng --}}
      <a href="{{ route('quantri.nguoi-dung.create') }}" style="display: flex; flex-direction: column; gap: 8px; padding: 20px; background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; text-decoration: none; transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(110,20,35,0.08)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-size: 16px; font-weight: 600; color: #241417;">Thêm người dùng</span>
          <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5F3; display: flex; align-items: center; justify-content: center; color: #8B1E2D;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
          </div>
        </div>
        <p style="font-size: 13px; color: #6B5559;">Tạo tài khoản, gán vai trò và kho</p>
      </a>

      {{-- Card 2: Người dùng bị khóa --}}
      <a href="{{ route('quantri.nguoi-dung.index', ['trang_thai' => 'Khóa']) }}" style="display: flex; flex-direction: column; gap: 8px; padding: 20px; background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; text-decoration: none; transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(110,20,35,0.08)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-size: 16px; font-weight: 600; color: #241417;">Người dùng bị khóa</span>
          <div style="width: 36px; height: 36px; border-radius: 8px; background: {{ $soTaiKhoanKhoa > 0 ? '#FDE8E8' : '#FAF5F3' }}; display: flex; align-items: center; justify-content: center; color: {{ $soTaiKhoanKhoa > 0 ? '#C81E1E' : '#6B5559' }};">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          </div>
        </div>
        <div style="display: flex; align-items: baseline; gap: 6px;">
          <span style="font-size: 24px; font-weight: 700; color: {{ $soTaiKhoanKhoa > 0 ? '#C81E1E' : '#241417' }};">{{ $soTaiKhoanKhoa }}</span>
          <span style="font-size: 13px; color: #6B5559;">tài khoản</span>
        </div>
      </a>

      {{-- Card 3: Danh mục Kho --}}
      <a href="{{ route('quantri.kho.index') }}" style="display: flex; flex-direction: column; gap: 8px; padding: 20px; background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; text-decoration: none; transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(110,20,35,0.08)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-size: 16px; font-weight: 600; color: #241417;">Danh mục kho</span>
          <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5F3; display: flex; align-items: center; justify-content: center; color: #8B1E2D;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
          </div>
        </div>
        <div style="display: flex; align-items: baseline; gap: 6px;">
          <span style="font-size: 24px; font-weight: 700; color: #241417;">{{ $tongKho }}</span>
          <span style="font-size: 13px; color: #6B5559;">kho hệ thống</span>
        </div>
      </a>

      {{-- Card 4: Nhật ký gần đây --}}
      <a href="{{ route('quantri.nhat-ky.index') }}" style="display: flex; flex-direction: column; gap: 8px; padding: 20px; background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; text-decoration: none; transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(110,20,35,0.08)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-size: 16px; font-weight: 600; color: #241417;">Nhật ký gần đây</span>
          <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5F3; display: flex; align-items: center; justify-content: center; color: #8B1E2D;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          </div>
        </div>
        <p style="font-size: 13px; color: #6B5559;">Tra cứu thao tác trên hệ thống</p>
      </a>

    </div>
  </div>

  {{-- Bảng Nhật ký gần đây --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #EADFDC;">
      <h2 style="font-size: 16px; font-weight: 600; color: #241417;">Nhật ký thao tác gần đây</h2>
      <a href="{{ route('quantri.nhat-ky.index') }}" style="font-size: 13px; font-weight: 600; color: #8B1E2D;">Xem toàn bộ nhật ký &rarr;</a>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
        <thead>
          <tr style="background: #FAF5F3; color: #6B5559; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 20px; font-weight: 600;">Thời điểm</th>
            <th style="padding: 12px 20px; font-weight: 600;">Người thực hiện</th>
            <th style="padding: 12px 20px; font-weight: 600;">Thao tác</th>
            <th style="padding: 12px 20px; font-weight: 600;">Loại chứng từ</th>
            <th style="padding: 12px 20px; font-weight: 600;">Nội dung</th>
          </tr>
        </thead>
        <tbody>
          @forelse($nhatKyGanDay as $log)
            <tr style="border-bottom: 1px solid #F3EAE8;">
              <td style="padding: 12px 20px; color: #6B5559; white-space: nowrap;">
                {{ $log->created_at->format('d/m/Y H:i') }}
              </td>
              <td style="padding: 12px 20px; font-weight: 500; color: #241417;">
                {{ $log->causer ? $log->causer->Ho_ten : ($log->properties['user_name'] ?? 'Hệ thống') }}
              </td>
              <td style="padding: 12px 20px;">
                @php
                  $event = $log->event ?? ($log->properties['action'] ?? 'Thao tác');
                  $color = match($event) {
                    'Tạo' => '#0E9F6E',
                    'Sửa' => '#3F83F8',
                    'Khóa' => '#E02424',
                    'Đăng nhập' => '#046C4E',
                    'Đăng xuất' => '#76A9FA',
                    default => '#4B5563'
                  };
                @endphp
                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; background: {{ $color }}15; color: {{ $color }};">
                  {{ $event }}
                </span>
              </td>
              <td style="padding: 12px 20px; color: #4B5563;">
                {{ $log->log_name ?? '—' }}
              </td>
              <td style="padding: 12px 20px; color: #241417;">
                {{ $log->description }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="padding: 24px; text-align: center; color: #6B5559;">
                Chưa có nhật ký hoạt động nào.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection


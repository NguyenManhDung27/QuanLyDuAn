<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Soul Wine – Hệ thống quản lý kho')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      margin: 0;
      background: #FAF5F3;
      font-family: 'Be Vietnam Pro', sans-serif;
      color: #241417;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    a { color: #8B1E2D; text-decoration: none; }
    a:hover { color: #5E1320; }

    /* HEADER */
    .app-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 12px 28px;
      background: #FFFFFF;
      border-bottom: 1px solid #EADFDC;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .brand-box {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .brand-icon {
      width: 34px;
      height: 45px;
      display: block;
      object-fit: contain;
    }
    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      color: #6E1423;
      letter-spacing: 0.04em;
      line-height: 1.1;
      font-weight: 600;
    }
    .brand-sub {
      font-size: 12px;
      color: #6B5559;
    }
    .header-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .notification-wrapper {
      position: relative;
    }
    .notification-bell {
      position: relative;
      width: 42px;
      height: 42px;
      border-radius: 999px;
      border: 1px solid #EADFDC;
      background: #FFFFFF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #6E1423;
      cursor: pointer;
      transition: all 0.15s ease;
      outline: none;
    }
    .notification-bell:hover {
      background: #FDF9F8;
      border-color: #DCCAC6;
    }
    .bell-badge {
      position: absolute;
      top: -4px;
      right: -6px;
      min-width: 20px;
      height: 20px;
      padding: 0 5px;
      border-radius: 999px;
      background: #B42318;
      color: #FFFFFF;
      font-size: 11px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #FFFFFF;
    }
    .notification-dropdown {
      position: absolute;
      top: calc(100% + 10px);
      right: 0;
      width: 340px;
      background: #FFFFFF;
      border: 1px solid #EADFDC;
      border-radius: 12px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
      z-index: 1050;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .notif-dropdown-header {
      padding: 12px 16px;
      background: #FAF6F5;
      border-bottom: 1px solid #EADFDC;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .notif-header-badge {
      font-size: 12px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 999px;
      background: #F6ECEA;
      color: #6E1423;
    }
    .notif-dropdown-body {
      max-height: 360px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }
    .notif-item {
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      text-decoration: none;
      border-bottom: 1px solid #F3ECE9;
      transition: background 0.15s ease;
    }
    .notif-item:hover {
      background: #FDF9F8;
    }
    .notif-item:last-child {
      border-bottom: none;
    }
    .notif-item-content {
      display: flex;
      flex-direction: column;
      gap: 2px;
      flex: 1;
    }
    .notif-item-title {
      font-size: 13px;
      font-weight: 600;
      color: #241417;
    }
    .notif-item-sub {
      font-size: 11px;
      color: #6B5559;
      line-height: 1.4;
    }
    .notif-item-badge {
      min-width: 22px;
      height: 22px;
      padding: 0 6px;
      border-radius: 999px;
      background: #B42318;
      color: #FFFFFF;
      font-size: 11px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .notif-empty-state {
      padding: 24px 16px;
      text-align: center;
      font-size: 13px;
      color: #6B5559;
    }
    .notif-dropdown-footer {
      padding: 10px 16px;
      background: #FAF6F5;
      border-top: 1px solid #EADFDC;
      text-align: center;
    }
    .notif-footer-link {
      font-size: 12px;
      font-weight: 600;
      color: #6E1423;
      text-decoration: none;
    }
    .notif-footer-link:hover {
      text-decoration: underline;
    }
    .warehouse-badge {
      display: inline-flex;
      align-items: center;
      height: 32px;
      padding: 0 12px;
      border-radius: 999px;
      background: #F6ECEA;
      color: #5E1320;
      font-size: 13px;
      font-weight: 600;
    }
    .user-info {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }
    .user-name {
      font-size: 14px;
      font-weight: 600;
      color: #241417;
    }
    .user-role {
      font-size: 12px;
      color: #6B5559;
    }
    .btn-header-outline {
      height: 38px;
      padding: 0 14px;
      border-radius: 8px;
      border: 1px solid #DCCAC6;
      background: #FFFFFF;
      color: #241417;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .btn-header-outline:hover {
      background: #FAF5F3;
      border-color: #8B1E2D;
      color: #8B1E2D;
    }
    .btn-header-primary {
      height: 38px;
      padding: 0 16px;
      border-radius: 8px;
      border: 0;
      background: #6E1423;
      color: #FFFFFF;
      font: inherit;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s ease;
      display: inline-flex;
      align-items: center;
    }
    .btn-header-primary:hover {
      background: #8B1E2D;
    }

    /* MAIN WRAPPER */
    .app-body {
      display: flex;
      flex: 1 1 auto;
      min-height: calc(100vh - 69px);
    }

    /* SIDEBAR */
    .app-sidebar {
      width: 260px;
      flex-shrink: 0;
      background: #4A0E1A;
      padding: 20px 12px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      min-height: 100%;
    }
    .sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .menu-group {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .group-title {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
      color: #D9B8B3;
      padding: 6px 12px;
      text-transform: uppercase;
    }
    .menu-item {
      display: flex;
      align-items: center;
      gap: 10px;
      min-height: 40px;
      padding: 0 12px;
      border-radius: 8px;
      font-size: 14px;
      color: #F3E4E1;
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .menu-item:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #FFFFFF;
    }
    .menu-item.active {
      background: #8B1E2D;
      color: #FFFFFF;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    /* CONTENT */
    .app-main {
      flex: 1 1 auto;
      min-width: 0;
      padding: 28px 36px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .breadcrumb-box {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .breadcrumb-path {
      font-size: 13px;
      color: #6B5559;
    }
    .page-title {
      font-size: 26px;
      font-weight: 700;
      color: #241417;
      margin: 0;
    }
    .page-desc {
      font-size: 14px;
      color: #6B5559;
      margin: 0;
    }

    /* CARDS */
    .card-box {
      background: #FFFFFF;
      border: 1px solid #EADFDC;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    /* ALERTS */
    .alert-success {
      padding: 12px 16px;
      border-radius: 8px;
      background: #DEF7EC;
      border: 1px solid #BCF0DA;
      color: #03543F;
      font-size: 14px;
    }
    .alert-danger {
      padding: 12px 16px;
      border-radius: 8px;
      background: #FDE8E8;
      border: 1px solid #F8B4B4;
      color: #9B1C1C;
      font-size: 14px;
    }
  </style>
  @stack('styles')
</head>
<body>
  @php
    $authUser = auth()->user();
    $tenKho = $authUser && $authUser->kho ? $authUser->kho->Ten_kho : 'Kho Tổng';
    $tenVaiTro = $authUser && $authUser->vaiTro ? $authUser->vaiTro->Ten_vai_tro : ($authUser->Ma_vai_tro ?? 'Người dùng');
    $bellCount = \App\Services\NotificationService::getCountForUser($authUser);
    $homeRoute = match ($authUser->Ma_vai_tro ?? '') {
        'QUAN_TRI' => route('quantri.trang-chu'),
        'QL_KHO' => route('kho.trang-chu'),
        'NV_KHO' => route('nv-kho.trang-chu'),
        'KE_TOAN' => route('ke-toan.trang-chu'),
        'KINH_DOANH' => route('kinh-doanh.trang-chu'),
        default => route('dashboard'),
    };
  @endphp

  {{-- HEADER CHÍNH --}}
  <header class="app-header">
    <div class="brand-box">
      <img class="brand-icon" src="{{ asset('images/logo-icon.png') }}" alt="Soul Wine Logo">
      <div style="display: flex; flex-direction: column;">
        <span class="brand-name">SOUL WINE</span>
        <span class="brand-sub">Hệ thống quản lý kho</span>
      </div>
    </div>

    <div class="header-actions">
      <div class="notification-wrapper">
        <button type="button" class="notification-bell" id="bellToggleBtn" title="Việc cần xử lý ({{ $bellCount }})" aria-expanded="false" aria-haspopup="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
          <span class="bell-badge" id="bellBadge" style="{{ $bellCount > 0 ? '' : 'display: none;' }}">{{ $bellCount }}</span>
        </button>

        <div class="notification-dropdown" id="bellDropdown" style="display: none;">
          <div class="notif-dropdown-header">
            <div style="font-weight: 700; font-size: 14px; color: #241417;">Việc cần xử lý</div>
            <span class="notif-header-badge" id="dropdownTotalBadge">{{ $bellCount }}</span>
          </div>
          <div class="notif-dropdown-body" id="bellDropdownBody">
            @php
              $userActionItems = \App\Services\NotificationService::getRoleActionItems($authUser);
              $activeCards = array_filter($userActionItems['cards'] ?? [], fn($c) => ($c['count'] ?? 0) > 0);
            @endphp
            @forelse($activeCards as $card)
              <a href="{{ $card['href'] }}" class="notif-item">
                <div class="notif-item-content">
                  <div class="notif-item-title">{{ $card['label'] }}</div>
                  <div class="notif-item-sub">{{ $card['sub'] }}</div>
                </div>
                <span class="notif-item-badge">{{ $card['count'] }}</span>
              </a>
            @empty
              <div class="notif-empty-state">
                Hiện không có việc nào tồn đọng cần xử lý.
              </div>
            @endforelse
          </div>
          <div class="notif-dropdown-footer">
            <a href="{{ $homeRoute }}" class="notif-footer-link">Xem trang tổng quan &rarr;</a>
          </div>
        </div>
      </div>

      <span class="warehouse-badge">{{ $tenKho }}</span>

      <div class="user-info">
        <span class="user-name">{{ $authUser->Ho_ten ?? $authUser->Ten_dang_nhap }}</span>
        <span class="user-role">{{ $tenVaiTro }}</span>
      </div>

      <a href="{{ route('password.change') }}" class="btn-header-outline">Đổi mật khẩu</a>

      <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-header-primary">Đăng xuất</button>
      </form>
    </div>
  </header>

  {{-- BODY: SIDEBAR + CONTENT --}}
  <div class="app-body">
    <aside class="app-sidebar">
      @include('layouts.sidebar')
    </aside>

    <main class="app-main">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      @if(session('error'))
        <div class="alert-danger">{{ session('error') }}</div>
      @endif

      @yield('content')
    </main>
  </div>

  <script>
    (function() {
      const bellBtn = document.getElementById('bellToggleBtn');
      const bellDropdown = document.getElementById('bellDropdown');
      const bellBadge = document.getElementById('bellBadge');
      const dropdownTotalBadge = document.getElementById('dropdownTotalBadge');
      const dropdownBody = document.getElementById('bellDropdownBody');

      if (!bellBtn || !bellDropdown) return;

      // Toggle popover dropdown
      bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = bellDropdown.style.display === 'none';
        bellDropdown.style.display = isHidden ? 'flex' : 'none';
        bellBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
      });

      // Close when clicking outside
      document.addEventListener('click', function(e) {
        if (!bellDropdown.contains(e.target) && !bellBtn.contains(e.target)) {
          bellDropdown.style.display = 'none';
          bellBtn.setAttribute('aria-expanded', 'false');
        }
      });

      // Helper function to safely escape HTML
      function escapeHtml(text) {
        if (!text) return '';
        const p = document.createElement('p');
        p.textContent = text;
        return p.innerHTML;
      }

      // Live Polling every 15s to auto-decrement when tasks are completed
      function pollNotificationStatus() {
        fetch('{{ route('thong-bao.dem') }}', { credentials: 'same-origin' })
          .then(res => {
            if (!res.ok) return null;
            return res.json();
          })
          .then(data => {
            if (!data) return;
            const count = data.count || 0;
            const cards = data.cards || [];

            // Update badge on bell button
            if (bellBadge) {
              bellBadge.textContent = count;
              bellBadge.style.display = count > 0 ? 'inline-flex' : 'none';
            }

            // Update dropdown total badge
            if (dropdownTotalBadge) {
              dropdownTotalBadge.textContent = count;
            }

            // Update dropdown list
            if (dropdownBody) {
              if (cards.length === 0) {
                dropdownBody.innerHTML = '<div class="notif-empty-state">Hiện không có việc nào tồn đọng cần xử lý.</div>';
              } else {
                dropdownBody.innerHTML = cards.map(c => `
                  <a href="${escapeHtml(c.href)}" class="notif-item">
                    <div class="notif-item-content">
                      <div class="notif-item-title">${escapeHtml(c.label)}</div>
                      <div class="notif-item-sub">${escapeHtml(c.sub || '')}</div>
                    </div>
                    <span class="notif-item-badge">${c.count}</span>
                  </a>
                `).join('');
              }
            }
          })
          .catch(() => {});
      }

      // Check on window tab focus and every 15s
      window.addEventListener('focus', pollNotificationStatus);
      setInterval(pollNotificationStatus, 15000);
    })();
  </script>

  @stack('scripts')
</body>
</html>


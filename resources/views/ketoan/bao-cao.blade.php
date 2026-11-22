@extends('layouts.app')

@section('title', 'Báo cáo – Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

  {{-- Header & Actions --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 4px;">
      <span style="font-size: 13px; color: #6B5559;">Báo cáo / <strong style="color: #241417;">{{ $data['name'] }}</strong></span>
      <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Báo cáo</h1>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
      <button type="button" onclick="window.print()" title="In hoặc lưu thành PDF bằng chức năng in của trình duyệt" 
              style="height: 42px; padding: 0 16px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
        In / Lưu PDF
      </button>

      <a href="{{ route('ke-toan.bao-cao.export', ['tab' => $tab, 'kho' => $maKho, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'tai_ngay' => $taiNgay, 'q' => $q]) }}" 
         style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3v12"></path>
          <path d="M7 10l5 5 5-5"></path>
          <path d="M5 21h14"></path>
        </svg>
        Xuất Excel
      </a>
    </div>
  </div>

  {{-- 6 Tabs Selector --}}
  <div role="tablist" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabs = [
        0 => 'Tồn kho',
        1 => 'Nhập – xuất – tồn',
        2 => 'Doanh thu – lãi gộp',
        3 => 'Mua hàng',
        4 => 'Hàng trả và giao thất bại',
        5 => 'Điều chỉnh tồn',
      ];
    @endphp
    @foreach($tabs as $idx => $label)
      <a href="{{ route('ke-toan.bao-cao.index', ['tab' => $idx, 'kho' => $maKho, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'tai_ngay' => $taiNgay, 'q' => $q]) }}"
         role="tab"
         style="height: 40px; padding: 0 16px; border-radius: 999px; font: inherit; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; cursor: pointer; transition: all 0.15s ease;
                {{ $tab === $idx ? 'border: 0; background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  {{-- Form bộ lọc --}}
  <form method="GET" action="{{ route('ke-toan.bao-cao.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; padding: 16px 18px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC;">
    <input type="hidden" name="tab" value="{{ $tab }}">

    @if(!$data['range'])
      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
        Tại ngày
        <input type="date" name="tai_ngay" value="{{ $taiNgay }}" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
      </label>
    @else
      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
        Từ ngày
        <input type="date" name="tu_ngay" value="{{ $tuNgay }}" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
      </label>
      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
        Đến ngày
        <input type="date" name="den_ngay" value="{{ $denNgay }}" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
      </label>
    @endif

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 150px;">
      Kho
      <select name="kho" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="all" {{ $maKho === 'all' ? 'selected' : '' }}>Tất cả kho</option>
        @foreach($khos as $k)
          <option value="{{ $k->Ma_kho }}" {{ $maKho === $k->Ma_kho ? 'selected' : '' }}>{{ $k->Ten_kho }}</option>
        @endforeach
      </select>
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 200px;">
      {{ $data['searchLabel'] }}
      <input type="search" name="q" value="{{ $q }}" placeholder="{{ $data['searchPh'] }}" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <button type="submit" style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
      Xem báo cáo
    </button>
  </form>

  <p style="margin: 0; font-size: 14px; color: #3B2A2D;">
    {{ $data['desc'] }} 
    <span style="color: #6B5559;">
      @if(!$data['range'])
        Tại ngày: {{ \Carbon\Carbon::parse($taiNgay)->format('d/m/Y') }} · {{ $maKho === 'all' ? 'Tất cả kho' : ($khos->firstWhere('Ma_kho', $maKho)->Ten_kho ?? $maKho) }}.
      @else
        Kỳ đang xem: {{ \Carbon\Carbon::parse($tuNgay)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($denNgay)->format('d/m/Y') }} · {{ $maKho === 'all' ? 'Tất cả kho' : ($khos->firstWhere('Ma_kho', $maKho)->Ten_kho ?? $maKho) }}.
      @endif
    </span>
  </p>

  {{-- 4 KPI Cards --}}
  @if(!empty($data['kpis']))
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
      @foreach($data['kpis'] as $kpi)
        <div style="display: flex; flex-direction: column; gap: 6px; padding: 16px 20px; border-radius: 12px; 
                    {{ $kpi['highlight'] ? 'background: #6E1423; color: #FFFFFF;' : 'background: #FFFFFF; border: 1px solid #EADFDC; color: #241417;' }}">
          <span style="font-size: 13px; color: {{ $kpi['highlight'] ? '#F1D9D5' : '#6B5559' }};">
            {{ $kpi['label'] }}
          </span>
          <span style="font-size: 24px; font-weight: 700; letter-spacing: -0.01em;">
            {{ $kpi['value'] }}
          </span>
        </div>
      @endforeach
    </div>
  @endif

  {{-- Biểu đồ 12 tháng (Doanh thu & Lãi gộp) --}}
  @if(!empty($data['hasChart']) && !empty($data['bars']))
    <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC;">
      <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
        <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Doanh thu và lãi gộp theo tháng</h2>
        <div style="display: flex; gap: 16px; font-size: 13px; color: #3B2A2D;">
          <span style="display: inline-flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; border-radius: 3px; background: #8B1E2D;"></span> Doanh thu
          </span>
          <span style="display: inline-flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; border-radius: 3px; background: #D9A7A0;"></span> Lãi gộp
          </span>
        </div>
      </div>

      <div style="display: flex; gap: 10px;">
        <div style="flex: 1; display: flex; align-items: flex-end; gap: 12px; height: 200px; border-left: 1px solid #EADFDC; border-bottom: 1px solid #EADFDC; padding: 0 10px; background-image: linear-gradient(#F3ECEA 1px, transparent 1px); background-size: 100% 33.3%;">
          @foreach($data['bars'] as $b)
            <div title="{{ $b['tip'] }}" style="flex: 1; display: flex; align-items: flex-end; justify-content: center; gap: 4px; height: 100%;">
              <div style="width: 45%; max-width: 18px; height: {{ max(4, $b['revHeight']) }}%; background: #8B1E2D; border-radius: 4px 4px 0 0;"></div>
              <div style="width: 45%; max-width: 18px; height: {{ max(4, $b['gpHeight']) }}%; background: #D9A7A0; border-radius: 4px 4px 0 0;"></div>
            </div>
          @endforeach
        </div>
      </div>
      <div style="display: flex; gap: 12px; padding-left: 10px; padding-right: 10px;">
        @foreach($data['bars'] as $b)
          <span style="flex: 1; text-align: center; font-size: 11px; color: #6B5559;">{{ $b['m'] }}</span>
        @endforeach
      </div>
    </section>
  @endif

  {{-- Bảng báo cáo --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: auto; max-height: 520px;">
    <table style="width: 100%; min-width: 1000px; border-collapse: collapse; font-size: 14px; font-variant-numeric: tabular-nums;">
      <thead>
        <tr style="position: sticky; top: 0; z-index: 1; background: #F6ECEA;">
          @foreach($data['cols'] as $c)
            <th scope="col" style="padding: 12px 14px; font-weight: 600; color: #5E1320; white-space: nowrap; text-align: {{ $c['align'] }}; border-bottom: 1px solid #E7D6D2;">
              {{ $c['label'] }}
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @if(empty($data['rows']))
          <tr>
            <td colspan="{{ count($data['cols']) }}" style="padding: 32px; text-align: center; color: #6B5559;">
              Không có dữ liệu trong kỳ này.
            </td>
          </tr>
        @else
          @foreach($data['rows'] as $row)
            <tr style="border-top: 1px solid #F0E6E3;">
              @foreach($row['cells'] as $cell)
                <td style="padding: 11px 14px; text-align: {{ $cell['align'] }}; {{ isset($cell['color']) ? 'font-weight: 600; color: ' . $cell['color'] . ';' : '' }}">
                  {{ $cell['v'] }}
                </td>
              @endforeach
            </tr>
          @endforeach
        @endif
      </tbody>
      @if(!empty($data['rows']) && !empty($data['foot']))
        <tfoot>
          <tr style="position: sticky; bottom: 0; background: #6E1423; color: #FFFFFF; font-weight: 700;">
            @foreach($data['foot'] as $f)
              <td style="padding: 12px 14px; text-align: {{ $f['align'] }}; {{ isset($f['color']) ? 'color: ' . $f['color'] . ';' : '' }}">
                {{ $f['v'] }}
              </td>
            @endforeach
          </tr>
        </tfoot>
      @endif
    </table>
  </div>

  <span style="font-size: 13px; color: #6B5559;">{{ $data['note'] }} Số âm màu đỏ, số dương màu xanh.</span>

</div>
@endsection


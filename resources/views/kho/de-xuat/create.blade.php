@extends('layouts.app')

@section('title', 'Tạo đề xuất nhập kho – Soul Wine')

@section('content')
<form method="POST" action="{{ route('kho.de-xuat.store') }}" id="proposalForm" style="display: flex; flex-direction: column; gap: 20px;">
  @csrf

  {{-- Header: Back link, Title, Badge & Nút Hành động --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <a href="{{ route('kho.de-xuat.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500;">
        ← Đề xuất nhập kho
      </a>
      <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
        <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Đề xuất nhập kho</h1>
        <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; background: #EEEAE9; color: #4A3F41;">
          Đang cập nhật
        </span>
      </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
      <button type="submit" name="action" value="save" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#F6ECEA';" onmouseout="this.style.background='#FFFFFF';">
        Lưu
      </button>

      <button type="submit" name="action" value="send" onclick="return confirm('Gửi đề xuất cho Kế toán duyệt? Sau khi gửi sẽ không sửa được trừ khi thu hồi.');" style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
        Gửi duyệt
      </button>
    </div>
  </div>

  {{-- Thông báo Alerts --}}
  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px;">
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div style="padding: 14px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px;">
      <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Section 1: Thông tin chung --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Thông tin chung</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px;">
      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
        Mã đề xuất
        <input value="[Tự sinh khi lưu]" readonly style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #F6F1EF; color: #6B5559;">
      </label>

      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
        Nhà cung cấp <span style="color: #9B1C1C;">*</span>
        <select name="Ma_ncc" required style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF; color: #241417;">
          <option value="">-- Chọn nhà cung cấp --</option>
          @foreach($nhacungcaps as $ncc)
            <option value="{{ $ncc->Ma_ncc }}" {{ old('Ma_ncc') === $ncc->Ma_ncc ? 'selected' : '' }}>
              {{ $ncc->Ten_ncc }} ({{ $ncc->Quoc_gia }})
            </option>
          @endforeach
        </select>
      </label>

      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
        Kho nhận
        <input value="{{ $kho->Ten_kho ?? 'Kho Hà Nội' }}" readonly style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #F6F1EF; color: #6B5559;">
      </label>

      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
        Ngày dự kiến nhận đủ <span style="color: #9B1C1C;">*</span>
        <input type="date" name="Ngay_du_kien_nhan_du" value="{{ old('Ngay_du_kien_nhan_du', now()->addDays(14)->toDateString()) }}" required style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF; color: #241417;">
      </label>

      <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
        Người lập / Ngày lập
        <input value="{{ auth()->user()->Ho_ten }} · {{ date('d/m/Y') }}" readonly style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #F6F1EF; color: #6B5559;">
      </label>
    </div>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
      Ghi chú
      <textarea name="Ghi_chu_phe_duyet" rows="2" placeholder="Ghi chú cho Kế toán hoặc nhà cung cấp" style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical; color: #241417;">{{ old('Ghi_chu_phe_duyet') }}</textarea>
    </label>
  </section>

  {{-- Section 2: Sản phẩm đặt mua --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
      <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Sản phẩm đặt mua</h2>

      <button type="button" onclick="addProductRow()" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 8px; border: 1px dashed #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#F6ECEA';" onmouseout="this.style.background='#FFFFFF';">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Thêm sản phẩm</span>
      </button>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 860px; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="background: #F6ECEA; text-align: left;">
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 120px;">SL đặt</th>
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 160px;">Đơn giá mua (VNĐ)</th>
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 180px;">Thành tiền (VNĐ)</th>
            <th scope="col" style="padding: 10px 12px; width: 60px; text-align: center;">Xóa</th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          {{-- Hàng mẫu đầu tiên --}}
          <tr class="product-row" style="border-top: 1px solid #F0E6E3;">
            <td style="padding: 8px 12px;">
              <select name="items[0][Ma_san_pham]" required onchange="onProductSelect(this)" style="height: 40px; width: 100%; min-width: 240px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF; color: #241417;">
                <option value="">-- Chọn sản phẩm --</option>
                @foreach($sanphams as $sp)
                  <option value="{{ $sp->Ma_san_pham }}" data-cost="{{ (int)$sp->Gia_von_binh_quan }}">
                    {{ $sp->Ten_san_pham }} {{ $sp->Nien_vu ? '– ' . $sp->Nien_vu : '' }} ({{ $sp->Dung_tich }}ml)
                  </option>
                @endforeach
              </select>
            </td>
            <td style="padding: 8px 12px; text-align: right;">
              <input type="number" name="items[0][So_luong_dat]" value="10" min="1" required oninput="calcRow(this)" style="height: 40px; width: 100px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; text-align: right;">
            </td>
            <td style="padding: 8px 12px; text-align: right;">
              <input type="number" name="items[0][Don_gia_mua]" value="0" min="0" required oninput="calcRow(this)" style="height: 40px; width: 140px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; text-align: right;">
            </td>
            <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: #241417;" class="row-total">
              0
            </td>
            <td style="padding: 8px 12px; text-align: center;">
              <button type="button" onclick="removeProductRow(this)" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #EADFDC; background: #FFFFFF; color: #8B1E2D; font-size: 18px; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#F6ECEA';" onmouseout="this.style.background='#FFFFFF';">
                ×
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; font-size: 15px; padding-top: 10px; border-top: 1px solid #F0E6E3;">
      <span style="color: #6B5559;">Tổng giá trị (chưa thuế):</span>
      <span style="font-size: 18px; font-weight: 700; color: #6E1423;" id="grandTotalText">0 VNĐ</span>
    </div>
  </section>
</form>

<script>
  let rowCount = 1;

  const productOptionsHtml = `
    <option value="">-- Chọn sản phẩm --</option>
    @foreach($sanphams as $sp)
      <option value="{{ $sp->Ma_san_pham }}" data-cost="{{ (int)$sp->Gia_von_binh_quan }}">
        {{ $sp->Ten_san_pham }} {{ $sp->Nien_vu ? '– ' . $sp->Nien_vu : '' }} ({{ $sp->Dung_tich }}ml)
      </option>
    @endforeach
  `;

  function addProductRow() {
    const tbody = document.getElementById('productTableBody');
    const tr = document.createElement('tr');
    tr.className = 'product-row';
    tr.style.borderTop = '1px solid #F0E6E3';

    tr.innerHTML = `
      <td style="padding: 8px 12px;">
        <select name="items[${rowCount}][Ma_san_pham]" required onchange="onProductSelect(this)" style="height: 40px; width: 100%; min-width: 240px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF; color: #241417;">
          ${productOptionsHtml}
        </select>
      </td>
      <td style="padding: 8px 12px; text-align: right;">
        <input type="number" name="items[${rowCount}][So_luong_dat]" value="10" min="1" required oninput="calcRow(this)" style="height: 40px; width: 100px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; text-align: right;">
      </td>
      <td style="padding: 8px 12px; text-align: right;">
        <input type="number" name="items[${rowCount}][Don_gia_mua]" value="0" min="0" required oninput="calcRow(this)" style="height: 40px; width: 140px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; text-align: right;">
      </td>
      <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: #241417;" class="row-total">
        0
      </td>
      <td style="padding: 8px 12px; text-align: center;">
        <button type="button" onclick="removeProductRow(this)" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #EADFDC; background: #FFFFFF; color: #8B1E2D; font-size: 18px; cursor: pointer;">
          ×
        </button>
      </td>
    `;
    tbody.appendChild(tr);
    rowCount++;
  }

  function removeProductRow(btn) {
    const rows = document.querySelectorAll('.product-row');
    if (rows.length <= 1) {
      alert('Đề xuất phải có ít nhất một dòng sản phẩm.');
      return;
    }
    const tr = btn.closest('tr');
    tr.remove();
    recalcGrandTotal();
  }

  function onProductSelect(select) {
    const opt = select.selectedOptions[0];
    const tr = select.closest('tr');
    const priceInput = tr.querySelector('input[name*="[Don_gia_mua]"]');
    if (opt && opt.dataset.cost && Number(opt.dataset.cost) > 0) {
      if (priceInput.value == 0) {
        priceInput.value = opt.dataset.cost;
      }
    }
    calcRow(select);
  }

  function calcRow(el) {
    const tr = el.closest('tr');
    const qtyInput = tr.querySelector('input[name*="[So_luong_dat]"]');
    const priceInput = tr.querySelector('input[name*="[Don_gia_mua]"]');
    const totalTd = tr.querySelector('.row-total');

    const qty = Number(qtyInput.value) || 0;
    const price = Number(priceInput.value) || 0;
    const total = qty * price;

    totalTd.textContent = new Intl.NumberFormat('vi-VN').format(total);
    recalcGrandTotal();
  }

  function recalcGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.product-row').forEach(tr => {
      const qtyInput = tr.querySelector('input[name*="[So_luong_dat]"]');
      const priceInput = tr.querySelector('input[name*="[Don_gia_mua]"]');
      const qty = Number(qtyInput.value) || 0;
      const price = Number(priceInput.value) || 0;
      grandTotal += (qty * price);
    });

    document.getElementById('grandTotalText').textContent = new Intl.NumberFormat('vi-VN').format(grandTotal) + ' VNĐ';
  }

  // Khởi tạo tính ban đầu
  document.addEventListener('DOMContentLoaded', () => {
    recalcGrandTotal();
  });
</script>
@endsection


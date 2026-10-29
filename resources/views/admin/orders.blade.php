@extends('admin.layout')

@section('title', 'إدارة الطلبات | تسويق')
@section('page_title', 'قائمة الطلبات والمبيعات')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <div style="position: relative; flex: 1; min-width: 260px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <input type="text" id="orderSearchInput" onkeyup="filterOrdersTable()" placeholder="ابحث برقم الطلب أو اسم العميل أو الهاتف..." style="width: 100%; padding: 12px 42px 12px 16px; border-radius: 14px; border: 1px solid var(--border); outline: none;">
        </div>
        <div style="background: white; padding: 10px 22px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: var(--primary); font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>إجمالي الطلبات: {{ count($orders) }}</span>
        </div>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>إسناد لكابتن</th>
                        <th>حالة الطلب</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td style="font-weight: 800; color: var(--primary);">#{{ $order->id }}</td>
                            <td>
                                <div style="font-weight: 700;">{{ $order->customer_name }}</div>
                                <small style="color: var(--text-muted); font-size: 11px;">
                                    <i class="fa-solid fa-phone" style="font-size: 10px; margin-left: 4px;"></i>
                                    {{ $order->customer_phone }}
                                </small>
                            </td>
                            <td style="font-weight: 800; white-space: nowrap;">
                                {{ number_format($order->total_amount, 2) }} <small style="font-size: 11px; font-weight: normal; color: var(--text-muted);">ريال</small>
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.assignCaptain', $order) }}" method="POST">
                                    @csrf
                                    <select name="captain_id" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 12px; outline: none; background: white; color: var(--text-main); max-width: 170px;">
                                        <option value="">-- اختر كابتن --</option>
                                        @foreach($captains as $captain)
                                            <option value="{{ $captain->id }}" {{ $order->assigned_captain_id == $captain->id ? 'selected' : '' }}>
                                                {{ $captain->name }} ({{ $captain->is_available ? 'متصل' : 'غير متصل' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 12px; outline: none; background: white; font-weight: 600; max-width: 140px;">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>معلق</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>قيد التجهيز</option>
                                        <option value="on_the_way" {{ $order->status == 'on_the_way' ? 'selected' : '' }}>في الطريق</option>
                                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                    </select>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline" style="padding: 6px 14px; font-size: 12px;" title="عرض التفاصيل">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>التفاصيل</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-inbox" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد طلبات مسجلة حتى الآن
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterOrdersTable() {
            const input = document.getElementById('orderSearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('ordersTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const text = tr[i].textContent || tr[i].innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
@endsection

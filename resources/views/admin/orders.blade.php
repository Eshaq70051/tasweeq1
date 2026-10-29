@extends('admin.layout')

@section('title', 'إدارة الطلبات')
@section('page_title', 'قائمة الطلبات والمبيعات')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" placeholder="ابحث برقم الطلب أو اسم العميل..." style="width: 100%; padding: 12px 20px; border-radius: 15px; border: 1px solid var(--border); outline: none;">
        </div>
        <div style="background: white; padding: 10px 20px; border-radius: 99px; border: 1px solid var(--border); font-weight: bold; color: var(--primary); font-size: 14px;">
            إجمالي الطلبات: {{ count($orders) }}
        </div>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>إسناد لكابتن</th>
                        <th>حالة الطلب</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td style="font-weight: 800; color: var(--primary);">#{{ $order->id }}</td>
                            <td>
                                <div style="font-weight: 700;">{{ $order->customer_name }}</div>
                                <small style="color: var(--text-muted);">{{ $order->customer_phone }}</small>
                            </td>
                            <td style="font-weight: 800;">{{ number_format($order->total_amount, 2) }} ريال</td>
                            <td>
                                <form action="{{ route('admin.orders.assignCaptain', $order) }}" method="POST">
                                    @csrf
                                    <select name="captain_id" onchange="this.form.submit()" style="padding: 8px; border-radius: 10px; border: 1px solid var(--border); font-size: 11px; outline: none; background: white; color: var(--secondary); max-width: 150px;">
                                        <option value="">إسناد لكابتن</option>
                                        @foreach($captains as $captain)
                                            <option value="{{ $captain->id }}" {{ $order->assigned_captain_id == $captain->id ? 'selected' : '' }}>
                                                {{ $captain->name }} ({{ $captain->is_available ? 'متصل 🟢' : 'مغلق ⚪' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" style="padding: 8px; border-radius: 10px; border: 1px solid var(--border); font-size: 11px; outline: none; background: white; max-width: 120px;">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>⌛ معلق</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>⚙️ تجهيز</option>
                                        <option value="on_the_way" {{ $order->status == 'on_the_way' ? 'selected' : '' }}>🚚 طريق</option>
                                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>✅ تم</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>❌ ملغي</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn" style="padding: 8px 12px; font-size: 11px; background: #eee;">عرض</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; padding: 40px;">لا توجد طلبات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

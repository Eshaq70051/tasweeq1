<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'إدارة تسويق')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #068494;
            --primary-dark: #056d7a;
            --primary-soft: rgba(6, 132, 148, 0.08);
            --secondary: #0B7A75;
            --bg: #F4F7F8;
            --sidebar-bg: #ffffff;
            --text-main: #1A1C1E;
            --text-muted: #6C7278;
            --danger: #FF4D4D;
            --success: #27AE60;
            --warning: #F2994A;
            --info: #2D9CDB;
            --border: #E5E9EB;
            --sidebar-width: 260px;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); font-family: 'Cairo', sans-serif; color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* Sidebar Responsive Logic */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-left: 1px solid var(--border);
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            right: 0; top: 0;
            z-index: 1001;
            transition: transform 0.3s ease;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            padding: 24px;
            transition: margin 0.3s ease;
            width: 100%;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 16px 24px;
            border-radius: 18px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
            border: 1px solid var(--border);
        }

        .menu-btn { display: none; background: var(--primary-soft); border: none; padding: 10px 14px; border-radius: 10px; cursor: pointer; color: var(--primary); font-size: 18px; }

        /* Nav Links */
        .nav { display: flex; flex-direction: column; gap: 6px; padding-bottom: 28px; }
        .nav a { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding: 12px 16px; 
            border-radius: 12px; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 14px;
            transition: all 0.25s ease; 
        }
        .nav a i {
            width: 22px;
            text-align: center;
            font-size: 16px;
            color: #8C949A;
            transition: color 0.25s ease;
        }
        .nav a:hover { 
            background: var(--primary-soft); 
            color: var(--primary); 
        }
        .nav a:hover i {
            color: var(--primary);
        }
        .nav a.active { 
            background: var(--primary-soft); 
            color: var(--primary); 
            font-weight: 700;
            box-shadow: inset 4px 0 0 var(--primary); 
        }
        .nav a.active i {
            color: var(--primary);
        }

        /* Helpers */
        .panel { 
            background: white; 
            border-radius: 20px; 
            padding: 24px; 
            border: 1px solid var(--border); 
            box-shadow: 0 4px 18px rgba(0,0,0,0.02); 
            margin-bottom: 24px; 
        }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table { width: 100%; border-collapse: collapse; min-width: 680px; }
        .table th { padding: 14px 14px; background: #f8fbfb; color: var(--text-muted); font-size: 13px; font-weight: 700; text-align: right; white-space: nowrap; border-bottom: 1px solid var(--border); }
        .table td { padding: 14px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 13px; }
        .table tbody tr:last-child td { border-bottom: 0; }
        .table tbody tr:hover { background: #fafdfd; }

        .status-badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px;
            justify-content: center; 
            padding: 5px 12px; 
            border-radius: 999px; 
            font-size: 12px; 
            font-weight: 700; 
            white-space: nowrap; 
        }
        .status-success { background: rgba(39, 174, 96, 0.12); color: #168447; }
        .status-danger { background: rgba(255, 77, 77, 0.12); color: #c93636; }
        .status-pending { background: rgba(242, 153, 74, 0.14); color: #b96818; }
        .status-info { background: rgba(45, 156, 219, 0.14); color: #1f73a5; }

        .online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .online-dot.active {
            background-color: var(--success);
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.25);
        }
        .online-dot.offline {
            background-color: #A0AEC0;
        }

        input, select, textarea { font-family: inherit; font-size: 13px; }
        input:focus, select:focus, textarea:focus { outline: 2px solid rgba(6, 132, 148, 0.18); border-color: var(--primary) !important; }
        
        .btn { 
            padding: 9px 18px; 
            border-radius: 12px; 
            font-weight: 700; 
            font-size: 13px;
            cursor: pointer; 
            border: none; 
            transition: all 0.25s ease; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            gap: 8px; 
            font-family: inherit; 
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { background: #f8fbfb; border-color: var(--primary); color: var(--primary); }
        .btn-danger { background: rgba(255, 77, 77, 0.12); color: var(--danger); }
        .btn-danger:hover { background: var(--danger); color: white; }

        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { 
            background: white; 
            padding: 22px; 
            border-radius: 18px; 
            border: 1px solid var(--border); 
            box-shadow: 0 4px 14px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card .info { display: flex; flex-direction: column; }
        .stat-card .title { font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 6px; }
        .stat-card .value { font-size: 26px; font-weight: 800; color: var(--primary); line-height: 1.2; }
        .stat-card .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--primary-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        /* Overlay */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; }

        @media (max-width: 992px) {
            :root { --sidebar-width: 0px; }
            .sidebar { transform: translateX(100%); }
            .sidebar.show { transform: translateX(0); width: 260px; }
            .main-content { margin-right: 0; }
            .menu-btn { display: inline-flex; align-items: center; justify-content: center; }
            .sidebar-overlay.show { display: block; }
        }

        @media (max-width: 576px) {
            .stat-grid { grid-template-columns: 1fr; }
            .top-bar { flex-direction: row; font-size: 14px; }
            .main-content { padding: 14px; }
            .panel { padding: 18px; border-radius: 16px; }
            .top-bar { padding: 12px 16px; margin-bottom: 18px; }
        }

        /* Pagination & Filter Styling */
        .filter-card {
            background: white;
            border-radius: 18px;
            padding: 18px 22px;
            border: 1px solid var(--border);
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.02);
        }
        .filter-tabs {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            margin-bottom: 16px;
            scrollbar-width: thin;
        }
        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            background: #F4F7F8;
            color: var(--text-muted);
            border: 1px solid var(--border);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .filter-pill:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .filter-pill.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .filter-pill .badge-count {
            background: rgba(0,0,0,0.08);
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
        }
        .filter-pill.active .badge-count {
            background: rgba(255,255,255,0.25);
            color: white;
        }
        
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 24px;
            padding: 16px 20px;
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border);
        }
        .pagination-container .info-text {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
        }
        .pagination-container nav svg {
            width: 16px;
            height: 16px;
            display: inline-block;
            vertical-align: middle;
        }
        .pagination-container nav div:first-child {
            display: none;
        }
        .pagination-container nav span, 
        .pagination-container nav a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            margin: 0 2px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            color: var(--text-main);
            border: 1px solid var(--border);
            background: white;
            transition: all 0.2s;
        }
        .pagination-container nav span[aria-current="page"] span,
        .pagination-container nav a.active {
            background: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
        .pagination-container nav a:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="overlay" onclick="toggleMenu()"></div>
    
    <aside class="sidebar" id="sidebar">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding-bottom: 18px; border-bottom: 1px solid var(--border);">
            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 12px; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; box-shadow: 0 4px 10px rgba(6, 132, 148, 0.25);">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <span style="font-size: 18px; font-weight: 800; color: var(--primary); display: block; line-height: 1.2;">تسويق</span>
                <small style="color: var(--text-muted); font-size: 11px;">لوحة تحكم الإدارة</small>
            </div>
        </div>

        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>الرئيسية</span>
            </a>
            <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <i class="fa-solid fa-shapes"></i>
                <span>التصنيفات</span>
            </a>
            <a href="{{ route('admin.products') }}" class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>المنتجات</span>
            </a>
            <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>الطلبات</span>
            </a>
            <a href="{{ route('admin.coupons') }}" class="{{ request()->routeIs('admin.coupons*') ? 'active' : '' }}">
                <i class="fa-solid fa-ticket"></i>
                <span>كوبونات الخصم</span>
            </a>
            <a href="{{ route('admin.carts') }}" class="{{ request()->routeIs('admin.carts*') ? 'active' : '' }}">
                <i class="fa-solid fa-basket-shopping"></i>
                <span>السلات الحالية</span>
            </a>
            <a href="{{ route('admin.favorites') }}" class="{{ request()->routeIs('admin.favorites*') ? 'active' : '' }}">
                <i class="fa-solid fa-heart"></i>
                <span>المفضلة</span>
            </a>
            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>المستخدمين</span>
            </a>
            <a href="{{ route('admin.captains') }}" class="{{ request()->routeIs('admin.captains*') ? 'active' : '' }}">
                <i class="fa-solid fa-motorcycle"></i>
                <span>الكباتن</span>
            </a>
            <a href="{{ route('admin.sliders') }}" class="{{ request()->routeIs('admin.sliders*') ? 'active' : '' }}">
                <i class="fa-solid fa-images"></i>
                <span>العروض الترويجية</span>
            </a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i>
                <span>إعدادات النظام</span>
            </a>
            <a href="{{ route('admin.payment-methods') }}" class="{{ request()->routeIs('admin.payment-methods*') ? 'active' : '' }}">
                <i class="fa-solid fa-credit-card"></i>
                <span>وسائل الدفع</span>
            </a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--danger); margin-top: 15px;">
                <i class="fa-solid fa-arrow-right-from-bracket" style="color: var(--danger);"></i>
                <span>تسجيل الخروج</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="menu-btn" onclick="toggleMenu()" title="القائمة">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 style="margin:0; font-size: 20px; font-weight: 800;">@yield('page_title', 'الرئيسية')</h1>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: var(--primary-soft); padding: 7px 16px; border-radius: 99px; font-weight: 700; font-size: 13px; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>{{ auth()->user()->name }} (مدير)</span>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div style="background: rgba(39, 174, 96, 0.12); color: #168447; border: 1px solid rgba(39, 174, 96, 0.25); padding: 14px 18px; border-radius: 14px; margin-bottom: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fff1f1; color: #b42318; padding: 14px 18px; border: 1px solid #f5c2c0; border-radius: 14px; margin-bottom: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fff1f1; color: #b42318; padding: 14px 18px; border: 1px solid #f5c2c0; border-radius: 14px; margin-bottom: 22px;">
                <div style="font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>تعذر حفظ البيانات:</span>
                </div>
                <ul style="margin: 8px 0 0; padding-right: 24px; font-size: 13px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

    <script>
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('overlay').classList.toggle('show');
        }
    </script>
</body>
</html>

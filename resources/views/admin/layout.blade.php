<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'إدارة تسويق')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #068494;
            --primary-dark: #056d7a;
            --primary-soft: rgba(6, 132, 148, 0.08);
            --secondary: #0B7A75;
            --bg: #F2F9F9;
            --sidebar-bg: #ffffff;
            --text-main: #1A1C1E;
            --text-muted: #6C7278;
            --danger: #FF4D4D;
            --success: #27AE60;
            --warning: #F2994A;
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
            padding: 20px;
            transition: margin 0.3s ease;
            width: 100%;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 20px;
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        .menu-btn { display: none; background: var(--primary-soft); border: none; padding: 10px; border-radius: 10px; cursor: pointer; color: var(--primary); font-size: 20px; }

        /* Nav Links */
        .nav { display: flex; flex-direction: column; gap: 8px; padding-bottom: 28px; }
        .nav a { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-radius: 14px; color: var(--text-muted); text-decoration: none; font-weight: 600; transition: 0.3s; }
        .nav a:hover, .nav a.active { background: var(--primary-soft); color: var(--primary); }
        .nav a.active { box-shadow: inset 4px 0 0 var(--primary); }

        /* Helpers */
        .panel { background: white; border-radius: 24px; padding: 25px; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table { width: 100%; border-collapse: collapse; min-width: 680px; }
        .table th { padding: 14px 12px; background: #f8fbfb; color: var(--text-muted); font-size: 12px; text-align: right; white-space: nowrap; }
        .table td { padding: 15px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 13px; }
        .table tbody tr:last-child td { border-bottom: 0; }
        .table tbody tr:hover { background: #fbfefe; }
        .status-badge { display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .status-success { background: rgba(39, 174, 96, 0.12); color: #168447; }
        .status-danger { background: rgba(255, 77, 77, 0.12); color: #c93636; }
        .status-pending { background: rgba(242, 153, 74, 0.14); color: #b96818; }
        input, select, textarea { font-family: inherit; }
        input:focus, select:focus, textarea:focus { outline: 2px solid rgba(6, 132, 148, 0.18); border-color: var(--primary) !important; }
        .btn { padding: 10px 20px; border-radius: 12px; font-weight: 700; cursor: pointer; border: none; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 20px; border: 1px solid var(--border); }
        .stat-card .value { font-size: 24px; font-weight: 800; color: var(--primary); }

        /* Overlay */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; }

        @media (max-width: 992px) {
            :root { --sidebar-width: 0px; }
            .sidebar { transform: translateX(100%); }
            .sidebar.show { transform: translateX(0); width: 260px; }
            .main-content { margin-right: 0; }
            .menu-btn { display: block; }
            .sidebar-overlay.show { display: block; }
        }

        @media (max-width: 576px) {
            .stat-grid { grid-template-columns: 1fr; }
            .top-bar { flex-direction: row; font-size: 14px; }
            .main-content { padding: 12px; }
            .panel { padding: 16px; border-radius: 18px; }
            .top-bar { padding: 12px 14px; margin-bottom: 18px; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="overlay" onclick="toggleMenu()"></div>
    
    <aside class="sidebar" id="sidebar">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 35px;">
            <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 10px; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800;">T</div>
            <span style="font-size: 20px; font-weight: 800; color: var(--primary);">تسويق الإدارة</span>
        </div>

        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span>🏠 الرئيسي</span></a>
            <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}"><span>📂 التصنيفات</span></a>
            <a href="{{ route('admin.products') }}" class="{{ request()->routeIs('admin.products') ? 'active' : '' }}"><span>📦 المنتجات</span></a>
            <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders') ? 'active' : '' }}"><span>🛒 الطلبات</span></a>
            <a href="{{ route('admin.carts') }}" class="{{ request()->routeIs('admin.carts') ? 'active' : '' }}"><span>🧺 السلات</span></a>
            <a href="{{ route('admin.favorites') }}" class="{{ request()->routeIs('admin.favorites') ? 'active' : '' }}"><span>❤️ المفضلة</span></a>
            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}"><span>👥 المستخدمين</span></a>
            <a href="{{ route('admin.captains') }}" class="{{ request()->routeIs('admin.captains') ? 'active' : '' }}"><span>🛵 الكباتن</span></a>
            <a href="{{ route('admin.sliders') }}" class="{{ request()->routeIs('admin.sliders') ? 'active' : '' }}"><span>🖼️ العروض</span></a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"><span>⚙️ الإعدادات</span></a>
            <a href="{{ route('admin.payment-methods') }}" class="{{ request()->routeIs('admin.payment-methods*') ? 'active' : '' }}"><span>💳 وسائل الدفع</span></a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--danger); margin-top: 20px;"><span>🚪 خروج</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="menu-btn" onclick="toggleMenu()">☰</button>
                <h1 style="margin:0; font-size: 22px;">@yield('page_title', 'الرئيسية')</h1>
            </div>
            <div style="background: var(--primary-soft); padding: 8px 15px; border-radius: 99px; font-weight: bold; font-size: 13px; color: var(--primary);">
                {{ auth()->user()->name }} (مدير)
            </div>
        </header>

        @if(session('success'))
            <div style="background: var(--success); color: white; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: bold;">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div style="background: #fff1f1; color: #b42318; padding: 15px; border: 1px solid #f5c2c0; border-radius: 12px; margin-bottom: 25px; font-weight: bold;">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div style="background: #fff1f1; color: #b42318; padding: 15px; border: 1px solid #f5c2c0; border-radius: 12px; margin-bottom: 25px;">
                <strong>تعذر حفظ البيانات:</strong>
                <ul style="margin: 8px 0 0; padding-right: 20px;">
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

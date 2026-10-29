<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | لوحة إدارة تسويق</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #068494;
            --primary-dark: #056d7a;
            --primary-soft: rgba(6, 132, 148, 0.08);
            --bg: #F4F7F8;
            --border: #E5E9EB;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #EAF7F8 0%, #F4F7F8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 24px;
            padding: 35px 30px;
            box-shadow: 0 12px 35px rgba(6, 132, 148, 0.08);
            border: 1px solid var(--border);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, var(--primary), #0B7A75);
            border-radius: 16px;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 14px;
            box-shadow: 0 6px 16px rgba(6, 132, 148, 0.25);
        }

        .brand-title {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 800;
            color: #1A1C1E;
        }

        .brand-subtitle {
            margin: 0;
            color: #6C7278;
            font-size: 13px;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 700;
            font-size: 13px;
            color: #334155;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 15px;
        }

        input {
            width: 100%;
            padding: 12px 42px 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.25s ease;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            outline: 2px solid rgba(6, 132, 148, 0.18);
        }

        button {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 13px;
            font-size: 15px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        button:hover {
            background: var(--primary-dark);
        }

        .error-alert {
            background: #FFF1F1;
            color: #B42318;
            border: 1px solid #F5C2C0;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <div class="brand-logo">
                <i class="fa-solid fa-store"></i>
            </div>
            <h2 class="brand-title">تسويق</h2>
            <p class="brand-subtitle">لوحة تحكم الإدارة والمبيعات</p>
        </div>

        @if ($errors->any())
            <div class="error-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="field">
                <label for="email">البريد الإلكتروني</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
                </div>
            </div>

            <div class="field">
                <label for="password">كلمة المرور</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                <span>تسجيل الدخول</span>
            </button>
        </form>
    </div>
</body>
</html>

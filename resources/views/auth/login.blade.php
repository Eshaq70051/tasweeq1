<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | لوحة الإدارة</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f6f7ff, #eef4ff);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .box {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        }
        h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #2d3748;
        }
        .field {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }
        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dfe3ea;
            border-radius: 10px;
            font-size: 15px;
        }
        button {
            width: 100%;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            color: #c53030;
            font-size: 13px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>لوحة الإدارة</h2>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="field">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">تسجيل الدخول</button>
        </form>
    </div>
</body>
</html>

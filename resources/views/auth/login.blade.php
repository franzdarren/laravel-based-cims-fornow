<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · CIMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="brand">
            <div class="brand-mark">✚</div>
            <div><b>Clinic Inventory</b><span>Management System</span></div>
        </div>

        @if ($errors->any())
            <div class="notice danger" style="margin-bottom:14px">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="stack">
            @csrf
            <div class="field">
                <label class="req">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" autofocus required>
            </div>
            <div class="field">
                <label class="req">Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn primary" style="justify-content:center">Sign in</button>
        </form>

        <div class="login-hint">
            <b>Demo accounts</b> (seeded sample data):<br>
            Administrator — <span class="mono">avillanueva@clinic.local</span> / <span class="mono">password</span><br>
            Nurse — <span class="mono">ncruz@clinic.local</span> / <span class="mono">password</span><br>
            Supervisor — <span class="mono">mlim@clinic.local</span> / <span class="mono">password</span>
        </div>
    </div>
</div>
</body>
</html>

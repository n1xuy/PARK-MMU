<!DOCTYPE html>
<html>
<head>
    <title>Admin Password Change</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background-color: white;
            min-height: 100vh;
            display: block;
        }
        
        @media (max-width: 600px) {
            .card {
                margin: 24px 8px;
                max-width: 100vw;
                padding: 16px;
            }
        }
        
        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
        }
        
        input[type="password"] {
            width: 100%;
            border: none;
            border-bottom: 1px solid #cccccc;
            padding: 10px 0;
            font-size: 16px;
            outline: none;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            background-color: #d9d9d9;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 15px 0;
        }
        
        .btn:hover {
            background-color: #c4c4c4;
        }
        
        .or-divider {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
        }
        
        .or-divider span {
            padding: 0 10px;
            color: #666;
        }
        
        .cancel-btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: 1px solid #d9d9d9;
            border-radius: 50px;
            color: #333;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            background-color: white;
        }
        
        .cancel-btn:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
        <div class="admin-header">
        <div class="logo-section">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo" class="admin-logo">
            </a>
        </div>
        <div class="admin-title">
            ADMIN{{ Auth::guard('admin')->user()?->username ? ' - ' . Auth::guard('admin')->user()->username : '' }}
        </div>
    </div>

    <div class="card">
        <h1>Change Password</h1>
        <p class="subtitle">Update your account password</p>
        
        <form method="POST" action="{{ route('admin.pwupdate') }}">
            @csrf
            <div class="input-group">
                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password">
            </div>
            
            <div class="input-group">
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="new_password">
            </div>
            
            <div class="input-group">
                <label for="confirm-password">Again Password</label>
                <input type="password" id="confirm-password" name="new_password_confirmation">
            </div>

            @if(session('error'))
                <p style="color: red;">{{ session('error') }}</p>
            @endif
            @if(session('success'))
                <p style="color: green;">{{ session('success') }}</p>
            @endif

            <button type="submit" class="btn">Update Password</button>
            
            <div class="or-divider">
                <span>or</span>
            </div>
            
			<a href="{{ route('admin.menu') }}" class="cancel-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
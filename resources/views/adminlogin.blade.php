<!DOCTYPE html>
<html>
<head>
    
    <title>Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background-color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ccc;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 30px;
            margin-right: 10px;
        }
        
        .admin-title {
            font-size: 24px;
            font-weight: bold;
        }
        
        .card {
            width: 100%;
            max-width: 450px;
            margin: 40px auto;
            padding: 20px;
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
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            border: none;
            border-bottom: 1px solid #cccccc;
            padding: 10px 0;
            font-size: 16px;
            outline: none;
        }
        
        .btn {
            width: 50%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            background-color: #d9d9d9;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 15px auto;
            display: block; 
            text-align: center;    
        }
        
        .btn:hover {
            background-color: #c4c4c4;
        }
        
        .forgot-password {
            text-align: right;
        }
        
        .forgot-password a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }

        .return-btn {
            display: block;
            width: 50%;
            padding: 15px;
            border: 1px solid #d9d9d9;
            border-radius: 50px;
            color: #333;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            background-color:#d9d9d9;
            margin: 15px auto;
            display: block;
        }

        .return-btn:hover {
            background-color: #f5f5f5;
        }
        
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo">
        </div>
        <div class="admin-title">ADMIN</div>
    </header>

    <div class="card">
        <h1>Admin Login</h1>
        <p class="subtitle">Enter to continue</p>
        
    <form action="{{ route('adminlogin') }}" method="POST">
        @csrf

        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            @error('username')
            <span style="color: red" class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            @error('password')
            <span style="color: red" class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="forgot-password">
            <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="btn">Login</button>
        <a href="{{ route('home') }}" class="return-btn">Return</a>
    </form>
    </div>
</body>
</html>
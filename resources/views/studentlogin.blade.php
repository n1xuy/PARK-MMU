<!DOCTYPE html>
<html>
<head>
    <title>Student Login - MMU Parking Finder</title>
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
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ccc;
        }

        .logo {
            height: 30px;
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
            margin-bottom: 30px;
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
        
        .login-btn {
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
        
        .login-btn:hover {
            background-color: #c4c4c4;
        }
        
        .register-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .register-text {
            font-size: 14px;
            color: #666;
        }
        
        .register-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link:hover {
            text-decoration: underline;
        }
        
        .return-btn {
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
            margin-top: 10px;
        }

        .return-btn:hover {
            background-color: #f5f5f5;
        }

        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-section">
            <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo" class="logo">
        </div>
    </header>
    
    <div class="card">
        <h1>Login</h1>
        
        <form action="{{ route('userlogin') }}" method="POST">
            @if(request()->has('redirect'))
                <input type="hidden" name="redirect" value="{{ request()->input('redirect') }}">
            @endif
            @csrf

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required>
                @error('username')
                    <span class="error-message">{{ $message }}</span> 
                @enderror
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span class="error-message">{{ $message }}</span>    
                @enderror
            </div>
            
            <button type="submit" class="login-btn">LOGIN</button>
            
            <div class="register-section">
                <span class="register-text">Haven't registered yet? </span>
                <a href="{{ route('student.register') }}" class="register-link">Register now</a>
            </div>
            
            <a href="{{ route('home') }}" class="return-btn">RETURN</a>
        </form>
    </div>
</body>
</html>
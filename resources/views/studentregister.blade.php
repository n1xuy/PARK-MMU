<!DOCTYPE html>
<html>
<head>
    <title>Student Registration - MMU Parking Finder</title>
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
            justify-content: flex-start;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ccc;
        }

        .logo {
            height: 30px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: none;
            border-bottom: 1px solid #cccccc;
            padding: 10px 0;
            font-size: 16px;
            outline: none;
        }
        
        .register-btn {
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
        
        .register-btn:hover {
            background-color: #c4c4c4;
        }
        
        .login-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .login-text {
            font-size: 14px;
            color: #666;
        }
        
        .login-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link:hover {
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
    </style>
</head>
<body>
    <header>
        <div class="logo-section">
            <img src="{{ asset('images/(1)LOGO.png') }}" alt="Logo" class="logo">
        </div>
    </header>
    

    <div class="card">
        <h1>Register</h1>
        
        <form action="/student-register" method="POST">
            @csrf

            <div class="input-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" class="form-control" name="fullname" required>
               @error('fullname')
                <span style="color: red" class="error-message">{{ $message }}</span>
               @enderror
            </div>
            
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                @error('username')
                 <span style="color: red" class="error-message">{{ $message }}</span>   
                @enderror
            </div>
            
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                @error('email')
                <span style="color: red" class="error-message">{{ $message }}</span>   
                @enderror
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
                @error('confirm-password')
                <span style="color: red" class="error-message">{{ $message }}</span>   
                @enderror
            </div>
            
            <button type="submit" class="register-btn">REGISTER</button>
            
            <div class="login-section">
                <span class="login-text">Already have an account? </span>
                <a href="{{ route('student.login') }}" class="login-link">Login</a>
            </div>
            
            <a href="{{ route('home') }}" class="return-btn">RETURN</a>
        </form>
    </div>
</body>
</html> 
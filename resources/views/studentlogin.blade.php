<!DOCTYPE html>
<html>
<head>
    <title>Student Login - MMU Parking Finder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
    --primary-color: #0066cc;
    --secondary-color: #f5f5f5;
    --text-color: #333;
    --light-gray: #d9d9d9;
    --border-color: #cccccc;
    --error-color: #e74c3c;
    }

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

body {
  background-color: white;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  color: var(--text-color);
}

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-color);
  flex-shrink: 0;
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.logo {
  height: 2rem;
  object-fit: contain;
}

.card {
  flex: 1;
  width: 100%;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--secondary-color);
}

.form-container {
  width: 100%;
  max-width: 28rem;
  background-color: white;
  padding: 2rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

h1 {
  margin: 0 0 1.5rem 0;
  text-align: center;
  font-size: 1.75rem;
}

.input-group {
  margin-bottom: 1.5rem;
  position: relative;
}

label {
  display: block;
  font-size: 1rem;
  color: var(--text-color);
  margin-bottom: 0.75rem;
  font-weight: 500;
}

input[type="text"],
input[type="password"] {
  width: 100%;
  border: none;
  border-bottom: 2px solid var(--border-color);
  padding: 0.75rem 0;
  font-size: 1rem;
  outline: none;
  -webkit-appearance: none;
  transition: all 0.3s ease;
  background-color: transparent;
}

input[type="text"]:focus,
input[type="password"]:focus {
  border-bottom-color: var(--primary-color);
}

.input-highlight {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 2px;
  width: 0;
  background-color: var(--primary-color);
  transition: width 0.3s ease;
}

input[type="text"]:focus ~ .input-highlight,
input[type="password"]:focus ~ .input-highlight {
  width: 100%;
}

.login-btn {
  width: 100%;
  padding: 1rem;
  border: none;
  border-radius: 2rem;
  background-color: var(--primary-color);
  color: white;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin: 2rem 0 1rem 0;
  transition: all 0.3s ease;
  -webkit-tap-highlight-color: transparent;
}

.login-btn:hover,
.login-btn:active {
  background-color: #0052a3;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 102, 204, 0.3);
}

.register-section {
  text-align: center;
  margin: 1.5rem 0;
}

.register-text {
  font-size: 1rem;
  color: #666;
}

.register-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  -webkit-tap-highlight-color: transparent;
  transition: all 0.2s ease;
}

.register-link:hover,
.register-link:active {
  text-decoration: underline;
  color: #0052a3;
}

.return-btn {
  display: block;
  width: 100%;
  padding: 1rem;
  border: 2px solid var(--light-gray);
  border-radius: 2rem;
  color: var(--text-color);
  text-align: center;
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  background-color: white;
  margin-top: 1rem;
  transition: all 0.3s ease;
  -webkit-tap-highlight-color: transparent;
}

.return-btn:hover,
.return-btn:active {
  background-color: var(--secondary-color);
  border-color: #c4c4c4;
}

.error-message {
  color: var(--error-color);
  font-size: 0.875rem;
  margin-top: 0.5rem;
  display: block;
  font-weight: 500;
}

/* Mobile screens */
@media (max-width: 768px) {
  header {
    padding: 0.75rem 1rem;
  }
  
  .logo {
    height: 1.75rem;
  }
  
  .card {
    padding: 1rem;
    background-color: white;
  }
  
  .form-container {
    padding: 1.5rem;
    box-shadow: none;
  }
  
  h1 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .input-group {
    margin-bottom: 1.25rem;
  }
  
  label {
    font-size: 0.9375rem;
    margin-bottom: 0.5rem;
  }
  
  input[type="text"],
  input[type="password"] {
    padding: 0.5rem 0;
    font-size: 1rem;
  }
  
  .login-btn,
  .return-btn {
    padding: 0.875rem;
    font-size: 1rem;
  }
  
  .login-btn {
    margin: 1.5rem 0 1rem 0;
  }
  
  .return-btn {
    margin-top: 1rem;
  }
  
  .register-section {
    margin: 1.25rem 0;
  }
  
  .register-text,
  .register-link {
    font-size: 0.9375rem;
  }
  
  .error-message {
    font-size: 0.8125rem;
  }
}

/* Small mobile screens */
@media (max-width: 480px) {
  .card {
    padding: 0;
  }
  
  .form-container {
    padding: 1.5rem 1.25rem;
    border-radius: 0;
  }
  
  .login-btn {
    margin: 1.75rem 0 1rem 0;
  }
}
    </style>
</head>
<body>
    <header>
        <div class="logo-section">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="MMU Parking Logo" class="logo">
            </a>
        </div>
    </header>
    
    <div class="card">
        <div class="form-container">
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
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>

    <title>Admin Dashboard - MMU Parking Finder</title>
    <link href="{{ asset('css/admin-styles.css') }}" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <a href="{{ route('home') }}">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
                </a>
            </div>
             <div class="admin-title">
                ADMIN{{ Auth::guard('admin')->user()?->username ? ' - ' . Auth::guard('admin')->user()->username : '' }}
            </div>
        </div>
        
        <div class="button-container">
            <a href="{{ route('admin.announce') }}" class="admin-button">ANNOUNCEMENT EDIT</a>
            <a href="{{ route('admin.report') }}" class="admin-button">REPORT DATA</a>
            <a href="{{ route('admin.parkmanage') }}" class="admin-button">PARKING MANAGEMENT</a>
            <a href="{{ route('admin.syslogs')}}" class="admin-button">SYSTEM LOGS</a>
            <a href="{{ route('admin.changepw') }}" class="admin-button">CHANGE PASSWORD</a>
        </div>
        
        <a href="{{ route('home') }}" class="back-button">
            <img src="{{ asset('images/return page.png') }}" alt="Back">
        </a>
    </div>
</body>
</html> 
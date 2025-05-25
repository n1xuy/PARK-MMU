<!DOCTYPE html>
<html>
<head>
    <title>Report Data - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/report-styles.css') }}">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
            </div>
            <h1 class="admin-title">ADMIN</h1>
        </div>
        
        <div class="report-content">
            <h2 class="page-title">REPORT DATA</h2>
            
            <div class="table-container">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Report Status</th>
                            <th>User Type</th>
                            <th>User ID</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>No Parking</td>
                            <td>Student</td>
                            <td>1211101234</td>
                            <td>2024-02-20</td>
                            <td>14:30</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Full Parking</td>
                            <td>Staff</td>
                            <td>ST123456</td>
                            <td>2024-02-20</td>
                            <td>15:45</td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="{{ route('admin.menu') }}" class="back-button">
            <img src="{{ asset('images/return page.png') }}" alt="Back">
        </a>
    </div>
</body>
</html> 
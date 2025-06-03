<!DOCTYPE html>
<html>
<head>
    <title>System Logs - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/logs-styles.css')}}" >
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <a href="{{ route('home') }}">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
                </a>
            </div>
            <h1 class="admin-title">ADMIN</h1>
        </div>

        <div class="logs-content">
            <h2 class="page-title">SYSTEM LOGS</h2>

            <div class="table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Admin Id</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->admin->id ?? 'Unknown' }}</td>
                        <td>{{ $log->action ?? 'Unknown' }}</td>
                        <td>{{ $log->description ?? 'Unknown' }}</td>
                        <td>{{ $log->created_at->format('Y-m-d') }}</td>
                        <td>{{ $log->created_at->format('H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No logs available</td>
                    </tr>
                     @endforelse
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
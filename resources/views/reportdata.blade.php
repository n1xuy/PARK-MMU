<!DOCTYPE html>
<html>
<head>
    <title>Report Data - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/report-styles.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <a href="{{ route('home') }}" class="admin-logo">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
                </a>
            </div>
            <h1 class="admin-title">
                ADMIN{{ Auth::guard('admin')->user()?->username ? ' - ' . Auth::guard('admin')->user()->username : '' }}
            </h1>
        </div>
        
        <div class="report-content">
            <h2 class="page-title">REPORT DATA</h2>
            
            <div class="table-container">
                <table class="report-table">
                    <thead>
                    <tr>
                    <th>No</th>
                    <th>Parking Zone</th>
                    <th>Parking Status</th>
                    <th>User Name</th>
                    <th>USER ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    </tr>
                </thead>
                </tbody>
    
                     @foreach ($reports as $index => $report)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                           <td>{{ $report->parkingZone->location ?? 'Unknown Location' }}</td>
                            <td>
                                @switch($report->status)
                                    @case(1)
                                        Empty
                                        @break
                                    @case(2)
                                        Half Full
                                        @break
                                    @case(3)
                                        Full Parking
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $report->user->fullname ?? 'Unknown' }}</td> 
                            <td>{{ $report->user->id ?? 'N/A' }}</td>
                            <td>{{ $report->created_at->format('Y-m-d') }}</td>
                            <td>{{ $report->created_at->format('H:i') }}</td>
                        </tr>
                        @endforeach
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
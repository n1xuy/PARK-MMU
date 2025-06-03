<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    @if (session('cleared'))
        <div class="clear-notice">{{ session('cleared') }}</div>
    @endif
    <header>
        <div class="logo">
            <a href="{{ route ('admin.login')}}">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="MMU Parking Logo" class="logo-img">
            </a>
        </div>
        @auth
        <div class="user-dropdown">
            <button class="login-btn" id="userDropdownBtn">
                {{ Auth::user()->fullname }} ▼
            </button>
            <div class="dropdown-content" id="dropdownContent">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
        @else
        <button onclick="window.location='{{ route('student.login') }}'" class="login-btn">Login</button>
        @endauth
    </header>

    <div class="announcement-bar">
        <div class="announcement-content">
            @if ($announcement)
                <div class="announcement-display">
                    <h3>{{ $announcement->title }}</h3><br>
                    {{ $announcement->date }} at {{ \Carbon\Carbon::parse($announcement->time)->format('h:i A') }}<br>
                    {{ $announcement->details }}
                </div>
            @else
                <div class="announcement-display">🚫 No current announcements.</div>
            @endif
        </div>
    </div>

    <div class="main-content">
        <div class="map-container">
            <img src="{{ asset('images/MMU PARKING ZONE.png') }}" alt="MMU Campus Map" class="campus-map-bg">
            <div class="parking-zones">
    <!-- Zone 1 -->
    <div class="parking-zone" data-zone="1" style="top: 15%; left: 20%;">
        <div class="zone-marker">P1</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 2 -->
    <div class="parking-zone" data-zone="2" style="top: 25%; left: 30%;">
        <div class="zone-marker">P2</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 3 -->
    <div class="parking-zone" data-zone="3" style="top: 35%; left: 40%;">
        <div class="zone-marker">P3</div>
        <div class="zone-status staff">STAFF</div>
    </div>
    <!-- Zone 4 -->
    <div class="parking-zone" data-zone="4" style="top: 45%; left: 50%;">
        <div class="zone-marker">P4</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 5 -->
    <div class="parking-zone" data-zone="5" style="top: 55%; left: 60%;">
        <div class="zone-marker">P5</div>
        <div class="zone-status h-full">H-FULL</div>
    </div>
    <!-- Zone 6 -->
    <div class="parking-zone" data-zone="6" style="top: 65%; left: 70%;">
        <div class="zone-marker">P6</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 7 -->
    <div class="parking-zone" data-zone="7" style="top: 75%; left: 25%;">
        <div class="zone-marker">P7</div>
        <div class="zone-status full">FULL</div>
    </div>
    <!-- Zone 8 -->
    <div class="parking-zone" data-zone="8" style="top: 85%; left: 35%;">
        <div class="zone-marker">P8</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 9 -->
    <div class="parking-zone" data-zone="9" style="top: 40%; left: 80%;">
        <div class="zone-marker">P9</div>
        <div class="zone-status staff">STAFF</div>
    </div>
    <!-- Zone 10 -->
    <div class="parking-zone" data-zone="10" style="top: 50%; left: 20%;">
        <div class="zone-marker">P10</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 11 -->
    <div class="parking-zone" data-zone="11" style="top: 60%; left: 30%;">
        <div class="zone-marker">P11</div>
        <div class="zone-status h-full">H-FULL</div>
    </div>
    <!-- Zone 12 -->
    <div class="parking-zone" data-zone="12" style="top: 70%; left: 40%;">
        <div class="zone-marker">P12</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 13 -->
    <div class="parking-zone" data-zone="13" style="top: 80%; left: 50%;">
        <div class="zone-marker">P13</div>
        <div class="zone-status full">FULL</div>
    </div>
    <!-- Zone 14 -->
    <div class="parking-zone" data-zone="14" style="top: 30%; left: 60%;">
        <div class="zone-marker">P14</div>
        <div class="zone-status empty">EMPTY</div>
    </div>
    <!-- Zone 15 -->
    <div class="parking-zone" data-zone="15" style="top: 90%; left: 70%;">
        <div class="zone-marker">P15</div>
        <div class="zone-status staff">STAFF</div>
    </div>
</div>
        </div>
        </div>

        <div class="legend">
            <h3>LEGEND :</h3>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="color-box full-dnl"></div>
                    <span>FULL (DNL)</span>
                </div>
                <div class="legend-item">
                    <div class="color-box h-full-dnl"></div>
                    <span>H-FULL (DNL)</span>
                </div>
                <div class="legend-item">
                    <div class="color-box empty-dnl"></div>
                    <span>EMPTY (DNL)</span>
                </div>
                <div class="legend-item">
                    <div class="color-box full"></div>
                    <span>FULL</span>
                </div>
                <div class="legend-item">
                    <div class="color-box h-full"></div>
                    <span>HALF-FULL</span>
                </div>
                <div class="legend-item">
                    <div class="color-box empty"></div>
                    <span>EMPTY</span>
                </div>
                <div class="legend-item">
                    <div class="color-box staff"></div>
                    <span>STAFF</span>
                </div>
                <div class="legend-item">
                    <div class="color-box free"></div>
                    <span>FREE</span>
                </div>
            </div>
        </div>
    </div>

    <div class="logo-watermark">
        <img src="{{ asset('images/(1)LOGO.png') }}" alt="Watermark" class="watermark">
    </div>

    <script>

    function updateAnnouncement(title, details, time) {
    const announcementElement = document.getElementById('current-announcement');
    const timeElement = document.getElementById('announcement-time');
    
    announcementElement.textContent = `${title} - ${details}`;
    if (timeElement) {
        timeElement.textContent = `(${time})`;
    } else {
        const timeSpan = document.createElement('small');
        timeSpan.id = 'announcement-time';
        timeSpan.textContent = `(${time})`;
        announcementElement.parentNode.insertBefore(timeSpan, announcementElement.nextSibling);
    }
}

     window.addEventListener('announcementUpdated', function(e) {
    updateAnnouncement(e.detail.title, e.detail.details, e.detail.time);
});
    window.addEventListener('storage', function(event) {
    if (event.key === 'announcementUpdate') {
        const data = JSON.parse(event.newValue);
        updateAnnouncement(data.title, data.details, data.time);
    }
})

        document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.parking-zone').forEach(zone => {
            zone.addEventListener('click', function() {
                const zoneNumber = this.getAttribute('data-zone');
                window.location.href = `/parking-detail/${zoneNumber}`;
            });
        });

        const dropdownBtn = document.getElementById('userDropdownBtn');
        if (dropdownBtn) {
            dropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.getElementById('dropdownContent').classList.toggle('show');
            });
        }
    });

    window.onclick = function(event) {
        if (!event.target.matches('#userDropdownBtn')) {
            document.querySelectorAll('.dropdown-content.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    }

    </script>
</body>
</html> 
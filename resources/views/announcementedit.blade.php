<!DOCTYPE html>
<html>
<head>
    <title>Announcement Edit - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/announcement-styles.css') }}">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="logo-section">
                <img src="{{ asset('images/(1)LOGO.png') }}" alt="ParkMMU Logo" class="admin-logo">
            </div>
            <h1 class="admin-title">ADMIN</h1>
        </div>
        
        <div class="announcement-content">
            <h2 class="page-title">ANNOUNCEMENT EDIT</h2>
            
            <form class="announcement-form" id="announcementForm">
            @csrf
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="{{ $announcement->title ?? '' }}" required>
            </div>
            
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" value="{{ isset($announcement) ? $announcement->created_at->format('Y-m-d') : date('Y-m-d') }}" required>
            </div>
            
            <div class="form-group">
                <label for="time">Time:</label>
                <input type="time" id="time" name="time" value="{{ isset($announcement) ? $announcement->created_at->format('H:i') : date('H:i') }}" required>
            </div>
            
            <div class="form-group">
                <label for="details">Details:</label>
                <textarea id="details" name="details" rows="6" required>{{ $announcement->details ?? '' }}</textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="save-button">Save</button>
                <button type="button" class="clear-button" onclick="document.getElementById('announcementForm').reset()">Clear</button>
            </div>
        </div>
    </div>
        
        <a href="{{ route('admin.menu') }}" class="back-button">
            <img src="{{ asset('images/return page.png') }}" alt="Back">
        </a>
    </div>
</body>

<script>
    document.getElementById('announcementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const saveBtn = document.querySelector('.save-button');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    fetch('{{ route("announcement.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            title: document.getElementById('title').value,
            date: document.getElementById('date').value,
            time: document.getElementById('time').value,
            details: document.getElementById('details').value
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Announcement updated successfully!');
            
            const updateEvent = new CustomEvent('announcementUpdated', {
                detail: {
                    title: data.announcement.title,
                    details: data.announcement.details,
                    time: data.announcement.time
                }
            });
            window.dispatchEvent(updateEvent);
            
            localStorage.setItem('announcementUpdate', JSON.stringify({
                title: data.announcement.title,
                details: data.announcement.details,
                time: data.announcement.time
            }));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating announcement: ' + error.message);
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';
    });
});
    </script>
</html> 
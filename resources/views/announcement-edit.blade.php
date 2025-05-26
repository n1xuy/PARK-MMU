<!DOCTYPE html>
<html>
<head>
    <title>Announcement Edit - MMU Parking Finder</title>
    <link rel="stylesheet" href="{{ asset('css/admin-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/announcement-styles.css') }}">
    <style>
        .notice {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 8px;
            font-weight: bold;
            display: none;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .save-button, .clear-button {
            padding: 10px 18px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .save-button {
            background-color: #28a745;
            color: white;
        }
        .clear-button {
            background-color: #dc3545;
            color: white;
        }
        .save-button:hover {
            background-color: #218838;
        }
        .clear-button:hover {
            background-color: #c82333;
        }
    </style>
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
        
        <div class="announcement-content">
            <h2 class="page-title">ANNOUNCEMENT EDIT</h2>   

            @if (session('success'))
                <div class="notice" id="notice">{{ session('success') }}</div>
            @endif

            @php
                $isEditing = isset($announcement) && $announcement;
            @endphp

            <form class="announcement-form" method="POST"
                  action="{{ $isEditing ? route('announcements.update', $announcement->id) : route('announcements.store') }}">
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title"
                           value="{{ old('title', $isEditing ? $announcement->title : '') }}" required>
                </div>

                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date"
                           value="{{ old('date', $isEditing ? $announcement->date : '') }}" required>
                </div>

                <div class="form-group">
                    <label for="time">Time:</label>
                    <input type="time" id="time" name="time"
                           value="{{ old('time', $isEditing ? $announcement->time : '') }}" required>
                </div>

                <div class="form-group">
                    <label for="details">Details:</label>
                    <textarea id="details" name="details" rows="6" required>{{ old('details', $isEditing ? $announcement->details : '') }}</textarea>
                </div>

                <div class="form-actions">
                    <button class="save-button" type="submit">
                        {{ $isEditing ? 'Update' : 'Create' }}
                    </button>

                    @if($isEditing)
                    <form method="POST" action="{{ route('announcements.clear', ['id' => $announcement->id]) }}">
                        @csrf
                        <button type="submit" class="clear-button">Clear</button>
                    </form>
                    @endif
                </div>

                <div style="margin-top: 20px;">
                    <a href="{{ route('admin.menu') }}" class="back-button">
                        <img src="{{ asset('images/return page.png') }}" alt="Back">
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let notice = document.getElementById('notice');
            if (notice) {
                notice.style.display = 'block';
                setTimeout(() => {
                    notice.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>
</html>

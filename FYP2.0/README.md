# MMU Parking Finder System

## Overview
MMU Parking Finder is a web-based system designed to help students and staff at Multimedia University find available parking spaces in real-time. Users can report parking zone statuses, view the current status of all parking zones, and receive updates about parking availability.

## Features
- Real-time parking zone status visualization
- User registration and authentication
- Reporting system for parking availability
- Admin dashboard for management
- System logs for tracking activities
- Google Maps integration for each parking zone

## Setup Instructions

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### Installation

1. **Set up the database**
   - Create a MySQL database named `mmu_parking`
   - Import the `db_structure.sql` file to create tables and initial data:
     ```
     mysql -u username -p mmu_parking < db_structure.sql
     ```
   - Or run the SQL file through phpMyAdmin

2. **Configure the system**
   - Edit `config.php` and update database credentials:
     ```php
     $servername = "localhost";
     $username = "your_db_username";
     $password = "your_db_password";
     $dbname = "mmu_parking";
     ```

3. **Set up web server**
   - Upload all files to your web server directory
   - Make sure the web server has write permissions for logs and uploads directories

4. **Access the system**
   - Navigate to the website URL
   - Default admin credentials:
     - Username: admin
     - Password: admin123

### File Structure
- `index.php` - Main page with parking zone map
- `login.php` - User login handler
- `register.php` - User registration handler
- `parking-zone-detail.php` - Individual parking zone details and reporting
- `admin-dashboard.php` - Admin dashboard
- `config.php` - Database configuration and utility functions
- `*.html` files - HTML templates
- CSS files for styling

## Usage

### For Students/Staff
1. Register an account (or login if you already have one)
2. View the main map to see current parking statuses
3. Click on a parking zone to view details
4. Report the current status of a parking zone using color buttons:
   - Green: EMPTY
   - Orange: HALF-FULL
   - Red: FULL
5. View location on Google Maps

### For Administrators
1. Login with admin credentials
2. Access the admin dashboard
3. Manage parking zones, announcements, and users
4. View system logs and reports

## Troubleshooting
- If you encounter database connection issues, verify your database credentials in `config.php`
- For permission errors, check file and directory permissions
- Check PHP error logs for additional information

## Security Notes
- Default admin password should be changed immediately after installation
- Implement HTTPS for secure communication
- Regularly backup the database 
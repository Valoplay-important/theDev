# Guest Logging System - Setup Guide

## What's Been Added

Your website now has a complete guest logging system that tracks visitor information:
- **Email address** of visitors
- **Date & Time** of visit
- **Device type** (Mobile, Desktop, or Tablet)
- **IP Address** of visitor
- **User Agent** information

---

## Files Created

### 1. `setup_db.php` 
- Creates the MySQL database and table automatically
- **Run this ONCE to initialize the database**

### 2. `log_guest.php`
- Backend PHP script that receives and stores visitor data
- Validates emails and handles the database insertion

### 3. `view_logs.php`
- Dashboard to view all guest logs
- Shows statistics (total visits, unique visitors, device breakdown)
- Displays all visitor information in a table

### 4. `dev.html` (Updated)
- Added guest log button (📝) in bottom-right corner
- Beautiful modal form for visitors to sign in
- Automatically detects date, time, and device type
- Form sends data to `log_guest.php` via AJAX

---

## Setup Instructions

### Step 1: Start XAMPP MySQL Server

1. Open **XAMPP Control Panel**
2. Click **START** button next to **MySQL**
3. Wait for it to show "Running" status (should say port 3306)

### Step 2: Initialize the Database

After MySQL is running:

1. Open your browser and go to: `http://localhost/phpmyadmin/`
2. Copy and paste the entire content of `setup_db.php` into the SQL section
3. Click **Go** to execute

**OR** use the command line (after MySQL is running):
```bash
cd c:\xampp\htdocs\theDev
C:\xampp\php\php.exe setup_db.php
```

### Step 3: Test the System

1. Go to `http://localhost/theDev/dev.html`
2. Click the **📝 button** in the bottom-right corner
3. Enter your email and click **SEND IT →**
4. You should see a success message

### Step 4: View Guest Logs

Visit `http://localhost/theDev/view_logs.php` to see:
- Dashboard with visitor statistics
- Complete list of all logged visits
- Device types and visit timestamps

---

## Database Schema

The system creates a table called `guest_logs` with the following columns:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Auto-increment ID |
| email | VARCHAR(255) | Visitor's email |
| visit_date | DATE | Date of visit (YYYY-MM-DD) |
| visit_time | TIME | Time of visit (HH:mm:ss) |
| device_type | VARCHAR(50) | Mobile/Desktop/Tablet |
| user_agent | TEXT | Browser/device information |
| ip_address | VARCHAR(45) | Visitor's IP address |
| created_at | TIMESTAMP | When it was recorded |

---

## How It Works

1. **Visitor clicks the 📝 button** on your website
2. **Modal appears** with auto-detected info:
   - Current date
   - Current time
   - Device type (detected from browser)
3. **Visitor enters their email** and clicks "SEND IT →"
4. **JavaScript sends the data** to `log_guest.php` via AJAX
5. **PHP validates & stores** in the database
6. **Success message appears** and modal closes

---

## Features

✅ **Auto-detection**: Device type, date, and time detected automatically
✅ **Email validation**: Prevents invalid emails from being stored
✅ **Beautiful UI**: Matches your portfolio design perfectly
✅ **Analytics dashboard**: View all stats at `view_logs.php`
✅ **AJAX submission**: No page reload needed
✅ **Mobile responsive**: Works great on all devices
✅ **Security**: Uses prepared statements to prevent SQL injection

---

## Customization

### Change Button Text/Emoji
Edit the button in `dev.html`:
```html
<button class="guest-log-btn" onclick="openGuestModal()">📝</button>
```
Change `📝` to any emoji or text you prefer

### Modify Modal Styling
Edit the CSS in `dev.html` under `/* ── GUEST LOG MODAL ── */`

### Add More Fields
Edit `log_guest.php` and `view_logs.php` to add additional fields

---

## Troubleshooting

### "Connection failed" error
→ Make sure MySQL is running in XAMPP Control Panel

### "Permission denied" when accessing view_logs.php
→ Make sure you're accessing via localhost: `http://localhost/theDev/view_logs.php`

### Database not created
→ Run `setup_db.php` first (must have MySQL running)

### Email not saving
→ Check that the email format is correct (contains @)

---

## Next Steps

1. **Start MySQL** in XAMPP Control Panel
2. **Run setup_db.php** to create the database
3. **Test the form** on your website
4. **View the logs** at `view_logs.php`

Your guest logging system is ready to track visitor engagement! 🚀

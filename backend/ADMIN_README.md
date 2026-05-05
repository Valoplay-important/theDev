# 🎯 Quick Start - Admin Access

## **Default Admin Credentials**

| Field | Value |
|-------|-------|
| **URL** | `http://localhost/theDev/backend/admin_login.php` |
| **Password** | `grapika2026` |

⚠️ **CHANGE THIS IMMEDIATELY!**

---

## **Security Improvements Made**

### ✅ **SQL Injection Prevention**
- All database queries now use prepared statements
- User input is never concatenated into SQL

### ✅ **Cross-Site Scripting (XSS) Prevention**
- All outputs are HTML-escaped
- Prevents malicious script injection

### ✅ **Admin Access Control**
- Login page with password protection
- Session timeout after 30 minutes
- Secure session cookies

### ✅ **Input Validation**
- Length limits on all inputs
- Email format validation
- Date/time format validation
- Required field checking

### ✅ **Rate Limiting**
- Max 5 messages per minute per IP
- Prevents spam and automated attacks

### ✅ **Secure Headers**
- X-Frame-Options (prevents clickjacking)
- X-Content-Type-Options (prevents MIME sniffing)
- X-XSS-Protection (browser XSS filter)
- Content-Security-Policy (restricts resource loading)

### ✅ **Error Handling**
- No sensitive database info exposed to users
- Proper HTTP status codes (429 for rate limit, 405 for wrong method)

---

## **How to Access Admin Panel**

### **Visitor Logs:**
```
http://localhost/theDev/backend/view_logs.php
```

### **Messages:**
```
http://localhost/theDev/backend/view_messages.php
```

Both will redirect to login if not authenticated.

---

## **⚠️ IMPORTANT: Change Admin Password!**

1. Open file: `backend/config.php`
2. Find line 10: `define('ADMIN_PASSWORD', 'grapika2026');`
3. Change to a strong password (mix uppercase, lowercase, numbers, special chars)
4. Save the file

**Example strong password:**
```php
define('ADMIN_PASSWORD', 'MyGrapika2026@Secure!');
```

---

## **Session Management**

- **Session Timeout**: 30 minutes of inactivity
- **Logout**: Click LOGOUT button on admin pages
- **Max Requests**: 5 messages per minute per IP

---

## **Files Created/Updated**

| File | Purpose |
|------|---------|
| `config.php` | Security configuration & helpers |
| `admin_login.php` | Admin authentication page |
| `log_guest.php` | Improved visitor logging |
| `submit_message.php` | Improved message handler |
| `view_logs.php` | Protected visitor logs |
| `view_messages.php` | Protected messages |
| `SECURITY.md` | Full security documentation |

---

## **Testing**

### **Try submitting a message:**
1. Visit: `http://localhost/theDev/`
2. Fill contact form
3. Click "SEND IT →"
4. Message appears in admin panel

### **View all visitor logs:**
1. Visit: `http://localhost/theDev/backend/view_logs.php`
2. Login with admin password
3. See all visitors to your site

---

## **More Security Tips**

See `backend/SECURITY.md` for:
- ✅ Complete security guide
- ✅ Best practices
- ✅ Production recommendations
- ✅ Troubleshooting
- ✅ Regular maintenance

---

🔒 **Your website is now protected against:**
- SQL Injection attacks
- XSS (Cross-Site Scripting) attacks
- Unauthorized access
- CSRF attacks
- Rate-based attacks
- MIME-type sniffing
- Clickjacking

Keep your password safe and change it regularly! 🛡️

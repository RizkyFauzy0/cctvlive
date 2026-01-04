# Testing Guide for Live CCTV Manager

## Pre-requisites for Testing

Before testing the application, ensure you have:

1. ✅ PHP 7.4+ installed
2. ✅ MySQL 5.7+ installed and running
3. ✅ Web server (Apache/Nginx) configured
4. ✅ Database imported from `database.sql`
5. ✅ Database credentials configured in `config/database.php`

## Manual Testing Checklist

### 1. Database Setup ✓

- [ ] Database `cctvlive` created successfully
- [ ] Tables `users` and `cameras` created
- [ ] Default admin user inserted
- [ ] Database connection works from PHP

### 2. Home Page (index.php)

**Test Cases:**

- [ ] Page loads without errors
- [ ] Navigation bar displays correctly
- [ ] Empty state shows when no cameras exist
- [ ] Camera grid displays when cameras are active
- [ ] Each camera card shows:
  - [ ] Camera name
  - [ ] Location
  - [ ] Live badge indicator
  - [ ] Watch button
  - [ ] Copy button
- [ ] Copy button copies stream URL to clipboard
- [ ] Watch button navigates to view page
- [ ] Responsive layout works on mobile

**Expected Results:**
- Clean UI with glass morphism effect
- Smooth animations
- All buttons functional
- No JavaScript errors in console

### 3. Admin Panel (admin.php)

**Test Cases:**

#### Statistics Dashboard
- [ ] Total cameras count displays correctly
- [ ] Active cameras count displays correctly
- [ ] Inactive cameras count displays correctly
- [ ] Cards animate on page load

#### Add Camera
- [ ] "Add New Camera" button opens modal
- [ ] Modal form displays all fields
- [ ] Required field validation works
- [ ] Form submission creates new camera
- [ ] Unique stream key is generated
- [ ] Success notification displays
- [ ] Page reloads with new camera in table
- [ ] Modal closes after success

#### Edit Camera
- [ ] Edit button opens modal with camera data
- [ ] All fields pre-populated correctly
- [ ] Changes save successfully
- [ ] Success notification displays
- [ ] Table updates with new data

#### Toggle Status
- [ ] Toggle button changes camera status
- [ ] Status badge updates (Active/Inactive)
- [ ] Success notification displays

#### Delete Camera
- [ ] Delete button shows confirmation dialog
- [ ] Confirming deletes the camera
- [ ] Camera removed from table
- [ ] Statistics update correctly
- [ ] Canceling preserves the camera

#### Table Display
- [ ] All cameras listed in table
- [ ] RTSP URLs truncated appropriately
- [ ] Stream keys truncated appropriately
- [ ] Status badges color-coded correctly
- [ ] Action buttons aligned properly

### 4. View Stream Page (view.php)

**Test Cases:**

- [ ] Page loads with valid stream key
- [ ] Camera details display correctly:
  - [ ] Name
  - [ ] Location
  - [ ] Live badge
  - [ ] Status indicator
- [ ] Stream placeholder shows with instructions
- [ ] Info panel shows:
  - [ ] Stream key
  - [ ] RTSP URL
  - [ ] Added date
- [ ] Stream stats panel displays
- [ ] Copy URL button works
- [ ] VLC button copies RTSP URL
- [ ] Share URL input is populated
- [ ] Share copy button works
- [ ] Fullscreen button triggers fullscreen
- [ ] Invalid stream key shows "Not Found" page
- [ ] Back button navigates to home

### 5. REST API (api/cameras.php)

**Test Cases:**

#### GET Requests

**Get All Cameras:**
```bash
curl http://localhost/cctvlive/api/cameras.php
```
- [ ] Returns JSON response
- [ ] Success field is true
- [ ] Data contains cameras array
- [ ] Count field is correct

**Get Active Cameras:**
```bash
curl "http://localhost/cctvlive/api/cameras.php?status=active"
```
- [ ] Returns only active cameras
- [ ] Response format correct

**Get Camera by Stream Key:**
```bash
curl "http://localhost/cctvlive/api/cameras.php?stream_key=abc123..."
```
- [ ] Returns single camera object
- [ ] 404 for non-existent key

**Get Camera by ID:**
```bash
curl "http://localhost/cctvlive/api/cameras.php?id=1"
```
- [ ] Returns single camera object
- [ ] 404 for non-existent ID

#### POST Requests (Create Camera)

```bash
curl -X POST http://localhost/cctvlive/api/cameras.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Camera",
    "location": "Test Location",
    "rtsp_url": "rtsp://test.example.com/stream",
    "status": "active"
  }'
```

- [ ] Creates new camera
- [ ] Generates unique stream key
- [ ] Returns 201 status code
- [ ] Returns created camera data
- [ ] Validates required fields (name, rtsp_url)
- [ ] Returns 400 for missing required fields

#### PUT Requests (Update Camera)

```bash
curl -X PUT http://localhost/cctvlive/api/cameras.php \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "name": "Updated Camera",
    "status": "inactive"
  }'
```

- [ ] Updates camera successfully
- [ ] Returns updated camera data
- [ ] Returns 404 for non-existent ID
- [ ] Returns 400 for missing ID
- [ ] Only updates provided fields

#### DELETE Requests

```bash
curl -X DELETE "http://localhost/cctvlive/api/cameras.php?id=1"
```

- [ ] Deletes camera successfully
- [ ] Returns 200 status code
- [ ] Returns 404 for non-existent ID
- [ ] Returns 400 for missing ID

### 6. JavaScript Functionality (assets/js/app.js)

**Test Cases:**

- [ ] Copy to clipboard works in modern browsers
- [ ] Fallback copy works in older browsers
- [ ] Notifications display correctly
- [ ] Notification auto-dismiss after timeout
- [ ] Modal opens/closes properly
- [ ] Modal closes on outside click
- [ ] Modal closes on Escape key
- [ ] Form validation works
- [ ] API calls handle errors gracefully
- [ ] Page reloads after successful operations
- [ ] Console shows no JavaScript errors

### 7. UI/UX Testing

**Visual Tests:**

- [ ] Dark theme applied consistently
- [ ] Glass morphism effect visible on cards
- [ ] Gradient backgrounds render correctly
- [ ] Icons display properly (Font Awesome)
- [ ] Animations are smooth (no jank)
- [ ] Hover effects work on interactive elements
- [ ] Color scheme matches specifications:
  - Primary: #0f172a
  - Secondary: #1e293b
  - Accent: #3b82f6

**Responsive Tests:**

Test on different screen sizes:
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

Elements to verify:
- [ ] Navigation responsive
- [ ] Camera grid adjusts columns
- [ ] Tables scroll horizontally on mobile
- [ ] Modals fit screen size
- [ ] Buttons stack on mobile
- [ ] Text remains readable

### 8. Security Testing

**Test Cases:**

- [ ] SQL injection prevented (prepared statements used)
- [ ] XSS prevention (htmlspecialchars used)
- [ ] CSRF tokens (should be implemented for production)
- [ ] Input validation on all forms
- [ ] Error messages don't reveal sensitive info
- [ ] Database credentials not exposed
- [ ] .htaccess protects config files
- [ ] Stream keys are cryptographically secure (32 chars)

### 9. Performance Testing

**Test Cases:**

- [ ] Page load time < 2 seconds
- [ ] API response time < 500ms
- [ ] No memory leaks in JavaScript
- [ ] Database queries optimized
- [ ] Static assets cached properly
- [ ] GZIP compression enabled

### 10. Cross-Browser Testing

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (Chrome, Safari)

## Automated Testing (Optional)

For future development, consider adding:

1. **PHPUnit** for backend testing
2. **Jest** for JavaScript testing
3. **Selenium** for E2E testing
4. **PHP CodeSniffer** for code quality

## Known Limitations

1. **RTSP Playback**: Browsers don't support RTSP natively
   - Users must use VLC or stream converter
   - Consider implementing MediaMTX integration

2. **Authentication**: Basic admin panel without session management
   - Should implement proper authentication for production

3. **Real-time Updates**: No WebSocket support
   - Page refresh required to see changes

## Reporting Issues

When reporting issues, include:

1. PHP version
2. MySQL version
3. Browser and version
4. Steps to reproduce
5. Expected vs actual behavior
6. Error messages (if any)
7. Screenshots (if applicable)

## Test Results

Date: _______________
Tester: _______________

Overall Status: [ ] PASS  [ ] FAIL

Notes:
_____________________________________________
_____________________________________________
_____________________________________________

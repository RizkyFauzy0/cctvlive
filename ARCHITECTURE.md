# Application Architecture

## Overview

Live CCTV Manager is a web-based application built with native PHP, MySQL, and Tailwind CSS. It follows a simple MVC-like pattern with separation of concerns.

## Technology Stack

### Backend
- **PHP 7.4+**: Server-side logic and database operations
- **MySQL 5.7+**: Relational database for data persistence
- **PDO**: Database abstraction layer with prepared statements

### Frontend
- **HTML5**: Semantic markup
- **Tailwind CSS 3.x**: Utility-first CSS framework
- **JavaScript (Vanilla)**: Client-side interactivity
- **Font Awesome 6.x**: Icon library

### Web Server
- **Apache** (with .htaccess) or **Nginx** (with custom config)

## Directory Structure

```
cctvlive/
├── config/              # Configuration files
│   └── database.php     # Database connection and utilities
├── api/                 # REST API endpoints
│   └── cameras.php      # Camera CRUD operations
├── assets/              # Static assets
│   └── js/
│       └── app.js       # Frontend JavaScript
├── includes/            # Reusable components
│   ├── header.php       # Common header
│   └── footer.php       # Common footer
├── index.php            # Home page (camera grid)
├── admin.php            # Admin panel
├── view.php             # Stream viewer
├── database.sql         # Database schema
├── install.sh           # Installation script
├── .htaccess            # Apache configuration
├── nginx.conf.example   # Nginx configuration example
├── .env.example         # Environment variables template
├── .gitignore           # Git ignore rules
├── LICENSE              # MIT License
├── README.md            # Main documentation
├── TESTING.md           # Testing guide
└── ARCHITECTURE.md      # This file
```

## Database Schema

### Tables

#### `users`
Stores admin user credentials for authentication.

| Column     | Type         | Description                    |
|------------|--------------|--------------------------------|
| id         | INT          | Primary key (auto-increment)   |
| username   | VARCHAR(50)  | Unique username                |
| password   | VARCHAR(255) | Hashed password (bcrypt)       |
| email      | VARCHAR(100) | Email address                  |
| created_at | TIMESTAMP    | Creation timestamp             |
| updated_at | TIMESTAMP    | Last update timestamp          |

#### `cameras`
Stores camera information and RTSP stream details.

| Column     | Type           | Description                      |
|------------|----------------|----------------------------------|
| id         | INT            | Primary key (auto-increment)     |
| name       | VARCHAR(100)   | Camera name                      |
| location   | VARCHAR(255)   | Physical location                |
| rtsp_url   | TEXT           | RTSP stream URL                  |
| stream_key | VARCHAR(32)    | Unique stream identifier         |
| status     | ENUM           | 'active' or 'inactive'           |
| created_at | TIMESTAMP      | Creation timestamp               |
| updated_at | TIMESTAMP      | Last update timestamp            |

### Indexes
- `stream_key`: Unique index for fast lookups
- `status`: Index for filtering active/inactive cameras

## Application Flow

### 1. Home Page (index.php)

```
User Request → index.php
    ↓
Load header.php (includes navigation)
    ↓
Query active cameras from database
    ↓
Display camera grid or empty state
    ↓
Load footer.php (includes JavaScript)
```

**Key Features:**
- Display all active cameras in a responsive grid
- Live badge indicators
- Quick actions (Watch, Copy URL)
- Empty state with call-to-action

### 2. Admin Panel (admin.php)

```
User Request → admin.php
    ↓
Load header.php
    ↓
Fetch statistics (total, active, inactive)
    ↓
Fetch all cameras from database
    ↓
Display dashboard and camera table
    ↓
Handle CRUD operations via JavaScript + API
    ↓
Load footer.php
```

**Key Features:**
- Statistics dashboard with counts
- Camera management table
- CRUD operations via modal forms
- Real-time updates

### 3. View Stream Page (view.php)

```
User Request → view.php?key={stream_key}
    ↓
Validate stream_key parameter
    ↓
Query camera by stream_key
    ↓
If found: Display stream viewer
If not found: Show error page
    ↓
Load footer.php
```

**Key Features:**
- Dedicated stream viewing interface
- Camera information panel
- Stream statistics
- Share options
- RTSP instructions

### 4. REST API (api/cameras.php)

```
HTTP Request → api/cameras.php
    ↓
Parse request method (GET/POST/PUT/DELETE)
    ↓
Route to appropriate handler
    ↓
Execute database operation
    ↓
Return JSON response
```

**Endpoints:**

| Method | Endpoint                        | Description              |
|--------|---------------------------------|--------------------------|
| GET    | /api/cameras.php                | Get all cameras          |
| GET    | /api/cameras.php?status=active  | Get filtered cameras     |
| GET    | /api/cameras.php?stream_key=... | Get single camera        |
| GET    | /api/cameras.php?id=1           | Get camera by ID         |
| POST   | /api/cameras.php                | Create new camera        |
| PUT    | /api/cameras.php                | Update camera            |
| DELETE | /api/cameras.php?id=1           | Delete camera            |

## Data Flow

### Creating a Camera

```
User fills form → JavaScript validates input
    ↓
POST request to API (JSON)
    ↓
API validates data
    ↓
Generate unique stream_key
    ↓
Insert into database
    ↓
Return created camera data (JSON)
    ↓
JavaScript shows success notification
    ↓
Page reloads to show new camera
```

### Viewing a Stream

```
User clicks "Watch" → Navigate to view.php?key=xxx
    ↓
PHP validates stream_key
    ↓
Query database for camera
    ↓
If found: Render stream viewer
    ↓
Display RTSP URL and instructions
    ↓
User can copy URL for VLC or converters
```

## Security Measures

### 1. SQL Injection Prevention
- **Prepared Statements**: All database queries use PDO prepared statements
- **Parameter Binding**: User input never directly concatenated into SQL

```php
$stmt = $pdo->prepare("SELECT * FROM cameras WHERE id = ?");
$stmt->execute([$id]);
```

### 2. XSS Prevention
- **Output Escaping**: All user-generated content escaped with `htmlspecialchars()`
- **JSON Encoding**: API responses use `json_encode()`

```php
echo htmlspecialchars($camera['name']);
```

### 3. CSRF Protection
- **Note**: CSRF tokens should be implemented for production
- Forms should include CSRF tokens validated server-side

### 4. Authentication
- **Password Hashing**: Bcrypt used for password storage
- **Session Management**: Should be implemented for production

### 5. File Access Control
- **.htaccess**: Protects config directory
- **Nginx Config**: Denies access to sensitive files

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Resource data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description"
}
```

### HTTP Status Codes
- **200**: Success
- **201**: Created
- **400**: Bad Request
- **404**: Not Found
- **405**: Method Not Allowed
- **500**: Server Error

## Frontend Architecture

### JavaScript Modules

The application uses vanilla JavaScript organized into functional modules:

1. **Clipboard Operations**
   - `copyStreamUrl()`: Copy stream viewer URL
   - `copyShareUrl()`: Copy shareable link
   - `copyToClipboard()`: Generic copy function

2. **UI Interactions**
   - `showNotification()`: Display toast messages
   - `openAddModal()`: Open add camera modal
   - `editCamera()`: Open edit modal with data
   - `closeModal()`: Close modal dialogs

3. **API Communication**
   - Form submission handler
   - `toggleStatus()`: Update camera status
   - `deleteCamera()`: Remove camera

4. **Media Controls**
   - `openInVLC()`: Copy RTSP for VLC
   - `toggleFullscreen()`: Fullscreen mode

### Event Handling
- Form submissions intercepted via `addEventListener`
- Modal close on outside click
- Keyboard shortcuts (Escape to close modal)
- Error handling for API calls

## Configuration Management

### Database Configuration (config/database.php)

Centralizes database connection logic:
- Connection parameters
- PDO instance (singleton pattern)
- Utility functions (generateStreamKey, getBaseUrl)

### Environment Variables (.env.example)

Template for environment-specific configuration:
- Database credentials
- Application settings
- Security settings

## Styling Architecture

### Tailwind CSS Utility Classes

The UI uses Tailwind's utility-first approach:
- Layout: `flex`, `grid`, `container`
- Spacing: `p-*`, `m-*`, `gap-*`
- Colors: `bg-*`, `text-*`, `border-*`
- Effects: `hover:*`, `transition`, `rounded-*`

### Custom CSS

Additional styles in `<style>` blocks:
- `.glass`: Glass morphism effect
- `.gradient-text`: Gradient text color
- `.pulse-badge`: Pulsing animation
- `.animate-fade-in`: Fade in animation

## Performance Considerations

### Database
- Indexed columns (stream_key, status)
- Prepared statement caching
- Connection pooling (single PDO instance)

### Frontend
- CDN-hosted assets (Tailwind, Font Awesome)
- Minimal custom JavaScript
- Event delegation where applicable
- Debounced API calls

### Caching
- Browser caching for static assets
- HTTP cache headers (.htaccess / nginx)
- GZIP compression enabled

## Scalability Considerations

### Current Limitations
- Single server architecture
- No load balancing
- No session clustering
- Limited concurrent streams

### Future Improvements
1. **Database**: Master-slave replication
2. **Caching**: Redis for session storage
3. **CDN**: Static asset delivery
4. **Load Balancer**: Multiple app servers
5. **Queue**: Background job processing
6. **WebSocket**: Real-time updates

## RTSP Stream Handling

### Challenge
Web browsers don't support RTSP protocol natively.

### Solutions

#### 1. VLC Media Player
Users copy RTSP URL and paste in VLC.

#### 2. Stream Conversion
Convert RTSP to web-compatible formats:

**MediaMTX** (Recommended)
```bash
# Converts RTSP to HLS/WebRTC
mediamtx & 
# Access via: http://localhost:8888/stream/
```

**FFmpeg**
```bash
# Convert RTSP to HLS
ffmpeg -i rtsp://camera-url \
  -c:v copy -c:a aac \
  -f hls -hls_time 2 \
  output.m3u8
```

#### 3. Future Implementation
- Integrate MediaMTX API
- Automatic transcoding
- In-browser playback (HLS.js)

## Error Handling

### PHP
- Try-catch blocks for database operations
- Graceful error messages
- Logging (should be implemented)

### JavaScript
- Try-catch for async operations
- User-friendly error notifications
- Console logging for debugging

### Database
- Connection error handling
- Query error handling
- Transaction rollback (for future use)

## Testing Strategy

See [TESTING.md](TESTING.md) for comprehensive testing guide.

### Unit Testing (Recommended)
- PHPUnit for backend logic
- Jest for JavaScript functions

### Integration Testing
- API endpoint testing
- Database operation testing

### End-to-End Testing
- Selenium for UI testing
- Cypress for modern E2E

## Deployment

### Development
```bash
php -S localhost:8000
```

### Production
1. Configure web server (Apache/Nginx)
2. Set up MySQL database
3. Import schema
4. Configure database credentials
5. Set proper file permissions
6. Enable HTTPS
7. Configure firewall

### Docker (Optional)
Consider containerizing for easier deployment:
- PHP-FPM container
- MySQL container
- Nginx container
- Docker Compose orchestration

## Maintenance

### Regular Tasks
- Database backups
- Log rotation
- Security updates
- Performance monitoring

### Monitoring
- Database query performance
- API response times
- Error rates
- User activity

## Contributing

See repository README for contribution guidelines.

## License

MIT License - See [LICENSE](LICENSE) file.

---

**Last Updated**: January 2024
**Version**: 1.0.0

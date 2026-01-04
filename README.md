# Live CCTV Manager 📹

A modern web application for managing RTSP camera streams with a beautiful dark-themed UI. Built with PHP, MySQL, and Tailwind CSS. Features integrated MediaMTX support for browser-based HLS streaming.

## ✨ Features

- **Dashboard View**: Grid display of all active camera streams
- **Admin Panel**: Full CRUD operations for camera management
- **Stream Viewer**: Dedicated page for viewing individual camera feeds
- **MediaMTX Integration**: Built-in support for HLS streaming in browsers
- **Auto-Registration**: Cameras automatically register with MediaMTX
- **REST API**: JSON API for programmatic access
- **Modern UI**: Glass morphism design with smooth animations
- **Responsive**: Mobile-friendly responsive layout
- **Secure**: Prepared statements for SQL injection prevention
- **Unique Stream Keys**: Auto-generated unique identifiers for each camera
- **HLS Player**: Native browser playback with HLS.js integration

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser
- **MediaMTX** (optional, for browser streaming) - See [MediaMTX Installation Guide](INSTALL_MEDIAMTX.md)

### Installation Options

Choose the installation method that suits your environment:

- 📘 **[Local/VPS Installation](QUICKSTART.md)** - For local development or VPS servers
- 🌐 **[Shared Hosting & aaPanel](INSTALL_HOSTING.md)** - For cPanel/Shared hosting and aaPanel
- 🎥 **[MediaMTX Setup](INSTALL_MEDIAMTX.md)** - Enable browser-based HLS streaming
- 🤖 **Automated Installation** - Use `install.sh` script (Linux/Mac)

### Quick Installation (Local/VPS)

1. **Clone the repository**
   ```bash
   git clone https://github.com/RizkyFauzy0/cctvlive.git
   cd cctvlive
   ```

2. **Configure database**
   
   Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cctvlive');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Import database schema**
   ```bash
   mysql -u your_username -p < database.sql
   ```

4. **Set up web server**
   
   Point your web server document root to the project directory.

5. **Access the application**
   
   Open your browser and navigate to:
   - **Home**: `http://localhost/cctvlive/`
   - **Admin Panel**: `http://localhost/cctvlive/admin.php`

## 📂 Project Structure

```
cctvlive/
├── config/
│   ├── database.php          # Database configuration
│   └── mediamtx.php          # MediaMTX configuration
├── assets/
│   └── js/
│       └── app.js            # Frontend JavaScript
├── includes/
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   └── stream-helper.php     # MediaMTX helper functions
├── api/
│   ├── cameras.php           # Camera REST API endpoints
│   └── mediamtx.php          # MediaMTX API endpoints
├── index.php                 # Home page (camera grid)
├── admin.php                 # Admin panel
├── view.php                  # Stream viewer with HLS player
├── database.sql              # Database schema
├── INSTALL_MEDIAMTX.md       # MediaMTX setup guide
└── README.md                 # Documentation
```

## 📖 Usage Guide

### Default Admin Credentials

- **Username**: `admin`
- **Password**: `admin123`

> ⚠️ **Important**: Change the default password after first login!

### Adding a Camera

1. Navigate to the Admin Panel
2. Click "Add New Camera"
3. Fill in the camera details:
   - **Name**: Camera identifier (e.g., "Main Entrance")
   - **Location**: Physical location (e.g., "Building A - Floor 1")
   - **RTSP URL**: Full RTSP stream URL (e.g., `rtsp://admin:password@192.168.1.100:554/stream1`)
   - **Status**: Active or Inactive
4. Click "Save Camera"

The system will automatically generate a unique stream key for sharing.

### Viewing a Stream

1. From the home page, click "Watch" on any camera card
2. The stream viewer will open with camera details
3. Use the quick actions:
   - **Copy URL**: Copy the shareable stream link
   - **VLC**: Copy RTSP URL for VLC Player
   - **Fullscreen**: Toggle fullscreen mode

### Managing Cameras

In the Admin Panel, you can:
- **Edit**: Modify camera details
- **Toggle Status**: Enable/disable cameras
- **Delete**: Remove cameras permanently

## 🔌 REST API Documentation

Base URL: `/api/cameras.php`

### Get All Cameras

```http
GET /api/cameras.php
```

Optional query parameters:
- `status`: Filter by status (`active` or `inactive`)

**Response:**
```json
{
  "success": true,
  "message": "Cameras retrieved",
  "data": {
    "cameras": [...],
    "count": 5
  }
}
```

### Get Single Camera

```http
GET /api/cameras.php?stream_key={key}
GET /api/cameras.php?id={id}
```

**Response:**
```json
{
  "success": true,
  "message": "Camera found",
  "data": {
    "id": 1,
    "name": "Main Entrance",
    "location": "Building A",
    "rtsp_url": "rtsp://...",
    "stream_key": "abc123...",
    "status": "active",
    "created_at": "2024-01-01 00:00:00"
  }
}
```

### Add Camera

```http
POST /api/cameras.php
Content-Type: application/json

{
  "name": "Camera Name",
  "location": "Location",
  "rtsp_url": "rtsp://...",
  "status": "active"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Camera created successfully",
  "data": {...}
}
```

### Update Camera

```http
PUT /api/cameras.php
Content-Type: application/json

{
  "id": 1,
  "name": "Updated Name",
  "status": "inactive"
}
```

### Delete Camera

```http
DELETE /api/cameras.php?id={id}
```

## 🎥 Browser-Based Streaming with MediaMTX

### Automatic HLS Streaming

This application now includes **built-in MediaMTX integration** for streaming RTSP cameras directly in web browsers using HLS!

#### Features:
- ✅ **Auto-registration**: Cameras automatically register with MediaMTX when added
- ✅ **Browser playback**: Watch streams directly in any modern browser
- ✅ **Low latency**: Optimized HLS configuration for minimal delay
- ✅ **Auto-cleanup**: Streams are automatically removed when cameras are deleted
- ✅ **Status monitoring**: Real-time MediaMTX connection status in admin panel

### Quick Setup

1. **Install MediaMTX** - Follow the [complete installation guide](INSTALL_MEDIAMTX.md)

2. **Add a Camera** - In the admin panel, add your RTSP camera. The stream will automatically register with MediaMTX.

3. **Watch in Browser** - Click "Watch" on any camera to view the live HLS stream in your browser!

### MediaMTX Installation

See the [MediaMTX Installation Guide](INSTALL_MEDIAMTX.md) for:
- Step-by-step installation instructions
- Configuration for aaPanel and VPS
- Firewall setup
- Troubleshooting tips

### Manual Streaming Methods

If MediaMTX is not installed, you can still use:

#### Method 1: VLC Media Player

1. Open VLC Media Player
2. Go to **Media** > **Open Network Stream**
3. Paste the RTSP URL
4. Click **Play**

#### Method 2: FFmpeg
```bash
# Convert RTSP to HLS
ffmpeg -i rtsp://camera-url -c:v copy -c:a aac -f hls -hls_time 2 -hls_list_size 3 output.m3u8
```

## 🎨 UI Color Scheme

- **Primary**: `#0f172a` (Dark Navy)
- **Secondary**: `#1e293b` (Slate)
- **Accent**: `#3b82f6` (Blue)
- **Success**: `#10b981` (Green)
- **Error**: `#ef4444` (Red)
- **Warning**: `#f59e0b` (Yellow)

## 🔒 Security Features

- ✅ Prepared SQL statements (prevents SQL injection)
- ✅ Input validation and sanitization
- ✅ Unique stream keys (32-character hex)
- ✅ CSRF protection ready
- ✅ XSS prevention with `htmlspecialchars()`

### Security Recommendations

1. Change default admin password
2. Use HTTPS in production
3. Implement session-based authentication
4. Add rate limiting for API endpoints
5. Regularly update dependencies

## 🛠️ Troubleshooting

### Database Connection Error

**Problem**: "Database connection failed"

**Solution**: 
- Check MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database `cctvlive` exists

### Camera Not Displaying

**Problem**: Camera shows but won't play

**Solution**:
- RTSP streams don't play in browsers natively
- Use VLC or a stream converter (see RTSP Playback section)
- Verify RTSP URL is correct and accessible

### API Errors

**Problem**: API returns 500 error

**Solution**:
- Check PHP error logs
- Verify JSON request format
- Ensure proper Content-Type headers

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👤 Author

**RizkyFauzy0**

- GitHub: [@RizkyFauzy0](https://github.com/RizkyFauzy0)

## 🌟 Acknowledgments

- Tailwind CSS for the beautiful UI framework
- Font Awesome for icons
- PHP and MySQL communities

---

**Note**: This application is designed for managing RTSP streams. For actual video playback in browsers, you'll need to implement a transcoding solution like MediaMTX, FFmpeg, or WebRTC.

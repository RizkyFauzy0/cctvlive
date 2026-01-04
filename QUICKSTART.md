# Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Prerequisites Check ✓

Make sure you have:
- [x] PHP 7.4 or higher
- [x] MySQL 5.7 or higher  
- [x] Apache or Nginx web server
- [x] Modern web browser

```bash
# Check PHP version
php -v

# Check MySQL version
mysql --version
```

### Step 2: Download & Extract

```bash
# Clone the repository
git clone https://github.com/RizkyFauzy0/cctvlive.git
cd cctvlive
```

### Step 3: Automated Installation 🤖

Run the installation script:

```bash
chmod +x install.sh
./install.sh
```

The script will:
1. ✓ Verify prerequisites
2. ✓ Prompt for database credentials
3. ✓ Create database and tables
4. ✓ Configure the application
5. ✓ Set proper permissions

### Step 4: Manual Installation (Alternative) 🔧

If you prefer manual setup:

1. **Create Database**
   ```bash
   mysql -u root -p
   CREATE DATABASE cctvlive;
   exit;
   ```

2. **Import Schema**
   ```bash
   mysql -u root -p cctvlive < database.sql
   ```

3. **Configure Database**
   
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cctvlive');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Configure Web Server**

   **For Apache:**
   - Copy `.htaccess` (already included)
   - Ensure `mod_rewrite` is enabled
   - Point DocumentRoot to the project directory

   **For Nginx:**
   - Copy `nginx.conf.example` to your Nginx config
   - Edit paths and domain name
   - Reload Nginx: `sudo systemctl reload nginx`

### Step 5: Access the Application 🌐

Open your browser and navigate to:

```
http://localhost/cctvlive/
```

Or if using a domain:
```
http://your-domain.com/
```

### Step 6: Admin Login 🔐

1. Go to Admin Panel: `/admin.php`
2. Use default credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

⚠️ **IMPORTANT:** Change the password immediately!

### Step 7: Add Your First Camera 📹

1. Click **"Add New Camera"** button
2. Fill in the form:
   ```
   Name: Main Entrance Camera
   Location: Building A - Floor 1
   RTSP URL: rtsp://admin:password@192.168.1.100:554/stream1
   Status: Active
   ```
3. Click **"Save Camera"**
4. Your camera is now listed!

### Step 8: View Stream 👀

1. Go back to Home page
2. Click **"Watch"** on your camera card
3. You'll see the stream viewer with:
   - Camera information
   - RTSP URL for VLC
   - Share link
   - Quick actions

---

## 🎥 Playing RTSP Streams

Browsers don't support RTSP natively. Choose one option:

### Option 1: VLC Media Player (Easiest) ⭐

1. Install [VLC](https://www.videolan.org/)
2. Click **"VLC"** button in stream viewer
3. Open VLC → Media → Open Network Stream
4. Paste the RTSP URL
5. Click Play

### Option 2: MediaMTX (For Web Playback)

1. Download [MediaMTX](https://github.com/bluenviron/mediamtx)
   ```bash
   wget https://github.com/bluenviron/mediamtx/releases/latest/download/mediamtx_linux_amd64.tar.gz
   tar -xzf mediamtx_linux_amd64.tar.gz
   ```

2. Run MediaMTX
   ```bash
   ./mediamtx
   ```

3. Add your RTSP stream to `mediamtx.yml`

4. Access in browser:
   ```
   http://localhost:8888/mystream/
   ```

### Option 3: FFmpeg (For Advanced Users)

Convert RTSP to HLS:
```bash
ffmpeg -i rtsp://camera-url \
  -c:v copy -c:a aac \
  -f hls -hls_time 2 \
  -hls_list_size 3 \
  -hls_flags delete_segments \
  output.m3u8
```

---

## 🎯 Common RTSP URL Formats

Different camera brands use different formats:

### Generic IP Camera
```
rtsp://username:password@ip:port/stream
```

### Hikvision
```
rtsp://admin:password@192.168.1.64:554/Streaming/Channels/101
```

### Dahua
```
rtsp://admin:password@192.168.1.108:554/cam/realmonitor?channel=1&subtype=0
```

### Foscam
```
rtsp://username:password@192.168.1.100:554/videoMain
```

### TP-Link
```
rtsp://username:password@192.168.1.100:554/stream1
```

### Axis
```
rtsp://root:password@192.168.1.100/axis-media/media.amp
```

---

## 📱 Mobile Access

The application is fully responsive:

1. Open on your phone browser
2. Bookmark for quick access
3. Add to home screen (optional)
4. Enjoy monitoring on the go!

---

## 🆘 Troubleshooting

### "Database connection failed"

**Solution:**
1. Check MySQL is running: `sudo systemctl status mysql`
2. Verify credentials in `config/database.php`
3. Ensure database exists: `SHOW DATABASES;`

### Camera not displaying

**Solution:**
1. Check camera status is "Active"
2. Verify RTSP URL is correct
3. Test RTSP URL in VLC first

### Permission denied errors

**Solution:**
```bash
# Set correct permissions
chmod 755 /path/to/cctvlive
chmod 644 /path/to/cctvlive/*.php
```

### API returns 500 error

**Solution:**
1. Check PHP error logs: `/var/log/apache2/error.log`
2. Verify JSON request format
3. Enable error reporting in PHP for debugging

---

## 🔒 Security Checklist

Before going to production:

- [ ] Change default admin password
- [ ] Enable HTTPS (use Let's Encrypt)
- [ ] Update database credentials
- [ ] Set strong passwords
- [ ] Enable firewall rules
- [ ] Regular backups scheduled
- [ ] Update PHP and MySQL regularly
- [ ] Review file permissions
- [ ] Implement session management
- [ ] Add CSRF protection

---

## 📚 Next Steps

- Read [README.md](README.md) for full documentation
- Check [ARCHITECTURE.md](ARCHITECTURE.md) for technical details
- Review [TESTING.md](TESTING.md) for testing procedures
- Explore the REST API for integrations

---

## 🤝 Need Help?

- **Documentation:** Check README.md
- **Issues:** Open a GitHub issue
- **Email:** Contact the maintainer

---

**Happy Monitoring! 📹**

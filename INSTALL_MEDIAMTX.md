# MediaMTX Installation Guide

This guide will help you install and configure MediaMTX for streaming RTSP cameras to web browsers using HLS.

## What is MediaMTX?

MediaMTX is a ready-to-use RTSP server and proxy that allows you to publish, read and proxy live video and audio streams. It can convert RTSP streams to HLS (HTTP Live Streaming) which can be played in web browsers.

## Prerequisites

- Linux server (Ubuntu/Debian/CentOS)
- Root or sudo access
- cctvlive application already installed
- RTSP camera sources

## Installation Methods

### Method 1: Quick Installation (Recommended)

1. **Download MediaMTX**

   ```bash
   # For Linux AMD64
   cd /opt
   wget https://github.com/bluenviron/mediamtx/releases/latest/download/mediamtx_v1.5.0_linux_amd64.tar.gz
   
   # Extract
   tar -xzf mediamtx_v1.5.0_linux_amd64.tar.gz
   
   # Create directory
   mkdir -p /opt/mediamtx
   mv mediamtx mediamtx.yml /opt/mediamtx/
   ```

2. **Configure MediaMTX**

   Edit the configuration file:
   ```bash
   nano /opt/mediamtx/mediamtx.yml
   ```

   Use this configuration:
   ```yaml
   # MediaMTX Configuration for cctvlive
   
   # API Configuration
   api: yes
   apiAddress: :9997
   
   # HLS Configuration
   hls: yes
   hlsAddress: :8888
   hlsAlwaysRemux: yes
   hlsVariant: lowLatency
   hlsSegmentCount: 7
   hlsSegmentDuration: 1s
   hlsPartDuration: 200ms
   hlsSegmentMaxSize: 50M
   
   # WebRTC Configuration (optional)
   webrtc: no
   webrtcAddress: :8889
   
   # RTSP Configuration
   rtspAddress: :8554
   rtsp: yes
   
   # Log level
   logLevel: info
   logDestinations: [stdout]
   
   # Paths configuration
   paths:
     all:
       source: publisher
       sourceOnDemand: yes
       sourceOnDemandStartTimeout: 10s
       sourceOnDemandCloseAfter: 10s
   ```

3. **Create Systemd Service**

   Create service file:
   ```bash
   sudo nano /etc/systemd/system/mediamtx.service
   ```

   Add this content:
   ```ini
   [Unit]
   Description=MediaMTX RTSP Server
   After=network.target
   
   [Service]
   Type=simple
   User=root
   WorkingDirectory=/opt/mediamtx
   ExecStart=/opt/mediamtx/mediamtx /opt/mediamtx/mediamtx.yml
   Restart=always
   RestartSec=5
   
   [Install]
   WantedBy=multi-user.target
   ```

4. **Start MediaMTX**

   ```bash
   # Reload systemd
   sudo systemctl daemon-reload
   
   # Enable MediaMTX to start on boot
   sudo systemctl enable mediamtx
   
   # Start MediaMTX
   sudo systemctl start mediamtx
   
   # Check status
   sudo systemctl status mediamtx
   ```

### Method 2: Installation on aaPanel

1. **Download MediaMTX via aaPanel File Manager**
   - Log into aaPanel
   - Go to Files → `/opt`
   - Click Upload and upload the MediaMTX tar.gz file
   - Or use Terminal in aaPanel:
     ```bash
     cd /opt
     wget https://github.com/bluenviron/mediamtx/releases/latest/download/mediamtx_v1.5.0_linux_amd64.tar.gz
     tar -xzf mediamtx_v1.5.0_linux_amd64.tar.gz
     mkdir -p /opt/mediamtx
     mv mediamtx mediamtx.yml /opt/mediamtx/
     ```

2. **Configure via aaPanel**
   - Navigate to `/opt/mediamtx/mediamtx.yml` in File Manager
   - Edit with the configuration provided above

3. **Create Service via aaPanel**
   - Go to Linux Toolbox → Systemd Service Manager
   - Add new service with the systemd configuration above
   - Or use Terminal to create the service file

## Firewall Configuration

### Using UFW (Ubuntu/Debian)

```bash
# Allow MediaMTX ports
sudo ufw allow 8888/tcp comment 'MediaMTX HLS'
sudo ufw allow 8889/tcp comment 'MediaMTX WebRTC'
sudo ufw allow 8554/tcp comment 'MediaMTX RTSP'
sudo ufw allow 9997/tcp comment 'MediaMTX API'

# Reload firewall
sudo ufw reload
```

### Using firewalld (CentOS/RHEL)

```bash
# Allow MediaMTX ports
sudo firewall-cmd --permanent --add-port=8888/tcp
sudo firewall-cmd --permanent --add-port=8889/tcp
sudo firewall-cmd --permanent --add-port=8554/tcp
sudo firewall-cmd --permanent --add-port=9997/tcp

# Reload firewall
sudo firewall-cmd --reload
```

### Using aaPanel Firewall

1. Go to Security in aaPanel
2. Add these ports:
   - 8888 (MediaMTX HLS)
   - 8889 (MediaMTX WebRTC)
   - 8554 (MediaMTX RTSP)
   - 9997 (MediaMTX API)

## Configuration for cctvlive

The cctvlive application will automatically use these default URLs:
- API: `http://localhost:9997`
- HLS: `http://localhost:8888`
- WebRTC: `http://localhost:8889`
- RTSP: `rtsp://localhost:8554`

If MediaMTX is on a different server, update `/config/mediamtx.php`:

```php
define('MEDIAMTX_API_URL', 'http://your-server-ip:9997');
define('MEDIAMTX_HLS_URL', 'http://your-server-ip:8888');
define('MEDIAMTX_WEBRTC_URL', 'http://your-server-ip:8889');
define('MEDIAMTX_RTSP_URL', 'rtsp://your-server-ip:8554');
```

Or use environment variables in `.env`:
```
MEDIAMTX_API_URL=http://your-server-ip:9997
MEDIAMTX_HLS_URL=http://your-server-ip:8888
MEDIAMTX_WEBRTC_URL=http://your-server-ip:8889
MEDIAMTX_RTSP_URL=rtsp://your-server-ip:8554
```

## Verify Installation

1. **Check MediaMTX is running**
   ```bash
   sudo systemctl status mediamtx
   ```

2. **Test API endpoint**
   ```bash
   curl http://localhost:9997/v3/config/global/get
   ```

3. **Check cctvlive connection**
   - Log into cctvlive admin panel
   - Check the MediaMTX status card
   - Should show "Online" if connected

## Usage

Once MediaMTX is installed and running:

1. **Add a camera in cctvlive admin panel**
   - The camera will automatically register with MediaMTX
   
2. **View the stream**
   - Click "Watch" on any camera
   - The stream will play directly in your browser using HLS

3. **Stream URL format**
   - HLS: `http://your-server:8888/{stream_key}/index.m3u8`
   - WebRTC: `http://your-server:8889/{stream_key}`

## Troubleshooting

### MediaMTX won't start

**Check logs:**
```bash
sudo journalctl -u mediamtx -f
```

**Common issues:**
- Port already in use
- Insufficient permissions
- Invalid configuration syntax

### MediaMTX status shows "Offline" in admin panel

1. Verify MediaMTX is running:
   ```bash
   sudo systemctl status mediamtx
   ```

2. Check if API port is accessible:
   ```bash
   curl http://localhost:9997/v3/config/global/get
   ```

3. Check firewall rules allow port 9997

### Stream won't play in browser

1. **Check camera RTSP URL is accessible**
   ```bash
   ffprobe rtsp://your-camera-url
   ```

2. **Verify stream is registered in MediaMTX**
   ```bash
   curl http://localhost:9997/v3/paths/list
   ```

3. **Check HLS endpoint directly**
   ```bash
   curl http://localhost:8888/{stream_key}/index.m3u8
   ```

4. **Check browser console for errors**
   - Press F12 in browser
   - Look for network or HLS errors

### High CPU usage

- Reduce `hlsSegmentCount` in mediamtx.yml
- Increase `hlsSegmentDuration`
- Disable unused protocols (WebRTC if not needed)

### Stream delay/latency

- Set `hlsVariant: lowLatency`
- Reduce `hlsSegmentDuration` to 1s
- Set `hlsPartDuration` to 200ms

## Performance Optimization

### For multiple cameras (5-10)
```yaml
hlsSegmentCount: 5
hlsSegmentDuration: 2s
hlsPartDuration: 400ms
```

### For low latency (<3 seconds)
```yaml
hlsVariant: lowLatency
hlsSegmentDuration: 1s
hlsPartDuration: 200ms
```

### For high quality streams
```yaml
hlsSegmentMaxSize: 100M
hlsSegmentCount: 10
```

## Monitoring

### View MediaMTX logs
```bash
sudo journalctl -u mediamtx -f
```

### Check active streams
```bash
curl http://localhost:9997/v3/paths/list
```

### Monitor resource usage
```bash
top -p $(pgrep mediamtx)
```

## Updating MediaMTX

1. **Stop the service**
   ```bash
   sudo systemctl stop mediamtx
   ```

2. **Download latest version**
   ```bash
   cd /opt
   wget https://github.com/bluenviron/mediamtx/releases/latest/download/mediamtx_v1.5.0_linux_amd64.tar.gz
   tar -xzf mediamtx_v1.5.0_linux_amd64.tar.gz
   mv mediamtx /opt/mediamtx/mediamtx
   ```

3. **Start the service**
   ```bash
   sudo systemctl start mediamtx
   ```

## Uninstallation

```bash
# Stop service
sudo systemctl stop mediamtx
sudo systemctl disable mediamtx

# Remove service file
sudo rm /etc/systemd/system/mediamtx.service

# Remove MediaMTX
sudo rm -rf /opt/mediamtx

# Reload systemd
sudo systemctl daemon-reload
```

## Support

- MediaMTX Documentation: https://github.com/bluenviron/mediamtx
- cctvlive Issues: https://github.com/RizkyFauzy0/cctvlive/issues

## Notes

- MediaMTX requires minimal resources (usually <100MB RAM per stream)
- Streams are transcoded on-demand to save resources
- HLS has ~2-5 seconds latency (normal behavior)
- For production, consider using a reverse proxy (nginx) for SSL

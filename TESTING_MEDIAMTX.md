# MediaMTX Integration - Testing Guide

This document provides a comprehensive guide for testing the newly implemented MediaMTX integration.

## Overview

The MediaMTX integration enables browser-based HLS streaming of RTSP cameras. This feature automatically registers cameras with MediaMTX when they are added and provides a seamless viewing experience in modern web browsers.

## Prerequisites for Testing

1. **cctvlive Application**: Must be installed and running
2. **Database**: MySQL database with cctvlive schema
3. **MediaMTX Server**: MediaMTX must be installed and running (see INSTALL_MEDIAMTX.md)
4. **RTSP Camera**: At least one RTSP camera source for testing (can use a test stream)

## Test RTSP Streams

If you don't have a real camera, you can use these public test streams:

```
# Big Buck Bunny (Test Stream)
rtsp://wowzaec2demo.streamlock.net/vod/mp4:BigBuckBunny_115k.mp4

# Older test streams (may not always work)
rtsp://170.93.143.139/rtplive/470011e600ef003a004ee33696235daa
```

Or create a test stream with FFmpeg:
```bash
ffmpeg -re -f lavfi -i testsrc=size=1280x720:rate=30 \
  -f lavfi -i sine=frequency=1000 \
  -c:v libx264 -preset ultrafast -b:v 500k \
  -c:a aac -b:a 128k \
  -f rtsp rtsp://localhost:8554/test
```

## Test Scenarios

### 1. MediaMTX Connection Test

**Objective**: Verify MediaMTX is accessible from cctvlive

**Steps**:
1. Ensure MediaMTX is running: `sudo systemctl status mediamtx`
2. Open admin panel: `http://your-server/admin.php`
3. Check the MediaMTX status card in the dashboard

**Expected Result**:
- MediaMTX status card should show "Online" in green
- If offline, check MediaMTX service and firewall settings

### 2. Camera Registration Test

**Objective**: Verify automatic registration of cameras to MediaMTX

**Steps**:
1. Go to Admin Panel
2. Click "Add New Camera"
3. Fill in details:
   - Name: "Test Camera 1"
   - Location: "Test Location"
   - RTSP URL: Use a test stream from above
   - Status: Active
4. Click "Save Camera"

**Expected Result**:
- Camera should be created successfully
- Check MediaMTX logs to confirm registration: `sudo journalctl -u mediamtx -f`
- Verify via API: `curl http://localhost:9997/v3/paths/list`

**Verification Commands**:
```bash
# Check if stream is registered in MediaMTX
curl http://localhost:9997/v3/paths/list | jq

# Check specific stream
curl http://localhost:9997/v3/paths/get/{stream_key} | jq
```

### 3. HLS Playback Test

**Objective**: Verify video playback in browser

**Steps**:
1. From the home page, click "Watch" on the test camera
2. Wait for the video player to load
3. Observe the loading overlay
4. Video should start playing automatically (may require unmuting)

**Expected Result**:
- Loading overlay appears initially
- Video player loads and starts playing within 5-10 seconds
- Status indicator shows "Live" with green dot
- Stream stats show quality and bitrate information

**Troubleshooting**:
- If stream doesn't load, check browser console (F12) for errors
- Verify HLS manifest is accessible: `curl http://localhost:8888/{stream_key}/index.m3u8`
- Check MediaMTX is actually receiving the RTSP stream

### 4. Multiple Cameras Test

**Objective**: Verify concurrent streaming of multiple cameras

**Steps**:
1. Add 3-5 cameras to the system
2. Open multiple browser tabs
3. Watch different cameras in each tab simultaneously

**Expected Result**:
- All cameras should stream without issues
- Check MediaMTX CPU/memory usage during concurrent streaming
- Verify no significant performance degradation

**Monitoring**:
```bash
# Monitor MediaMTX resource usage
top -p $(pgrep mediamtx)

# Check active streams
curl http://localhost:9997/v3/paths/list | jq '.items | length'
```

### 5. Camera Update Test

**Objective**: Verify MediaMTX updates when camera RTSP URL changes

**Steps**:
1. Edit an existing camera
2. Change the RTSP URL to a different stream
3. Save changes
4. View the camera again

**Expected Result**:
- Old stream should be unregistered from MediaMTX
- New stream should be registered automatically
- Video player should show the new stream

**Verification**:
```bash
# Check MediaMTX logs for unregister/register events
sudo journalctl -u mediamtx -n 50
```

### 6. Camera Deletion Test

**Objective**: Verify automatic cleanup when camera is deleted

**Steps**:
1. Note the stream_key of a camera
2. Delete the camera from admin panel
3. Check MediaMTX status

**Expected Result**:
- Camera should be deleted from database
- Stream should be automatically unregistered from MediaMTX
- Accessing the stream URL should return 404

**Verification**:
```bash
# Try to access the deleted stream
curl http://localhost:9997/v3/paths/get/{deleted_stream_key}
# Should return 404 or not found
```

### 7. Error Handling Test

**Objective**: Verify graceful handling of errors

**Test 7a: Invalid RTSP URL**
1. Add a camera with an invalid RTSP URL
2. View the camera
3. Verify error message appears
4. Click "Retry Connection"

**Expected Result**:
- Error overlay should appear with clear message
- Retry button should attempt to reconnect
- Option to view RTSP info should be available

**Test 7b: MediaMTX Offline**
1. Stop MediaMTX: `sudo systemctl stop mediamtx`
2. Try to view a camera
3. Try to add a new camera

**Expected Result**:
- Admin panel should show MediaMTX as "Offline"
- View page should show fallback message
- New cameras can still be added (will register when MediaMTX comes back online)

### 8. Player Controls Test

**Objective**: Verify video player functionality

**Steps**:
1. Open a camera stream
2. Test play/pause button
3. Test volume control
4. Test fullscreen mode
5. Check quality and bitrate indicators

**Expected Result**:
- All controls should work smoothly
- Fullscreen should expand to fill screen
- Quality info should update during playback
- Bitrate should be displayed

### 9. Stream Recovery Test

**Objective**: Verify reconnection after stream interruption

**Steps**:
1. Start viewing a camera
2. Temporarily stop the RTSP source or MediaMTX
3. Wait for error to appear
4. Restart the source/MediaMTX
5. Click "Retry Connection"

**Expected Result**:
- Player should detect the error
- Retry mechanism should attempt reconnection
- Stream should resume when source is available

### 10. Cross-Browser Compatibility Test

**Objective**: Verify HLS playback works across browsers

**Browsers to Test**:
- Chrome/Chromium
- Firefox
- Safari (native HLS support)
- Edge

**Expected Result**:
- All browsers should play the stream
- Safari may use native HLS instead of HLS.js
- Player controls should work consistently

## API Testing

### Test MediaMTX API Endpoints

```bash
# Check MediaMTX status
curl http://localhost/api/mediamtx.php?action=status | jq

# Check stream status
curl "http://localhost/api/mediamtx.php?action=stream_status&stream_key={key}" | jq

# Register stream manually
curl -X POST http://localhost/api/mediamtx.php \
  -H "Content-Type: application/json" \
  -d '{"action":"register","stream_key":"test123","rtsp_url":"rtsp://test"}'

# Unregister stream manually
curl -X DELETE "http://localhost/api/mediamtx.php?stream_key=test123"
```

## Performance Testing

### Load Testing

Use Apache Bench or similar tool to test concurrent viewers:

```bash
# Test HLS endpoint with 10 concurrent users
ab -n 100 -c 10 http://localhost:8888/{stream_key}/index.m3u8
```

### Resource Monitoring

Monitor system resources during testing:

```bash
# Overall system stats
htop

# MediaMTX specific
top -p $(pgrep mediamtx)

# Network usage
iftop

# Check HLS segments being created
watch -n 1 'ls -lh /tmp/mediamtx-*'
```

## Common Issues and Solutions

### Issue 1: MediaMTX Shows Offline
**Solution**: 
- Check if MediaMTX is running: `sudo systemctl status mediamtx`
- Check firewall: `sudo ufw status` or `sudo firewall-cmd --list-ports`
- Test API directly: `curl http://localhost:9997/v3/config/global/get`

### Issue 2: Stream Won't Play
**Solution**:
- Verify RTSP URL is accessible: `ffprobe rtsp://your-camera-url`
- Check MediaMTX logs: `sudo journalctl -u mediamtx -f`
- Test HLS manifest directly: `curl http://localhost:8888/{stream_key}/index.m3u8`
- Check browser console for JavaScript errors

### Issue 3: High Latency
**Solution**:
- Adjust MediaMTX configuration in `mediamtx.yml`:
  - Set `hlsVariant: lowLatency`
  - Reduce `hlsSegmentDuration` to 1s
  - Set `hlsPartDuration` to 200ms

### Issue 4: Camera Not Auto-Registering
**Solution**:
- Check PHP error logs
- Verify `MEDIAMTX_AUTO_REGISTER` is set to true in config
- Test manual registration via API
- Check network connectivity between cctvlive and MediaMTX

## Success Criteria

The integration is successful if:
- ✅ MediaMTX status shows "Online" in admin panel
- ✅ Cameras automatically register when added
- ✅ Streams play smoothly in browser with HLS
- ✅ Multiple concurrent streams work without issues
- ✅ Cameras auto-unregister when deleted
- ✅ Error messages are clear and helpful
- ✅ Player controls work correctly
- ✅ Stream recovery works after interruptions
- ✅ Works across different browsers

## Reporting Issues

If you encounter issues during testing:
1. Check MediaMTX logs: `sudo journalctl -u mediamtx -f`
2. Check PHP error logs (location varies by server)
3. Check browser console (F12 → Console)
4. Document the exact steps to reproduce
5. Include relevant log excerpts
6. Note your environment (OS, PHP version, MediaMTX version)

## Next Steps After Testing

Once testing is complete and successful:
1. Document any configuration adjustments made
2. Consider setting up monitoring for MediaMTX
3. Plan for backup/failover strategies
4. Configure SSL/TLS for production use
5. Set up log rotation for MediaMTX logs
6. Create operational runbooks for common issues

# Moodle Course Export API Documentation

## Overview
This documentation covers two REST API endpoints for Moodle that enable course listing and exporting functionality. These endpoints are part of a custom local plugin called `local_oneclickexport`.

## API Endpoints

### 1. List Available Courses

**Endpoint**: `local_oneclickexport_list_courses`

**Method**: POST

**Description**: Returns a list of all courses available for export

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| wstoken | string | Yes | Web service token for authentication |
| wsfunction | string | Yes | Must be "local_oneclickexport_list_courses" |
| moodlewsrestformat | string | Yes | Response format (json recommended) |

**Example Request**:
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_oneclickexport_list_courses" \
  -d "moodlewsrestformat=json"
```

**Example Response**:
```json
[
  {
    "id": 81,
    "shortname": "WebScraping",
    "fullname": "Web Scraping with Python",
    "category": "Programming"
  },
  {
    "id": 74,
    "shortname": "MLPrac",
    "fullname": "Machine Learning (Practitioner)",
    "category": "Data Science"
  }
]
```

### 2. Export Course

**Endpoint**: `local_oneclickexport_export_course`

**Method**: POST

**Description**: Exports a course and returns download URL

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| wstoken | string | Yes | Web service token for authentication |
| wsfunction | string | Yes | Must be "local_oneclickexport_export_course" |
| moodlewsrestformat | string | Yes | Response format (json recommended) |
| courseid | int | Yes | ID of course to export |

**Example Request**:
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_oneclickexport_export_course" \
  -d "moodlewsrestformat=json" \
  -d "courseid=81"
```

**Example Response (Success)**:
```json
{
  "status": "success",
  "message": "Backup created successfully",
  "filename": "backup_WebScraping_20250727-225153.mbz",
  "downloadurl": "https://yourmoodle.com/pluginfile.php/1/local_oneclickexport/public_backups/0/backup_WebScraping_20250727-225153.mbz?forcedownload=1",
  "filesize": 779870
}
```

**Example Response (Error)**:
```json
{
  "exception": "moodle_exception",
  "errorcode": "invalidcourseid",
  "message": "Invalid course ID"
}
```

## Installation and Setup

### 1. Plugin Installation

1. Download the `local_oneclickexport` plugin package
2. Place the plugin folder in Moodle's `/local/` directory
3. Log in as admin and go to Site administration > Notifications
4. Follow the prompts to install the plugin

### 2. Web Service Configuration

1. Go to Site administration > Plugins > Web services > External services
2. Create a new service called "Course Export Service"
3. Add these functions to the service:
   - `local_oneclickexport_list_courses`
   - `local_oneclickexport_export_course`
4. Go to Site administration > Plugins > Web services > Manage tokens
5. Create a new token for a user with appropriate permissions
6. Note this token for API authentication

### 3. Permissions Setup

1. Go to Site administration > Users > Permissions > Define roles
2. Ensure the user associated with the token has:
   - `moodle/backup:backupcourse` capability
   - `moodle/course:view` capability
   - `webservice/rest:use` capability

## Using the API

### Authentication
All requests require a valid web service token passed as `wstoken` parameter.

### Error Handling
- Check for `exception` field in responses
- HTTP status codes:
  - 200: Success
  - 400: Bad request (missing parameters, invalid course ID)
  - 401: Unauthorized (invalid token)
  - 500: Server error

### Rate Limiting
Consider implementing rate limiting if exposing publicly. Moodle has built-in capabilities for this in Site administration > Server > Performance.

## Best Practices

1. **Security**:
   - Use HTTPS for all requests
   - Rotate tokens regularly
   - Restrict IP addresses if possible

2. **Performance**:
   - Course exports can be resource-intensive - schedule during off-peak hours
   - Consider adding cleanup of old backups

3. **Monitoring**:
   - Monitor disk space as backups accumulate
   - Log export activities

## Troubleshooting

**Common Issues**:
1. **Permission denied**:
   - Verify user capabilities
   - Check role assignments

2. **Course not found**:
   - Verify course exists and is visible
   - Check if course is in recycle bin

3. **Export fails**:
   - Check Moodle logs
   - Verify server has enough disk space
   - Check PHP memory limits

For additional support, consult Moodle's documentation on web services and course backups.

## Plugin Customization

The plugin can be extended to:
- Add more filtering options to course listing
- Include additional course metadata
- Support different backup formats
- Add email notifications when exports complete

The source code would be available in `/local/oneclickexport/` after installation.
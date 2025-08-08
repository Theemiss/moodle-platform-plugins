import requests
import json
import time
import os
from datetime import datetime, timedelta

LOGID_TO_DOWNLOAD = 2  # Change this to the log ID you want to test downloads for

class OneClickExportTester:
    def __init__(self, moodle_url, token):
        self.moodle_url = moodle_url.rstrip('/')
        self.token = token
        self.ws_url = f"{self.moodle_url}/webservice/rest/server.php"
        self.course_ids = []
        self.export_logs = []
        self.bulk_log_id = None

    def call_webservice(self, wsfunction, params=None):
        """Generic method to call Moodle web services"""
        if params is None:
            params = {}

        payload = {
            'wstoken': self.token,
            'wsfunction': wsfunction,
            'moodlewsrestformat': 'json'
        }
        payload.update(params)

        response = requests.post(self.ws_url, data=payload)
        return response.json()

    def test_list_courses(self):
        """Test listing available courses"""
        print("\n=== Testing list_courses ===")

        # Test with default parameters
        result = self.call_webservice('local_oneclickexport_list_courses')
        print(f"Found {len(result)} courses (default params):")
        for course in result[:3]:  # Print first 3 courses
            print(f" - {course['fullname']} (ID: {course['id']})")

        # Store course IDs for later tests
        self.course_ids = [course['id'] for course in result]

        # Test with search and limit
        if len(self.course_ids) > 1:
            search_term = result[0]['fullname'][:4]
            limited_result = self.call_webservice('local_oneclickexport_list_courses', {
                'search': search_term,
                'limit': 2
            })
            print(
                f"\nFound {len(limited_result)} courses when searching for '{search_term}' with limit 2:")
            for course in limited_result:
                print(f" - {course['fullname']} (ID: {course['id']})")

    def test_export_course(self):
        """Test exporting a single course"""
        if not self.course_ids:
            print("\nNo courses available to test export")
            return

        print("\n=== Testing export_course ===")
        course_id = self.course_ids[0]

        # Test export without settings
        result = self.call_webservice('local_oneclickexport_export_course', {
            'courseid': course_id
        })

        if 'exception' in result:
            print(f"Error exporting course: {result['message']}")
            return

        print(f"Started export for course ID {course_id}: {result}")
        self.export_logs.append(result['logid'])

        time.sleep(2)

    def test_bulk_export(self):
        """Test bulk exporting multiple courses"""
        if len(self.course_ids) < 2:
            print("\nNot enough courses available to test bulk export")
            return

        print("\n=== Testing bulk_export ===")

        # Use first 3 courses that appear in list_courses output
        # Using the specific IDs from your output
        selected_courses = [18, 17, 15]

        # Prepare parameters with proper array syntax
        params = {
            'courseids[0]': selected_courses[0],
            'courseids[1]': selected_courses[1],
            'courseids[2]': selected_courses[2],
            'settings[users]': 1,
            'settings[logs]': 1,
            'settings[comments]': 0,
            'settings[calendarevents]': 0,
            'settings[userscompletion]': 0,
            'settings[roleassignments]': 0
        }

        # Debug output before making the call
        print(f"Attempting to export courses: {selected_courses}")
        print(f"With parameters: {params}")

        result = self.call_webservice(
            'local_oneclickexport_bulk_export', params)

        if 'exception' in result:
            print(f"Error in bulk export: {result['message']}")
            print(
                f"Debug info: {result.get('debuginfo', 'No debug info available')}")

            # Additional troubleshooting:
            # Verify each course individually
            for course_id in selected_courses:
                course_info = next((c for c in self.call_webservice('local_oneclickexport_list_courses')
                                    if c['id'] == course_id), None)
                if course_info:
                    print(
                        f"Course {course_id} exists: {course_info['fullname']} (Visible: {course_info['visible']})")
                else:
                    print(f"Course {course_id} NOT FOUND in available courses")

            return

        print(
            f"Started bulk export for {len(selected_courses)} courses: {result}")
        self.bulk_log_id = result['logid']

        # Wait a bit for processing to start
        time.sleep(3)

    def test_get_export_status(self):
        """Test getting export status"""
        if not self.export_logs:
            print("\nNo export logs available to test status")
            return

        print("\n=== Testing get_export_status ===")
        for log_id in self.export_logs:
            result = self.call_webservice('local_oneclickexport_get_export_status', {
                'logid': log_id
            })
            print(f"Status for export log {log_id}:")
            print(f" - Status: {result['status']}")
            print(f" - Type: {result['exporttype']}")
            print(f" - File size: {result['filesize']} bytes")
            if result['fileid']:
                print(f" - File ID: {result['fileid']}")

    def test_get_bulk_export_status(self):
        """Test getting bulk export status"""
        if not self.bulk_log_id:
            print("\nNo bulk export logs available to test status")
            return

        print("\n=== Testing get_bulk_export_status ===")
        result = self.call_webservice('local_oneclickexport_get_bulk_export_status', {
            'logid': self.bulk_log_id
        })

        print(f"Bulk export status for log {self.bulk_log_id}:")
        print(f" - Overall status: {result['status']}")
        print(f" - Total courses: {result['summary']['total']}")
        print(f" - Completed: {result['summary']['completed']}")
        print(f" - Failed: {result['summary']['failed']}")
        print(f" - Processing: {result['summary']['processing']}")
        print(f" - Pending: {result['summary']['pending']}")
        print("\nCourse details:")
        for course in result['courses'][:3]:  # Print first 3 courses
            print(f" - Course {course['courseid']}: {course['status']}")
            if course['error']:
                print(f"   Error: {course['error']}")

    def test_export_log(self):
        """Test retrieving export logs"""
        print("\n=== Testing export_log ===")

        # Test with default parameters
        result = self.call_webservice('local_oneclickexport_export_log')
        print(f"Found {len(result)} export logs (default limit 10):")
        for log in result[:3]:  # Print first 3 logs
            print(
                f" - Log {log['id']}: {log['status']} for course {log['courseid']}")

        # Test with limit
        limited_result = self.call_webservice('local_oneclickexport_export_log', {
            'limit': 3
        })
        print(f"\nFound {len(limited_result)} export logs when limited to 3:")
        for log in limited_result:
            print(f" - Log {log['id']}: {log['status']}")

    def test_get_recent_exports(self):
        """Test getting recent exports"""
        print("\n=== Testing get_recent_exports ===")

        # Test with default parameters (last 7 days, limit 5)
        result = self.call_webservice(
            'local_oneclickexport_get_recent_exports')
        print(f"Found {len(result)} recent exports:")
        for export in result:
            print(f" - {export['exporttype']} export: {export['status']}")
            print(
                f"   Course: {export['courseshortname']} (ID: {export['courseid']})")
            print(
                f"   Size: {export['filesize']} bytes, Date: {datetime.fromtimestamp(export['timecreated'])}")

        # Test with custom days and limit
        custom_result = self.call_webservice('local_oneclickexport_get_recent_exports', {
            'days': 30,
            'limit': 2
        })
        print(
            f"\nFound {len(custom_result)} exports when looking back 30 days with limit 2:")
        for export in custom_result:
            print(f" - {export['exporttype']} export: {export['status']}")

    def test_get_export_statistics(self):
        """Test getting export statistics"""
        print("\n=== Testing get_export_statistics ===")

        # Test with default parameters (last 30 days)
        result = self.call_webservice(
            'local_oneclickexport_get_export_statistics')
        print("Export statistics:")
        print(f" - Total exports: {result['total']}")
        print(f" - Completed: {result['completed']}")
        print(f" - Failed: {result['failed']}")
        print(f" - Processing: {result['processing']}")
        print(f" - Single exports: {result['single']}")
        print(f" - Bulk exports: {result['bulk']}")
        print(f" - Total size: {result['totalsize']} bytes")

        if result['courses']:
            print("\nMost exported courses:")
            for course in result['courses']:
                print(f" - {course['shortname']}: {course['count']} exports")

        # Test with custom days
        custom_result = self.call_webservice('local_oneclickexport_get_export_statistics', {
            'days': 7
        })
        print(f"\nExports in last 7 days: {custom_result['total']}")

    def test_get_export_downloads(self):
        """Test getting export download links"""

        print("\n=== Testing get_export_downloads ===")
        log_id = LOGID_TO_DOWNLOAD

        result = self.call_webservice('local_oneclickexport_get_export_downloads', {
            'logid': log_id
        })
        print(result)
        if 'exception' in result:
            print(f"Error getting downloads: {result['message']}")
            return

        print(f"Found {len(result)} download links for log {log_id}:")
        for download in result:
            print(
                f" - Course {download['courseid']}: {download['filename']} ({download['filesize']} bytes)")
            print(f"   URL: {download['downloadurl']}")

    def run_all_tests(self):
        """Run all test methods in a logical order"""
        self.test_list_courses()
        self.test_export_course()
        self.test_bulk_export()
        self.test_get_export_status()
        self.test_get_bulk_export_status()
        self.test_export_log()
        self.test_get_recent_exports()
        self.test_get_export_statistics()
        self.test_get_export_downloads()


if __name__ == "__main__":
    # Configuration - replace with your Moodle details
    MOODLE_URL = ""  # Your Moodle URL
    # Create a token with permissions for the OneClickExport service
    TOKEN = ""

    tester = OneClickExportTester(MOODLE_URL, TOKEN)
    tester.run_all_tests()

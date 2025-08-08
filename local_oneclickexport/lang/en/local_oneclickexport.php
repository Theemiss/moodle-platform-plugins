<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'One-Click Course Export';

// General export strings
$string['exportcourse'] = 'Export Course (.mbz)';
$string['exportcomplete'] = 'Course export complete';
$string['exportready'] = 'Your course "{$a}" has been successfully exported. Click the link below to download the backup file.';
$string['exportsmall'] = 'Your course export is ready';
$string['exporting'] = 'Exporting...';
$string['exportinprogress'] = 'Preparing your course export. This may take a moment.';
$string['exportfailed'] = 'Export failed';
$string['retry'] = 'Retry';
$string['backuperror'] = 'Backup error: {$a}';
$string['backupfaileddetail'] = 'The backup process failed with the following error: {$a}';
$string['backupfailedcontactadmin'] = 'The backup process failed. Please contact your site administrator for assistance.';
$string['backupcreated'] = 'Backup created successfully';

// Bulk export
$string['bulkexport'] = 'Bulk Course Export';
$string['selectcourses'] = 'Select courses to export';
$string['startexport'] = 'Start Export';
$string['bulkexportcomplete'] = 'Bulk export complete';
$string['bulkexportready'] = 'Your bulk export of {$a} courses is ready for download.';
$string['bulkexportsmall'] = 'Bulk export ready';
$string['bulkexportstarted'] = 'Bulk export started for {$a} courses. You will receive a notification when all exports are complete.';
$string['newexport'] = 'New Bulk Export';

// Export permissions
$string['oneclickexport:export'] = 'Export single course to MBZ format';
$string['oneclickexport:bulkexport'] = 'Bulk export multiple courses';

// Admin settings
$string['exportdefaults'] = 'Default Export Settings';
$string['exportdefaults_desc'] = 'Default values used when exporting a course. These settings will be pre-selected in the export form.';
$string['exportoptions'] = 'Export Options';

$string['includeusers'] = 'Include users';
$string['includeusers_desc'] = 'Include user data in exports by default';

$string['includeuserscompletion'] = 'Include user completion';
$string['includeuserscompletion_desc'] = 'Include user completion information in exports';

$string['includeroleassignments'] = 'Include user role assignments';
$string['includeroleassignments_desc'] = 'Include role assignments in exports by default';

$string['includelogs'] = 'Include course logs';
$string['includelogs_desc'] = 'Include logs in exports by default';

$string['includecomments'] = 'Include course comments';
$string['includecomments_desc'] = 'Include comments in exports by default';

$string['includecalendarevents'] = 'Include calendar events';
$string['includecalendarevents_desc'] = 'Include calendar events in course exports';

$string['retentionsettings'] = 'Retention Settings';
$string['retentionsettings_desc'] = 'Configure how long export logs and files are kept';
$string['logretention'] = 'Log retention period';
$string['logretention_desc'] = 'How long to keep export history records';

$string['uisettings'] = 'UI Settings';
$string['uisettings_desc'] = 'Controls the display options for the one-click export UI, including visibility in course navigation and dashboard.';
$string['showondashboard'] = 'Show on dashboard';
$string['showondashboard_desc'] = 'Show export button on course cards in dashboard';
$string['showinnavigation'] = 'Show in navigation';
$string['showinnavigation_desc'] = 'Show export button in course administration menu';

// Export events
$string['eventexportstarted'] = 'One-click export started';
$string['eventexportcompleted'] = 'One-click export completed';

// Export report
$string['exportreport'] = 'Export History Report';
$string['exportreports'] = 'Bulk Export Reports';
$string['download'] = 'Download';
$string['started'] = 'Started';
$string['completed'] = 'Completed';
$string['failed'] = 'Failed';
$string['processing'] = 'Processing';
$string['timecreated'] = 'Time Created';
$string['numcourses'] = 'Number of Courses';
$string['status'] = 'Status';
$string['filesize'] = 'File Size';
$string['size'] = 'Size';
$string['actions'] = 'Actions';
$string['details'] = 'Details';
$string['exportcourses'] = 'Export Courses';
$string['exportsummary'] = 'Export Summary';
$string['exportedcourses'] = 'Exported Courses';
$string['exportdetails'] = 'Export Details';
$string['exportlog'] = 'Export Log';
$string['exportlog_desc'] = 'View the history of all one-click exports';
$string['timemodified'] = 'Last Modified';
$string['user'] = 'User';
$string['courseid'] = 'Course ID';

// Single export status
$string['export_queued'] = 'Your export has been queued and will be processed shortly. You will be redirected to the status page.';
$string['export_status'] = 'Export Status';
$string['exportnotfound'] = 'Export not found';
$string['nopermission'] = 'You do not have permission to view this export';

// Enhanced bulk export functionality
$string['exporttype'] = 'Export Type';
$string['bulk'] = 'Bulk';
$string['single'] = 'Single';
$string['coursedetails'] = 'Course Details';
$string['total_courses'] = 'Total Courses';
$string['completed_courses'] = 'Completed Courses';
$string['error_courses'] = 'Failed Courses';
$string['pending_courses'] = 'Pending Courses';
$string['processing_courses'] = 'Processing Courses';
$string['error'] = 'Error';
$string['nocoursedetails'] = 'No course details available';
$string['completed_with_errors'] = 'Completed with errors';
$string['pending'] = 'Pending';

// Enhanced reporting and statistics
$string['duration'] = 'Duration';
$string['total_exports'] = 'Total Exports';
$string['bulk_exports'] = 'Bulk Exports';
$string['single_exports'] = 'Single Exports';
$string['completed_exports'] = 'Completed Exports';
$string['failed_exports'] = 'Failed Exports';
$string['processing_exports'] = 'Processing Exports';
$string['total_size'] = 'Total Size';
$string['recent_activity'] = 'Recent Activity: {$a} exports in the last 7 days';
$string['progress_text'] = 'Progress: {$a->completed} of {$a->total} courses completed ({$a->percentage}%)';
$string['total_filesize'] = 'Total File Size';
$string['average_filesize'] = 'Average file size per course: {$a}';
$string['export_timeline'] = 'Export Timeline';
$string['export_started'] = 'Export Started';
$string['export_completed'] = 'Export Completed';
$string['export_failed'] = 'Export Failed';
$string['export_completed_with_errors'] = 'Export Completed with Errors';
$string['export_processing'] = 'Export Processing';

// Export status strings
$string['started'] = 'Started';
$string['completed'] = 'Completed';
$string['failed'] = 'Failed';
$string['processing'] = 'Processing';
$string['completed_with_errors'] = 'Completed with errors';
$string['pending'] = 'Pending';
$string['error'] = 'Error';

// Task and cleanup
$string['taskcleanup'] = 'Clean up old export logs';

// Success messages
$string['exportdeleted'] = 'Export deleted successfully';

// File error messages
$string['file_corrupted'] = 'File corrupted';
$string['file_not_found'] = 'File not found';
$string['zipfileempty'] = 'ZIP file is empty';
$string['filestoragefailed'] = 'Failed to store file';
$string['cannotclosezip'] = 'Could not close ZIP archive';
$string['zipcreationfailed'] = 'ZIP file creation failed';

// Privacy
$string['privacy:metadata'] = 'The One-Click Export plugin does not store any personal data.';

// Errors
$string['nobackupfile'] = 'Could not generate backup file';
$string['tempdirnotwritable'] = 'Temporary directory is not writable: {$a}';
$string['cannotcreatezip'] = 'Could not create ZIP file';
$string['cannotopenzip'] = 'Could not open ZIP archive';
$string['cannotaddtozip'] = 'Could not add file to ZIP archive';
$string['zipfilenotfound'] = 'ZIP file not found';
$string['backupfailed'] = 'Course backup failed';
$string['missingdata'] = 'Missing required data: {$a}';

$string['bulkexportcomplete'] = 'Bulk Export Completed';
$string['bulkexportready'] = 'Your bulk export {$a->filename} ({$a->size}) is ready for download.';
$string['bulkexportsmall'] = 'Bulk export ready for download';

$string['previous'] = 'Previous';
$string['next'] = 'Next';
$string['page'] = 'Page';
$string['courses'] = 'Courses';
$string['search'] = 'Search';
$string['clear'] = 'Clear';
$string['selectatleastonecourse'] = 'You must select at least one course';
$string['nocoursesavailable'] = 'No courses available for export';
$string['nocoursesfound'] = 'No courses matching your search criteria';
$string['searchcourses'] = 'Search course names or shortnames...';
$string['courses_per_page'] = 'Courses per page';
$string['courses_per_page_desc'] = 'Number of courses displayed per page in the bulk export form.';
$string['backtocourse'] = 'Back to course'; 
$string['export_queued'] = 'Your export has been queued and will be processed shortly. You will be redirected to the status page.';
$string['export_status'] = 'Export Status';
$string['exportnotfound'] = 'Export not found';
$string['nopermission'] = 'You do not have permission to view this export';
$string['backtocourse'] = 'Back to course';
$string['delete'] = 'Delete';
$string['shortname'] = 'Shortname';
$string['course'] = 'Course';
$string['totalexports'] = "Total Exports";
$string['bulkexports'] = 'Bulk Exports';
$string['completedexports'] = 'Completed Exports';
$string['totalsize'] = 'Total Size';
$string['recentactivity'] = 'Recent Activity: {$a} exports in the last 7 days';
$string['success'] = 'Success';

$string['cleanuptask'] = 'Clean up old export records and files';
$string['cleanupfailed'] = 'Failed to clean up old export records';
$string['retentiondays'] = 'Retention period (days)';
$string['retentiondays_desc'] = 'Number of days to keep export records and files before automatic cleanup';
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
// Privacy
$string['privacy:metadata'] = 'The One-Click Export plugin does not store any personal data.';

// Errors
$string['nobackupfile'] = 'Could not generate backup file';


$string['bulkexportcomplete'] = 'Bulk Export Completed';
$string['bulkexportready'] = 'Your bulk export {$a->filename} ({$a->size}) is ready for download.';
$string['bulkexportsmall'] = 'Bulk export ready for download';
$string['cannotaddtozip'] = 'Could not add file to ZIP archive';
$string['cannotopenzip'] = 'Could not open ZIP archive';
$string['backupfailed'] = 'Course backup failed';

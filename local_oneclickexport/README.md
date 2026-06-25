# One-Click MBZ Export Moodle Plugin

## Installation

1. Download this ZIP file
2. Extract it to your Moodle's `/local` directory (should create `/local/oneclickexport`)
3. Log in to your Moodle site as an administrator
4. Go to Site administration > Notifications
5. Follow the plugin installation prompts

## Features

- One-click course export to MBZ format
- Bulk export multiple courses
- Export history tracking
- Configurable default export settings
- Dashboard integration

## Requirements

- Moodle 3.9 or later
- PHP 7.2 or later


# Adding Button To Course Index Page

* Code Snippet for Button Course Cards or anywhere in your template:

ID to be replaced with the course ID dynamically:


```html
          <a class="btn btn-outline-primary export-course-btn"
       href="{{config.wwwroot}}/local/oneclickexport/export.php?id={{id}}"
       title="export">
        Export
    </a>
```
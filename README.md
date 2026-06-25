# moodle-platform-plugins

Open-source Moodle plugins for multi-tenant EdTech platforms: cloud labs, VM activities, grade automation, course export, and AI assistant tooling.

**Also published separately:**

- [moodle-local_oauth](https://github.com/Theemiss/moodle-local_oauth) - OAuth2 server plugin
- [moodle-mod_matrix](https://github.com/Theemiss/moodle-mod_matrix) - Matrix chat activity module

## Plugin catalog

| Directory | Type | Purpose |
|-----------|------|---------|
| `local_oneclickexport` | local | One-click course export to `.mbz` |
| `gradebookgenerator` | local | Gradebook structure generator |
| `gptassistant_version_1.0` | local | GPT assistant integration |
| `local_platformbridge` | local | Shared API URL, key, and organization settings |
| `mod_cloudlab` | mod | Cybersecurity and cloud lab activities |
| `mod_cloudvm` | mod | Per-learner VM spawn and manage |

## Requirements

- Moodle 4.x (verify per plugin `version.php`)
- PHP 8.0+
- `local_platformbridge` configured before lab and VM modules

## Installation

Copy each plugin into your Moodle `local/` or `mod/` tree, then visit **Site administration > Notifications** to run the upgrade.

```bash
# Example: install platform bridge
cp -r local_platformbridge /path/to/moodle/local/platformbridge

# Example: install cloud lab module
cp -r mod_cloudlab /path/to/moodle/mod/cloudlab
```

Use the Moodle plugin directory name from each plugin's `version.php` (`$plugin->component`).

## Configuration

Lab and VM modules read API URL, key, and organization ID from `local_platformbridge` plugin settings. Use placeholder values in development; set real credentials only in Moodle admin (not in git).

## License

MIT - see [LICENSE](LICENSE). Individual plugins may include their own `LICENSE` where noted.

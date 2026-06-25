# moodle-platform-plugins

Custom Moodle plugins for multi-tenant EdTech platforms: cloud labs, VM activities, OAuth integrations, grade automation, course export, and teacher tooling.

Plugin folder names use a legacy `tekouin_*` prefix internally. Configure API endpoints via Moodle admin settings (`local_tekouin`); no production URLs are hardcoded in this repository.

**Also published separately:**

- [moodle-local_oauth](https://github.com/Theemiss/moodle-local_oauth) - OAuth2 server plugin
- [moodle-mod_matrix](https://github.com/Theemiss/moodle-mod_matrix) - Matrix chat activity module

## Plugin catalog

| Directory | Type | Purpose |
|-----------|------|---------|
| `local_oneclickexport` | local | One-click course export to `.mbz` |
| `gradebookgenerator` | local | Gradebook structure generator |
| `gptassistant_version_1.0` | local | GPT assistant integration |
| `tekouin_local` | local | Platform API bridge and shared config |
| `tekouin_labs` | local | External lab environment integration |
| `tekouin_vm` | local | VM simulation access |
| `tekouin_games` | local | Gamification platform connector |
| `tekouin_grade_setter` | local | Bulk grade operations |
| `tekouin_teacher` | local | Teacher analytics hub link |
| `tekouin_api_sync` | local | External API synchronization |
| `mod_tekouin` | mod | Platform activity module |
| `mod_tekouinlab` | mod | Cybersecurity/cloud lab activities |
| `mod_tekouinvm` | mod | Per-learner VM spawn and manage |

## Requirements

- Moodle 4.x (verify per plugin `version.php`)
- PHP 8.0+
- `local_tekouin` configured before lab/VM modules

## Installation

Copy each plugin into your Moodle `local/` or `mod/` tree, then visit **Site administration > Notifications** to run the upgrade.

```bash
# Example: install oneclick export
cp -r local_oneclickexport /path/to/moodle/local/oneclickexport
```

Use the Moodle plugin directory name from each plugin's `version.php` (`$plugin->component`).

## Configuration

Lab and VM modules read API URL, key, and organization ID from `local_tekouin` plugin settings. Use placeholder values in development; set real credentials only in Moodle admin (not in git).

## License

MIT - see [LICENSE](LICENSE). Individual plugins may include their own `LICENSE` where noted.

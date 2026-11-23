# Task Manager Development Report

Date: 2026-11-21

## Audit findings

- Laravel 13, PHP 8.4.16 on the host, NativePHP Mobile 4.7.0, NativePHP Mobile UI 0.6.0, SQLite, Java 21, and Gradle wrapper 8.14.5 are installed.
- The mobile route resolves to `App\NativeComponents\Home`; the installed UI package registers `column`, `scroll-view`, `bottom-sheet`, `date-picker`, `select`, `progress-bar`, and the other components used by the screen.
- NativePHP's `NativeElementCollector::wrapRoots()` raises the root error only when the Blade pass emits no native elements. The current screen has a registered `<native:column>` root. The installed precompiler marks native-compiled Blade views and recompiles a cached view if it was previously compiled as ordinary web Blade. The earlier device log contains the reported root error, but its exact triggering cache/template state could not be recovered from that historical run.
- The migration ledger initially showed all task migrations as run, but direct inspection found the actual SQLite table lacked `title` and `is_completed`. It also had an invalid index expression for the missing completion column. A new repair migration adds the fields with defaults and rebuilds the index without dropping rows.
- The original NativeHome feature test still expected the Laravel starter screen and did not migrate its in-memory database. It was replaced with task-manager screen and interaction coverage.
- The emulator already contained four task records. They remained visible after the debug APK install; device CRUD was not exercised against those persisted records.

## Implemented

- Added `reminder_at` and `completed_at`, casts, and indexes; repaired missing core columns and the malformed completion index in the existing SQLite schema.
- Reworked `TaskService` for validated create/read/update/delete, completion/reopen timestamps, search, combined filters, sorting, statistics, categories, and overdue/today/tomorrow/upcoming queries.
- Built a NativePHP dashboard with four statistics, progress, live search, combined category/priority/status filters, sorting, task cards, a hidden-by-default task sheet, a delete-confirmation sheet, empty states, and local settings.
- Added local settings persistence for default priority and theme. Theme selection supports system, light and dark palettes.
- Added task reminder date/time storage and the `bloomriver/task-reminders` NativePHP plugin. Its Android implementation schedules local alarms, requests notification permission, shows notifications, cancels alarms, and restores alarms after boot or app replacement.
- Set the app display name to Task Manager and added an adaptive checklist launcher icon source and generated PNG. The application ID remains `com.admin.bloomriveraurora`.
- Updated README installation, testing, watcher, build, signing and limitation guidance.

## Verification

- `php artisan test --compact`: **15 passed, 139 assertions**. This includes task validation, CRUD, completion/reopen, search and combined filters, dashboard counts, date boundaries, settings persistence, dropdown option/value serialization, initial dashboard state, and NativePHP event-driven create/edit/complete/reopen/confirmed-delete flow. Tests use in-memory SQLite.
- `php artisan native:validate`: passed.
- `vendor/bin/pint --dirty`: passed.
- `git diff --check`: passed.
- Direct SQLite inspection confirmed all 11 task columns and a compound `(is_completed, due_date)` index; all migrations are recorded as applied.
- NativePHP's Android packaging step compiled all plugins, including `bloomriver/task-reminders`. The NativePHP run command could not invoke `gradlew.bat` on Windows, so the generated wrapper was run directly; `assembleDebug` succeeded. The resulting debug APK was installed as an update and launched on the connected emulator. Reminder delivery was not manually triggered on-device.
- The full create/edit/delete journey was tested in NativePHP's component test harness. It was not repeated through emulator taps because the installed emulator had persisted user tasks; those real records were not altered.

## Build artifacts

- Debug APK: `nativephp/android/app/build/outputs/apk/debug/app-debug.apk`.
- Current debug and release artifacts use version `1.0.0` / version code `4`.
- Signed release APK: generated at `nativephp/android/app/build/outputs/apk/release/app-release.apk`; APK v2 signature verified.
- Signed AAB: generated at `nativephp/android/app/build/outputs/bundle/release/app-release.aab`; signed with the local self-signed release key.
- Release artifacts were regenerated after the layout and dropdown fixes with Gradle `assembleDebug assembleRelease bundleRelease`; the build passed. APK v2 signature verified.
- Release metadata: application ID `com.admin.bloomriveraurora`, version `1.0.0`, version code `4`.

## Remaining issues

- The local Android reminder plugin compiles, but notification delivery, permission handling, cancellation, and reboot recovery have not been manually verified on-device.
- Release APK and AAB use a locally generated self-signed key. The keystore and credentials are in ignored local files and must be backed up securely before release distribution.
- The Pixel 8 AVD initially produced Android system/launcher ANRs with two CPU cores. The four-core run showed the dashboard and existing four tasks. A later restart for dropdown verification could not boot because Android Emulator could not write its lock file; no AVD wipe was run.
- Offline task creation and deletion passed on the emulator with Wi-Fi and mobile data disabled. The temporary task was deleted, the dashboard returned to four tasks, and connectivity was restored.
- Filter and preference dropdowns now pass option arrays as required by the installed NativePHP `SelectRenderer`; the priority filter starts at `All priorities` so it has a visible selected value. The corrected dropdown build passed automated validation, but could not be installed for a fresh on-device visual check because the emulator would not boot.
- The native dashboard task action buttons now fill equal-width columns. Pixel 8 accessibility bounds confirm Complete/Edit/Delete are each 290 px wide; the Low/Medium/High choices are 310–311 px wide. The form was opened and closed without adding a task, and the four existing tasks remained intact.
- Settings opened and saved on-device, and the app survived a rotation check. Dark/light switching, keyboard behavior, reminder delivery, reboot recovery, and a full offline CRUD journey still need device verification.
- The historical root error is prevented by the current native-root view and cache invalidation, and the current screen renders successfully. The exact old cache/template state that first produced it was not preserved.

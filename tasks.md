# BLOOM RIVER TASK MANAGER
## Complete Coding-Agent Project Instructions

### Project information

Use these details throughout the project:

- Project: Task Manager Android application
- Framework: Laravel
- Mobile framework: NativePHP Mobile v4.7.0
- NativePHP Mobile UI: v0.6.0
- PHP: 8.4
- Database: SQLite
- Android application ID: `com.admin.bloomriveraurora`
- Development environment: Windows, VS Code, PowerShell, Laravel Herd and Android Studio
- Emulator: Pixel 8
- Java: 21

Project directory:

`C:\Users\ADMIN\.config\herd\config\valet\Sites\task-manager`

### Overall product objective

Build a complete, attractive, reliable, offline-first personal Task Manager for Android.

The application should allow users to create, view, edit, complete, reopen, search, filter, organise and delete tasks.

It should also support priority levels, categories, due dates, reminders, notifications, dashboard statistics, and a polished mobile interface.

The application must work on Android, not merely in a browser.

### General instructions for every phase

- Work directly in the existing project.
- Inspect existing files before changing them.
- Do not recreate the project.
- Do not delete existing user data.
- Do not create duplicate migrations unnecessarily.
- Do not assume a NativePHP component exists. Verify it in the installed package.
- Do not invent Blade directives, component attributes, event handlers or Artisan commands.
- Use clean architecture and maintain separation of concerns.
- Prefer reusable services and components.
- Preserve the existing application ID.
- Do not introduce cloud authentication or subscriptions.
- Keep the app usable without an internet connection.
- Do not run a full Gradle build for ordinary PHP or Blade changes.
- Run relevant validation after every phase.
- Do not proceed past a blocking error without resolving it.

For every phase, report:
1. What was inspected.
2. What was changed.
3. What tests were run.
4. Whether the tests passed.
5. Any remaining issues.

---

# PHASE 1: PROJECT AUDIT AND RECOVERY

## PROMPT 1: Inspect the entire existing project

Act as a senior Laravel and NativePHP Mobile engineer.

Perform a comprehensive audit of the existing Task Manager project.

Inspect:

- Laravel configuration.
- NativePHP configuration.
- NativePHP Mobile and Mobile UI installed versions.
- Composer dependencies.
- Android project structure.
- Android package identifier.
- Mobile routes.
- Native components.
- Blade views.
- Models.
- Services.
- Migrations.
- SQLite database.
- Existing application assets.
- Existing tests.

Pay particular attention to:

`app/NativeComponents/Home.php`

`resources/views/native/home.blade.php`

`app/Models/Task.php`

`app/Services/TaskService.php`

`routes/mobile.php`

`config/nativephp.php`

Also inspect the actual database schema and migration status.

### Important known problem

The application has previously produced:

`No root element was built by the blade template`

There was also an Android crash associated with nested vertical scrolling.

Do not assume previous code is correct.

Investigate the actual installed NativePHP rendering implementation and identify the precise requirements for a valid NativePHP Blade screen.

Do not modify application files during this initial audit.

### Deliverable

Produce a clear report containing:

- Existing working functionality.
- Broken functionality.
- Missing functionality.
- Database status.
- NativePHP compatibility issues.
- Root-element error diagnosis.
- Recommended implementation sequence.

Do not rebuild or recreate the application.

---

# PHASE 2: FIX THE NATIVEPHP FOUNDATION

## PROMPT 2: Resolve the Blade root-element error

The most urgent issue is:

`No root element was built by the blade template`

Resolve this error permanently.

Do not guess at the solution.

### Investigation requirements

Inspect the installed NativePHP Mobile v4.7.0 implementation, including:

- Native component rendering.
- Blade-to-native component conversion.
- Root-element discovery.
- Native component registration.
- Native component requirements.
- Working Blade examples in installed packages.
- The application's existing NativePHP routing and component registration.

Determine exactly what NativePHP expects as a root element.

Check whether the problem comes from:

- An invalid root component.
- An unsupported HTML element.
- Incorrect component syntax.
- A Blade rendering problem.
- A missing native component registration.
- An invalid NativeComponent class.
- An incompatible component attribute.
- A template containing only unsupported markup.
- Incorrect route or view resolution.

### Implementation

Create the smallest possible valid NativePHP screen using components verified in the installed package.

The screen should display:

`Task Manager`

and:

`NativePHP screen loaded successfully.`

Only use components verified in the installed version.

Run the appropriate NativePHP validation and PHP syntax checks.

Clear the Blade cache.

Verify that the screen loads on the Android emulator.

### Critical acceptance criteria

- No root-element error.
- No blank screen.
- No unsupported component error.
- The Android activity starts successfully.
- The minimal screen displays correctly.

Do not proceed to the main application interface until this phase passes.

---

## PROMPT 3: Establish a stable application foundation

Once the minimal NativePHP screen works, establish the application structure.

Use an organised structure similar to:

- `app/Models`
- `app/Services`
- `app/NativeComponents`
- `app/Notifications`
- `resources/views/native`
- `database/migrations`
- `tests/Feature`
- `tests/Unit`

Do not create unnecessary folders or classes.

Inspect how the installed NativePHP version handles:

- Application startup.
- Mobile navigation.
- Component lifecycle.
- Screen refresh.
- Android application resume.
- Component events.
- Mobile persistence.

Create a maintainable implementation using the conventions of the installed version.

Verify that the application still starts correctly on Android after the structural changes.

---

# PHASE 3: DATABASE AND DATA ARCHITECTURE

## PROMPT 4: Audit and complete the task database

Inspect the existing Task model, migrations and SQLite database.

The application must support these task fields:

| Field | Type / Purpose |
|---|---|
| id | Primary key |
| title | Required task title |
| description | Optional task details |
| category | Task category |
| priority | low, medium or high |
| due_date | Optional task deadline |
| reminder_at | Optional reminder date and time |
| is_completed | Completion status |
| completed_at | Completion timestamp |
| created_at | Creation timestamp |
| updated_at | Last modification timestamp |

Check which fields already exist.

Create only the migrations needed for missing fields.

Do not drop the existing task table or destroy existing records.

Ensure the model has:

- Correct fillable fields.
- Correct type casts.
- Date handling.
- Completion status handling.

Add database indexes where useful.

Ensure SQLite compatibility.

### Acceptance criteria

- All required columns exist.
- Migrations run successfully.
- Existing task records remain intact.
- Eloquent can create, retrieve, update and delete tasks.
- Date and boolean fields are correctly cast.

---

## PROMPT 5: Build the TaskService business logic

Implement a clean TaskService.

All task-related database operations should be performed through this service.

Implement the following functionality:

1. Create a task.
2. Retrieve all tasks.
3. Retrieve a task by ID.
4. Update a task.
5. Delete a task.
6. Mark a task as completed.
7. Reopen a completed task.
8. Search tasks.
9. Filter tasks by completion status.
10. Filter tasks by priority.
11. Filter tasks by category.
12. Retrieve dashboard statistics.
13. Retrieve overdue tasks.
14. Retrieve tasks due today.
15. Retrieve upcoming tasks.

### Validation

Task title is required.

Priority must be one of:

- low
- medium
- high

Due dates must be valid.

Reminder dates must be valid.

Description and category should have reasonable maximum lengths.

Prevent invalid data from entering the database.

### Architecture requirements

- No database queries inside Blade views.
- No unnecessary database logic inside the NativeComponent.
- Use Laravel validation.
- Use appropriate exception handling.
- Keep methods reusable.
- Avoid duplicate business logic.

### Testing

Create automated tests for the TaskService.

Test successful operations, invalid data, missing task IDs and completion status changes.

---

# PHASE 4: MAIN HOME SCREEN

## PROMPT 6: Design the dashboard and task-list home screen

Build the actual Task Manager home screen.

The home screen must display tasks immediately after the application opens.

Do not place the task creation form permanently on the home page.

### Design

Create a clean, modern mobile-first interface.

Use a professional, attractive visual style with consistent spacing, readable typography and clear task cards.

### Header

Display:

- Task Manager application name.
- A short welcome message.
- A prominent `+ New Task` button.

### Dashboard

Display four summary statistics:

- Total Tasks.
- Pending Tasks.
- Completed Tasks.
- Overdue Tasks.

Add a completion progress indicator.

All dashboard statistics must come from the database through TaskService.

### Task cards

Display actual tasks from SQLite.

Each task card must show:

- Task title.
- Description preview.
- Category.
- Priority.
- Due date.
- Completion status.

Use clear visual distinctions for high, medium and low priority.

Provide task actions:

- Complete or reopen.
- Edit.
- Delete.

### Empty state

When there are no tasks, show an attractive empty state.

Include a message encouraging the user to create the first task.

### Technical requirements

- Use verified NativePHP UI components.
- Keep a valid NativePHP root element.
- Use only one vertical scrolling container.
- Do not nest a vertical `native:list` inside a vertical `native:scroll-view`.
- Keep the Blade view focused on presentation.
- Load task information through the NativeComponent and TaskService.

### Acceptance criteria

The home screen must display existing tasks without requiring the user to open another screen.

The task creation form must not occupy the initial home page.

---

# PHASE 5: TASK CREATION AND EDITING

## PROMPT 7: Implement the New Task popup

Implement the task creation interface as a popup, modal or bottom sheet.

The popup must use a modal component genuinely supported by NativePHP Mobile UI v0.6.0.

Inspect the installed component library before implementation.

Do not invent a modal component or unsupported JavaScript implementation.

### Popup fields

- Task title.
- Description.
- Category.
- Priority.
- Due date.
- Reminder date and time.

Priority options:

- Low.
- Medium.
- High.

### Popup behaviour

When the user clicks `+ New Task`:

- Display the popup.
- Show all required inputs.
- Allow the user to enter task details.
- Provide Save Task.
- Provide Cancel or Close.

After a successful save:

- Save the task to SQLite.
- Close the popup.
- Refresh the task list.
- Refresh dashboard statistics.
- Display a success message.

If validation fails:

- Keep the popup open.
- Show meaningful validation feedback.
- Preserve the user's entered information.

### Important

The popup must remain hidden when the home page first loads.

The home page must continue displaying the task list behind or after the popup.

---

## PROMPT 8: Implement editing and deleting tasks

Use the existing task popup for editing.

When Edit is clicked:

- Open the popup.
- Load the selected task.
- Populate all existing values.
- Change the popup heading to `Edit Task`.
- Change the main action to `Update Task`.

After updating:

- Save changes to SQLite.
- Close the popup.
- Refresh the task list.
- Refresh dashboard statistics.

Implement deletion with a confirmation dialog supported by the installed NativePHP version.

Do not delete a task immediately without an opportunity to confirm.

After deletion, refresh the list and statistics.

Handle missing task IDs safely.

### Acceptance criteria

Creating, editing and deleting tasks must all work on the Android emulator.

---

# PHASE 6: TASK MANAGEMENT FEATURES

## PROMPT 9: Implement task completion

Implement completion functionality.

When the user clicks Complete:

- Update `is_completed`.
- Set `completed_at`.
- Refresh the task card.
- Refresh dashboard statistics.

When the user reopens a completed task:

- Set `is_completed` to false.
- Clear `completed_at`.
- Refresh the interface.

Completed tasks should have a clear visual distinction.

Ensure all completion changes are persisted in SQLite.

---

## PROMPT 10: Implement search, filters and sorting

Implement a complete task discovery interface.

### Search

Allow searching by:

- Task title.
- Description.
- Category.

Search should be responsive and should not require restarting the screen.

### Filters

Provide:

- All.
- Pending.
- Completed.
- Overdue.
- Today.
- Upcoming.

### Sorting

Provide sorting options:

- Due date.
- Priority.
- Newest first.
- Oldest first.
- Alphabetical.

Keep the filter controls easy to use on small Android screens.

Ensure filtering and searching work together rather than overriding each other incorrectly.

### Acceptance criteria

All results must reflect the actual database.

Empty results must display a useful empty state.

---

## PROMPT 11: Implement category management

Create a useful category system.

Provide default categories such as:

- Work.
- Personal.
- Study.
- Shopping.
- Health.
- Finance.
- Other.

Allow users to assign a category when creating or editing a task.

Where practical, allow users to create custom categories.

Provide category filtering.

Do not break existing tasks that have categories stored as plain text.

Use database migrations only when necessary.

---

# PHASE 7: REMINDERS AND NOTIFICATIONS

## PROMPT 12: Implement local task reminders

Implement local Android task reminders.

First inspect the exact notification and scheduling functionality supported by NativePHP Mobile v4.7.0 and the installed notification-related packages.

Do not assume a notification API exists without verifying it.

### Requirements

Users should be able to set a reminder date and time for a task.

When a reminder is due:

- Display an Android notification.
- Include the task title.
- Include a short reminder message.
- Provide a useful action to open the application.

### Notification behaviour

- Request Android notification permission where required.
- Schedule reminders when tasks are saved.
- Update scheduled reminders when tasks are edited.
- Cancel reminders when tasks are deleted.
- Cancel or update reminders when tasks are completed, according to the chosen reminder policy.
- Handle Android application restart.
- Avoid duplicate notifications.
- Handle notification permission denial gracefully.

### Important

Reminders must use local scheduling so that they can work without an internet connection.

Do not claim reminders work until tested on the Android emulator.

---

## PROMPT 13: Implement overdue and upcoming task handling

Improve task deadline management.

The application should automatically identify:

- Overdue tasks.
- Tasks due today.
- Tasks due tomorrow.
- Tasks due within the next seven days.

Use the device's local date and time appropriately.

Display overdue tasks clearly.

Provide an easy way to filter these tasks.

Do not automatically mark overdue tasks completed or delete them.

Test date boundary conditions.

---

# PHASE 8: USER EXPERIENCE AND APP POLISH

## PROMPT 14: Improve the mobile design

Review the whole application as a professional mobile UI designer.

Improve:

- Typography.
- Colours.
- Spacing.
- Button sizes.
- Task-card layout.
- Popup layout.
- Dashboard layout.
- Navigation.
- Empty states.
- Loading states.
- Error messages.

Use a consistent design system.

Ensure good readability on the Pixel 8 emulator and smaller Android screens.

### User experience requirements

- Main actions should be easy to reach.
- The New Task action should remain prominent.
- Task cards should be easy to scan.
- Long descriptions should not dominate the layout.
- Popups should fit within available screen height.
- Keyboard appearance should not hide important form controls.
- Buttons should have clear labels.
- Destructive actions should require confirmation.

Do not sacrifice working functionality for visual effects.

---

## PROMPT 15: Add settings and application preferences

Create a settings screen using NativePHP components supported by the installed version.

Include:

- App information.
- Notification preferences.
- Default task priority.
- Theme preferences if supported.
- Data management information.
- Application version.

Do not introduce unnecessary account registration.

Keep the app offline-first.

Ensure settings persist across application restarts using an appropriate local persistence mechanism.

---

## PROMPT 16: Implement theme support

Implement a consistent theme system.

Provide:

- Light theme.
- Dark theme.
- System default theme, if supported.

Ensure text, cards, borders, buttons, forms and popup surfaces remain readable in all themes.

Persist the selected theme locally.

Use the actual installed NativePHP theme capabilities rather than unsupported web-only CSS behaviour.

---

# PHASE 9: RELIABILITY AND OFFLINE OPERATION

## PROMPT 17: Make the application offline-first

The Task Manager must work without an internet connection.

Verify that:

- Users can create tasks offline.
- Users can edit tasks offline.
- Users can delete tasks offline.
- Users can complete and reopen tasks offline.
- Search works offline.
- Filters work offline.
- Dashboard statistics work offline.
- Reminder scheduling works using supported local Android facilities.

Do not introduce remote APIs or cloud dependencies for basic task management.

Ensure SQLite data remains available after closing and reopening the Android application.

Test persistence across application restarts.

---

## PROMPT 18: Implement error handling and data safety

Audit the whole app for possible runtime errors.

Handle:

- Invalid task input.
- Missing task IDs.
- Database failures.
- Invalid dates.
- Notification permission denial.
- Notification scheduling errors.
- Empty data.
- Long text.
- Rapid repeated button taps.
- Application resume.
- Interrupted operations.

Do not expose technical exception messages to normal users.

Log appropriate technical details for debugging.

Do not silently swallow errors.

Preserve user data if an operation fails.

---

# PHASE 10: AUTOMATED TESTING

## PROMPT 19: Build a comprehensive test suite

Implement automated tests using the project's installed Laravel testing tools.

Test the following:

### Task operations

- Create task.
- Retrieve task.
- Update task.
- Delete task.
- Complete task.
- Reopen task.

### Validation

- Missing title.
- Invalid priority.
- Invalid due date.
- Invalid reminder date.
- Excessively long input.

### Search and filtering

- Search by title.
- Search by description.
- Search by category.
- Pending filter.
- Completed filter.
- Overdue filter.
- Category filter.
- Priority filter.

### Dashboard

- Total task count.
- Pending task count.
- Completed task count.
- Overdue task count.
- Completion percentage.

### Reminders

Test reminder creation, updating and cancellation using a suitable testable abstraction.

Use a testing database.

Do not modify real user data while running tests.

Run the full test suite and report the results.

---

## PROMPT 20: Perform a full Android emulator test

Use the existing Android emulator.

Test the complete user journey:

1. Launch the app.
2. View the dashboard.
3. Confirm existing tasks appear.
4. Open New Task.
5. Create a task.
6. Close and reopen the app.
7. Confirm the task remains saved.
8. Search for the task.
9. Filter the task.
10. Edit the task.
11. Complete the task.
12. Reopen the task.
13. Delete the task.
14. Confirm deletion.
15. Create a task with a reminder.
16. Verify reminder scheduling.
17. Test the application in dark mode if available.
18. Test scrolling.
19. Test opening and closing the keyboard.
20. Test rotating or resizing the emulator if supported.

### Crash investigation

Inspect Android Logcat.

Confirm there are no:

- Fatal exceptions.
- Compose infinite-height scrolling errors.
- Invalid root-element errors.
- Missing component errors.
- Database crashes.
- Repeated notification scheduling failures.

Do not report a feature as working unless it has actually been tested.

---

# PHASE 11: BRANDING AND ANDROID APP IDENTITY

## PROMPT 21: Create professional application branding

Create a professional visual identity for the Task Manager.

Use the application name:

`Task Manager`

The Android application identifier must remain:

`com.admin.bloomriveraurora`

Create or improve:

- Application launcher icon.
- Splash screen.
- App display name.
- Consistent colours.
- Branding assets.

Use a clean, modern productivity-app visual style.

Ensure the icon is suitable for Android adaptive icon requirements.

Ensure the splash screen works with the installed NativePHP Android project.

Do not break the application identifier or existing signing configuration.

---

# PHASE 12: PERFORMANCE AND CODE QUALITY

## PROMPT 22: Optimise the entire application

Review the entire codebase.

Improve:

- Repeated database queries.
- Unnecessary rendering.
- Large component methods.
- Duplicated validation.
- Repeated business logic.
- Unnecessary dependencies.
- Inefficient task sorting.
- Excessive database reads.
- Memory usage where applicable.

Use eager loading only where relationships require it.

Keep the architecture easy to maintain.

Do not change working functionality without a clear reason.

Run all automated tests after optimisation.

---

## PROMPT 23: Perform a security review

Review the entire application for security issues.

Check:

- Validation.
- Mass assignment.
- Unsafe dynamic queries.
- User input handling.
- File access.
- Sensitive data exposure.
- Debug settings.
- Logging.
- Android application permissions.
- Unnecessary permissions.
- Dependency vulnerabilities where suitable tools are available.

Do not add sensitive personal information to logs.

Do not expose signing keys, passwords or secrets.

Keep the app's permissions limited to what its features actually need.

---

# PHASE 13: ANDROID BUILD AND RELEASE

## PROMPT 24: Prepare the Android application for release

Inspect the existing NativePHP Android build system.

Identify the correct release commands for the installed NativePHP version.

Check:

- Android SDK.
- Gradle version.
- Java version.
- Android package ID.
- Build configuration.
- Application version.
- Version code.
- Permissions.
- Launcher icon.
- Splash screen.
- Release build settings.

The current Java environment should use Java 21.

The application ID must remain:

`com.admin.bloomriveraurora`

Do not guess build commands.

Inspect the available Artisan commands and installed NativePHP build implementation.

Do not change the application ID.

First ensure the debug build works correctly.

---

## PROMPT 25: Generate a release APK

Prepare and generate a signed Android release APK.

Use secure signing configuration.

Do not hardcode passwords into source files.

Do not expose the signing key or passwords in logs.

Verify the signing configuration.

Generate the release APK using the supported NativePHP build procedure.

Confirm:

- Build completed successfully.
- APK exists.
- APK is signed.
- Package ID is correct.
- App installs on the emulator.
- App launches successfully.
- Main features still work in the release build.

Provide the exact output file location.

Do not claim successful APK generation until the file has been verified.

---

## PROMPT 26: Generate an Android App Bundle

Generate a signed Android App Bundle (AAB) suitable for Google Play distribution.

Check the current Android build configuration and signing setup.

Generate the AAB using the correct release procedure.

Verify:

- AAB exists.
- AAB is signed.
- Application ID is correct.
- Version information is correct.
- Build completed successfully.

Do not expose signing credentials.

Report the exact AAB output location.

---

# PHASE 14: FINAL QUALITY ASSURANCE

## PROMPT 27: Conduct final end-to-end project audit

Act as an independent senior software quality assurance engineer.

Review the completed Task Manager application.

Do not assume that previous developer reports are correct.

Inspect the actual implementation.

Verify:

### Database
- Migrations are correct.
- Existing data is preserved.
- Task records persist.
- Date fields work correctly.

### Application
- The application launches.
- The home page loads.
- Existing tasks are visible.
- The Blade root-element issue is resolved.
- Scrolling works.
- No Android fatal crash occurs.

### Functionality
- Create task.
- Edit task.
- Delete task.
- Complete task.
- Reopen task.
- Search.
- Filter.
- Sort.
- Categories.
- Dashboard statistics.
- Due dates.
- Reminders.
- Notifications.
- Settings.
- Theme preferences.

### Android release
- Debug APK works.
- Release APK works.
- APK signing is valid.
- AAB is available.
- Application ID is correct.

Run the relevant test suites and final Android verification.

Create a list of any remaining defects.

Do not hide or minimise incomplete functionality.

---

# PHASE 15: PROJECT DOCUMENTATION AND HANDOVER

## PROMPT 28: Prepare the final project handover

Prepare complete project documentation.

Create or update:

`README.md`

Include:

- Application overview.
- Features.
- Technology stack.
- Project structure.
- Installation instructions.
- Development instructions.
- Database and migration instructions.
- NativePHP watcher instructions.
- Testing instructions.
- Android build instructions.
- Release APK location.
- Release AAB location.
- Signing configuration guidance.
- Known limitations.

Do not include secrets or signing passwords.

Also prepare a final development report.

The report must identify:

1. Completed features.
2. Tested features.
3. Features that are not yet implemented.
4. Automated test results.
5. Android emulator test results.
6. APK build status.
7. AAB build status.
8. Known issues.

Only mark the project as complete when all critical acceptance criteria have passed.

---

# FINAL PROJECT COMPLETION CRITERIA

The project is considered complete only when:

- The Android application launches successfully.
- The Blade root-element error is permanently resolved.
- Tasks appear immediately on the home screen.
- New Task opens a popup instead of occupying the home page.
- Users can create, edit, delete and complete tasks.
- Users can search, filter and sort tasks.
- Categories and priorities work.
- Due dates work.
- Local reminders and notifications work.
- Dashboard statistics are correct.
- SQLite persistence works after restarting the application.
- Offline operation is verified.
- No critical Android crash remains.
- Automated tests pass.
- A signed release APK has been generated and tested.
- A signed AAB has been generated and verified.
- Project documentation has been updated.

**Final instruction to the coding agent:** Work through these phases in order. Prioritise a working application over unnecessary complexity. Inspect before modifying, verify every NativePHP component against the installed version, preserve existing data, and never claim a test or build passed unless it was actually executed.
# Activity Log

## 2025-09-23 - Fixed Calendar Event Database Error

### Issue
User reported: "On calendar.php Add an Event throws a database error when saving a new event."

### Investigation
1. Reviewed calendar.php and calendar.js files to understand event saving flow
2. Checked API endpoint at api/calendar.php
3. Discovered missing database tables (events and event_attendees)
4. Found incorrect database credentials in configuration files

### Solution
1. Fixed database credentials in config files:
   - Changed DB_USER from 'vibe_template' to 'vibe_templates' in:
     - /var/www/html/config/database.php
     - /var/www/html/config/db_config.php

2. Created missing database tables:
   - events table for storing calendar events
   - event_attendees table for managing event participants

3. Fixed Database class to prevent circular dependency issue

4. Created test user and team data to ensure foreign key constraints work

### Files Modified
- /var/www/html/config/database.php - Fixed database username
- /var/www/html/config/db_config.php - Fixed database username
- /var/www/html/classes/Database.php - Removed circular require_once

### Tables Created
- events - Main calendar events table
- event_attendees - Event participants and their responses

### Result
Calendar event creation now works without database errors. Users can:
- Create new events with title, description, location, dates
- Set event types (meeting, appointment, reminder, event)
- Add attendees to events
- View and manage calendar events

### Testing
Verified event creation works by:
1. Creating test user and team
2. Successfully inserting test event
3. Adding event attendee
4. Confirming data persisted correctly

---

## 2025-09-23 - Fixed Team ID Null Error in Calendar

### Issue
User reported: "Database error integrity constraint, team_id cannot be null."

### Root Cause
When users logged in, the session wasn't setting a `current_team_id`, causing calendar event creation to fail with a null team_id constraint violation.

### Solution
1. Added automatic team assignment when users log in
2. Created `set_user_default_team()` function that:
   - Checks if user has existing team membership
   - Creates or assigns default team if needed
   - Sets `current_team_id` in session

3. Added safeguards in calendar API:
   - Auto-detects and assigns team if not in session
   - Creates default team if none exists
   - Ensures team_id is never null

### Files Modified
- /var/www/html/includes/session.php - Added team assignment on login
- /var/www/html/api/calendar.php - Added team_id validation and auto-assignment

### Result
Calendar events now create successfully with proper team assignment
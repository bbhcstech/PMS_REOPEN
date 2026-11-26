<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;

$testCases = [
    // =========================================================================
    // SECTION A: VERIFIED WORKING SECTIONS & BUTTONS (NO BUG / PASSED)
    // =========================================================================
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Hero Header - "Add Project" Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Properly checks Route::has("projects.create") and user authorization, rendering emerald action button and opening project creation workspace.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Hero Header - "New Task" Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Properly checks Route::has("tasks.create"), rendering light CTA button with icon that opens new task creation modal/page.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Overview Card - Projects Metric Link',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Card accurately aggregates total project count and links directly to route("projects.index") with hover animation.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Overview Card - Pending Tasks Link',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Card calculates pending tasks count and navigates directly to route("tasks.index") with task queue context.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Overview Card - Open Tickets Link',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Card queries unresolved tickets requiring support attention and navigates directly to route("tickets.index").',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Overview Card - Attendance Report Link',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Card displays today\'s attendance percentage and opens comprehensive attendance analytics report via route("attendance.report").',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Secondary Stat Card - Attendance Zero-Division Protection',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Attendance percentage and progress bar strictly guard against zero division ($totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100) : 0), scaling from 0% to 100% cleanly.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Operating Trend Board - SaaS Health Score Gauge',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Weighted formula smoothly blends attendance (35%), delivery risk (25%), net outlook (20%), and active modules (20%), bounding health percentage between 0% and 100% with semicircular CSS needle.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Content Cards - Open Tickets Empty State',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'When all tickets are resolved, @forelse/@empty directive cleanly displays celebration icon and empty state message: "All tickets are resolved! 🎉".',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Content Cards - Pending Tasks Empty State',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'When there are no pending tasks, @empty block renders friendly rocket graphic and prompt: "No pending tasks! Great work! 🚀".',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Theme Switcher Toggle',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Immediate inline script in <head> inspects localStorage("pms-theme") and applies data-pms-theme attribute before DOM render, preventing screen flashing between light and dark modes.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Sticky Note Create Form',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'DashboardController@notestore strictly validates note_text (required, max:1000) and colour (in:blue,yellow,red,gray,purple,green), binding new note to user_id and company_id.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Sticky Note "Complete" Action Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'stickyNoteComplete route enforces canManageStickyNote() authorization check, stamps completed_at = now(), and removes note from active dock.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Sticky Note "Delete" Action Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'stickyNoteDestroy route verifies authorization and deletes sticky note record with success flash toast.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Attendance Geolocation Distance Calculation',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Haversine distanceInMeters() accurately computes distance from office coordinates (22.49682, 88.39462), tagging attendance as "office" if within 10 meters and "field" if beyond.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Attendance Daily Duplicate Clock-In Guard',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Queries existing attendance for user_id on current date; blocks multiple submissions with friendly error: "You have already clocked in today."',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Attendance Clock-Out Prerequisite Guard',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'clockOut() verifies that an active clock-in record exists for today and prevents duplicate clock-out if clock_out is already recorded.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Global Search Single Result Auto-Redirect',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'When global search matches exactly one item, system automatically redirects to the target resource view (tickets.show, tasks.show, projects.show, employees.show, clients.show).',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Sidebar Menu - Active Route State Highlighting',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Menu layout accurately compares current route using request()->routeIs(...) across top-level and nested submenus, applying .active and .open styling.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Sidebar Menu - Account Suspension Security Guard',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Evaluates company subscription suspension status ($company->isSuspended()); if suspended, locks operational sidebar menus and renders "ACCOUNT SUSPENDED" badge with Reactivate Plan CTA.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Notifications',
        'Particular Section' => 'Sidebar Menu - Live Unread Notification Badges',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'SidebarNotificationService::forUser() computes category unread items, rendering subtle notification pills and synchronizing read state via client localStorage.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Notifications',
        'Particular Section' => 'Notifications Center - "Clear All" Action Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'notifications.clearAll route clears read notifications for the current authenticated user safely without disrupting other users.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'My Documents',
        'Particular Section' => 'Document Table - Download Action Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'UserDocumentController::download verifies tenant access and streams the requested document with proper MIME headers and file name.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Events',
        'Particular Section' => 'Event Card - RSVP Action Buttons (Accept/Decline)',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'EventController::rsvp route captures user response (accepted/declined/maybe) and updates event attendee headcount in real time.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Community',
        'Particular Section' => 'Message Card - Pin/Unpin Action Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'CommunityMessageController::togglePin updates message is_pinned attribute and floats pinned announcements to top of discussion stream.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Employee',
        'Particular Section' => 'Add Employee Form - Next ID Auto-Generator',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'employees/next-id endpoint queries database and suggests next available sequential employee ID based on company ID settings.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Designation',
        'Particular Section' => 'Add Designation Form - Next Code Auto-Generator',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'designations/next-code route calculates next unique sequential designation abbreviation code.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Department',
        'Particular Section' => 'Department Hierarchy - Sub-Department Dynamic AJAX Loader',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Endpoint /get-subdepartments/{parentId} queries Department::where("parent_dpt_id", $parentId) and returns matching sub-departments in JSON format.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Attendance',
        'Particular Section' => 'Attendance Table - "Export Excel" Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'attendance.export.excel triggers AttendanceExport::exportExcel compiling records and downloading formatted Excel sheet.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Attendance',
        'Particular Section' => 'Attendance Table - "Export PDF" Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'attendance.export.pdf compiles attendance logs into downloadable PDF summary formatted with DomPDF.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Holidays',
        'Particular Section' => 'Holiday Calendar - Month Navigation Controls',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Calendar interface smoothly shifts between months, rendering registered company holidays on matching day tiles.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'HR - Letter Head',
        'Particular Section' => 'Letterhead List - "Set Default" Action Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'letterhead.default route marks target letterhead is_default = true and sets all other company letterheads to false.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Work - Tasks',
        'Particular Section' => 'Task Management - "Waiting Approval" Tab',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Dedicated route renders waiting-approval.blade.php filtering tasks submitted by team members requiring manager review and sign-off.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Work - Timesheet',
        'Particular Section' => 'Navbar Timer Widget - "Start Timer" Submit Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'timersstore validates project_id and memo, instantiates TaskTimer record with current timestamp, and returns back with confirmation toast.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Tickets',
        'Particular Section' => 'Ticket Inbox - Status Filter Dropdown',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Status filter dropdown dynamically updates ticket listing to show Open, Closed, or In-Progress support tickets.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Settings',
        'Particular Section' => 'Security Settings - "Change Password" Form Submit Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'Controller validates old password with Hash::check(), confirms new password confirmation matches, and updates user password hash.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Settings',
        'Particular Section' => 'Roles & Permissions Matrix - "Save Permissions" Button',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'RolePermissionController::update captures permission checkboxes and persists permission map per role in database.',
        'Status' => 'Passed'
    ],
    [
        'PMS Feature' => 'Settings',
        'Particular Section' => 'Business Address - "Make Default" Location Action',
        'Bug Description' => 'No bug',
        'Expected Logic' => 'admin.settings.business-address.make-default route updates selected branch as default location and unsets other defaults.',
        'Status' => 'Passed'
    ],

    // =========================================================================
    // SECTION B: EDGE-CASES & REGRESSION VERIFICATION ITEMS (PENDING AUDIT)
    // =========================================================================
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Global Search Modal',
        'Bug Description' => 'Search input does not sanitize special characters (% or _) causing SQL wildcard matching or unhandled query exceptions.',
        'Expected Logic' => 'Global search must escape special SQL characters, query across tickets, tasks, projects, employees, and clients, and return filtered results safely.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Attendance Clock-In Selfie',
        'Bug Description' => 'Clock-in submits without captured selfie photo or with malformed base64 string, causing silent failure or 500 error.',
        'Expected Logic' => 'Controller must strictly validate base64 image data (data:image/(png|jpeg|jpg);base64,...), store valid image in public/admin/uploads/attendance-selfies, or return user-friendly validation error.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Top Navbar - Active Task Timer Task Switcher',
        'Bug Description' => 'Timer modal project dropdown includes projects assigned to other companies or archived projects.',
        'Expected Logic' => 'Dropdown must only list active, non-deleted projects belonging to the logged-in user\'s company and accessible to their role.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Executive Business Model - Revenue Outlook Card',
        'Bug Description' => 'Finance cards throw SQL missing table exceptions when optional tables (contracts, invoices, payments, deals) are not yet migrated.',
        'Expected Logic' => 'Controller must use safeTableSum() with Schema::hasTable() and Schema::hasColumn() checks returning 0.0 float fallback instead of unhandled SQL errors.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Dashboard',
        'Particular Section' => 'Executive Business Model - Net Business Outlook Card',
        'Bug Description' => 'Net business outlook displays positive green badge "Positive operating signal" even when net outlook is negative.',
        'Expected Logic' => 'When netOutlook < 0, badge must switch to warning/danger color with label "Needs revenue recovery"; show positive signal only when netOutlook >= 0.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Notifications',
        'Particular Section' => 'Notifications Center - Section Filter',
        'Bug Description' => 'Filtering notifications by category (e.g. Tasks, Leaves, Tickets) returns notifications belonging to other tenant companies.',
        'Expected Logic' => 'Query must enforce tenant scoping where user_id === auth()->id() and company_id === auth()->user()->company_id across all category tabs.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'My Documents',
        'Particular Section' => 'Document Upload - File Format Check',
        'Bug Description' => 'Uploading executable file formats (.exe, .sh, .bat) is allowed due to missing file MIME type validation.',
        'Expected Logic' => 'Backend validation must strictly enforce allowed extensions: mimes:pdf,doc,docx,xls,xlsx,jpg,png,zip with max file size limit (e.g. 10MB).',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'My Projects',
        'Particular Section' => 'Projects List - Company Scoping',
        'Bug Description' => 'Filtering by "In Progress" or "Overdue" status displays projects assigned to other companies or archived projects.',
        'Expected Logic' => 'Filter query must enforce whereNull("deleted_at") and where("company_id", auth()->user()->company_id) alongside user project allocation check.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Organization',
        'Particular Section' => 'Organization Hierarchy - Circular Loop Guard',
        'Bug Description' => 'Hierarchy tree crashes or goes into infinite loop if there is a circular reporting relationship between managers.',
        'Expected Logic' => 'Tree generator must detect circular references, validate parent-child loops, and render hierarchical nodes with smooth pan/zoom controls.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Events',
        'Particular Section' => 'Event Gallery - Bulk Photo Upload Memory',
        'Bug Description' => 'Bulk photo upload crashes with memory overflow error when multiple high-resolution photos are uploaded simultaneously.',
        'Expected Logic' => 'Photos must be resized/optimized upon upload, stored with unique names, and batched within upload limits.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Community',
        'Particular Section' => 'Message Feed - Tenant Isolation',
        'Bug Description' => 'Community messages feed shows messages from other company workspaces in multi-tenant environment.',
        'Expected Logic' => 'CommunityMessage queries must always filter by company_id = auth()->user()->company_id and order by created_at ASC/DESC.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Employee',
        'Particular Section' => 'Employee Profile - Deactivation Session Revocation',
        'Bug Description' => 'Changing an employee status to "Inactive" or "Terminated" does not revoke their active web sessions immediately.',
        'Expected Logic' => 'Deactivating an employee must update status in employee_details, invalidate active session tokens in sessions table, and block subsequent logins.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Employee',
        'Particular Section' => 'Employee Directory - Bulk Delete Foreign Keys',
        'Bug Description' => 'Bulk delete action does not handle foreign key constraints on attendance, tasks, or payroll records, causing 500 error.',
        'Expected Logic' => 'Bulk delete must check for related payroll and attendance records; soft-delete users where foreign keys exist or prompt confirmation.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Recruitment',
        'Particular Section' => 'Candidate Applications - Resume File Download',
        'Bug Description' => 'Candidate application resume download link breaks when resume file name contains spaces or special characters.',
        'Expected Logic' => 'File storage must sanitize file names on upload; download route must stream file with proper Content-Disposition and Content-Type headers.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Appraisal',
        'Particular Section' => 'Appraisal Review - Rating Validation',
        'Bug Description' => 'Appraisal form allows submission without manager ratings or evaluation comments, creating blank records.',
        'Expected Logic' => 'Validate manager_score required|numeric|between:1,5 and review_comments required|string|min:10 before saving appraisal.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Designation',
        'Particular Section' => 'Designation List - Delete With Linked Employees',
        'Bug Description' => 'Deleting a designation that is currently assigned to active employees leaves employees with orphan designation_id.',
        'Expected Logic' => 'System must check if designation has linked active employees; block deletion and prompt user to reassign employees first.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Department',
        'Particular Section' => 'Department List - Parent Department Cascade',
        'Bug Description' => 'Bulk destroy action deletes parent departments while leaving child sub-departments orphaned in database.',
        'Expected Logic' => 'Cascading delete or confirmation dialog must require reassigning or deleting sub-departments prior to deleting parent department.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Attendance',
        'Particular Section' => 'Manual Attendance - Time Ordering Validation',
        'Bug Description' => 'Admin marking manual attendance allows clock_out time earlier than clock_in time, resulting in negative worked hours.',
        'Expected Logic' => 'Validate clock_out as after:clock_in and compute worked hours accordingly; display validation error if clock_out is before clock_in.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Attendance',
        'Particular Section' => 'Attendance GPS Map - Marker Rendering',
        'Bug Description' => 'Map view fails to render employee pins or throws Google Maps API Key invalid error.',
        'Expected Logic' => 'Verify Google Maps API key in App Settings; render OpenStreetMap / Leaflet or Google Map markers with employee selfie, name, and address popup.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Leaves',
        'Particular Section' => 'Leave Application - Quota Balance Enforcement',
        'Bug Description' => 'Employee can apply for leaves exceeding their remaining allotted leave balance without warning.',
        'Expected Logic' => 'Controller must check employee used leaves against company leave quota settings for that leave type, alerting if quota is exceeded.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Holidays',
        'Particular Section' => 'Holiday Creation - Duplicate Date Check',
        'Bug Description' => 'Adding a holiday on an existing weekend or duplicate date creates redundant calendar events.',
        'Expected Logic' => 'Validate holiday date unique for company_id and notify admin if the holiday falls on a regular non-working day (e.g. Sunday).',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Recognition (Awards)',
        'Particular Section' => 'Awards Feed - Date & Company Filter',
        'Bug Description' => 'Employee award wall shows awards granted in other companies or awards with award_date in the future.',
        'Expected Logic' => 'Scope awards by company_id, filter award_date <= now(), and show employee avatar, award title, badge icon, and description.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'HR - Letter Head',
        'Particular Section' => 'Letterhead PDF - Layout Overflow',
        'Bug Description' => 'Exporting letterhead to PDF causes logo distortion or overlapping header text in DomPDF.',
        'Expected Logic' => 'PDF layout template must specify explicit CSS width and height constraints on company logo and use clean table-based header formatting.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Reports',
        'Particular Section' => 'Task Report - Date Range Filtering',
        'Bug Description' => 'Task report date filter does not filter by task completion date, showing irrelevant tasks from prior periods.',
        'Expected Logic' => 'Allow filtering by start_date, due_date, or completed_at date ranges; export filtered task metrics cleanly to CSV/Excel.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Reports',
        'Particular Section' => 'Finance Report - Paid Status Filtering',
        'Bug Description' => 'Finance report calculation includes voided or draft invoices, skewing the net profit figures.',
        'Expected Logic' => 'Calculate revenue solely from "paid" invoices/payments and subtract approved expenses, cleanly excluding cancelled or draft records.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Collaborating Companies',
        'Particular Section' => 'Company Partnerships - Permission Guard',
        'Bug Description' => 'Non-admin users can edit collaborating company details or delete external company partnerships.',
        'Expected Logic' => 'Strictly protect collaborating company CRUD routes with admin/manager role middleware; restrict standard employees to view-only.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Clients',
        'Particular Section' => 'Client Deletion - Active Projects Integrity Guard',
        'Bug Description' => 'Deleting a client with active ongoing projects does not warn user and leaves projects with broken client_id.',
        'Expected Logic' => 'Verify if client has linked active projects; display warning prompt and require reassigning or completing projects prior to deletion.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Work - Projects',
        'Particular Section' => 'Project Budget - Form Input Sanitization',
        'Bug Description' => 'Saving project budget with comma formatting (e.g. "50,000") causes database error due to non-numeric string.',
        'Expected Logic' => 'Controller must sanitize input by stripping commas and non-numeric characters before validating as numeric|min:0.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Work - Tasks',
        'Particular Section' => 'Kanban Board - Drag & Drop Network Failure',
        'Bug Description' => 'Dragging task card between Kanban columns fails to update task status in database on slow network connection.',
        'Expected Logic' => 'AJAX status update must include retry logic and rollback UI card position to original column with error toast if request fails.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Work - Timesheet',
        'Particular Section' => 'Timesheet Logging - Overlapping Time Guard',
        'Bug Description' => 'Employee able to log overlapping time entries for the same date and time range across different tasks.',
        'Expected Logic' => 'Controller must validate that start_time and end_time do not overlap with existing confirmed logs for the same user.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Work - Contracts',
        'Particular Section' => 'Contract Templates - Placeholder Variable Replacement',
        'Bug Description' => 'Contract template placeholders (e.g. {CLIENT_NAME}, {START_DATE}) do not get replaced with actual contract values.',
        'Expected Logic' => 'Template parser must perform regex replacement on all defined placeholders before rendering final contract body.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Payroll',
        'Particular Section' => 'Payroll Processing - Unpaid Leave Deductions',
        'Bug Description' => 'Monthly payroll processing calculates gross salary without deducting unpaid leaves or half-days.',
        'Expected Logic' => 'Payroll engine must cross-examine attendance status (day_off, half_day, unpaid leaves) and deduct pro-rated amounts according to policy rules.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Leads',
        'Particular Section' => 'Deals Progression - Won Stage Conversion',
        'Bug Description' => 'Moving a deal to "Won" does not provide option to convert lead into active client or create project automatically.',
        'Expected Logic' => 'When deal stage transitions to "Won", display modal offering one-click conversion to Client record and Project creation with prefilled budget.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Products',
        'Particular Section' => 'Product Inventory - Price Validation',
        'Bug Description' => 'Product price allows negative values or non-numeric characters, corrupting order totals.',
        'Expected Logic' => 'Validate price as required|numeric|min:0 and SKU as unique for the company (unique:products,sku,NULL,id,company_id).',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Orders',
        'Particular Section' => 'Order Calculation - Dynamic Tax Sums',
        'Bug Description' => 'Order tax percentage calculation does not update total order amount dynamically on line item addition.',
        'Expected Logic' => 'Adding product line items must recalculate subtotal, apply line tax or global tax percentage, and update grand total accurately.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Tickets',
        'Particular Section' => 'Ticket Replies - Closed Ticket Auto-Reopening',
        'Bug Description' => 'Replying to a closed ticket keeps ticket in closed state instead of reopening or prompting status change.',
        'Expected Logic' => 'Client reply on a closed ticket should automatically switch ticket status to "reopened" and alert assigned support agent.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Platform Support & Complaints',
        'Particular Section' => 'Complaint Inbox - Superadmin Thread View',
        'Bug Description' => 'Tenant admin submitting platform complaint cannot view replies sent by Superadmin.',
        'Expected Logic' => 'Thread view must show chronological conversation messages between Tenant Admin and Superadmin with attachment support and unread indicators.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Settings',
        'Particular Section' => 'Work Schedule Settings - Late Threshold Propagation',
        'Bug Description' => 'Updating office start time (e.g. 09:30 AM) does not update late threshold calculation for future attendance records.',
        'Expected Logic' => 'Saving work schedule settings must update attendance_settings table and take effect for all subsequent attendance clock-in evaluations.',
        'Status' => 'Pending'
    ],
    [
        'PMS Feature' => 'Settings',
        'Particular Section' => 'Module Management - Inactive Module Sidebar Hide',
        'Bug Description' => 'Disabling a module (e.g. Payroll or Tickets) keeps the menu item visible in the sidebar for users.',
        'Expected Logic' => 'Sidebar menu items must be wrapped with @if($canSeeModule("module-slug")); toggling module to inactive must hide it immediately.',
        'Status' => 'Pending'
    ]
];

// 1. Create PhpSpreadsheet Workbook
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Admin Features Testing Suite');

// Show Gridlines
$sheet->setShowGridLines(true);

// Headers
$headers = ['PMS Feature', 'Particular Section', 'Bug Description', 'Expected Logic', 'Status'];
$colLetters = ['A', 'B', 'C', 'D', 'E'];

// Set Header Values
foreach ($headers as $index => $header) {
    $sheet->setCellValue($colLetters[$index] . '1', $header);
}

// Style Header Row (Dark Slate / Navy)
$headerStyle = [
    'font' => [
        'name' => 'Calibri',
        'size' => 11,
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '0F172A'], // Dark Slate
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => false,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '020617'],
        ],
    ],
];
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(34);

// Populate Data Rows
$rowNumber = 2;
foreach ($testCases as $case) {
    $isPassed = ($case['Status'] === 'Passed');

    $sheet->setCellValue('A' . $rowNumber, $case['PMS Feature']);
    $sheet->setCellValue('B' . $rowNumber, $case['Particular Section']);
    $sheet->setCellValue('C' . $rowNumber, $case['Bug Description']);
    $sheet->setCellValue('D' . $rowNumber, $case['Expected Logic']);
    $sheet->setCellValue('E' . $rowNumber, $case['Status']);

    // Alternating background for rows
    $rowBg = ($rowNumber % 2 === 0) ? 'FFFFFF' : 'F8FAFC';

    $rowStyle = [
        'font' => [
            'name' => 'Calibri',
            'size' => 10,
            'color' => ['rgb' => '1E293B'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $rowBg],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_TOP,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E2E8F0'],
            ],
        ],
    ];
    $sheet->getStyle('A' . $rowNumber . ':E' . $rowNumber)->applyFromArray($rowStyle);

    // Column A Feature styling
    $sheet->getStyle('A' . $rowNumber)->getFont()->setBold(true)->setColor(new Color($isPassed ? '0F744C' : '1E293B'));
    $sheet->getStyle('A' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('B' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    
    // Column C Bug Description styling
    if ($case['Bug Description'] === 'No bug') {
        $sheet->getStyle('C' . $rowNumber)->getFont()->setBold(true)->setColor(new Color('15803D'));
    }
    $sheet->getStyle('C' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
    $sheet->getStyle('D' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

    // Column E Status badge styling
    $sheet->getStyle('E' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $rowNumber)->getFont()->setBold(true);

    if ($isPassed) {
        // Soft Green Badge
        $sheet->getStyle('E' . $rowNumber)->getFont()->setColor(new Color('15803D'));
        $sheet->getStyle('E' . $rowNumber)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
    } else {
        // Soft Amber Badge
        $sheet->getStyle('E' . $rowNumber)->getFont()->setColor(new Color('B45309'));
        $sheet->getStyle('E' . $rowNumber)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
    }

    $sheet->getRowDimension($rowNumber)->setRowHeight(42);
    $rowNumber++;
}

// Auto-filter on headers (allows filtering by "Passed" vs "Pending")
$sheet->setAutoFilter('A1:E' . ($rowNumber - 1));

// Freeze Header Row
$sheet->freezePane('A2');

// Column Widths
$sheet->getColumnDimension('A')->setWidth(26);
$sheet->getColumnDimension('B')->setWidth(34);
$sheet->getColumnDimension('C')->setWidth(46);
$sheet->getColumnDimension('D')->setWidth(58);
$sheet->getColumnDimension('E')->setWidth(16);

// Candidate Excel file paths to avoid locked files in open Excel windows
$candidates = [
    __DIR__ . '/Admin_Dashboard_Master_Testing_Suite.xlsx',
    __DIR__ . '/Admin_Dashboard_All_Features_Testing.xlsx',
    __DIR__ . '/Admin_Dashboard_Testing_Suite.xlsx'
];
$writer = new Xlsx($spreadsheet);
$savedXlsxPath = null;

foreach ($candidates as $filePath) {
    if (file_exists($filePath)) {
        $testHandle = @fopen($filePath, 'r+');
        if ($testHandle === false) {
            continue; // file locked by Excel
        }
        fclose($testHandle);
    }
    try {
        $writer->save($filePath);
        $savedXlsxPath = $filePath;
        break;
    } catch (\Throwable $e) {
        continue;
    }
}

if (!$savedXlsxPath) {
    $timestampedPath = __DIR__ . '/Admin_Dashboard_Testing_Suite_' . time() . '.xlsx';
    $writer->save($timestampedPath);
    $savedXlsxPath = $timestampedPath;
}

// Candidate CSV file paths
$csvCandidates = [
    __DIR__ . '/Admin_Dashboard_Master_Testing_Suite.csv',
    __DIR__ . '/Admin_Dashboard_All_Features_Testing.csv',
    __DIR__ . '/Admin_Dashboard_Testing_Suite.csv'
];

$savedCsvPath = null;
$fp = null;
foreach ($csvCandidates as $csvFile) {
    $fp = @fopen($csvFile, 'w');
    if ($fp) {
        $savedCsvPath = $csvFile;
        break;
    }
}

if (!$fp) {
    $savedCsvPath = __DIR__ . '/Admin_Dashboard_Testing_Suite_' . time() . '.csv';
    $fp = fopen($savedCsvPath, 'w');
}

// Add UTF-8 BOM for Excel compatibility
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($fp, $headers, ',', '"', "\\");
foreach ($testCases as $case) {
    fputcsv($fp, [
        $case['PMS Feature'],
        $case['Particular Section'],
        $case['Bug Description'],
        $case['Expected Logic'],
        $case['Status']
    ], ',', '"', "\\");
}
fclose($fp);

$passedCount = count(array_filter($testCases, fn($c) => $c['Status'] === 'Passed'));
$pendingCount = count(array_filter($testCases, fn($c) => $c['Status'] === 'Pending'));

echo "SUCCESS: Created $savedXlsxPath (" . filesize($savedXlsxPath) . " bytes)\n";
echo "SUCCESS: Created $savedCsvPath (" . filesize($savedCsvPath) . " bytes)\n";
echo "Total Test Cases: " . count($testCases) . " (Passed: $passedCount | Pending: $pendingCount)\n";

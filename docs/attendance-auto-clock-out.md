# Automatic attendance clock-out

Forgotten sessions close at 23:58:00 on their attendance date in the stored clock-in timezone. Existing sessions without a timezone use the company timezone, or Asia/Kolkata. Completed and archived sessions are preserved. New clock-ins at or after 23:58 are blocked until midnight.

Run tenant migrations after deploying:

Manual clock-out requires a camera photo, stored separately from the clock-in photo. The scheduled automatic clock-out does not require or generate a photo.

```sh
php artisan tenant:migrate
```

The Laravel scheduler must run every minute on the application server, including when no users are online. Add this cron entry using the deployed application's absolute directory:

```cron
* * * * * cd /path/to/PMS_REOPEN && php artisan schedule:run >> /dev/null 2>&1
```

For Windows, schedule `php artisan schedule:run` every minute with the application directory as the working directory. For local development, run `php artisan schedule:work` in a terminal.

The scheduled command `php artisan attendance:auto-clock-out` scans registered company databases separately. It catches up overdue sessions using their original cutoff, rather than the job's execution time. Authenticated company requests also catch up the signed-in user's overdue sessions. Open-session duration stops increasing at the cutoff even when the scheduler is delayed.

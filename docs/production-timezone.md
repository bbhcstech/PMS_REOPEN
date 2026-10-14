# Website time configuration (India / IST)

The website uses one business timezone, Asia/Kolkata (UTC+05:30). The application clock comes from deployment configuration, not company localization settings or a user's browser/location. New attendance sessions use IST; existing sessions retain their stored timezone for duration and automatic clock-out calculations.

## Production deployment

Deploy the changed PHP files, Blade templates and `public/admin/assets/js/pms-time.js`. Update the application's production `.env` (the directory containing `artisan`):

```dotenv
APP_TIMEZONE=Asia/Kolkata
DB_TIMEZONE=+05:30
```

Then run from that directory:

```sh
php artisan config:clear
php artisan config:cache
php artisan view:clear
php artisan queue:restart
php artisan app:time-check
```

Restart persistent application processes if used (Octane, Horizon or `schedule:work`). Ordinary cron calls to `schedule:run` reload configuration on their next run. Refresh open browser tabs so they receive the new JavaScript. Do not clear application data or session tables.

The database config applies `+05:30` to each MySQL/MariaDB connection session: mysql, central, tenant and session_db. Dynamically selected company databases inherit the tenant connection setting. This avoids needing MySQL timezone tables or changing the database server's global timezone. Keep the server's actual clock synchronized via its operating system; an incorrect clock cannot be fixed by a timezone setting.

`app:time-check` is read-only. It checks PHP/application timezone, connection session timezone and application/database clock agreement. Its tenant check uses the configured default tenant database; actual company requests use the same tenant connection options.

## What changed locally

Local `.env` already had `APP_TIMEZONE=Asia/Kolkata`; `DB_TIMEZONE=+05:30` was added. `.env.example` now contains both. `AppServiceProvider` no longer loads `loc_timezone` from a tenant database before tenant selection. Localization shows the deployment timezone and cannot override it. Attendance and its scheduled command use the deployment timezone for new sessions and calendar-day rules. Browser dashboards, attendance timers, timestamp formatting and timesheet defaults use explicit IST rather than the device timezone; live dashboards use a server-seeded clock.

## Existing records

This deployment does not rewrite historical records or add 5 hours 30 minutes to every timestamp.

MySQL `TIMESTAMP` values represent instants and are converted by the connection session timezone. `DATETIME` and `TIME` values have no timezone metadata: a past UTC value written into a `DATETIME` field will remain a UTC wall time until it is audited and converted. A value written under the wrong database session timezone can also represent the wrong instant.

If old records still look wrong after deployment, compare a known event's actual time with its raw stored value, column type, original application timezone and original database session timezone. Identify the affected tables and date ranges before a backed-up, targeted correction. For example, 05:48 UTC is 11:18 IST, but 05:48 already stored as IST must remain 05:48. The screenshot alone cannot establish which case applies. Check newly created records separately to confirm future writes.

There is no database migration required for this configuration fix.

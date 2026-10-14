# Employee invitations and SMTP setup

Open **Admin > Settings > Email** and enter the values supplied by your email provider:

- Driver: SMTP
- Host: the provider's SMTP hostname
- Port and encryption: usually 587 with TLS (STARTTLS), or 465 with SSL (implicit TLS). Follow the provider's instructions.
- Username and password: the SMTP account or provider app password
- From address: an address the provider permits this account to send from
- From name: your company or HR team name

Save the settings, then use **Send Test Email** with an address you control. Saved credentials apply to outgoing mail in that company workspace. Passwords are encrypted when saved; leaving the password field blank preserves the saved password.

Employee **Invite by Email** uses these settings. Development Log/Array transports do not count as email delivery. If sending fails, the new account and profile are rolled back and an error is shown.

**Invite by Link** works without SMTP. Enter a new employee's email address and generate a recipient-specific link. Share it with that employee. The signed link expires after seven days and becomes invalid after the employee sets their password. It cannot be generated for an existing account.

Before production use, make sure the application's configured public URL matches its HTTPS domain so the generated links open the correct site. A real delivery test still requires valid provider credentials and outbound SMTP access from the deployment server.

Regression checks (no real mail sent):

```powershell
D:/xampp/php/php.exe tests/regression/employee_invites_smtp.php
node tests/regression/employee_invite_ui.cjs
```

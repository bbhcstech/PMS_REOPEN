# Company assistant

Add `GROQ_API_KEY=your-key` to `.env`. Optional: `GROQ_MODEL=llama-3.3-70b-versatile`.
If configuration is cached, run `php artisan config:clear` after changing these values.
Never put the API key in browser JavaScript. No additional packages or database migrations are required.

Company creation automatically saves an agent UUID, the company-based assistant name, and provider in the company's existing settings. Renaming a company updates the assistant name without changing its UUID. Existing companies receive their identity on first access. Find **Company Assistant** in the company sidebar, or visit `/company/assistant` after signing in.

This is live retrieval, not model fine-tuning. Every question reads current authorized records without a shared index, chat cache or stored conversation. Company/profile, department/designation, event, project/task, and personal attendance/leave fields are allowlisted. No credentials, salaries, banking fields, attachments or arbitrary tables are sent. Missing ownership columns fail closed. Company admin can retrieve company projects/tasks; other users receive assigned projects/tasks only, subject to module access. Workforce records are always personal. Other modules and file contents are not indexed by this initial implementation.

Relevant snippets and the question are sent to Groq; its account terms and data handling apply. The provider is instructed to refuse unrelated questions and unsupported facts. Answers without valid company source IDs are rejected. Model output is untrusted plain text, never executed or inserted as HTML. As with any language model, source validation does not prove that every sentence is accurate; users can inspect source records in the UI. The agent cannot change company records.

Requests require a valid active company login, matching session company and physical tenant DB. Client-provided company/agent IDs and message histories are rejected. Rate limiting allows 15 requests per minute per authenticated user. API errors return a generic unavailable message without exposing provider responses or secrets.

Run `php tests/regression/company_ai.php` for offline checks using isolated SQLite fixtures and fake HTTP responses. Production Groq connectivity requires the real API key.

## Super Admin assistant and names

Each platform Super Admin has a separate namespaced identity and a **Platform Assistant** sidebar entry at `/super-admin/assistant`. The assistant uses only that administrator's current profile name and allowlisted central company-registry summaries. It never switches into company databases to retrieve operations, personal employee data, or other administrators' profiles. Central-guard identities and legacy platform identities use separate namespaces. Requests and rate limits are independent from company assistants.

The Super Admin assistant name is `<profile name> Platform Assistant`. The company assistant display name is `<company name> Assistant · <signed-in profile name>`. Names are resolved from current records whenever the assistant is opened or queried, so profile/company renames require no manual assistant configuration and never change the identity. Company settings preserve the base company assistant name; bulk company updates are synchronized when the assistant is next opened. Company/user identity checks reject tabs belonging to a previous account even when the new user belongs to the same company.

Run `php tests/regression/platform_ai.php` to cover both platform and company agent isolation and rename behavior.

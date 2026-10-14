<?php

namespace App\Services;

use App\Models\Central\SuperAdmin;
use Illuminate\Support\Facades\{Auth, DB, Schema};

class PlatformAiAgent
{
    /** Namespaced identity is stable across renames and distinct from company agents. */
    public function identity(): array
    {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        $central = Auth::guard('super_admin')->user();
        if ($central) {
            $user = SuperAdmin::findOrFail($central->id);
            abort_unless($user->is_active, 403);
            $key = 'platform:super_admin:' . $user->id;
        } else {
            $bound = Auth::guard('web')->user();
            abort_unless($bound && !$bound->company_id, 403);
            // A legacy platform ID must be read from the fixed primary database,
            // never from an impersonated tenant with an overlapping user ID.
            $user = DB::connection('session_db')->table('users')->where('id', $bound->id)->first();
            abort_unless($user && !$user->company_id && in_array(strtolower((string) $user->role), ['superadmin', 'super-admin', 'super_admin'], true)
                && $user->is_active && ($user->login_allowed ?? true) && empty($user->archived_at), 403);
            $key = 'platform:web:' . hash('sha256', DB::connection('session_db')->getDatabaseName()) . ':' . $user->id;
        }
        return ['id' => $key, 'name' => trim((string) $user->name) . ' Platform Assistant', 'profile_name' => $user->name];
    }

    public function records(string $question): array
    {
        $identity = $this->identity();
        $records = [['id' => $identity['id'], 'type' => 'platform profile', 'facts' => [
            'profile_name' => $identity['profile_name'], 'assistant_name' => $identity['name'], 'role' => 'Super Admin',
        ]]];
        $schema = Schema::connection('central');
        if (!$schema->hasTable('companies')) return $records;
        $columns = $schema->getColumnListing('companies');
        $fields = array_values(array_intersect(['id', 'name', 'company_code', 'status', 'trial_ends_at', 'max_users', 'max_projects', 'manually_suspended'], $columns));
        $base = DB::connection('central')->table('companies');
        if (in_array('deleted_at', $columns, true)) $base->whereNull('deleted_at');
        $records[] = ['id' => 'platform:registry', 'type' => 'platform companies summary', 'facts' => [
            'registered_companies' => (clone $base)->count(),
            'active_companies' => in_array('status', $columns, true) ? (clone $base)->where('status', 'active')->count() : null,
        ]];
        preg_match_all('/[\p{L}\p{N}]{3,}/u', mb_strtolower($question), $matches);
        $terms = array_values(array_diff(array_unique($matches[0]), ['what', 'which', 'about', 'please', 'show', 'tell', 'the', 'are', 'and', 'with', 'for', 'company', 'companies', 'platform', 'list', 'all']));
        $query = clone $base;
        if ($terms) $query->where(function ($q) use ($terms, $columns) {
            foreach (array_intersect(['name', 'company_code', 'status'], $columns) as $field)
                foreach ($terms as $term) $q->orWhere($field, 'like', '%' . $term . '%');
        });
        foreach ($query->select($fields)->orderBy('id')->limit(20)->get() as $company) {
            $facts = (array) $company; unset($facts['id']);
            $records[] = ['id' => 'platform:company:' . $company->id, 'type' => 'platform company registry', 'facts' => $facts];
        }
        return $records;
    }

    public function answer(string $question): array
    {
        $identity = $this->identity();
        return app(GroundedAiResponse::class)->answer($question, $this->records($question), $identity['name'], 'platform');
    }
}

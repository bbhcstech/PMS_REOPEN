<?php

namespace App\Services;

use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;

class CompanyAiKnowledge
{
    /** Only explicitly approved fields are sent to the provider. Never serialize a model. */
    public function retrieve(Company $company, User $user, string $question): array
    {
        $records = [['id' => 'company:' . $company->id, 'type' => 'company', 'facts' => [
            'name' => $company->name, 'website' => $company->website, 'address' => $company->address,
        ]]];
        $admin = WorkforceAccess::isAdmin($user);
        $definitions = [
            'events' => ['events', ['title', 'description', 'event_type', 'start_date', 'start_time', 'end_date', 'location', 'status']],
            'projects' => ['projects', ['name', 'project_code', 'description', 'status', 'start_date', 'deadline']],
            'tasks' => ['tasks', ['title', 'description', 'status', 'priority', 'due_date']],
            'attendance' => ['attendances', ['date', 'clock_in', 'clock_out', 'status']],
            'leaves' => ['leaves', ['leave_type', 'start_date', 'end_date', 'status', 'reason']],
        ];
        if ($user->canViewModule('employees')) {
            $definitions['departments'] = ['departments', ['dpt_name']];
            $definitions['designations'] = ['designations', ['name', 'level', 'unique_code']];
        }
        foreach ($definitions as $type => [$table, $fields]) {
            $module = in_array($type, ['departments', 'designations']) ? 'employees' : $type;
            if (!$user->canViewModule($module)) continue;
            $schema = Schema::connection('tenant');
            if (!$schema->hasTable($table)) continue;
            $columns = $schema->getColumnListing($table);
            // Missing ownership columns are never treated as shared data.
            if (!in_array('company_id', $columns, true)) continue;
            $fields = array_values(array_intersect($fields, $columns));
            if (!$fields || !in_array('id', $columns, true)) continue;
            $query = DB::connection('tenant')->table($table)->where('company_id', $company->id);
            if (in_array('deleted_at', $columns, true)) $query->whereNull('deleted_at');
            if (in_array('archived_at', $columns, true)) $query->whereNull('archived_at');
            if ($type === 'events' && !$admin) $query->where('status', '!=', 'draft');
            if (in_array($type, ['attendance', 'leaves'])) {
                // Personal workforce records never become shared AI knowledge, even for HR.
                $query->where('user_id', $user->id);
            }
            if ($type === 'projects' && !$admin) {
                if (!$schema->hasTable('project_user')) continue;
                $query->whereIn('id', DB::connection('tenant')->table('project_user')->select('project_id')->where('user_id', $user->id));
            }
            if ($type === 'tasks' && !$admin) {
                if (!in_array('assigned_to', $columns, true)) continue;
                $query->where('assigned_to', $user->id);
            }
            $terms = $this->terms($question);
            // Fetch relevant matches across the table, rather than only recent records.
            $topicRequested = collect($terms)->contains(fn ($term) => str_contains($type, $term) || str_contains($term, rtrim($type, 's')));
            if (!$topicRequested && $terms) {
                $query->where(function ($q) use ($terms, $fields) {
                    foreach ($fields as $field) foreach ($terms as $term) $q->orWhere($field, 'like', '%' . $term . '%');
                });
            }
            foreach ($query->select(array_merge(['id'], $fields))->orderByDesc('id')->limit(25)->get() as $row) {
                $facts = [];
                foreach ($fields as $field) $facts[$field] = Str::limit(strip_tags((string) $row->$field), 700, '');
                $records[] = ['id' => $type . ':' . $row->id, 'type' => $type, 'facts' => $facts];
            }
        }
        $terms = $this->terms($question);
        $ranked = [];
        foreach ($records as $record) {
            $text = mb_strtolower(json_encode($record, JSON_UNESCAPED_UNICODE));
            $score = count(array_filter($terms, fn ($term) => str_contains($text, $term)));
            if ($score) $ranked[] = ['score' => $score, 'record' => $record];
        }
        usort($ranked, fn ($a, $b) => $b['score'] <=> $a['score']);
        $selected = []; $bytes = 0;
        foreach (array_slice($ranked, 0, 20) as $item) {
            $size = strlen(json_encode($item['record']));
            if ($bytes + $size > 16000) break;
            $selected[] = $item['record']; $bytes += $size;
        }
        return $selected;
    }

    private function terms(string $question): array
    {
        preg_match_all('/[\p{L}\p{N}]{3,}/u', mb_strtolower($question), $matches);
        return array_values(array_diff(array_unique($matches[0]), ['the', 'and', 'what', 'which', 'with', 'that', 'this', 'about', 'please', 'show', 'tell', 'have', 'does', 'how', 'are', 'for', 'can', 'you', 'our', 'your']));
    }
}

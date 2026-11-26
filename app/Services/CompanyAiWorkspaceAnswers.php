<?php

namespace App\Services;

/** Short answers assembled from the same permission-checked facts sent to the assistant. */
class CompanyAiWorkspaceAnswers
{
    public function answer(string $question, array $records): ?array
    {
        $question = trim(preg_replace('/\s+/u', ' ', mb_strtolower($question)) ?? '', " \t\r\n?!. ");
        $records = array_column($records, null, 'id');

        if (preg_match('/^(?:(?:how many holidays?(?: (?:are there|present))?|(?:show|list)(?: me)?(?: the)? holidays?|what are the holidays)(?: (?:in|for))? |holidays )((?:this|next) (?:week|month))$/', $question, $period)) {
            $source = 'live:holidays';
            $facts = $records[$source]['facts'] ?? [];
            $key = str_replace(' ', '_', $period[1]);
            $count = $facts['holidays_'.$key.'_count'] ?? null;
            $answer = match (true) {
                $count === 0 => 'There are no company holidays '.$period[1].'.',
                $count === 1 => $this->list('There is 1 company holiday '.$period[1].':', $facts['holidays_'.$key] ?? null, ''),
                is_int($count) => $this->list("There are {$count} company holidays {$period[1]}:", $facts['holidays_'.$key] ?? null, ''),
                default => null,
            };
            if ($answer && is_array($facts['weekly_offs_'.$key] ?? null)) {
                $answer .= "\n\nRegular weekly days off:\n- ".implode("\n- ", $facts['weekly_offs_'.$key]);
            }
        } else {
            [$source, $answer] = match ($question) {
                'what are the office timings', 'what are our office hours', 'office timings', 'office hours' => [
                    'live:schedule', $this->officeHours($records['live:schedule']['facts'] ?? []),
                ],
                'who is my reporting manager', 'who is my manager', 'who do i report to' => [
                    'live:team', $this->manager($records['live:team']['facts']['your_manager'] ?? null),
                ],
                'what is my leave balance', 'my leave balance' => [
                    'live:leaves', $this->leaveBalance($records['live:leaves']['facts'] ?? []),
                ],
                'how many days was i late this month' => [
                    'live:attendance', $this->lateDays($records['live:attendance']['facts']['this_month']['days_by_status'] ?? null),
                ],
                'which of my tasks are due this week' => [
                    'live:work', $this->list('Your open tasks due this week:', $records['live:work']['facts']['my_tasks']['due_this_week'] ?? null, 'You have no open tasks due this week.'),
                ],
                'any upcoming company events' => [
                    'live:events', $this->list('Upcoming company events in the next 60 days:', $records['live:events']['facts']['next_60_days'] ?? null, 'There are no upcoming events listed for the next 60 days.'),
                ],
                'how do i apply for leave' => [
                    'live:guide', collect($records['live:guide']['facts']['how_to'] ?? [])->first(fn ($step) => str_starts_with($step, 'Apply for leave:')),
                ],
                default => [null, null],
            };
        }

        if (!$source) return null;

        return [
            'answer' => $answer ?: 'I couldn’t find that information in the workspace records you can access.',
            'sources' => $answer && isset($records[$source]) ? [$records[$source]] : [],
        ];
    }

    private function officeHours(array $facts): ?string
    {
        $hours = $facts['office_hours'] ?? '';
        if (!preg_match('/^\d{2}:\d{2} [AP]M to \d{2}:\d{2} [AP]M$/', $hours)) return null;

        return 'Office hours are '.$hours.'.'.(!empty($facts['working_days']) ? ' Working days: '.$facts['working_days'].'.' : '');
    }

    private function manager(?string $name): ?string
    {
        return $name === null ? null : ($name === 'Not assigned' ? 'You don’t have a reporting manager assigned.' : 'Your reporting manager is '.$name.'.');
    }

    private function leaveBalance(array $facts): ?string
    {
        $balance = $facts['balance']['remaining_days'] ?? null;
        if (!is_numeric($balance)) return null;

        return 'You have '.$balance.' leave '.((float) $balance === 1.0 ? 'day' : 'days').' remaining.';
    }

    private function lateDays(array|string|null $statuses): ?string
    {
        if ($statuses === null) return null;
        $count = (int) ($statuses['late'] ?? 0);

        return $count === 1 ? 'You have 1 recorded late day this month.' : 'You have '.$count.' recorded late days this month.';
    }

    private function list(string $heading, array|string|null $items, string $empty): ?string
    {
        if ($items === 'None') return $empty;

        return is_array($items) && $items ? $heading."\n- ".implode("\n- ", $items) : null;
    }
}

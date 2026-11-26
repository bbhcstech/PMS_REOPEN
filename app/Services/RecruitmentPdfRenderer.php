<?php

namespace App\Services;

use App\Models\RecruitmentRequirement;
use App\Support\RecruitmentFormat;
use Symfony\Component\Process\Process;

/**
 * Builds the single-page Recruitment Specification Sheet with pdfkit + pdf-lib
 * (resources/pdf/recruitment-sheet.mjs). Throws when Node or the libraries are unavailable,
 * so the caller can fall back to the DomPDF template.
 */
class RecruitmentPdfRenderer
{
    public function render(RecruitmentRequirement $requirement, array $policyCard): string
    {
        $script = resource_path('pdf/recruitment-sheet.mjs');
        if (! is_file($script) || ! is_dir(base_path('node_modules/pdfkit')) || ! is_dir(base_path('node_modules/pdf-lib'))) {
            throw new \RuntimeException('PDF libraries are not installed (run npm install).');
        }

        $process = new Process([config('services.node.binary', 'node'), $script], base_path(), null, json_encode($this->payload($requirement, $policyCard), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 30);
        $process->run();
        $pdf = $process->getOutput();
        if (! $process->isSuccessful() || ! str_starts_with($pdf, '%PDF')) {
            throw new \RuntimeException('Recruitment PDF renderer failed: ' . trim(mb_substr($process->getErrorOutput(), 0, 500)));
        }

        return $pdf;
    }

    public function payload(RecruitmentRequirement $requirement, array $policyCard): array
    {
        [$statusLabel, $statusTone] = RecruitmentFormat::status($requirement->status);
        $created = $requirement->created_at ?? now();
        $company = $this->companyName($requirement);
        $fonts = base_path('vendor/dompdf/dompdf/lib/fonts');

        return [
            'fonts' => ['regular' => $fonts . '/DejaVuSans.ttf', 'bold' => $fonts . '/DejaVuSans-Bold.ttf'],
            'meta' => ['company' => (string) $company],
            'doc' => [
                'ref' => 'REQ-' . $requirement->id . '-' . $created->format('Y'),
                'issued' => $created->format('M d, Y'),
                'generated' => now()->format('d M Y, h:i A'),
            ],
            'job' => [
                'title' => (string) $requirement->title,
                'department' => $requirement->department_name ?: 'General',
                'employmentType' => $requirement->employment_type ?: 'Full-time',
                'positions' => RecruitmentFormat::positions($requirement->positions),
                'experience' => RecruitmentFormat::experience($requirement->experience_required),
                'salary' => RecruitmentFormat::ctc($requirement->salary_range),
                'location' => $requirement->location ?: 'Headquarters',
                'status' => ['label' => $statusLabel, 'tone' => $statusTone],
                'postedBy' => $requirement->creator?->name ?? 'HR Department',
            ],
            'description' => $this->text($requirement->description),
            'qualifications' => $this->text($requirement->requirements_summary),
            'policy' => [
                'code' => $policyCard['code'] ?? ('POL-REC-' . date('Y')),
                'probation' => $policyCard['probation_period'] ?? null,
                'sla' => $policyCard['hiring_sla'] ?? null,
                'pipeline' => implode(' → ', array_map('trim', (array) ($policyCard['pipeline_stages'] ?? []))),
                'resume' => trim(implode(', ', array_filter(array_map('trim', explode(',', (string) ($policyCard['allowed_file_types'] ?? ''))))) . (isset($policyCard['max_resume_size']) ? ' (max ' . $policyCard['max_resume_size'] . ')' : '')),
                'equalOpportunity' => $policyCard['equal_opportunity'] ?? null,
            ],
            'images' => [
                'header' => $this->image(method_exists($requirement, 'pdfImageDataUri') ? $requirement->pdfImageDataUri('pdf_header_image') : null),
                'footer' => $this->image(method_exists($requirement, 'pdfImageDataUri') ? $requirement->pdfImageDataUri('pdf_footer_image') : null),
            ],
        ];
    }

    private function companyName(RecruitmentRequirement $requirement): string
    {
        try {
            $name = $requirement->company_id ? \App\Models\Company::whereKey($requirement->company_id)->value('name') : null;
        } catch (\Throwable $e) {
            $name = null;
        }
        return (string) ($name ?: auth()->user()?->company?->name ?: config('app.name', 'Company'));
    }

    private function text(?string $value): string
    {
        $text = preg_replace(["/\r\n?/", "/[ \t]+\n/", "/\n{3,}/"], ["\n", "\n", "\n\n"], strip_tags((string) $value));
        return trim(html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5));
    }

    /** data:image/png;base64,... -> ['mime' => 'image/png', 'base64' => '...'] (PNG/JPEG only). */
    private function image(?string $dataUri): ?array
    {
        if (! $dataUri || ! preg_match('#^data:(image/(?:png|jpe?g));base64,(.+)$#is', $dataUri, $m)) return null;
        return ['mime' => strtolower($m[1]), 'base64' => $m[2]];
    }
}

<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Job Requirement - {{ $requirement->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #ffffff;
            padding: 30px;
            line-height: 1.5;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px solid #7C3AED;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-title {
            font-size: 20px;
            font-weight: 800;
            color: #6D28D9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-type {
            font-size: 12px;
            color: #6b7280;
            font-weight: 700;
            margin-top: 3px;
        }

        .title-box {
            background: #f3e8ff;
            border-left: 5px solid #7C3AED;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .job-title {
            font-size: 18px;
            font-weight: 800;
            color: #4c1d95;
            margin-bottom: 4px;
        }
        .job-subtitle {
            font-size: 11px;
            color: #6d28d9;
            font-weight: 600;
        }

        .params-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .params-table td {
            width: 50%;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            background: #f9fafb;
        }
        .param-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 700;
            display: block;
            margin-bottom: 2px;
        }
        .param-value {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        .section-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 15px;
            background: #ffffff;
        }
        .section-header {
            font-size: 12px;
            font-weight: 800;
            color: #6D28D9;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f3e8ff;
            padding-bottom: 4px;
        }
        .section-text {
            font-size: 10.5px;
            color: #374151;
            white-space: pre-line;
            line-height: 1.6;
        }

        .policy-card-pdf {
            background: #faf5ff;
            border: 1px solid #d8b4fe;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .policy-title {
            font-size: 11px;
            font-weight: 800;
            color: #6D28D9;
            margin-bottom: 6px;
        }
        .policy-list {
            font-size: 10px;
            color: #4c1d95;
            line-height: 1.5;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">RECRUITMENT SPECIFICATION SHEET</div>
                <div class="doc-type">Official Job Requirement Posting for Personal & Professional Sharing</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div style="font-size: 9px; color: #6b7280;">Document Ref: <strong>REQ-{{ $requirement->id }}-{{ date('Y') }}</strong></div>
                <div style="font-size: 9px; color: #6b7280;">Date Issued: <strong>{{ $requirement->created_at->format('M d, Y') }}</strong></div>
            </td>
        </tr>
    </table>

    <div class="title-box">
        <div class="job-title">{{ $requirement->title }}</div>
        <div class="job-subtitle">Department: {{ $requirement->department_name ?? 'General' }} | Employment Type: {{ $requirement->employment_type }}</div>
    </div>

    <table class="params-table">
        <tr>
            <td>
                <span class="param-label">Vacancies / Positions</span>
                <span class="param-value">{{ $requirement->positions }} Position(s)</span>
            </td>
            <td>
                <span class="param-label">Experience Required</span>
                <span class="param-value">{{ $requirement->experience_required ?? 'Not Specified' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="param-label">Salary Range</span>
                <span class="param-value">{{ $requirement->salary_range ?? 'Negotiable' }}</span>
            </td>
            <td>
                <span class="param-label">Work Location</span>
                <span class="param-value">{{ $requirement->location ?? 'Headquarters' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="param-label">Status</span>
                <span class="param-value">{{ strtoupper((string) $requirement->status) }}</span>
            </td>
            <td>
                <span class="param-label">Posted By</span>
                <span class="param-value">{{ $requirement->creator?->name ?? 'HR Department' }}</span>
            </td>
        </tr>
    </table>

    @if($requirement->description)
    <div class="section-box">
        <div class="section-header">Job Description & Responsibilities</div>
        <div class="section-text">{{ $requirement->description }}</div>
    </div>
    @endif

    @if($requirement->requirements_summary)
    <div class="section-box">
        <div class="section-header">Candidate Qualifications & Skills</div>
        <div class="section-text">{{ $requirement->requirements_summary }}</div>
    </div>
    @endif

    {{-- RECRUITMENT POLICY SUMMARY --}}
    <div class="policy-card-pdf">
        <div class="policy-title">Company Recruitment Policy Guidelines (POL-REC-{{ date('Y') }})</div>
        <div class="policy-list">
            • <strong>Probation Period:</strong> {{ $policyCard['probation_period'] }}<br/>
            • <strong>Hiring SLA Target:</strong> {{ $policyCard['hiring_sla'] }}<br/>
            • <strong>Pipeline Stages:</strong> {{ implode(' ➔ ', $policyCard['pipeline_stages']) }}<br/>
            • <strong>Allowed Resume Formats:</strong> {{ $policyCard['allowed_file_types'] }} (Max {{ $policyCard['max_resume_size'] }})<br/>
            • <strong>Equal Opportunity:</strong> {{ $policyCard['equal_opportunity'] }}
        </div>
    </div>

    <div class="footer">
        Confidential — Corporate Recruitment Document | Generated on {{ date('Y-m-d H:i:s') }} | Authorized for Employee, Manager & HR Sharing
    </div>

</body>
</html>

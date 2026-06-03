
@extends('admin.layout.app')

@section('title', 'Create Employee')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        /* =============================================
           BRAND DESIGN SYSTEM & VARIABLES
           ============================================= */
        :root {
            /* Core Palette - Matching Listing Page */
            --primary: #0f744c;
            --secondary: #188b5e;
            --accent: #22c55e;
            --mint: #d1fae5;
            --text-main: #07130d;
            --text-muted: #52645a;
            --bg-base: #f7fbf9;
            --surface: #ffffff;

            /* Gradients */
            --grad-cta: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 40%, var(--accent) 80%, var(--mint) 100%);
            --grad-bg: linear-gradient(135deg, rgba(15, 116, 76, 0.12) 0%, rgba(24, 139, 94, 0.12) 40%, rgba(34, 197, 94, 0.12) 80%, rgba(209, 250, 229, 0.2) 100%);
            --grad-bg-hover: linear-gradient(135deg, rgba(15, 116, 76, 0.2) 0%, rgba(24, 139, 94, 0.2) 40%, rgba(34, 197, 94, 0.2) 80%, rgba(209, 250, 229, 0.3) 100%);

            /* Glassmorphism */
            --glass-bg: rgba(255, 255, 255, 0.92);
            --glass-border: rgba(255, 255, 255, 1);
            --shadow-soft: 0 10px 30px -10px rgba(15, 116, 76, 0.08);
            --shadow-hover: 0 20px 40px -12px rgba(15, 116, 76, 0.2);

            /* Motion */
            --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --smooth: cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-base);
            overflow-x: hidden;
        }

        /* Ambient Animated Background */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
            background: var(--bg-base);
        }

        .ambient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: var(--grad-bg);
            filter: blur(60px);
            opacity: 0.6;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            will-change: transform;
            animation: floatOrb 20s infinite alternate var(--smooth);
        }

        .orb-1 {
            width: 50vw;
            height: 50vw;
            background: var(--secondary);
            top: -20%;
            right: -10%;
        }

        .orb-2 {
            width: 40vw;
            height: 40vw;
            background: var(--accent);
            bottom: -10%;
            left: -10%;
            animation-delay: -5s;
        }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-50px, 30px) scale(1.1); }
        }

        .container-fluid {
            position: relative;
            z-index: 5;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* PAGE HEADER */
        .page-header-enterprise {
            background: var(--grad-cta);
            background-size: 200% 200%;
            animation: gradientFlow 10s ease infinite;
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -10px rgba(15, 116, 76, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .page-header-enterprise::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" opacity="0.05"><circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="2"/></svg>') repeat;
            opacity: 0.4;
            animation: slideBg 20s linear infinite;
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes slideBg {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }

        .header-title {
            position: relative;
            z-index: 2;
        }

        .header-title h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .header-title h1 i {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 18px;
            backdrop-filter: blur(8px);
        }

        .header-title p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0.5rem 0 0;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .btn-header-enterprise {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            z-index: 2;
            transition: all 0.4s var(--spring);
            position: relative;
            overflow: hidden;
        }

        .btn-header-enterprise::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0));
            transform: skewX(-25deg);
            transition: 0.6s;
            z-index: -1;
        }

        .btn-header-enterprise:hover::before {
            left: 200%;
        }

        .btn-header-enterprise:hover {
            transform: translateY(-3px) scale(1.02);
            background: white;
            color: var(--primary);
            box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.2);
        }

        /* ALERTS */
        .alert-custom-err {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #dc2626;
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        .alert-custom-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        /* MAIN CARD */
        .card-premium {
            background: var(--surface);
            border: 1px solid rgba(15, 116, 76, 0.12);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: all 0.4s var(--spring);
            margin-bottom: 2rem;
        }

        .card-premium:hover {
            box-shadow: var(--shadow-hover);
        }

        .card-header-premium {
            background: var(--grad-bg);
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header-premium h5 {
            margin: 0;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body-premium {
            padding: 2rem;
        }

        /* FORM ELEMENTS */
        .section-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            line-height: 1.3;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--grad-bg);
            border: 1px solid rgba(15, 116, 76, 0.12);
            border-radius: 16px;
            padding: 1.2rem 2rem;
            box-shadow: 0 10px 24px -18px rgba(15, 116, 76, 0.45);
        }

        .section-title i {
            color: var(--secondary);
            font-size: 1.55rem;
        }

        .form-label-premium {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label-premium i {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .mandatory-badge {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            font-size: 0.65rem;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            margin-left: auto;
        }

        .optional-badge {
            background: rgba(100, 116, 139, 0.1);
            color: var(--text-muted);
            font-size: 0.65rem;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            border: 1px solid rgba(100, 116, 139, 0.2);
            margin-left: auto;
        }

        .form-control-premium,
        .form-select-premium {
            background: #ffffff;
            border: 1.5px solid rgba(15, 116, 76, 0.2);
            border-radius: 14px;
            padding: 0.7rem 1.2rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.3s;
            color: var(--text-main);
            font-weight: 500;
        }

        .form-control-premium:focus,
        .form-select-premium:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(24, 139, 94, 0.15);
            outline: none;
            transform: translateY(-2px);
        }

        .input-group-premium {
            display: flex;
            border-radius: 14px;
            overflow: hidden;
            border: 1.5px solid rgba(15, 116, 76, 0.2);
            transition: all 0.3s;
            background: #fff;
        }

        .input-group-premium:focus-within {
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(24, 139, 94, 0.15);
            transform: translateY(-2px);
        }

        .input-group-premium .country-code-select {
            border: none;
            background: transparent;
            padding-left: 1rem;
            font-weight: 600;
            color: var(--primary);
            max-width: 100px;
            border-radius: 14px 0 0 14px;
        }

        .input-group-premium .mobile-input {
            border: none;
            background: transparent;
            border-left: 1.5px solid rgba(15, 116, 76, 0.2);
            border-radius: 0 14px 14px 0;
        }

        .input-group-premium .mobile-input:focus {
            box-shadow: none;
            transform: none;
        }

        .invalid-feedback {
            font-size: 0.75rem;
            font-weight: 500;
            color: #dc2626;
            margin-top: 0.4rem;
        }

        .is-invalid {
            border-color: #dc2626 !important;
        }

        /* SWITCH & CHECKBOX */
        .form-check-input:checked {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }

        .form-switch .form-check-input {
            width: 2.8rem;
            height: 1.4rem;
            cursor: pointer;
        }

        /* BUTTONS */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .btn-submit {
            background: var(--grad-cta);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 14px;
            background-size: 200% 200%;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            transition: all 0.3s var(--spring);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.25);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.35);
            background-position: 100% 50%;
            color: white;
        }

        .btn-cancel {
            background: white;
            color: var(--text-muted);
            padding: 0.8rem 2rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            border: 2px solid rgba(100, 116, 139, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: var(--bg-base);
            color: var(--primary);
            border-color: var(--primary);
        }

        /* ANIMATIONS */
        .fade-up-stagger > * {
            opacity: 0;
            animation: fadeUpItem 0.8s var(--spring) forwards;
        }

        @keyframes fadeUpItem {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .page-header-enterprise {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.5rem;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-submit, .btn-cancel {
                justify-content: center;
            }
            .container-fluid {
                padding: 1rem;
            }
            .card-body-premium {
                padding: 1.5rem;
            }
        }

        /* MODAL STYLES */
        .modal-content-premium {
            border: 1px solid rgba(15, 116, 76, 0.14);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(22, 39, 30, 0.18);
        }

        .modal-header-premium {
            background: linear-gradient(135deg, #f1faf5, #ffffff) !important;
            border-bottom: 1px solid rgba(15, 116, 76, 0.1);
            padding: 1.2rem 1.5rem;
        }

        .modal-title-premium {
            color: var(--primary);
            font-weight: 800;
        }

        .btn-modal-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: white;
        }

        /* Dark Mode Support */
        html[data-pms-theme="dark"] .container-fluid {
            color: #ffffff;
        }
    </style>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="container-fluid">
        <div class="page-header-enterprise fade-up-stagger">
            <div class="header-title">
                <h1 style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 800 !important;"><i class="fas fa-user-plus" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i> Add New Employee</h1>
                <p style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 800 !important;">Create employee profile and configuration settings</p>
            </div>
            <div>
                <a href="{{ route('employees.index') }}" class="btn-header-enterprise">
                    <i class="fas fa-arrow-left"></i>
                    BACK TO DIRECTORY
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-custom-success fade-up-stagger" style="animation-delay: 0.05s;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-custom-err fade-up-stagger" style="animation-delay: 0.05s;">
                <div class="fw-bold mb-2"><i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:</div>
                <ul class="mb-0 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $ed = $employee?->employeeDetail ?? null;
            function fmtDate($val) {
                if (!$val) return '';
                try {
                    return \Carbon\Carbon::parse($val)->format('Y-m-d');
                } catch (\Exception $e) {
                    return $val;
                }
            }
        @endphp

        <div class="card-premium fade-up-stagger" style="animation-delay: 0.1s;">
            <div class="card-header-premium">
                <h5><i class="fas fa-sliders-h"></i> Employee Configuration</h5>
            </div>

            <div class="card-body-premium">
                <form id="employeeForm" action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Hidden fields for new designation/department -->
                    <input type="hidden" name="new_designation" id="new_designation" value="">
                    <input type="hidden" name="new_designation_level" id="new_designation_level" value="">
                    <input type="hidden" name="new_department" id="new_department" value="">
                    <input type="hidden" name="new_sub_department" id="new_sub_department" value="">

                    <div class="section-title">
                        <i class="fas fa-id-card"></i> Account Details
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-id-badge"></i> Employee ID</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            @php
                                $empOption = old('employee_id_option') ?? (($ed && $ed->employee_id) ? 'custom' : 'auto');
                            @endphp
                            <div class="d-flex flex-wrap gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="employee_id_option" id="emp_auto" value="auto" {{ $empOption === 'auto' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="emp_auto">Auto-generate</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="employee_id_option" id="emp_custom" value="custom" {{ $empOption === 'custom' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="emp_custom">Custom ID</label>
                                </div>
                            </div>
                            <input type="text" id="employee_id_input" name="employee_id" class="form-control-premium readonly-like-normal"
                                   placeholder="e.g. BBH2025001"
                                   value="{{ old('employee_id') ?? ($nextEmployeeId ?? ($ed->employee_id ?? '')) }}"
                                   readonly>
                            @error('employee_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-user"></i> Employee Name</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <input type="text" name="name" class="form-control-premium" required value="{{ old('name') ?? ($employee?->name ?? '') }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-envelope"></i> Email</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <input type="email" name="email" class="form-control-premium email-input" required value="{{ old('email') ?? ($employee?->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-lock"></i> Password</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <div class="input-group-premium">
                                <input type="password" name="password" id="password" class="form-control-premium" autocomplete="off" minlength="8" style="border: none; border-radius: 14px;">
                                <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password" style="border: none; background: transparent;">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password" style="border: none; background: transparent;">
                                    <i class="fa fa-random"></i>
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Leave blank to auto-generate a password. Min 8 characters if setting manually.</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-briefcase"></i> Designation</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            @php $selectedDesignation = old('designation_id') ?? ($ed->designation_id ?? null); @endphp
                            <div class="input-group-premium" style="overflow: visible;">
                                <select name="designation_id" id="designation_id" class="form-select-premium" required style="flex: 1;">
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}" {{ $selectedDesignation == $designation->id ? 'selected' : '' }}>
                                            {{ $designation->name }}
                                            @if(!empty($designation->unique_code))
                                                ({{ $designation->unique_code }})
                                            @endif
                                            @if(!empty($designation->level))
                                                - Level {{ $designation->level }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" id="openDesignationModalBtn" title="Add/Edit Designation" style="border: none; background: var(--grad-bg); padding: 0 1rem;">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                            </div>
                            @error('designation_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-building"></i> Department</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <div class="input-group-premium" style="overflow: visible;">
                                @php $selectedPrt = old('parent_dpt_id') ?? ($ed->parent_dpt_id ?? ''); @endphp
                                <select name="parent_dpt_id" id="prt_department_id" class="form-select-premium" required style="flex: 1;">
                                    <option value="">Select</option>
                                    @foreach($prtdepartments as $dept)
                                        <option value="{{ $dept->id }}" {{ $selectedPrt == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->dpt_name }} @if(!empty($dept->dpt_code)) ({{ $dept->dpt_code }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" id="openPrtModalBtn" title="Add parent department" style="border: none; background: var(--grad-bg); padding: 0 1rem;">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                            </div>
                            @error('parent_dpt_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-layer-group"></i> Sub Department</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <div class="input-group-premium" style="overflow: visible;">
                                @php $selectedDpt = old('department_id') ?? ($ed->department_id ?? ''); @endphp
                                <select name="department_id" id="department_id" class="form-select-premium" data-selected="{{ $selectedDpt }}" style="flex: 1;">
                                    <option value="">Select</option>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" id="openDptModalBtn" title="Add department" style="border: none; background: var(--grad-bg); padding: 0 1rem;">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                            </div>
                            @error('department_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-image"></i> Profile Picture</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <input type="file" name="profile_picture" class="form-control-premium" id="profile_picture">
                            @if(!empty($employee?->profile_image))
                                <small class="text-muted d-block mt-1">Current: <a href="{{ asset($employee->profile_image) }}" target="_blank" style="color: var(--secondary);">view image</a></small>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-globe"></i> Country</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <select name="country" id="country" class="form-select-premium select2">
                                <option value="">Select Country</option>
                                @php $selectedCountry = old('country') ?? ($ed->country ?? ($employee?->country ?? 'India')); @endphp
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" data-flag="{{ $country->flag_url }}" {{ $selectedCountry == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-mobile-alt"></i> Mobile</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <div class="input-group-premium">
                                <span class="input-group-text" style="background: transparent; border: none; font-weight: 600; color: var(--primary);">+91</span>
                                <input id="mobile_only_digits" type="text" name="mobile" class="form-control-premium mobile-input" required maxlength="10" placeholder="9876543210"
                                       value="{{ old('mobile') ?? (($ed?->mobile ?? null) ? preg_replace('/^\+91/', '', $ed->mobile) : preg_replace('/^\+91/', '', ($employee?->mobile ?? ''))) }}" style="border: none;">
                                <div id="mobile-error" class="invalid-feedback mobile-error d-none"></div>
                            </div>
                            <input type="hidden" name="mobile_with_code" id="mobile_with_code" value="{{ old('mobile_with_code') ?? ($ed->mobile ?? $employee?->mobile ?? '') }}">
                            @error('mobile')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-venus-mars"></i> Gender</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            @php $gender = old('gender') ?? ($ed->gender ?? ($employee?->gender ?? '')) @endphp
                            <select name="gender" class="form-select-premium">
                                <option value="">Select</option>
                                <option value="Male" {{ $gender === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $gender === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ $gender === 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Prefer not to say" {{ $gender === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-calendar-check"></i> Joining Date</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <input type="date" required class="form-control-premium joining-date-input" name="joining_date" id="joining_date"
                                   value="{{ old('joining_date') ?? (isset($ed->joining_date) ? fmtDate($ed->joining_date) : date('Y-m-d')) }}">
                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Employee joining date</small>
                            @error('joining_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-birthday-cake"></i> Date of Birth</span>
                                <span class="mandatory-badge">Required</span>
                                <span class="text-muted" style="font-size: 0.65rem; margin-left: 0.5rem;">(As per government ID)</span>
                            </label>
                            <input type="date" name="dob" id="dob" class="form-control-premium" required
                                   value="{{ old('dob') ?? fmtDate($ed->dob ?? $employee?->dob ?? '') }}">
                            @error('dob')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-user-friends"></i> Reporting To</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            @php $selectedReporting = old('reporting_to') ?? ($ed->reporting_to ?? ''); @endphp
                            <select name="reporting_to" class="form-select-premium">
                                <option value="">Select</option>
                                @foreach($users as $userItem)
                                    <option value="{{ $userItem->id }}" {{ (string)$selectedReporting === (string)$userItem->id ? 'selected' : '' }} {{ $employee && $userItem->id == $employee->id ? 'disabled' : '' }}>
                                        {{ $userItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-language"></i> Change Language</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <select name="language" id="language" class="form-select-premium select2">
                                @php $lang = old('language') ?? ($ed->language ?? ($employee?->language ?? 'en')); @endphp
                                <option value="en" data-flag="https://flagcdn.com/w20/gb.png" {{ $lang === 'en' ? 'selected' : '' }}>English</option>
                                <option value="bn" data-flag="https://flagcdn.com/w20/bd.png" {{ $lang === 'bn' ? 'selected' : '' }}>Bengali</option>
                                <option value="hi" data-flag="https://flagcdn.com/w20/in.png" {{ $lang === 'hi' ? 'selected' : '' }}>Hindi</option>
                                <option value="fr" data-flag="https://flagcdn.com/w20/fr.png" {{ $lang === 'fr' ? 'selected' : '' }}>French</option>
                                <option value="de" data-flag="https://flagcdn.com/w20/de.png" {{ $lang === 'de' ? 'selected' : '' }}>German</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-user-tag"></i> User Role</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            @php $role = old('user_role') ?? ($employee?->role ?? 'employee') @endphp
                            <select name="user_role" class="form-select-premium select-picker" required>
                                <option value="">Select Role</option>
                                <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-premium">
                                <span><i class="fas fa-map-marker-alt"></i> Address</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <textarea name="address" class="form-control-premium" rows="2">{{ old('address') ?? ($ed->address ?? $employee?->address ?? '') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-premium">
                                <span><i class="fas fa-info-circle"></i> About</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <textarea name="about" class="form-control-premium" rows="2">{{ old('about') ?? ($ed->about ?? $employee?->about ?? '') }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-lock"></i> Login Allowed?</span>
                            </label>
                            @php $loginAllowed = (string) (old('login_allowed') ?? (string)($ed->login_allowed ?? $employee?->login_allowed ?? '1')); @endphp
                            <select name="login_allowed" class="form-select-premium">
                                <option value="1" {{ $loginAllowed === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $loginAllowed === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-bell"></i> Email Notifications?</span>
                            </label>
                            @php $emailNotif = (string) (old('email_notifications') ?? (string)($ed->email_notifications ?? $employee?->email_notifications ?? '1')); @endphp
                            <select name="email_notifications" class="form-select-premium">
                                <option value="1" {{ $emailNotif === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $emailNotif === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-code"></i> Skills</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <textarea name="skills" class="form-control-premium" rows="2" placeholder="Comma separated skills (e.g., Laravel, Vue.js, React)">{{ old('skills') ?? ($ed->skills ?? $employee?->skills ?? '') }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-clock"></i> Employment Type</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            @php $employmentType = old('employment_type') ?? ($ed->employment_type ?? $employee?->employment_type ?? '') @endphp
                            <select name="employment_type" id="employment_type" class="form-select-premium select-picker">
                                <option value="">Select</option>
                                <option value="full_time" {{ $employmentType === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ $employmentType === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="on_contract" {{ $employmentType === 'on_contract' ? 'selected' : '' }}>On Contract</option>
                                <option value="internship" {{ $employmentType === 'internship' ? 'selected' : '' }}>Internship</option>
                                <option value="trainee" {{ $employmentType === 'trainee' ? 'selected' : '' }}>Trainee</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-heart"></i> Marital Status</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            @php $marital = old('marital_status') ?? ($ed->marital_status ?? $employee?->marital_status ?? '') @endphp
                            <select name="marital_status" id="marital_status" class="form-select-premium select-picker">
                                <option value="">Select</option>
                                <option value="single" {{ $marital === 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ $marital === 'married' ? 'selected' : '' }}>Married</option>
                                <option value="widower" {{ $marital === 'widower' ? 'selected' : '' }}>Widower</option>
                                <option value="widow" {{ $marital === 'widow' ? 'selected' : '' }}>Widow</option>
                                <option value="separate" {{ $marital === 'separate' ? 'selected' : '' }}>Separate</option>
                                <option value="divorced" {{ $marital === 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="engaged" {{ $marital === 'engaged' ? 'selected' : '' }}>Engaged</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-building"></i> Business Address</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            <textarea name="business_address" class="form-control-premium" required rows="2">{{ old('business_address') ?? ($ed->business_address ?? $employee?->business_address ?? 'Kolkata') }}</textarea>
                            @error('business_address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-premium">
                                <span><i class="fas fa-toggle-on"></i> Status</span>
                                <span class="mandatory-badge">Required</span>
                            </label>
                            @php $status = old('status') ?? ($ed->status ?? 'Active') @endphp
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status-active" value="Active" {{ $status === 'Active' ? 'checked' : '' }} onchange="toggleExitDate()">
                                    <label class="form-check-label" for="status-active">Active</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status-inactive" value="Inactive" {{ $status === 'Inactive' ? 'checked' : '' }} onchange="toggleExitDate()">
                                    <label class="form-check-label" for="status-inactive">Inactive</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4" id="exit-date-container" style="display: {{ $status === 'Inactive' ? 'block' : 'none' }};">
                            <label class="form-label-premium">
                                <span><i class="fas fa-calendar-times"></i> Exit Date</span>
                                <span class="optional-badge">Optional</span>
                            </label>
                            <input type="date" name="exit_date" id="exit_date" class="form-control-premium" value="{{ old('exit_date') ?? fmtDate($ed->exit_date ?? '') }}">
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('employees.index') }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Discard
                        </a>
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-save"></i> Create Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modals: Parent Department, Department, Designation --}}
    <div class="modal fade" id="prtModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="addPrtDptForm">@csrf
                <div class="modal-content modal-content-premium">
                    <div class="modal-header modal-header-premium">
                        <h5 class="modal-title modal-title-premium"><i class="fas fa-building me-2"></i> Manage Parent Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding: 1.5rem;">
                        <table class="table table-bordered mb-4">
                            <thead class="table-light">
                                <tr><th>#</th><th>Parent Department Name</th><th width="120">Action</th></tr>
                            </thead>
                            <tbody id="prt-dpt-list">
                                @foreach($prtdepartments as $index => $prt)
                                    <tr id="prt-row-{{ $prt->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $prt->dpt_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-prt" data-id="{{ $prt->id }}">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--primary);">Parent Department Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="dpt_name" id="prt_dpt_name" class="form-control-premium" required>
                            <div id="prt-group-error" class="text-danger d-none mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.05);">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary">Save Department</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="dptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="addDptForm">@csrf
                <div class="modal-content modal-content-premium">
                    <div class="modal-header modal-header-premium">
                        <h5 class="modal-title modal-title-premium"><i class="fas fa-layer-group me-2"></i> Manage Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding: 1.5rem;">
                        <table class="table table-bordered mb-4">
                            <thead class="table-light">
                                <tr><th>#</th><th>Parent Department Name</th><th>Department Name</th><th width="120">Action</th></tr>
                            </thead>
                            <tbody id="dpt-list">
                                @foreach($departments as $index => $dpt)
                                    <tr id="dpt-row-{{ $dpt->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $dpt->parent?->dpt_name ?? 'N/A' }}</td>
                                        <td>{{ $dpt->dpt_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-dpt" data-id="{{ $dpt->id }}">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--primary);">Parent Department (optional)</label>
                            <select name="parent_dpt_id" id="dpt_parent_select" class="form-select-premium">
                                <option value="">None</option>
                                @foreach($prtdepartments as $pd)
                                    <option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--primary);">Department Name <sup class="text-danger">*</sup></label>
                            <input type="text" name="dpt_name" id="dpt_name" class="form-control-premium" required>
                            <div id="dpt-group-error" class="text-danger d-none mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.05);">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary">Save Department</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="designationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addDesignationForm">@csrf
                <div class="modal-content modal-content-premium">
                    <div class="modal-header modal-header-premium">
                        <h5 class="modal-title modal-title-premium"><i class="fas fa-briefcase me-2"></i> Manage Designations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding: 1.5rem;">
                        @if($designations->isNotEmpty())
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">Existing Designations</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-3">
                                        <thead class="table-light">
                                            <tr><th>Name</th><th>Level</th><th width="100">Actions</th></tr>
                                        </thead>
                                        <tbody id="designation-list">
                                            @foreach($designations as $des)
                                                <tr id="des-row-{{ $des->id }}">
                                                    <td>{{ $des->name }}</td>
                                                    <td>{{ $des->level ?? 'Not Set' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning edit-designation"
                                                                data-id="{{ $des->id }}"
                                                                data-name="{{ $des->name }}"
                                                                data-level="{{ $des->level ?? '' }}"
                                                                title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger delete-designation" data-id="{{ $des->id }}" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="border-top pt-3">
                            <h6 class="fw-bold" style="color: var(--primary);">Add New Designation</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">Designation Name <sup class="text-danger">*</sup></label>
                                <input type="text" name="name" id="designationName" class="form-control-premium" required>
                                <div class="text-danger mt-2 d-none" id="designation-error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">Designation Level <span class="text-danger">*</span></label>
                                <input type="number" min="0" max="6" name="level" class="form-control-premium" id="designationLevel" placeholder="Enter level (0-6)" required>
                                <small class="text-muted">Level range: 0-6 (e.g., 0=Intern, 1=Associate, 2=Sr. Associate, etc.)</small>
                            </div>
                        </div>

                        <input type="hidden" id="edit_designation_id" value="">
                        <input type="hidden" name="status" value="Active">
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.05);">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary" id="saveDesignationBtn">Add Designation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    input[disabled], input[readonly] {
        background-color: #fff !important;
        opacity: 1 !important;
        color: #212529 !important;
    }
    input.readonly-like-normal {
        border-color: #d1d5db;
        box-shadow: none;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 14px !important;
        border-color: rgba(15, 116, 76, 0.2) !important;
        min-height: 44px;
    }
    .input-group-premium .invalid-feedback {
        flex: 0 0 100%;
        width: 100%;
        padding-left: 1rem;
    }
    .modal-body {
        overflow-x: auto;
    }
    @media (max-width: 576px) {
        .page-header-enterprise {
            border-radius: 18px;
        }
        .header-title h1 {
            font-size: 1.35rem;
            line-height: 1.25;
        }
        .card-header-premium,
        .card-body-premium {
            padding: 1rem;
        }
        .input-group-premium {
            flex-wrap: wrap;
        }
        .input-group-premium .form-select-premium,
        .input-group-premium .form-control-premium {
            min-width: 0;
        }
        .input-group-premium > .btn {
            min-height: 44px;
        }
        .modal-dialog {
            margin: 0.75rem;
        }
        .table {
            min-width: 520px;
        }
    }
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const $el = id => document.getElementById(id);

function toggleExitDate() {
    const isInactive = $el('status-inactive') && $el('status-inactive').checked;
    const container = $el('exit-date-container');
    if (container) container.style.display = isInactive ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    toggleExitDate();

    const empInput = $el('employee_id_input');
    if (empInput) {
        empInput.classList.add('readonly-like-normal');
    }

    function updateEmpInputState() {
        const customRadio = $el('emp_custom');
        const autoRadio = $el('emp_auto');
        if (!empInput || !customRadio || !autoRadio) return;

        const isCustom = customRadio.checked;
        empInput.readOnly = !isCustom;
        empInput.required = isCustom;

        if (!isCustom && !empInput.value) {
            empInput.value = '{{ $nextEmployeeId ?? "" }}';
        }
    }

    updateEmpInputState();
    document.querySelectorAll('input[name="employee_id_option"]').forEach(radio => {
        radio.addEventListener('change', updateEmpInputState);
    });

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const passwordField = $el('password');
            const icon = this.querySelector('i');
            if (!passwordField) return;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    document.querySelectorAll('.generate-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const passwordField = $el('password');
            if (!passwordField) return;
            const randomPassword = Math.random().toString(36).slice(-10) + '!A1';
            passwordField.value = randomPassword;
        });
    });

    const desBtn = $el('openDesignationModalBtn');
    if (desBtn) desBtn.addEventListener('click', function () {
        $('#addDesignationForm')[0].reset();
        $('#edit_designation_id').val('');
        $('#saveDesignationBtn').text('Add Designation');
        $('#designation-error').addClass('d-none').text('');
        const modal = new bootstrap.Modal($el('designationModal'));
        modal.show();
    });

    const prtBtn = $el('openPrtModalBtn');
    if (prtBtn) prtBtn.addEventListener('click', function () {
        const modal = new bootstrap.Modal($el('prtModal'));
        modal.show();
    });

    const dptBtn = $el('openDptModalBtn');
    if (dptBtn) dptBtn.addEventListener('click', function () {
        const modal = new bootstrap.Modal($el('dptModal'));
        modal.show();
    });

    const mobileOnly = $el('mobile_only_digits');
    if (mobileOnly) {
        mobileOnly.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 10);
            if (v.length > 0 && v[0] === '0') v = v.replace(/^0+/, '');
            this.value = v;
            const mobileError = $el('mobile-error');
            if (mobileError) mobileError.classList.add('d-none');
            this.classList.remove('is-invalid');
        });
    }

    const form = document.getElementById('employeeForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const dobEl = $el('dob');
            if (dobEl && !dobEl.value) {
                e.preventDefault();
                dobEl.focus();
                alert('Please provide Date of Birth (DOB). It is required.');
                return false;
            }

            const mobileEl = $el('mobile_only_digits');
            if (mobileEl) {
                if (mobileEl.classList.contains('is-invalid')) {
                    e.preventDefault();
                    mobileEl.focus();
                    alert(($el('mobile-error') && $el('mobile-error').textContent) || 'Please fix the mobile number before submitting.');
                    return false;
                }

                const m = mobileEl.value.trim();
                if (!/^[1-9]\d{9}$/.test(m)) {
                    e.preventDefault();
                    mobileEl.focus();
                    alert('Please enter a valid 10-digit mobile number (no leading 0).');
                    return false;
                }
                const hidden = $el('mobile_with_code');
                if (hidden) hidden.value = '+91' + m;
            }

            const exitDateEl = $el('exit_date');
            if ($el('status-inactive') && $el('status-inactive').checked && exitDateEl && !exitDateEl.value) {
                e.preventDefault();
                exitDateEl.focus();
                alert('Please provide Exit Date when status is Inactive.');
                return false;
            }
        });
    }
});
</script>

<script>
$(document).ready(function() {
    // Load sub-departments when parent department changes
    function loadSubDepartments(parentId, selectedId = null) {
        const $sub = $('#department_id');
        $sub.empty().append('<option value="">Select</option>');
        if (!parentId) return;

        let url = '{{ route("employees.sub-departments", ":id") }}';
        url = url.replace(':id', parentId);

        $.get(url)
         .done(function (data) {
            if (!Array.isArray(data)) return;
            data.forEach(function (dept) {
                const isSelected = selectedId && parseInt(selectedId) === parseInt(dept.id);
                let text = dept.dpt_name;
                if (dept.dpt_code) text += ' (' + dept.dpt_code + ')';
                $sub.append($('<option>', { value: dept.id, text: text, selected: isSelected }));
            });
         })
         .fail(function () { console.error('Failed to load sub-departments'); });
    }

    const initialParent = $('#prt_department_id').val();
    const selectedSub   = $('#department_id').data('selected');

    if (initialParent) loadSubDepartments(initialParent, selectedSub);

    $('#prt_department_id').on('change', function () {
        loadSubDepartments($(this).val(), null);
    });

    let mobileCheckAjax = null;
    $('#mobile_only_digits').on('blur', function() {
        const value = $(this).val().trim();
        $('#mobile-error').addClass('d-none').text('');
        $(this).removeClass('is-invalid');

        if (!value) return;

        if (!/^[1-9]\d{9}$/.test(value)) {
            $(this).addClass('is-invalid');
            $('#mobile-error').text('Please enter a valid 10-digit mobile number').removeClass('d-none').addClass('d-block');
            return;
        }

        $('#mobile_with_code').val('+91' + value);

        if (mobileCheckAjax) mobileCheckAjax.abort();

        mobileCheckAjax = $.ajax({
            url: '{{ route("employees.check-mobile") }}',
            method: 'POST',
            data: {
                mobile: value,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.exists) {
                    $('#mobile_only_digits').addClass('is-invalid');
                    $('#mobile-error').text('This mobile number is already registered').removeClass('d-none').addClass('d-block');
                }
            },
            error: function(xhr, status) {
                if (status !== 'abort') console.error('Mobile check failed');
            }
        });
    });

    $('.email-input').on('blur', function() {
        const email = $(this).val().trim();
        if (!email) return;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Handle parent department form submission
    $('#addPrtDptForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const data = $form.serialize();
        $.ajax({
            url: '{{ route('parent-departments.store') }}',
            method: 'POST',
            data: data,
            success: function(res) {
                if (res.status === 'success' && res.dpt) {
                    $('#prt_department_id').append(`<option value="${res.dpt.id}" selected>${res.dpt.dpt_name}</option>`);
                    $('#prt-dpt-list').append(`<tr id="prt-row-${res.dpt.id}"><td>#</td><td>${res.dpt.dpt_name}</td><td><button type="button" class="btn btn-sm btn-danger delete-prt" data-id="${res.dpt.id}">Delete</button></td></tr>`);
                    $form[0].reset();
                    $('#prtModal').modal('hide');
                } else {
                    $('#prt-group-error').removeClass('d-none').text(res.message || 'Something went wrong.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Error occurred.');
                $('#prt-group-error').removeClass('d-none').text(msg);
            }
        });
    });

    // Delete parent department
    $(document).on('click', '.delete-prt', function () {
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this parent department?')) return;
        $.ajax({
            url: `{{ url('parent-departments') }}/${id}`,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status === 'success') {
                    $(`#prt-row-${id}`).remove();
                    $(`#prt_department_id option[value="${id}"]`).remove();
                }
            }
        });
    });

    // Handle department form submission
    $('#addDptForm').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.ajax({
            url: '{{ route('departments.store') }}',
            method: 'POST',
            data: data,
            success: function(res) {
                if (res.status === 'success' && res.dpt) {
                    const currentParent = $('#prt_department_id').val();
                    loadSubDepartments(currentParent, res.dpt.id);
                    $('#dpt-list').append(`<tr id="dpt-row-${res.dpt.id}"><td>#</td><td>${res.dpt.parent_name ? res.dpt.parent_name : 'N/A'}</td><td>${res.dpt.dpt_name}</td><td><button type="button" class="btn btn-sm btn-danger delete-dpt" data-id="${res.dpt.id}">Delete</button></td></tr>`);
                    $('#addDptForm')[0].reset();
                    $('#dptModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added', text: 'Department added successfully', timer: 1400, showConfirmButton: false });
                } else {
                    $('#dpt-group-error').removeClass('d-none').text(res.message || 'Error occurred.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Error occurred.');
                $('#dpt-group-error').removeClass('d-none').text(msg);
            }
        });
    });

    // Delete department
    $(document).on('click', '.delete-dpt', function () {
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this department?')) return;
        $.ajax({
            url: `{{ url('departments') }}/${id}`,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status === 'success') {
                    $(`#dpt-row-${id}`).remove();
                    $(`#department_id option[value="${id}"]`).remove();
                }
            }
        });
    });

    // Edit designation button click
    $(document).on('click', '.edit-designation', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const level = $(this).data('level') || '';

        $('#designationName').val(name);
        $('#designationLevel').val(level);
        $('#edit_designation_id').val(id);
        $('#saveDesignationBtn').text('Update Designation');
        $('#designation-error').addClass('d-none').text('');
    });

    // Delete designation
    $(document).on('click', '.delete-designation', function () {
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this designation? This may affect existing employees.')) return;

        $.ajax({
            url: `{{ url('designations') }}/${id}`,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.status === 'success') {
                    $(`#des-row-${id}`).remove();
                    $(`#designation_id option[value="${id}"]`).remove();
                    Swal.fire({ icon: 'success', title: 'Deleted', text: 'Designation deleted successfully', timer: 1400, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to delete designation' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete designation. Please try again.' });
            }
        });
    });

    // Handle designation form submission (Add/Edit)
    $('#addDesignationForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#saveDesignationBtn');
        const name = $('#designationName').val().trim();
        const level = $('#designationLevel').val();
        const editId = $('#edit_designation_id').val();
        const token = '{{ csrf_token() }}';
        const isEditMode = editId !== '';

        $('#designation-error').addClass('d-none').text('');

        if (!name) {
            $('#designation-error').removeClass('d-none').text('Please enter a designation name.');
            return;
        }

        if (level === '' || level === null) {
            $('#designation-error').removeClass('d-none').text('Please enter a level between 0-6.');
            return;
        }

        const levelNum = parseInt(level);
        if (isNaN(levelNum) || levelNum < 0 || levelNum > 6) {
            $('#designation-error').removeClass('d-none').text('Level must be a whole number between 0-6.');
            return;
        }

        $btn.prop('disabled', true);

        let url, method, data;
        if (isEditMode) {
            url = `{{ url('designations') }}/${editId}`;
            method = 'POST';
            data = { _token: token, _method: 'PUT', name: name, level: levelNum, status: 'Active' };
        } else {
            url = '{{ route('designations.ajax.store') }}';
            method = 'POST';
            data = { _token: token, name: name, level: levelNum, status: 'Active' };
        }

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function (res) {
                if (res.designation && res.designation.id) {
                    let label = res.designation.name;
                    if (res.designation.unique_code) label += ` (${res.designation.unique_code})`;
                    if (res.designation.level !== null) label += ` - Level ${res.designation.level}`;

                    if (isEditMode) {
                        $(`#des-row-${res.designation.id} td:first`).text(res.designation.name);
                        $(`#des-row-${res.designation.id} td:nth-child(2)`).text(res.designation.level !== null ? res.designation.level : 'Not Set');
                        $(`#des-row-${res.designation.id} .edit-designation`).data('name', res.designation.name).data('level', res.designation.level !== null ? res.designation.level : '');
                        const $option = $(`#designation_id option[value="${res.designation.id}"]`);
                        if ($option.length) $option.text(label);
                        Swal.fire({ icon: 'success', title: 'Updated', text: 'Designation updated successfully', timer: 1400, showConfirmButton: false });
                    } else {
                        const newRow = `<tr id="des-row-${res.designation.id}"><td>${res.designation.name}</td><td>${res.designation.level !== null ? res.designation.level : 'Not Set'}</td><td><button type="button" class="btn btn-sm btn-warning edit-designation" data-id="${res.designation.id}" data-name="${res.designation.name}" data-level="${res.designation.level !== null ? res.designation.level : ''}" title="Edit"><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-sm btn-danger delete-designation" data-id="${res.designation.id}" title="Delete"><i class="fas fa-trash"></i></button></td></tr>`;
                        $('#designation-list').append(newRow);
                        $('#designation_id').append(`<option value="${res.designation.id}">${label}</option>`);
                        Swal.fire({ icon: 'success', title: 'Created', text: 'Designation created successfully', timer: 1400, showConfirmButton: false });
                    }

                    $('#addDesignationForm')[0].reset();
                    $('#edit_designation_id').val('');
                    $('#saveDesignationBtn').text('Add Designation');
                    if (!isEditMode) $('#designationModal').modal('hide');
                } else {
                    $('#designation-error').removeClass('d-none').text(res.message || 'Operation completed but unexpected response.');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                    if (msg.toLowerCase().includes('duplicate') || msg.toLowerCase().includes('already exists')) {
                        msg = 'This designation name already exists. Please choose a different name.';
                    }
                } else if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                $('#designation-error').removeClass('d-none').text(msg);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // Initialize Select2 for country and language
    function formatCountry(state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    if ($('#country').length) {
        $('#country').select2({
            theme: "bootstrap-5",
            templateResult: formatCountry,
            templateSelection: formatCountry,
            placeholder: "Select Country",
            allowClear: true
        });
    }

    if ($('#language').length) {
        $('#language').select2({
            theme: "bootstrap-5",
            templateResult: formatCountry,
            templateSelection: formatCountry,
            placeholder: "Select Language",
            allowClear: true
        });
    }
});
</script>
@endpush

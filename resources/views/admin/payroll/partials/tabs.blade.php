<div class="payroll-tabs">
    <a href="{{ route('payroll.index') }}" class="payroll-tab-link {{ request()->routeIs('payroll.index') ? 'active' : '' }}">
        <span>📊</span> Dashboard
    </a>
    <a href="{{ route('payroll.salary-structures.index') }}" class="payroll-tab-link {{ request()->routeIs('payroll.salary-structures.*') ? 'active' : '' }}">
        <span>🏗️</span> Salary Structure
    </a>
    <a href="{{ route('payroll.employee-salary.index') }}" class="payroll-tab-link {{ request()->routeIs('payroll.employee-salary.*') ? 'active' : '' }}">
        <span>👤</span> Employee Salary
    </a>
    <a href="{{ route('payroll.processing') }}" class="payroll-tab-link {{ (request()->routeIs('payroll.processing') || request()->routeIs('payroll.preview')) ? 'active' : '' }}">
        <span>⚙️</span> Processing
    </a>
    <a href="{{ route('payroll.history') }}" class="payroll-tab-link {{ request()->routeIs('payroll.history') ? 'active' : '' }}">
        <span>📚</span> History
    </a>
    <a href="{{ route('payroll.payslips.index') }}" class="payroll-tab-link {{ (request()->routeIs('payroll.payslips.*') || request()->routeIs('payroll.payslip.*')) ? 'active' : '' }}">
        <span>🧾</span> Payslips
    </a>
    <a href="{{ route('payroll.formulas.index') }}" class="payroll-tab-link {{ request()->routeIs('payroll.formulas.*') ? 'active' : '' }}">
        <span>📐</span> Formulas & Rules
    </a>
</div>

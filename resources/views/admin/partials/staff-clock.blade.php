<section class="role-panel staff-clock-panel" id="staff-clock-panel" data-live-preserve>
    <h3><i class="bx bx-time-five"></i> My attendance</h3>
    <p>Clock in for your shift and clock out when you finish.</p>
    <div class="staff-clock-times">
        <span>Clock in <strong>{{ $attendance?->clock_in ?: 'Not clocked in' }}</strong></span>
        <span>Clock out <strong>{{ $attendance?->clock_out ?: 'Pending' }}</strong></span>
        <span>Worked <strong>{{ $attendance?->clock_out ? $attendance->total_duration : ($attendance?->clock_in ? 'Shift in progress' : '00:00:00') }}</strong></span>
    </div>
    <div class="staff-clock-actions">
    @if($attendance?->clock_in && ! $attendance->clock_out)
        <form method="POST" action="{{ route('dashboard.clockout') }}" id="employeeClockOutForm">
            @csrf
            <input type="hidden" name="clock_out_timezone" id="clockOutTimezone">
            <input type="hidden" name="clock_out_selfie" id="clockOutSelfie">
            <button class="employee-action-btn is-out" type="submit"><i class="bx bx-log-out-circle"></i> Clock Out</button>
        </form>
    @elseif(! $attendance?->clock_in)
        <form method="POST" action="{{ route('dashboard.clockin') }}" id="employeeClockInForm">
            @csrf
            <input type="hidden" name="clock_in_latitude" id="clockInLatitude">
            <input type="hidden" name="clock_in_longitude" id="clockInLongitude">
            <input type="hidden" name="clock_in_accuracy" id="clockInAccuracy">
            <input type="hidden" name="clock_in_address" id="clockInAddress">
            <input type="hidden" name="clock_in_selfie" id="clockInSelfie">
            <input type="hidden" name="clock_in_timezone" id="clockInTimezone">
            <button class="employee-action-btn is-in" type="submit" id="employeeClockInButton"><i class="bx bx-log-in-circle"></i> Clock In</button>
        </form>
    @else
        <span class="employee-complete"><i class="bx bx-check-circle"></i> Shift completed</span>
    @endif
        <a class="btn btn-outline-primary" href="{{ route('attendance.index') }}">My attendance</a>
        <a class="btn btn-outline-primary" href="{{ route('leaves.index') }}">My leaves</a>
        <a class="btn btn-primary" href="{{ route('leaves.create') }}">Apply leave</a>
    </div>
    <div id="clockRequirementStatus" role="status"></div>
    <p class="staff-clock-note">Your company admin manages attendance corrections and reviews your leave requests.</p>
    @if($attendance?->clock_in_address)<p>{{ $attendance->clock_in_address }}</p>@endif
</section>
@include('admin.partials.staff-clock-camera')
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/assets/css/pms-staff-clock.css') }}?v={{ filemtime(public_path('admin/assets/css/pms-staff-clock.css')) }}">
@endpush
@push('js')
<script src="{{ asset('admin/assets/js/pms-staff-clock.js') }}?v={{ filemtime(public_path('admin/assets/js/pms-staff-clock.js')) }}"></script>
@endpush

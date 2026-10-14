@php($clockWindowNote = \App\Services\AttendanceClockPolicy::windowSummary(auth()->user()))
@if($clockWindowNote)
    <div class="clock-window-note" role="note" style="display:flex;gap:6px;align-items:flex-start;font-size:12px;line-height:1.4;margin-top:8px;opacity:.9;">
        <i class="bx bx-time" aria-hidden="true" style="margin-top:2px;"></i>
        <span>{{ $clockWindowNote }}. A live photo is required to clock in.</span>
    </div>
@endif

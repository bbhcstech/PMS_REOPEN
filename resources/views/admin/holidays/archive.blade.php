@extends('admin.layout.app')
@section('title', 'Archived Holidays')

@section('content')
<div class="holiday-archive-page">
    <div class="archive-header">
        <div class="archive-title">
            <div class="archive-icon"><i class="fas fa-box-archive"></i></div>
            <div>
                <h1>Archived Holidays</h1>
                <p>Restore archived holiday records whenever they need to return to the active holiday list.</p>
            </div>
        </div>
        <a href="{{ route('holidays.index') }}" class="archive-back-btn">
            <i class="fas fa-arrow-left"></i> Back to Holidays
        </a>
    </div>

    @if(session('success'))
        <div class="archive-alert success"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="archive-alert error"><i class="fas fa-exclamation-circle"></i>{{ session('error') }}</div>
    @endif

    @php
        $items = $holidays->getCollection();
        $totalArchived = method_exists($holidays, 'total') ? $holidays->total() : $holidays->count();
        $weeklyCount = $items->where('type', 'weekly_holiday')->count();
        $specialCount = $items->where('type', '!=', 'weekly_holiday')->count();
        $monthlyArchived = $items->filter(fn ($holiday) => $holiday->archived_at && $holiday->archived_at->isCurrentMonth())->count();
    @endphp

    <div class="archive-stats">
        <div class="archive-stat"><span><i class="fas fa-archive"></i></span><div><small>Total Archived</small><strong>{{ $totalArchived }}</strong></div></div>
        <div class="archive-stat"><span><i class="fas fa-repeat"></i></span><div><small>Weekly</small><strong>{{ $weeklyCount }}</strong></div></div>
        <div class="archive-stat"><span><i class="fas fa-star"></i></span><div><small>Special</small><strong>{{ $specialCount }}</strong></div></div>
        <div class="archive-stat"><span><i class="fas fa-calendar-alt"></i></span><div><small>This Month</small><strong>{{ $monthlyArchived }}</strong></div></div>
    </div>

    <div class="archive-card">
        <div class="archive-card-head">
            <div>
                <h3>Archived Holiday Records</h3>
                <p>Search archived dates and restore them to the active calendar.</p>
            </div>
            <span class="archive-total"><i class="fas fa-database"></i>Total: {{ $totalArchived }}</span>
        </div>

        <form method="GET" action="{{ route('holidays.archive') }}" class="archive-search">
            <div class="archive-search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search archived holidays...">
            </div>
            <select name="per_page">
                @foreach([10, 20, 30, 40, 50, 100] as $size)
                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            @if(request()->hasAny(['search', 'per_page']))
                <a href="{{ route('holidays.archive') }}"><i class="fas fa-rotate-left"></i> Reset</a>
            @endif
        </form>

        <div class="table-responsive">
            @if($holidays->isEmpty())
                <div class="archive-empty">
                    <i class="fas fa-calendar-check"></i>
                    <h5>No Archived Holidays Found</h5>
                    <p>There are no holiday records in the archive right now.</p>
                </div>
            @else
                <table class="archive-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Holiday Reason</th>
                            <th>Type</th>
                            <th>Archived On</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holidays as $index => $holiday)
                            @php $date = \Carbon\Carbon::parse($holiday->date); @endphp
                            <tr>
                                <td>{{ $holidays->firstItem() + $index }}</td>
                                <td><strong>{{ $date->format('d M Y') }}</strong></td>
                                <td><span class="archive-day">{{ $date->format('l') }}</span></td>
                                <td>
                                    <div class="archive-reason">{{ $holiday->occassion ?: $holiday->title }}</div>
                                    <small>{{ $date->format('F Y') }}</small>
                                </td>
                                <td><span class="archive-type {{ $holiday->type === 'weekly_holiday' ? 'weekly' : 'special' }}">{{ $holiday->type === 'weekly_holiday' ? 'Weekly Holiday' : 'Special Holiday' }}</span></td>
                                <td>{{ $holiday->archived_at ? $holiday->archived_at->format('d M Y h:i A') : 'Unknown' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('holidays.restore', $holiday->id) }}" method="POST" onsubmit="return confirm('Restore this holiday to the active list?');">
                                        @csrf
                                        <button class="archive-restore-btn" type="submit"><i class="fas fa-trash-restore"></i> Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($holidays->hasPages())
                    <div class="archive-pagination">
                        <span>Showing {{ $holidays->firstItem() }} to {{ $holidays->lastItem() }} of {{ $holidays->total() }}</span>
                        {{ $holidays->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .holiday-archive-page { min-height: 100vh; padding: 30px 35px; background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%); color: #0a2e1f; }
    .archive-header, .archive-card, .archive-stat { background: rgba(255,255,255,.96); border: 1px solid rgba(16,185,129,.12); box-shadow: 0 18px 38px -18px rgba(16,185,129,.18); }
    .archive-header { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 28px 32px; border-radius: 28px; margin-bottom: 24px; animation: archiveRise .45s ease both; }
    .archive-title { display: flex; gap: 18px; align-items: center; }
    .archive-icon { width: 68px; height: 68px; border-radius: 22px; display: flex; align-items: center; justify-content: center; color: #fff; background: linear-gradient(145deg, #f59e0b, #d97706); font-size: 30px; }
    .archive-header h1 { margin: 0 0 6px; font-size: 34px; font-weight: 800; letter-spacing: 0; }
    .archive-header p { margin: 0; color: #5a6e63; font-weight: 600; }
    .archive-back-btn, .archive-search button, .archive-search a, .archive-restore-btn { border: 0; border-radius: 14px; min-height: 44px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 800; }
    .archive-back-btn { background: #f0f9f4; color: #0f744c; border: 1px solid rgba(16,185,129,.2); }
    .archive-alert { display: flex; gap: 10px; align-items: center; padding: 14px 18px; border-radius: 16px; margin-bottom: 18px; font-weight: 800; }
    .archive-alert.success { background: #d1fae5; color: #047857; } .archive-alert.error { background: #fee2e2; color: #b91c1c; }
    .archive-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .archive-stat { display: flex; gap: 14px; align-items: center; padding: 18px; border-radius: 18px; animation: archiveRise .5s ease both; }
    .archive-stat span { width: 48px; height: 48px; border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #047857; background: #d1fae5; font-size: 20px; }
    .archive-stat small { display: block; color: #667085; font-weight: 800; } .archive-stat strong { display: block; font-size: 2rem; line-height: 1; font-weight: 900; }
    .archive-card { border-radius: 24px; padding: 24px; animation: archiveRise .55s ease both; }
    .archive-card-head { display: flex; justify-content: space-between; gap: 14px; align-items: flex-start; margin-bottom: 18px; }
    .archive-card-head h3 { margin: 0; font-weight: 900; } .archive-card-head p { margin: 4px 0 0; color: #667085; font-weight: 650; }
    .archive-total { display: inline-flex; gap: 8px; align-items: center; padding: 8px 12px; border-radius: 999px; background: #ecfdf5; color: #047857; font-weight: 900; }
    .archive-search { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
    .archive-search-input { flex: 1; min-width: 240px; position: relative; }
    .archive-search-input i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #059669; }
    .archive-search input, .archive-search select { width: 100%; min-height: 46px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 10px 14px; font-weight: 650; }
    .archive-search input { padding-left: 40px; }
    .archive-search button { background: linear-gradient(145deg, #34d399, #059669); color: #fff; }
    .archive-search a { background: #f3f4f6; color: #374151; }
    .archive-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .archive-table th { padding: 14px; background: #f8fafc; color: #475467; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; }
    .archive-table td { padding: 15px 14px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
    .archive-table tbody tr:hover { background: #f0fdf4; }
    .archive-day, .archive-type { display: inline-flex; padding: 7px 10px; border-radius: 999px; font-weight: 900; font-size: .82rem; }
    .archive-day { background: #eef2ff; color: #3730a3; }
    .archive-type.weekly { background: #d1fae5; color: #047857; } .archive-type.special { background: #dbeafe; color: #1d4ed8; }
    .archive-reason { font-weight: 900; } .archive-reason + small { color: #667085; font-weight: 650; }
    .archive-restore-btn { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .archive-empty { text-align: center; padding: 50px 20px; color: #667085; } .archive-empty i { font-size: 4rem; color: #a7f3d0; margin-bottom: 14px; }
    .archive-pagination { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-top: 18px; color: #667085; font-weight: 700; }
    @keyframes archiveRise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 992px) { .archive-stats { grid-template-columns: repeat(2, minmax(0,1fr)); } .archive-header, .archive-card-head { flex-direction: column; } }
    @media (max-width: 576px) { .holiday-archive-page { padding: 16px; } .archive-stats { grid-template-columns: 1fr; } .archive-title { flex-direction: column; align-items: flex-start; } }
</style>
@endsection

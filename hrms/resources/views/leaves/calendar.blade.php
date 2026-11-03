@extends('layouts.app')

@section('content')
<div class="leave-calendar-page">
    <!-- Top Bar with Date Navigator & Controls -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold fs-5 text-dark">{{ $currentLabel }}</span>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('leaves.calendar', array_merge(request()->all(), ['date' => $prevDate])) }}" class="btn btn-outline-secondary py-1 px-2 border" title="Previous Week">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="{{ route('leaves.calendar', array_merge(request()->all(), ['date' => $nextDate])) }}" class="btn btn-outline-secondary py-1 px-2 border" title="Next Week">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            @if(request('date') && request('date') !== '2026-01-10')
                <a href="{{ route('leaves.calendar', array_merge(request()->except('date'), ['date' => '2026-01-10'])) }}" class="badge bg-light text-secondary text-decoration-none border px-2 py-1">
                    Reset to Default Week
                </a>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-list-check me-1"></i> Applications List
            </a>
            <a href="{{ route('leaves.apply') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-plus-lg me-1"></i> Apply for Leave
            </a>
        </div>
    </div>

    <!-- Page Title & Filter Bar Matching Screenshot 3 (P-HR) -->
    <div class="card card-custom p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h4 class="fw-bold mb-0 text-slate-900" style="font-size: 1.35rem;">Leave Calendar</h4>
            @if(request()->filled('search') || (request()->filled('team') && request('team') !== 'all') || (request()->filled('leave_type') && request('leave_type') !== 'all'))
                <a href="{{ route('leaves.calendar', ['date' => request('date', '2026-01-10')]) }}" class="btn btn-sm btn-outline-danger fw-semibold px-2 py-1" style="font-size: 0.78rem;">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('leaves.calendar') }}" id="filterForm">
            <input type="hidden" name="date" value="{{ $startDate->format('Y-m-d') }}">

            <div class="row g-2 align-items-center">
                <!-- Search Employee Input -->
                <div class="col-12 col-md-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="searchEmployeeInput" class="form-control ps-5" placeholder="Search employee by name..." value="{{ request('search') }}" autocomplete="off" style="border-radius: 10px; font-size: 0.88rem;">
                    </div>
                </div>

                <!-- Team / Department Filter -->
                <div class="col-6 col-md-2">
                    <select name="team" id="teamFilter" class="form-select" onchange="document.getElementById('filterForm').submit()" style="border-radius: 10px; font-size: 0.88rem;">
                        <option value="all" {{ !request('team') || request('team') === 'all' ? 'selected' : '' }}>Team (All)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_name }}" {{ request('team') === $dept->department_name ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Leave Type Filter -->
                <div class="col-6 col-md-2">
                    <select name="leave_type" id="leaveTypeFilter" class="form-select" onchange="document.getElementById('filterForm').submit()" style="border-radius: 10px; font-size: 0.88rem;">
                        <option value="all" {{ !request('leave_type') || request('leave_type') === 'all' ? 'selected' : '' }}>Leave Type (All)</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->name }}" {{ request('leave_type') === $type->name ? 'selected' : '' }}>
                                @if(str_contains($type->name, 'Paid')) 🌴
                                @elseif(str_contains($type->name, 'Sick')) 🤒
                                @elseif(str_contains($type->name, 'Casual')) ✨
                                @elseif(str_contains($type->name, 'Emergency')) ⚠️
                                @endif
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Display -->
                <div class="col-8 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px; font-size: 0.85rem;">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" value="{{ $rangeLabel }}" readonly style="border-radius: 0 10px 10px 0; font-size: 0.85rem; background: #fff;">
                    </div>
                </div>

                <!-- Filter Submit Button -->
                <div class="col-4 col-md-1 text-end">
                    <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-1" style="border-radius: 10px; font-size: 0.85rem;" title="Apply Filters">
                        <i class="bi bi-sliders"></i> Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Calendar Matrix Grid (Matching Screenshot 3) -->
    <div class="card card-custom p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 calendar-grid-table" id="calendarTable">
                <thead>
                    <tr class="text-center align-middle bg-light">
                        <th style="width: 220px; text-align: left; padding: 16px 20px;" class="fw-bold text-muted border-end">Team member</th>
                        @foreach($days as $day)
                            <th style="width: 130px; padding: 12px 6px;" class="day-head">
                                <div class="fw-bold text-dark fs-5">{{ $day['day'] }}</div>
                                <div class="text-muted fw-semibold" style="font-size: 0.78rem;">{{ $day['weekday'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="calendarTableBody">
                    @forelse($teamMembers as $emp)
                        <tr class="calendar-member-row" data-name="{{ strtolower($emp->name) }}" data-dept="{{ strtolower($emp->department->name ?? '') }}">
                            <!-- Team Member Column -->
                            <td class="member-cell">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-grip-vertical text-muted"></i>
                                    <img src="{{ $emp->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="{{ $emp->name }}">
                                    <div>
                                        <div class="fw-bold text-dark member-name-text" style="font-size: 0.88rem;">{{ $emp->name }}</div>
                                        <div class="text-muted" style="font-size: 0.74rem;">{{ $emp->department->name ?? 'General' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 7 Days Grid with Dynamic Leave Spans -->
                            @php
                                $skipUntil = -1;
                            @endphp

                            @for($i = 0; $i < 7; $i++)
                                @if($i < $skipUntil)
                                    @continue
                                @endif

                                @php
                                    $currentDateStr = $days[$i]['date'];

                                    // Find if any leave starts on this day (or was active from before day 0)
                                    $matchingLeave = $emp->leaves->first(function($l) use ($currentDateStr, $i, $days) {
                                        if ($l->status === 'rejected') return false;
                                        if ($l->start_date === $currentDateStr) return true;
                                        if ($i === 0 && $l->start_date < $days[0]['date'] && $l->end_date >= $currentDateStr) return true;
                                        return false;
                                    });
                                @endphp

                                @if($matchingLeave)
                                    @php
                                        // Calculate colspan: how many days within current 7-day window does this leave cover?
                                        $span = 1;
                                        for ($j = $i + 1; $j < 7; $j++) {
                                            if ($days[$j]['date'] <= $matchingLeave->end_date) {
                                                $span++;
                                            } else {
                                                break;
                                            }
                                        }
                                        $skipUntil = $i + $span;

                                        $typeName = $matchingLeave->leaveType->name ?? 'Paid Leave';
                                        $blockClass = 'leave-paid';
                                        $emoji = '🌴';

                                        if (str_contains($typeName, 'Sick')) {
                                            $blockClass = 'leave-sick';
                                            $emoji = '🤒';
                                        } elseif (str_contains($typeName, 'Casual')) {
                                            $blockClass = 'leave-casual';
                                            $emoji = '✨';
                                        } elseif (str_contains($typeName, 'Emergency')) {
                                            $blockClass = 'leave-emergency';
                                            $emoji = '⚠️';
                                        }
                                    @endphp
                                    <td colspan="{{ $span }}" class="p-1">
                                        <div class="leave-block {{ $blockClass }}">
                                            <div class="leave-title">{{ $emoji }} {{ $typeName }}</div>
                                            <div class="leave-note" title="{{ $matchingLeave->reason }}">
                                                Note: {{ $matchingLeave->reason ?? 'Leave scheduled.' }}
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td class="day-slot-empty"></td>
                                @endif
                            @endfor
                        </tr>
                    @empty
                        <tr id="noResultsRow">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                <div class="fw-bold text-dark fs-6">No matching leave records found</div>
                                <p class="small text-muted mb-3">Try adjusting your search criteria, team, or leave type filter.</p>
                                <a href="{{ route('leaves.calendar') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .calendar-grid-table {
        border-collapse: collapse;
        min-width: 900px;
    }
    .calendar-grid-table th, .calendar-grid-table td {
        border-color: #f1f5f9 !important;
        vertical-align: middle;
        height: 70px;
    }
    .member-cell {
        background-color: #ffffff;
        padding: 12px 18px !important;
        border-right: 2px solid #f1f5f9 !important;
    }
    .day-slot-empty {
        background-color: #ffffff;
        transition: background-color 0.15s ease;
    }
    .day-slot-empty:hover {
        background-color: #f8fafc;
    }
    .leave-block {
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 0.75rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .leave-block:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .leave-paid {
        background-color: #dcfce7;
        color: #166534;
        border-left: 4px solid #22c55e;
    }
    .leave-sick {
        background-color: #ffe4e6;
        color: #9f1239;
        border-left: 4px solid #f43f5e;
    }
    .leave-casual {
        background-color: #f3e8ff;
        color: #6b21a8;
        border-left: 4px solid #a855f7;
    }
    .leave-emergency {
        background-color: #fef9c3;
        color: #854d0e;
        border-left: 4px solid #eab308;
    }
    .leave-title {
        font-weight: 700;
        margin-bottom: 2px;
    }
    .leave-note {
        font-size: 0.68rem;
        opacity: 0.85;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchEmployeeInput');
        const rows = document.querySelectorAll('.calendar-member-row');
        const tableBody = document.getElementById('calendarTableBody');

        if (searchInput && rows.length > 0) {
            // Instant real-time client-side live search filtering
            searchInput.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach(function(row) {
                    const name = row.getAttribute('data-name') || '';
                    const dept = row.getAttribute('data-dept') || '';

                    if (!term || name.includes(term) || dept.includes(term)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Check if we need to show instant no results message
                let noResultEl = document.getElementById('clientNoResultsRow');
                if (visibleCount === 0) {
                    if (!noResultEl) {
                        noResultEl = document.createElement('tr');
                        noResultEl.id = 'clientNoResultsRow';
                        noResultEl.innerHTML = `
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-search fs-3 d-block mb-1 text-secondary"></i>
                                <span>No employees found matching "<strong>${term}</strong>"</span>
                            </td>
                        `;
                        tableBody.appendChild(noResultEl);
                    }
                } else if (noResultEl) {
                    noResultEl.remove();
                }
            });
        }
    });
</script>
@endpush

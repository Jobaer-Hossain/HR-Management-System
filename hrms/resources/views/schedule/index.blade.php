@extends('layouts.app')

@section('content')
<div class="schedule-planner-page">
    <!-- Top Header Bar Matching Screenshot 4 (Agendrix) -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-0 text-slate-900" style="font-size: 1.65rem; letter-spacing: -0.5px;">Schedule</h2>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Search Anything Input -->
            <div class="position-relative" style="width: 260px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control ps-5 pe-5" placeholder="Search anything" style="border-radius: 12px; font-size: 0.88rem; background: #fff;">
                <span class="position-absolute top-50 end-0 translate-middle-y me-2 badge bg-light text-muted border" style="font-size: 0.7rem;">⌘ F</span>
            </div>

            <!-- Date Range Selector -->
            <div class="border rounded-pill px-3 py-2 bg-white d-flex align-items-center gap-2" style="font-size: 0.85rem; font-weight: 600; color: #334155;">
                <i class="bi bi-calendar4 text-muted"></i>
                <span>{{ $selectedRange }}</span>
            </div>

            <!-- Filter Button -->
            <button class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px;" title="Filter Shifts">
                <i class="bi bi-sliders text-secondary"></i>
            </button>

            <!-- Add Shift Modal Button (Black circular plus) -->
            <button class="btn btn-dark rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px; background: #0f172a;" data-bs-toggle="modal" data-bs-target="#addShiftModal" title="Add Shift Slot">
                <i class="bi bi-plus-lg text-white"></i>
            </button>
        </div>
    </div>

    <!-- Shift Schedule Grid (Screenshot 4: Agendrix) -->
    <div class="card card-custom p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 schedule-table">
                <thead>
                    <tr class="text-center align-middle bg-light">
                        <th style="width: 220px; text-align: left; padding: 16px 24px;" class="fw-bold text-muted border-end">Employee Name</th>
                        @foreach($days as $day)
                            <th style="width: 135px; padding: 14px 6px;" class="day-col-header">
                                <span class="fw-bold text-secondary" style="font-size: 0.88rem;">{{ $day }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <!-- Employee Cell with 9h total hours badge -->
                            <td class="emp-profile-cell">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $emp->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" alt="{{ $emp->name }}">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">{{ $emp->name }}</div>
                                        <div class="text-muted" style="font-size: 0.74rem;">9h</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 7 Days Slots -->
                            @foreach($days as $day)
                                @php
                                    $slot = $emp->shiftSchedules->where('day_name', $day)->first();
                                @endphp
                                <td class="shift-slot-cell p-2">
                                    @if($slot)
                                        <div class="shift-card shift-{{ $slot->color ?? 'primary' }}">
                                            <div class="shift-time">{{ $slot->time_slot }}</div>
                                            <div class="shift-role">{{ $slot->role_title }}</div>
                                            <div class="shift-break">
                                                <i class="bi bi-pause"></i> {{ $slot->break_minutes }} m break
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No shift schedules recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal to Add New Shift Schedule -->
    <div class="modal fade" id="addShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 16px;">
                <form action="{{ route('schedule.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Assign Shift Slot</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Employee</label>
                            <select name="employee_id" class="form-select" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->designation->title ?? 'Staff' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Day of Week</label>
                            <select name="day_name" class="form-select" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Time Slot (e.g. 8:30 a - 3:30 p)</label>
                            <input type="text" name="time_slot" class="form-control" placeholder="8:30 a - 3:30 p" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Role Title (Clerk, Manager, Cashier)</label>
                            <input type="text" name="role_title" class="form-control" placeholder="Clerk" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Break (Minutes)</label>
                                <input type="number" name="break_minutes" class="form-control" value="25" min="0" max="120">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Color Theme</label>
                                <select name="color" class="form-select">
                                    <option value="primary">Teal / Cyan</option>
                                    <option value="warning">Amber / Yellow</option>
                                    <option value="success">Emerald Green</option>
                                    <option value="purple">Soft Purple</option>
                                    <option value="danger">Rose / Coral</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold">Save Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .schedule-table {
        border-collapse: collapse;
        min-width: 980px;
    }
    .schedule-table th, .schedule-table td {
        border-color: #f1f5f9 !important;
        vertical-align: middle;
        height: 82px;
    }
    .emp-profile-cell {
        background-color: #ffffff;
        padding: 12px 24px !important;
        border-right: 2px solid #f1f5f9 !important;
    }
    .shift-slot-cell {
        background-color: #fafbfc;
        transition: background-color 0.15s ease;
    }
    .shift-slot-cell:hover {
        background-color: #f1f5f9;
    }

    /* Shift Card Pills (Matching Screenshot 4 Agendrix) */
    .shift-card {
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 0.74rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .shift-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .shift-time {
        font-weight: 700;
        font-size: 0.78rem;
        line-height: 1.2;
    }
    .shift-role {
        font-size: 0.7rem;
        opacity: 0.85;
        margin-top: 1px;
    }
    .shift-break {
        font-size: 0.66rem;
        opacity: 0.75;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 2px;
    }

    /* Theme colors from Screenshot 4 */
    .shift-primary {
        background-color: #e0f2fe;
        color: #0369a1;
        border-left: 3px solid #0284c7;
    }
    .shift-warning {
        background-color: #fef3c7;
        color: #b45309;
        border-left: 3px solid #f59e0b;
    }
    .shift-success {
        background-color: #dcfce7;
        color: #15803d;
        border-left: 3px solid #16a34a;
    }
    .shift-purple {
        background-color: #ede9fe;
        color: #6d28d9;
        border-left: 3px solid #8b5cf6;
    }
    .shift-danger {
        background-color: #ffe4e6;
        color: #be123c;
        border-left: 3px solid #f43f5e;
    }
</style>
@endpush

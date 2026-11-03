@extends('layouts.app')

@section('content')
<div class="leave-calendar-page">
    <!-- Top Bar with Date Navigator & Controls -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold fs-5 text-dark">12 January 2026</span>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary py-1 px-2 border"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-outline-secondary py-1 px-2 border"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
        <div>
            <a href="{{ route('leaves.apply') }}" class="btn btn-primary fw-bold px-3 py-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-plus-lg me-1"></i> Apply for Leave
            </a>
        </div>
    </div>

    <!-- Page Title & Filter Bar Matching Screenshot 3 (P-HR) -->
    <div class="card card-custom p-4 mb-4">
        <h4 class="fw-bold mb-3 text-slate-900" style="font-size: 1.35rem;">Leave Calendar</h4>

        <div class="row g-2 align-items-center">
            <!-- Search Employee Input -->
            <div class="col-12 col-md-4">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control ps-5" placeholder="Search employee" style="border-radius: 10px; font-size: 0.88rem;">
                </div>
            </div>

            <!-- Team Filter -->
            <div class="col-6 col-md-2">
                <select class="form-select" style="border-radius: 10px; font-size: 0.88rem;">
                    <option selected>Team (All)</option>
                    <option>Human Resources</option>
                    <option>Engineering</option>
                    <option>IT</option>
                    <option>Operations</option>
                </select>
            </div>

            <!-- Leave Type Filter -->
            <div class="col-6 col-md-2">
                <select class="form-select" style="border-radius: 10px; font-size: 0.88rem;">
                    <option selected>Leave Type</option>
                    <option>🌴 Paid Leave</option>
                    <option>🤒 Sick Leave</option>
                    <option>✨ Casual Leaves</option>
                    <option>⚠️ Emergency Leave</option>
                </select>
            </div>

            <!-- Date Range Selector -->
            <div class="col-8 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px; font-size: 0.85rem;">
                        <i class="bi bi-calendar3"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" value="10 Jan 2026 - 16 Jan 2026" readonly style="border-radius: 0 10px 10px 0; font-size: 0.85rem; background: #fff;">
                </div>
            </div>

            <!-- Filter Icon Button -->
            <div class="col-4 col-md-1 text-end">
                <button class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-1" style="border-radius: 10px; font-size: 0.85rem;">
                    <i class="bi bi-sliders"></i> Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Calendar Matrix Grid (Matching Screenshot 3) -->
    <div class="card card-custom p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 calendar-grid-table">
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
                <tbody>
                    <!-- Row 1: Robert Fox (Human Resources) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Robert Fox">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Robert Fox</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">Human Resources</div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-paid">
                                <div class="leave-title"><i class="bi bi-tree-fill me-1"></i> Paid Leave</div>
                                <div class="leave-note">Note: During this time, I need to attend to an urgent family matter.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- Row 2: Ralph Edwards (Maintenance) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Ralph Edwards">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Ralph Edwards</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">Maintenance</div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="p-1">
                            <div class="leave-block leave-sick">
                                <div class="leave-title">🤒 Sick Leave</div>
                                <div class="leave-note">Note: Viral flu and fever.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- Row 3: Jane Cooper (HSEQ) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Jane Cooper">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Jane Cooper</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">HSEQ</div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-casual">
                                <div class="leave-title">✨ Casual Leaves</div>
                                <div class="leave-note">Note: Personal trip.</div>
                            </div>
                        </td>
                    </tr>

                    <!-- Row 4: Leslie Alexander (IT) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Leslie Alexander">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Leslie Alexander</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">IT</div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-sick">
                                <div class="leave-title">🤒 Sick Leave</div>
                                <div class="leave-note">Note: Medical checkup and rest.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- Row 5: Esther Howard (Manning) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Esther Howard">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Esther Howard</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">Manning</div>
                                </div>
                            </div>
                        </td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-paid">
                                <div class="leave-title"><i class="bi bi-tree-fill me-1"></i> Paid Leave</div>
                                <div class="leave-note">Note: Annual paid rest.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- Row 6: Guy Hawkins (Engineering) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Guy Hawkins">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Guy Hawkins</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">Engineering</div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-casual">
                                <div class="leave-title">✨ Casual Leaves</div>
                                <div class="leave-note">Note: Moving to new apartment.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <!-- Row 7: Bessie Cooper (Maintenance) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Bessie Cooper">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Bessie Cooper</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">Maintenance</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-1">
                            <div class="leave-block leave-sick">
                                <div class="leave-title">🤒 Sick Leave</div>
                                <div class="leave-note">Note: Eye infection.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-emergency">
                                <div class="leave-title">⚠️ Emergency Leave</div>
                                <div class="leave-note">Note: During this time, I need to attend to an urgent family matter.</div>
                            </div>
                        </td>
                    </tr>

                    <!-- Row 8: Kathryn Murphy (Operations) -->
                    <tr>
                        <td class="member-cell">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&h=100&fit=crop&crop=faces" class="avatar-sm" alt="Kathryn Murphy">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Kathryn Murphy</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">Operations</div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="2" class="p-1">
                            <div class="leave-block leave-sick">
                                <div class="leave-title">🤒 Sick Leave</div>
                                <div class="leave-note">Note: High fever rest.</div>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
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
    .leave-block {
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 0.75rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease;
    }
    .leave-block:hover {
        transform: translateY(-2px);
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

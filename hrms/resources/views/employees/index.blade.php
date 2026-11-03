@extends('layouts.app')

@section('content')
<div class="employees-page">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Employee Directory</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage staff records, departments, designations, and salary structures.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.mobile') }}" class="btn btn-outline-dark fw-bold px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-person-check"></i>
                <span>Self Check In/Out</span>
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-primary fw-bold px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add New Employee</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs Bar (Matching EasyTeam Prototype) -->
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
           class="status-tab-pill {{ !request('status') || request('status') === 'all' ? 'active' : '' }}">
            <span>All Staff</span>
            <span class="tab-count">{{ $statusCounts['all'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
           class="status-tab-pill {{ request('status') === 'active' ? 'active' : '' }}">
            <span class="status-dot bg-success"></span>
            <span>Active</span>
            <span class="tab-count">{{ $statusCounts['active'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'on_leave'])) }}"
           class="status-tab-pill {{ request('status') === 'on_leave' ? 'active' : '' }}">
            <span class="status-dot bg-warning"></span>
            <span>On Leave</span>
            <span class="tab-count">{{ $statusCounts['on_leave'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'on_boarding'])) }}"
           class="status-tab-pill {{ request('status') === 'on_boarding' ? 'active' : '' }}">
            <span class="status-dot bg-info"></span>
            <span>On Boarding</span>
            <span class="tab-count">{{ $statusCounts['on_boarding'] }}</span>
        </a>
        <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'inactive'])) }}"
           class="status-tab-pill {{ request('status') === 'inactive' ? 'active' : '' }}">
            <span class="status-dot bg-secondary"></span>
            <span>Inactive</span>
            <span class="tab-count">{{ $statusCounts['inactive'] }}</span>
        </a>
    </div>

    <!-- Search & Department Filter Bar -->
    <div class="card card-custom p-3 mb-4">
        <form method="GET" action="{{ route('employees.index') }}" id="employeeFilterForm">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="employeeSearchInput" class="form-control ps-5" placeholder="Search employee by name, code, email, or phone..." value="{{ request('search') }}" autocomplete="off" style="border-radius: 10px; font-size: 0.88rem;">
                    </div>
                </div>

                <div class="col-8 col-md-4">
                    <select name="department_id" class="form-select" onchange="document.getElementById('employeeFilterForm').submit()" style="border-radius: 10px; font-size: 0.88rem;">
                        <option value="all">Department (All Departments)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-4 col-md-3 text-end d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-outline-secondary d-flex align-items-center gap-1" style="border-radius: 10px; font-size: 0.85rem;">
                        <i class="bi bi-sliders"></i> Filter
                    </button>
                    @if(request('search') || (request('department_id') && request('department_id') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-danger d-flex align-items-center gap-1" style="border-radius: 10px; font-size: 0.85rem;">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Employee Directory Table -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="font-size: 0.78rem;">
                    <tr>
                        <th style="width: 24%;">Employee</th>
                        <th style="width: 18%;">Department & Role</th>
                        <th style="width: 18%;">Contact</th>
                        <th style="width: 18%;">Biometrics (Face ID)</th>
                        <th style="width: 11%;">Status</th>
                        <th style="text-align: end; width: 11%;">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.88rem;">
                    @forelse ($employees as $employee)
                        <tr id="employee-row-{{ $employee->id }}">
                            <!-- Employee Profile -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $employee->face_snapshot ?? $employee->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=faces' }}" class="avatar-sm" id="emp-avatar-{{ $employee->id }}" alt="{{ $employee->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        @if($employee->status === 'active')
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @elseif($employee->status === 'on_leave')
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-warning border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @elseif($employee->status === 'on_boarding')
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-info border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @else
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-secondary border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">{{ $employee->name }}</div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge bg-light text-muted border py-0 px-1" style="font-size: 0.7rem;">{{ $employee->employee_code ?? 'EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            <small class="text-muted" style="font-size: 0.72rem;">Joined: {{ $employee->joining_date ?? 'Jan 2024' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Department & Role -->
                            <td>
                                <span class="badge bg-light text-primary border mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                    {{ $employee->department->name ?? 'General' }}
                                </span>
                                <div class="small fw-semibold text-secondary" style="font-size: 0.8rem;">
                                    {{ $employee->designation->title ?? 'Staff' }}
                                </div>
                            </td>

                            <!-- Contact -->
                            <td>
                                <div class="text-dark" style="font-size: 0.82rem;"><i class="bi bi-envelope me-1 text-muted"></i>{{ $employee->email }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;"><i class="bi bi-telephone me-1 text-muted"></i>{{ $employee->phone ?? '+880 1700-000000' }}</div>
                            </td>

                            <!-- Biometrics (Face ID & Database Storage Option) -->
                            <td id="biometric-cell-{{ $employee->id }}">
                                @if($employee->face_descriptor)
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.74rem;">
                                            <i class="bi bi-shield-check me-1"></i> Enrolled
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; border-radius: 6px; height: 26px;" onclick="openFaceEnrollModal({{ $employee->id }}, '{{ addslashes($employee->name) }}', '{{ $employee->employee_code ?? 'EMP-'.$employee->id }}', '{{ $employee->face_snapshot ?? $employee->avatar }}')">
                                            <i class="bi bi-camera"></i> Update
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1 font-monospace" style="font-size: 0.68rem;">
                                        {{ $employee->face_biometric_id ?? 'BIO-FACE-'.$employee->id }}
                                    </small>
                                @else
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill" style="font-size: 0.74rem;">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                        <button type="button" class="btn btn-sm btn-primary py-0 px-2 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; border-radius: 6px; height: 26px;" onclick="openFaceEnrollModal({{ $employee->id }}, '{{ addslashes($employee->name) }}', '{{ $employee->employee_code ?? 'EMP-'.$employee->id }}', '{{ $employee->avatar }}')">
                                            <i class="bi bi-camera-fill"></i> Take Face
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.68rem;">128-D vector pending</small>
                                @endif
                            </td>

                            <!-- Status Pill -->
                            <td>
                                @if($employee->status === 'active')
                                    <span class="badge-approved d-inline-flex align-items-center gap-1">
                                        <span style="width: 6px; height: 6px; background-color: #16a34a; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                @elseif($employee->status === 'on_leave')
                                    <span class="badge-pending d-inline-flex align-items-center gap-1">
                                        <span style="width: 6px; height: 6px; background-color: #d97706; border-radius: 50%;"></span>
                                        On Leave
                                    </span>
                                @elseif($employee->status === 'on_boarding')
                                    <span class="badge d-inline-flex align-items-center gap-1" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem;">
                                        <span style="width: 6px; height: 6px; background-color: #0284c7; border-radius: 50%;"></span>
                                        On Boarding
                                    </span>
                                @else
                                    <span class="badge-rejected d-inline-flex align-items-center gap-1" style="background: #f1f5f9; color: #475569; border-color: #e2e8f0;">
                                        <span style="width: 6px; height: 6px; background-color: #64748b; border-radius: 50%;"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td style="text-align: end;">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.78rem;" title="Capture / Update Facial Biometrics in DB" onclick="openFaceEnrollModal({{ $employee->id }}, '{{ addslashes($employee->name) }}', '{{ $employee->employee_code ?? 'EMP-'.$employee->id }}', '{{ $employee->face_snapshot ?? $employee->avatar }}')">
                                        <i class="bi bi-camera-video"></i>
                                    </button>
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.78rem;" title="Edit Employee">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove {{ $employee->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 0.78rem;" title="Delete Employee">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                <div class="fw-bold text-dark fs-6">No employees found</div>
                                <p class="small text-muted mb-3">No staff members match the selected filter or search term.</p>
                                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Clean Bootstrap 5 Pagination -->
        @if($employees->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Take Facial Biometrics and Store in DB -->
<div class="modal fade" id="employeeFaceModal" tabindex="-1" aria-labelledby="employeeFaceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-bottom p-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-camera-video"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="employeeFaceModalLabel">Enroll Facial Biometrics in DB</h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Facial Recognition Biometrics</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 text-center">
                <!-- Target Employee Info Card -->
                <div class="d-flex align-items-center gap-3 p-3 mb-3 border rounded-3 bg-light text-start">
                    <img src="" id="modalEmpAvatar" class="avatar-sm" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;" alt="Avatar">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark" id="modalEmpName" style="font-size: 0.95rem;">Employee Name</div>
                        <span class="badge bg-secondary font-monospace" id="modalEmpCode" style="font-size: 0.72rem;">EMP-000</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem;">
                        <i class="bi bi-cpu me-1"></i> Ready
                    </span>
                </div>

                <!-- Camera Viewport with Reticle -->
                <div class="modal-camera-wrapper position-relative mx-auto mb-3" style="width: 280px; height: 280px; border-radius: 20px; overflow: hidden; background: #0f172a; border: 3px solid #2563eb; box-shadow: 0 10px 25px -5px rgba(37,99,235,0.4);">
                    <video id="modalFaceVideo" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                    
                    <div id="modalCamPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white p-3" style="background: rgba(15, 23, 42, 0.95);">
                        <i class="bi bi-person-bounding-box fs-1 mb-2 text-primary"></i>
                        <p class="small fw-semibold mb-0">Initializing Camera...</p>
                        <small class="text-white-50" style="font-size: 0.7rem;">Align face inside the frame</small>
                    </div>

                    <!-- HUD Reticle -->
                    <div class="modal-hud-reticle">
                        <div class="bracket top-l"></div>
                        <div class="bracket top-r"></div>
                        <div class="bracket btm-l"></div>
                        <div class="bracket btm-r"></div>
                        <div class="scan-laser-line"></div>
                    </div>
                </div>

                <canvas id="modalFaceCanvas" style="display: none;"></canvas>

                <!-- Feedback status alert -->
                <div id="modalEnrollAlert" class="alert alert-info py-2 small mb-3 text-start" style="font-size: 0.8rem; display: none;"></div>

                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold" id="modalRestartCamBtn" style="border-radius: 8px;">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset Camera
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4 py-2 fw-bold" id="modalCaptureStoreBtn" style="border-radius: 8px;">
                        <i class="bi bi-camera-fill me-1"></i> Capture & Store in DB
                    </button>
                </div>

                <div class="pt-2 border-top">
                    <label for="modalFaceFileInput" class="form-label text-muted small mb-1" style="font-size: 0.75rem;">
                        <i class="bi bi-upload me-1"></i> Or upload facial photo file directly:
                    </label>
                    <input type="file" id="modalFaceFileInput" accept="image/*" class="form-control form-control-sm" style="border-radius: 8px; font-size: 0.8rem;">
                </div>
            </div>

            <div class="modal-footer border-top bg-light p-3 px-4 d-flex justify-content-between">
                <small class="text-muted" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-check text-success"></i> Facial biometric data securely stored in MySQL DB
                </small>
                <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Modal Camera HUD */
    .modal-hud-reticle {
        position: absolute;
        top: 25px;
        left: 25px;
        right: 25px;
        bottom: 25px;
        pointer-events: none;
    }
    .modal-hud-reticle .bracket {
        position: absolute;
        width: 22px;
        height: 22px;
        border-color: #38bdf8;
        border-style: solid;
    }
    .modal-hud-reticle .top-l { top: 0; left: 0; border-width: 3px 0 0 3px; border-top-left-radius: 8px; }
    .modal-hud-reticle .top-r { top: 0; right: 0; border-width: 3px 3px 0 0; border-top-right-radius: 8px; }
    .modal-hud-reticle .btm-l { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-bottom-left-radius: 8px; }
    .modal-hud-reticle .btm-r { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-bottom-right-radius: 8px; }

    .scan-laser-line {
        position: absolute;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38bdf8, transparent);
        box-shadow: 0 0 8px #38bdf8;
        animation: modalScanLaser 2.2s infinite ease-in-out;
    }
    @keyframes modalScanLaser {
        0% { top: 5%; opacity: 0.2; }
        50% { top: 95%; opacity: 1; }
        100% { top: 5%; opacity: 0.2; }
    }
    /* Status Tab Pills (Matching EasyTeam Prototype) */
    .status-tab-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .status-tab-pill:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .status-tab-pill.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    .status-tab-pill .tab-count {
        background: #f1f5f9;
        color: #334155;
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 20px;
    }
    .status-tab-pill.active .tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush

@push('scripts')
<script>
let currentEnrollEmpId = null;
let modalVideo = null;
let modalStream = null;
let modalCanvas = null;
let modalPlaceholder = null;
let modalAlert = null;
let modalCaptureBtn = null;
let employeeFaceModalInstance = null;

function openFaceEnrollModal(empId, empName, empCode, empAvatar) {
    currentEnrollEmpId = empId;
    uploadedFileBase64 = null;
    const fin = document.getElementById('modalFaceFileInput');
    if (fin) fin.value = '';
    document.getElementById('modalEmpName').innerText = empName;
    document.getElementById('modalEmpCode').innerText = empCode;
    document.getElementById('modalEmpAvatar').src = empAvatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100';

    if (!modalAlert) modalAlert = document.getElementById('modalEnrollAlert');
    modalAlert.style.display = 'none';

    if (!employeeFaceModalInstance) {
        employeeFaceModalInstance = new bootstrap.Modal(document.getElementById('employeeFaceModal'));
    }
    employeeFaceModalInstance.show();
}

async function startModalCamera() {
    modalVideo = document.getElementById('modalFaceVideo');
    modalPlaceholder = document.getElementById('modalCamPlaceholder');

    try {
        if (modalStream) {
            modalStream.getTracks().forEach(t => t.stop());
        }
        modalStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 480, height: 480, facingMode: 'user' }
        });
        modalVideo.srcObject = modalStream;
        modalPlaceholder.style.display = 'none';
    } catch (err) {
        console.warn('Camera inaccessible or permission denied, using simulated capture fallback:', err);
        modalPlaceholder.style.display = 'flex';
        modalPlaceholder.innerHTML = `
            <i class="bi bi-camera-video-off fs-1 mb-2 text-warning"></i>
            <p class="small fw-semibold mb-0">Hardware Camera Offline</p>
            <small class="text-white-50" style="font-size: 0.7rem;">Click below to capture & store face embedding</small>
        `;
    }
}

function stopModalCamera() {
    if (modalStream) {
        modalStream.getTracks().forEach(t => t.stop());
        modalStream = null;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('employeeFaceModal');
    modalCanvas = document.getElementById('modalFaceCanvas');
    modalAlert = document.getElementById('modalEnrollAlert');
    modalCaptureBtn = document.getElementById('modalCaptureStoreBtn');
    const restartBtn = document.getElementById('modalRestartCamBtn');

    modalEl.addEventListener('shown.bs.modal', function() {
        startModalCamera();
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
        stopModalCamera();
    });

    if (restartBtn) {
        restartBtn.addEventListener('click', startModalCamera);
    }

    let uploadedFileBase64 = null;
    const fileInput = document.getElementById('modalFaceFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    uploadedFileBase64 = evt.target.result;
                    modalPlaceholder.style.display = 'flex';
                    modalPlaceholder.innerHTML = `
                        <img src="${uploadedFileBase64}" style="width: 100%; height: 100%; object-fit: cover;">
                    `;
                    modalAlert.style.display = 'block';
                    modalAlert.className = 'alert alert-info py-2 small mb-3 text-start';
                    modalAlert.innerHTML = `<i class="bi bi-image me-1"></i> Photo file loaded. Click "Capture & Store in DB" to save.`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    modalCaptureBtn.addEventListener('click', async function() {
        if (!currentEnrollEmpId) return;

        modalCaptureBtn.disabled = true;
        modalCaptureBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Extracting & Storing...`;
        modalAlert.style.display = 'block';
        modalAlert.className = 'alert alert-info py-2 small mb-3 text-start';
        modalAlert.innerHTML = `<i class="bi bi-cpu me-1"></i> Capturing facial biometrics and saving to database...`;

        // 1. Snapshot capture
        let snapshotData = null;
        if (modalStream && modalVideo && modalVideo.videoWidth > 0) {
            modalCanvas.width = modalVideo.videoWidth;
            modalCanvas.height = modalVideo.videoHeight;
            const ctx = modalCanvas.getContext('2d');
            ctx.drawImage(modalVideo, 0, 0);
            snapshotData = modalCanvas.toDataURL('image/jpeg', 0.85);
        } else if (uploadedFileBase64) {
            snapshotData = uploadedFileBase64;
        }

        if (!snapshotData) {
            modalAlert.className = 'alert alert-warning py-2 small mb-3 text-start';
            modalAlert.innerHTML = `<i class="bi bi-camera-video-off me-1"></i> Please start your camera or choose a photo file before enrolling.`;
            modalCaptureBtn.disabled = false;
            modalCaptureBtn.innerHTML = `<i class="bi bi-camera me-1"></i> Capture & Store Biometrics in DB`;
            return;
        }

        try {
            const res = await fetch("{{ route('employee.enroll-face') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    employee_id: currentEnrollEmpId,
                    face_snapshot: snapshotData
                })
            });

            const data = await res.json();
            if (data.success) {
                modalAlert.className = 'alert alert-success py-2 small mb-3 text-start';
                modalAlert.innerHTML = `<strong><i class="bi bi-check-circle-fill me-1"></i> Success!</strong> ${data.message}<br><small class="text-success font-monospace">Biometric ID: ${data.biometric_id}</small>`;

                // Dynamically update the row in the table
                const cell = document.getElementById('biometric-cell-' + currentEnrollEmpId);
                if (cell) {
                    cell.innerHTML = `
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.74rem;">
                                <i class="bi bi-shield-check me-1"></i> Enrolled
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; border-radius: 6px; height: 26px;" onclick="openFaceEnrollModal(${currentEnrollEmpId}, '${document.getElementById('modalEmpName').innerText}', '${document.getElementById('modalEmpCode').innerText}', '${snapshotData || document.getElementById('modalEmpAvatar').src}')">
                                <i class="bi bi-camera"></i> Update
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1 font-monospace" style="font-size: 0.68rem;">
                            ${data.biometric_id}
                        </small>
                    `;
                }

                if (snapshotData) {
                    const avatarImg = document.getElementById('emp-avatar-' + currentEnrollEmpId);
                    if (avatarImg) avatarImg.src = snapshotData;
                }

                setTimeout(() => {
                    if (employeeFaceModalInstance) employeeFaceModalInstance.hide();
                }, 1500);

            } else {
                modalAlert.className = 'alert alert-danger py-2 small mb-3 text-start';
                modalAlert.innerText = data.message || 'Enrollment failed.';
            }
        } catch (err) {
            console.error(err);
            modalAlert.className = 'alert alert-danger py-2 small mb-3 text-start';
            modalAlert.innerText = 'Network error storing biometrics in database.';
        } finally {
            modalCaptureBtn.disabled = false;
            modalCaptureBtn.innerHTML = `<i class="bi bi-camera-fill me-1"></i> Capture & Store in DB`;
        }
    });
});
</script>
@endpush

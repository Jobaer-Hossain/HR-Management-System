@extends('layouts.app')

@section('content')
<div class="page-heading d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow">ACCESS CONTROL</div>
        <h1 class="h3 fw-bold mb-1">User permissions</h1>
        <p class="text-muted mb-0">Control exactly which pages and actions each user can access.</p>
    </div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi bi-shield-check me-1"></i>Secure by default</span>
</div>

<div class="surface-card mb-4">
    <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="h5 fw-bold mb-1"><i class="bi bi-diagram-3 me-2 text-primary"></i>Role access matrix</h2>
            <p class="text-muted small mb-0">Set the default access for every dashboard role. User overrides are managed below.</p>
        </div>
        <form method="GET" action="{{ route('permissions.index') }}" class="d-flex align-items-center gap-2">
            <label for="rolePicker" class="small fw-semibold text-muted mb-0">Role</label>
            <select id="rolePicker" name="role" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 190px;">
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <form method="POST" action="{{ route('permissions.role.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="role" value="{{ $selectedRole }}">
        @php($protectedRole = in_array($selectedRole, ['super_admin', 'admin', 'owner'], true))
        <div class="permission-grid p-4">
            @foreach($labels as $permission => $label)
                <label class="permission-option {{ $protectedRole ? 'permission-locked' : '' }}">
                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" {{ !empty($roleEffective[$permission]) ? 'checked' : '' }} {{ $protectedRole ? 'disabled' : '' }}>
                    <span class="permission-check"><i class="bi bi-check"></i></span>
                    <span class="flex-grow-1"><strong>{{ $label }}</strong><small>{{ $permission }}</small></span>
                </label>
            @endforeach
        </div>
        <div class="p-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
            <small class="text-muted">
                @if($protectedRole)
                    <i class="bi bi-lock-fill me-1"></i>This platform role always has full access.
                @else
                    <i class="bi bi-lightning-charge me-1"></i>Role changes apply immediately to all users with this role.
                @endif
            </small>
            <button class="btn btn-primary px-4" type="submit" {{ $protectedRole ? 'disabled' : '' }}><i class="bi bi-shield-check me-1"></i>Save role access</button>
        </div>
    </form>
</div>

<div class="row g-4 align-items-start">
    <div class="col-12 col-xl-4">
        <div class="surface-card">
            <div class="p-4 border-bottom">
                <h2 class="h6 fw-bold mb-1">Select a user</h2>
                <p class="text-muted small mb-0">Role permissions are used as defaults. You can override them per user.</p>
            </div>
            <div class="list-group list-group-flush">
                @forelse($users as $user)
                    <a href="{{ route('permissions.index', ['user_id' => $user->id]) }}" class="list-group-item list-group-item-action px-4 py-3 {{ $selectedUser?->id === $user->id ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <span class="user-initial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <span class="flex-grow-1 min-w-0">
                                <span class="d-block fw-semibold text-truncate">{{ $user->name }}</span>
                                <small class="{{ $selectedUser?->id === $user->id ? 'text-white-50' : 'text-muted' }}">{{ ucwords(str_replace('_', ' ', $user->role)) }}</small>
                            </span>
                            <i class="bi bi-chevron-right small"></i>
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-muted">No users found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="surface-card">
            @if($selectedUser)
                <div class="p-4 border-bottom d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h2 class="h5 fw-bold mb-0">{{ $selectedUser->name }}</h2>
                            <span class="badge rounded-pill text-bg-light border">{{ ucwords(str_replace('_', ' ', $selectedUser->role)) }}</span>
                        </div>
                        <p class="text-muted small mb-0">Checked permissions are available to this user.</p>
                    </div>
                    <i class="bi bi-person-lock fs-3 text-primary"></i>
                </div>
                <form method="POST" action="{{ route('permissions.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <div class="permission-grid p-4">
                        @foreach($labels as $permission => $label)
                            <label class="permission-option">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" {{ !empty($effective[$permission]) ? 'checked' : '' }}>
                                <span class="permission-check"><i class="bi bi-check"></i></span>
                                <span class="flex-grow-1">
                                    <strong>{{ $label }}</strong>
                                    <small>{{ $permission }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="p-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Changes apply immediately after saving.</small>
                        <button class="btn btn-primary px-4" type="submit"><i class="bi bi-check2 me-1"></i>Save permissions</button>
                    </div>
                </form>
            @else
                <div class="empty-state p-5 text-center"><i class="bi bi-people fs-1 text-muted"></i><h2 class="h5 mt-3">Choose a user</h2></div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .surface-card { background: #fff; border: 1px solid #e5eaf1; border-radius: 18px; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); overflow: hidden; }
    .eyebrow { color: #2563eb; font-size: .7rem; font-weight: 800; letter-spacing: .12em; margin-bottom: .45rem; }
    .user-initial { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; color: #1d4ed8; background: #eff6ff; font-weight: 800; }
    .list-group-item.active .user-initial { color: #1d4ed8; background: #fff; }
    .permission-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .permission-option { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; border: 1px solid #e5eaf1; border-radius: 13px; cursor: pointer; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
    .permission-option:hover { border-color: #93c5fd; background: #f8fbff; }
    .permission-locked { cursor: not-allowed; opacity: .7; }
    .permission-option:has(input:checked) { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 2px rgba(37, 99, 235, .08); }
    .permission-option input { position: absolute; opacity: 0; pointer-events: none; }
    .permission-check { width: 22px; height: 22px; flex: 0 0 22px; display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid #cbd5e1; border-radius: 6px; color: #fff; background: #fff; }
    .permission-option input:checked + .permission-check { background: #2563eb; border-color: #2563eb; }
    .permission-check i { opacity: 0; font-size: .85rem; font-weight: 800; }
    .permission-option input:checked + .permission-check i { opacity: 1; }
    .permission-option strong, .permission-option small { display: block; }
    .permission-option strong { font-size: .86rem; }
    .permission-option small { color: #94a3b8; font-size: .72rem; margin-top: 2px; }
    @media (max-width: 767.98px) { .permission-grid { grid-template-columns: 1fr; padding: 1rem !important; } .surface-card .p-4 { padding: 1rem !important; } }
</style>
@endpush

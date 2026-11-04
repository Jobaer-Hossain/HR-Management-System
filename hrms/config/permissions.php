<?php

return [
    'roles' => [
        'super_admin' => ['*'],
        'admin' => ['*'],
        'owner' => ['*'],

        // HR Administrator: full staff management, face enrollment, leave approvals, attendance oversight
        'hr_manager' => [
            'dashboard.view', 'dashboard.hr',
            'employees.view', 'employees.create', 'employees.update', 'employees.delete', 'employees.enroll_face',
            'attendance.view', 'attendance.manage', 'attendance.face', 'attendance.self',
            'leaves.view', 'leaves.manage',
            'schedule.view', 'schedule.manage',
            'payroll.view',
        ],

        // Department Manager: team dashboard, team leaves, team schedules, team attendance
        'department_manager' => [
            'dashboard.view', 'dashboard.manager',
            'employees.view', // Scoped to department in controller
            'attendance.view', 'attendance.face', 'attendance.self',
            'leaves.view', 'leaves.manage',
            'schedule.view', 'schedule.manage',
            'payroll.self',
        ],

        // Finance Officer: payroll batches, disbursements, salary views
        'finance' => [
            'dashboard.view', 'dashboard.finance',
            'employees.view', // Read-only for salary structures
            'attendance.self',
            'payroll.view', 'payroll.manage',
        ],
        'finance_manager' => [
            'dashboard.view', 'dashboard.finance',
            'employees.view',
            'attendance.self',
            'payroll.view', 'payroll.manage',
        ],
        'payroll_officer' => [
            'dashboard.view', 'dashboard.finance',
            'employees.view',
            'attendance.self',
            'payroll.view', 'payroll.manage',
        ],

        // Regular Employee: strictly self-service only
        'employee' => [
            'dashboard.view',   // Personal portal only
            'attendance.self',  // Personal mobile check-in/out only
            'leaves.create',    // Apply for own leave only
            'leaves.self',      // View own leave balances on portal
            'schedule.self',    // View own shifts on portal
            'payroll.self',     // View own payslip only
        ],
    ],

    'labels' => [
        'dashboard.view' => 'View personal dashboard / portal',
        'dashboard.hr' => 'View HR executive dashboard',
        'dashboard.manager' => 'View manager team dashboard',
        'dashboard.finance' => 'View finance payroll dashboard',
        'employees.view' => 'View employee directory',
        'employees.create' => 'Add new employee records',
        'employees.update' => 'Edit employee records',
        'employees.delete' => 'Delete employee records',
        'employees.enroll_face' => 'Enroll staff facial biometrics in DB',
        'attendance.view' => 'View company-wide attendance logs',
        'attendance.manage' => 'Manage attendance overrides',
        'attendance.face' => 'Access facial scanner terminal',
        'attendance.self' => 'Use personal mobile check-in/out',
        'leaves.view' => 'View company leave management list',
        'leaves.create' => 'Submit leave applications',
        'leaves.manage' => 'Approve or reject employee leaves',
        'leaves.self' => 'View personal leave balances',
        'schedule.view' => 'View company shift planner',
        'schedule.manage' => 'Create or modify shift schedules',
        'schedule.self' => 'View personal work shifts',
        'payroll.view' => 'View corporate payroll runs',
        'payroll.manage' => 'Process payroll batches & payments',
        'payroll.self' => 'View personal payslip vouchers',
        'permissions.manage' => 'Manage system-wide permissions matrix',
    ],
];

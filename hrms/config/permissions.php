<?php

return [
    'roles' => [
        'hr_manager' => [
            'dashboard.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'attendance.view', 'attendance.manage', 'attendance.face', 'attendance.self', 'leaves.view', 'leaves.manage',
            'schedule.view', 'schedule.manage', 'payroll.view', 'payroll.manage', 'permissions.manage',
        ],
        'super_admin' => ['*'],
        'admin' => ['*'],
        'owner' => ['*'],
        'department_manager' => [
            'dashboard.view', 'employees.view', 'attendance.view', 'attendance.face', 'attendance.self',
            'leaves.view', 'leaves.manage', 'schedule.view', 'schedule.manage',
        ],
        'finance' => ['dashboard.view', 'employees.view', 'payroll.view', 'payroll.manage'],
        'finance_manager' => ['dashboard.view', 'employees.view', 'payroll.view', 'payroll.manage'],
        'payroll_officer' => ['dashboard.view', 'employees.view', 'payroll.view', 'payroll.manage'],
        'employee' => [
            'dashboard.view', 'attendance.view', 'attendance.self', 'leaves.view',
            'leaves.create', 'schedule.view', 'payroll.view',
        ],
    ],

    'labels' => [
        'dashboard.view' => 'View dashboards',
        'employees.view' => 'View employees',
        'employees.create' => 'Add employees',
        'employees.update' => 'Edit employees',
        'employees.delete' => 'Delete employees',
        'attendance.view' => 'View attendance',
        'attendance.manage' => 'Manage attendance',
        'attendance.face' => 'Use facial attendance',
        'attendance.self' => 'Use personal check-in/out',
        'leaves.view' => 'View leave records',
        'leaves.create' => 'Apply for leave',
        'leaves.manage' => 'Approve or reject leave',
        'schedule.view' => 'View schedules',
        'schedule.manage' => 'Manage schedules',
        'payroll.view' => 'View payroll',
        'payroll.manage' => 'Manage payroll',
        'permissions.manage' => 'Manage user permissions',
    ],
];

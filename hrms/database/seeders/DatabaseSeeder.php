<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\LeaveType;
use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\PayrollRun;
use App\Models\PayrollItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Multi-Tenant SaaS Organizations
        $tenant1 = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'EasyTeam Global Ltd.',
            'slug' => 'easyteam',
            'email' => 'contact@easyteam.com',
            'phone' => '+880-1711-000111',
            'status' => 'active',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'BDT',
        ]);

        $tenant2 = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Dhaka Enterprise Corp',
            'slug' => 'dhaka-enterprise',
            'email' => 'hr@dhaka-corp.com',
            'phone' => '+880-1811-222333',
            'status' => 'active',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'BDT',
        ]);

        // 2. Users (Role-based users matching SRS section 6)
        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'William Dolaz',
            'email' => 'admin@hrms.com',
            'password' => Hash::make('password123'),
            'role' => 'hr_manager',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
            'status' => 'active',
        ]);

        User::updateOrCreate(
            ['email' => 'superadmin@hrms.com'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'avatar' => null,
                'status' => 'active',
            ]
        );

        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Robert Fox',
            'email' => 'manager@hrms.com',
            'password' => Hash::make('password123'),
            'role' => 'department_manager',
            'avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100&auto=format&fit=crop&q=80',
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Jacob Jones',
            'email' => 'finance@hrms.com',
            'password' => Hash::make('password123'),
            'role' => 'finance',
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Cody Fisher',
            'email' => 'employee@hrms.com',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80',
            'status' => 'active',
        ]);

        // 3. Branches
        $branchHq = Branch::create([
            'tenant_id' => $tenant1->id,
            'branch_name' => 'Dhaka Corporate HQ (Banani)',
            'branch_code' => 'DHK-01',
            'city' => 'Dhaka',
            'address' => 'Road 11, Block D, Banani',
            'status' => 'active',
        ]);

        $branchCtg = Branch::create([
            'tenant_id' => $tenant1->id,
            'branch_name' => 'Chittagong Hub (Agrabad)',
            'branch_code' => 'CTG-01',
            'city' => 'Chittagong',
            'address' => 'Agrabad Commercial Area',
            'status' => 'active',
        ]);

        // 4. Departments
        $deptHr = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'Human Resources', 'department_code' => 'HR']);
        $deptEng = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $deptFin = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'Finance', 'department_code' => 'FIN']);
        $deptOps = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'Operations', 'department_code' => 'OPS']);
        $deptMaint = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'Maintenance', 'department_code' => 'MNT']);
        $deptHseq = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'HSEQ', 'department_code' => 'HSEQ']);
        $deptIt = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'IT', 'department_code' => 'IT']);
        $deptManning = Department::create(['tenant_id' => $tenant1->id, 'department_name' => 'Manning', 'department_code' => 'MAN']);

        // 5. Designations
        $desHrMgr = Designation::create(['tenant_id' => $tenant1->id, 'designation_name' => 'HR Manager', 'designation_level' => 'L5']);
        $desEngLead = Designation::create(['tenant_id' => $tenant1->id, 'designation_name' => 'Lead Full-Stack Engineer', 'designation_level' => 'L4']);
        $desClerk = Designation::create(['tenant_id' => $tenant1->id, 'designation_name' => 'Clerk', 'designation_level' => 'L2']);
        $desCashier = Designation::create(['tenant_id' => $tenant1->id, 'designation_name' => 'Cashier', 'designation_level' => 'L2']);
        $desOpsMgr = Designation::create(['tenant_id' => $tenant1->id, 'designation_name' => 'Operations Manager', 'designation_level' => 'L4']);
        $desSpecialist = Designation::create(['tenant_id' => $tenant1->id, 'designation_name' => 'Specialist', 'designation_level' => 'L3']);

        // 6. Leave Types (Matching Screenshot 3 P-HR Calendar)
        $leavePaid = LeaveType::create(['tenant_id' => $tenant1->id, 'name' => 'Paid Leave', 'code' => 'PL', 'icon' => 'palm', 'badge_color' => 'success', 'days_allowed' => 15, 'is_paid' => true]);
        $leaveSick = LeaveType::create(['tenant_id' => $tenant1->id, 'name' => 'Sick Leave', 'code' => 'SL', 'icon' => 'thermometer', 'badge_color' => 'danger', 'days_allowed' => 14, 'is_paid' => true]);
        $leaveCasual = LeaveType::create(['tenant_id' => $tenant1->id, 'name' => 'Casual Leaves', 'code' => 'CL', 'icon' => 'cup-hot', 'badge_color' => 'purple', 'days_allowed' => 10, 'is_paid' => true]);
        $leaveEmerg = LeaveType::create(['tenant_id' => $tenant1->id, 'name' => 'Emergency Leave', 'code' => 'EL', 'icon' => 'exclamation-triangle', 'badge_color' => 'warning', 'days_allowed' => 5, 'is_paid' => true]);

        // 7. Seed Key Employees (Exact roster from Prototypes: Screenshot 1, 3, 4)
        $staffData = [
            ['code' => 'EMP-101', 'name' => 'Cody Fisher', 'dept' => $deptEng->id, 'desig' => $desEngLead->id, 'status' => 'on_leave', 'salary' => 85000.00, 'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-102', 'name' => 'Robert Fox', 'dept' => $deptHr->id, 'desig' => $desHrMgr->id, 'status' => 'on_leave', 'salary' => 95000.00, 'avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-103', 'name' => 'Jenny Wilson', 'dept' => $deptOps->id, 'desig' => $desCashier->id, 'status' => 'on_leave', 'salary' => 45000.00, 'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-104', 'name' => 'Jacob Jones', 'dept' => $deptFin->id, 'desig' => $desClerk->id, 'status' => 'on_leave', 'salary' => 52000.00, 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-105', 'name' => 'Darrell Steward', 'dept' => $deptOps->id, 'desig' => $desClerk->id, 'status' => 'active', 'salary' => 48000.00, 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-106', 'name' => 'Brooklyn Simmons', 'dept' => $deptHr->id, 'desig' => $desHrMgr->id, 'status' => 'active', 'salary' => 78000.00, 'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-107', 'name' => 'Dianne Russell', 'dept' => $deptFin->id, 'desig' => $desClerk->id, 'status' => 'active', 'salary' => 54000.00, 'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-108', 'name' => 'Leslie Alexander', 'dept' => $deptIt->id, 'desig' => $desSpecialist->id, 'status' => 'active', 'salary' => 88000.00, 'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-109', 'name' => 'Floyd Miles', 'dept' => $deptOps->id, 'desig' => $desOpsMgr->id, 'status' => 'active', 'salary' => 72000.00, 'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-110', 'name' => 'Cameron Williamson', 'dept' => $deptMaint->id, 'desig' => $desClerk->id, 'status' => 'active', 'salary' => 42000.00, 'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-111', 'name' => 'Devon Lane', 'dept' => $deptFin->id, 'desig' => $desClerk->id, 'status' => 'inactive', 'salary' => 46000.00, 'avatar' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-112', 'name' => 'Esther Howard', 'dept' => $deptManning->id, 'desig' => $desOpsMgr->id, 'status' => 'active', 'salary' => 74000.00, 'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-113', 'name' => 'Ralph Edwards', 'dept' => $deptMaint->id, 'desig' => $desClerk->id, 'status' => 'on_boarding', 'salary' => 40000.00, 'avatar' => 'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-114', 'name' => 'Kristin Watson', 'dept' => $deptOps->id, 'desig' => $desCashier->id, 'status' => 'on_boarding', 'salary' => 44000.00, 'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-115', 'name' => 'Annette Black', 'dept' => $deptFin->id, 'desig' => $desClerk->id, 'status' => 'active', 'salary' => 50000.00, 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-116', 'name' => 'Jane Cooper', 'dept' => $deptHseq->id, 'desig' => $desSpecialist->id, 'status' => 'active', 'salary' => 62000.00, 'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-117', 'name' => 'Guy Hawkins', 'dept' => $deptEng->id, 'desig' => $desEngLead->id, 'status' => 'active', 'salary' => 70000.00, 'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-118', 'name' => 'Bessie Cooper', 'dept' => $deptMaint->id, 'desig' => $desClerk->id, 'status' => 'active', 'salary' => 45000.00, 'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&auto=format&fit=crop&q=80'],
            ['code' => 'EMP-119', 'name' => 'Kathryn Murphy', 'dept' => $deptOps->id, 'desig' => $desOpsMgr->id, 'status' => 'active', 'salary' => 75000.00, 'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&auto=format&fit=crop&q=80'],
        ];

        // ArcFace (buffalo_l) 512-Dimensional Unit-Normalized Feature Embedding Generator
        $genBioVector = function($seed) {
            mt_srand(crc32($seed));
            $vec = [];
            $sum = 0;
            for ($i = 0; $i < 512; $i++) {
                $v = mt_rand(-1000, 1000) / 1000.0;
                $vec[] = $v;
                $sum += $v * $v;
            }
            $mag = sqrt($sum);
            for ($i = 0; $i < 512; $i++) {
                $vec[$i] = round($vec[$i] / $mag, 5);
            }
            return $vec;
        };

        $employees = [];
        foreach ($staffData as $item) {
            $empEmail = strtolower(str_replace(' ', '.', $item['name'])) . '@easyteam.com';
            $emp = Employee::create([
                'tenant_id' => $tenant1->id,
                'branch_id' => $branchHq->id,
                'department_id' => $item['dept'],
                'designation_id' => $item['desig'],
                'employee_code' => $item['code'],
                'name' => $item['name'],
                'first_name' => explode(' ', $item['name'])[0],
                'last_name' => explode(' ', $item['name'])[1] ?? '',
                'email' => $empEmail,
                'phone' => '+880-1700-' . rand(100000, 999999),
                'avatar' => $item['avatar'],
                'face_descriptor' => null, // Freshly empty: admin takes facial enrollment from Employee Directory
                'face_snapshot' => null,
                'face_enrolled_at' => null,
                'face_biometric_id' => null,
                'status' => $item['status'],
                'salary' => $item['salary'],
                'joining_date' => '2024-01-15',
                'employment_type' => 'full_time',
            ]);
            $employees[$item['name']] = $emp;

            // Also seed User login account for each employee
            $empRole = 'employee';
            if ($item['dept'] === $deptHr->id) {
                $empRole = 'hr_manager';
            } elseif ($item['dept'] === $deptFin->id) {
                $empRole = 'finance';
            } elseif ($item['desig'] === $desOpsMgr->id || $item['desig'] === $desEngLead->id) {
                $empRole = 'department_manager';
            }

            User::updateOrCreate(
                ['email' => $empEmail],
                [
                    'tenant_id' => $tenant1->id,
                    'name' => $item['name'],
                    'email' => $empEmail,
                    'password' => Hash::make('password123'),
                    'role' => $empRole,
                    'avatar' => $item['avatar'],
                    'status' => 'active',
                ]
            );
        }

        // 8. Seed Leave Applications (Matching Prototype Screenshot 1 & 3)
        $leaveEntries = [
            ['name' => 'Brooklyn Simmons', 'type' => $leavePaid->id, 'dates' => ['2026-01-20', '2026-01-22'], 'status' => 'pending', 'reason' => 'Annual family vacation'],
            ['name' => 'Dianne Russell', 'type' => $leaveSick->id, 'dates' => ['2026-01-18', '2026-01-18'], 'status' => 'pending', 'reason' => 'Doctor appointment & seasonal flu'],
            ['name' => 'Floyd Miles', 'type' => $leavePaid->id, 'dates' => ['2026-02-12', '2026-02-17'], 'status' => 'pending', 'reason' => 'Annual personal leave'],
            ['name' => 'Cameron Williamson', 'type' => $leavePaid->id, 'dates' => ['2026-01-01', '2026-01-02'], 'status' => 'approved', 'reason' => 'New Year family holiday'],
            ['name' => 'Devon Lane', 'type' => $leaveCasual->id, 'dates' => ['2026-01-01', '2026-01-02'], 'status' => 'rejected', 'reason' => 'Short notice leave request'],
            // Prototype Screenshot 3 P-HR Leaves:
            ['name' => 'Robert Fox', 'type' => $leavePaid->id, 'dates' => ['2026-01-11', '2026-01-12'], 'status' => 'approved', 'reason' => 'During this time, I need to attend to an urgent family matter.'],
            ['name' => 'Ralph Edwards', 'type' => $leaveSick->id, 'dates' => ['2026-01-13', '2026-01-13'], 'status' => 'approved', 'reason' => 'Headache and fever'],
            ['name' => 'Jane Cooper', 'type' => $leaveCasual->id, 'dates' => ['2026-01-15', '2026-01-16'], 'status' => 'approved', 'reason' => 'Personal family event.'],
            ['name' => 'Leslie Alexander', 'type' => $leaveSick->id, 'dates' => ['2026-01-11', '2026-01-12'], 'status' => 'approved', 'reason' => 'Medical appointment.'],
            ['name' => 'Esther Howard', 'type' => $leavePaid->id, 'dates' => ['2026-01-10', '2026-01-11'], 'status' => 'approved', 'reason' => 'Annual paid leave.'],
            ['name' => 'Guy Hawkins', 'type' => $leaveCasual->id, 'dates' => ['2026-01-12', '2026-01-13'], 'status' => 'approved', 'reason' => 'Personal relocation & moving'],
            ['name' => 'Bessie Cooper', 'type' => $leaveSick->id, 'dates' => ['2026-01-10', '2026-01-10'], 'status' => 'approved', 'reason' => 'Eye checkup.'],
            ['name' => 'Bessie Cooper', 'type' => $leaveEmerg->id, 'dates' => ['2026-01-15', '2026-01-16'], 'status' => 'approved', 'reason' => 'During this time, I need to attend to an urgent family matter.'],
            ['name' => 'Kathryn Murphy', 'type' => $leaveSick->id, 'dates' => ['2026-01-13', '2026-01-14'], 'status' => 'approved', 'reason' => 'High fever recovery.'],
        ];

        foreach ($leaveEntries as $entry) {
            if (isset($employees[$entry['name']])) {
                LeaveApplication::create([
                    'tenant_id' => $tenant1->id,
                    'employee_id' => $employees[$entry['name']]->id,
                    'leave_type_id' => $entry['type'],
                    'start_date' => $entry['dates'][0],
                    'end_date' => $entry['dates'][1],
                    'total_days' => 2,
                    'reason' => $entry['reason'],
                    'status' => $entry['status'],
                ]);
            }
        }

        // 9. Seed Shift Schedules (Matching Screenshot 4 Agendrix Grid)
        $scheduleData = [
            ['name' => 'Darrell Steward', 'day' => 'Sat', 'slot' => '8:30 a - 3:30 p', 'role' => 'Clerk', 'break' => 25, 'color' => 'primary'],
            ['name' => 'Darrell Steward', 'day' => 'Wed', 'slot' => '5:40 a - 2:50 p', 'role' => 'Cashier', 'break' => 25, 'color' => 'primary'],
            ['name' => 'Esther Howard', 'day' => 'Sun', 'slot' => '5:15 a - 10:10 p', 'role' => 'Manager', 'break' => 20, 'color' => 'warning'],
            ['name' => 'Esther Howard', 'day' => 'Tues', 'slot' => '7:20 a - 9:25 p', 'role' => 'Clerk', 'break' => 30, 'color' => 'success'],
            ['name' => 'Jenny Wilson', 'day' => 'Sat', 'slot' => '6:40 a - 3:30 p', 'role' => 'Cashier', 'break' => 30, 'color' => 'primary'],
            ['name' => 'Jenny Wilson', 'day' => 'Thu', 'slot' => '8:30 a - 3:30 p', 'role' => 'Cashier', 'break' => 20, 'color' => 'danger'],
            ['name' => 'Robert Fox', 'day' => 'Sat', 'slot' => '2:30 a - 3:30 p', 'role' => 'Manager', 'break' => 20, 'color' => 'warning'],
            ['name' => 'Robert Fox', 'day' => 'Mon', 'slot' => '10: a - 1:30 p', 'role' => 'Clerk', 'break' => 25, 'color' => 'success'],
            ['name' => 'Robert Fox', 'day' => 'Wed', 'slot' => '8:30 a - 4:55 p', 'role' => 'Cashier', 'break' => 20, 'color' => 'primary'],
            ['name' => 'Kristin Watson', 'day' => 'Sat', 'slot' => '7:20 a - 3:30 p', 'role' => 'Cashier', 'break' => 40, 'color' => 'primary'],
            ['name' => 'Kristin Watson', 'day' => 'Sun', 'slot' => '6:20 a - 12:30 p', 'role' => 'Cashier', 'break' => 35, 'color' => 'warning'],
            ['name' => 'Kristin Watson', 'day' => 'Mon', 'slot' => '2:00 a - 1:00 p', 'role' => 'Clerk', 'break' => 40, 'color' => 'success'],
            ['name' => 'Kristin Watson', 'day' => 'Tues', 'slot' => '3:00 a - 4:20 p', 'role' => 'Manager', 'break' => 30, 'color' => 'danger'],
            ['name' => 'Kristin Watson', 'day' => 'Wed', 'slot' => '5:50 a - 10:00 p', 'role' => 'Cashier', 'break' => 30, 'color' => 'purple'],
            ['name' => 'Kristin Watson', 'day' => 'Fri', 'slot' => '10:00 a - 3:30 p', 'role' => 'Cashier', 'break' => 30, 'color' => 'primary'],
            ['name' => 'Ralph Edwards', 'day' => 'Sat', 'slot' => '8:30 a - 3:30 p', 'role' => 'Manager', 'break' => 20, 'color' => 'warning'],
            ['name' => 'Ralph Edwards', 'day' => 'Tues', 'slot' => '9:20 a - 8:40 p', 'role' => 'Clerk', 'break' => 25, 'color' => 'success'],
            ['name' => 'Annette Black', 'day' => 'Sat', 'slot' => '2:20 a - 4:25 p', 'role' => 'Clerk', 'break' => 25, 'color' => 'primary'],
            ['name' => 'Annette Black', 'day' => 'Mon', 'slot' => '7:30 a - 2:40 p', 'role' => 'Cashier', 'break' => 30, 'color' => 'purple'],
            ['name' => 'Annette Black', 'day' => 'Thu', 'slot' => '4:20 a - 3:30 p', 'role' => 'Cashier', 'break' => 30, 'color' => 'danger'],
        ];

        foreach ($scheduleData as $sch) {
            if (isset($employees[$sch['name']])) {
                ShiftSchedule::create([
                    'tenant_id' => $tenant1->id,
                    'employee_id' => $employees[$sch['name']]->id,
                    'date' => date('Y-m-d'),
                    'day_name' => $sch['day'],
                    'time_slot' => $sch['slot'],
                    'role_title' => $sch['role'],
                    'break_minutes' => $sch['break'],
                    'color' => $sch['color'],
                    'status' => 'scheduled',
                ]);
            }
        }

        // 10. Seed Attendance (Matching Screenshot 2 Numla Check In / Check Out)
        foreach ($employees as $emp) {
            Attendance::create([
                'tenant_id' => $tenant1->id,
                'employee_id' => $emp->id,
                'attendance_date' => date('Y-m-d'),
                'check_in' => '09:41:00',
                'check_out' => null, // active day
                'worked_minutes' => 310,
                'status' => $emp->status === 'on_leave' ? 'on_leave' : 'present',
                'location_note' => 'You are 10 Km away from your office (Remote Check-In)',
                'source' => 'web_biometric',
            ]);
        }

        // 11. Seed Payroll Run (Exact numbers from Screenshot 1 EasyTeam: $512,872.92 Total, $321,272.90 Paid, $191,600.02 Unpaid)
        $payrollRun = PayrollRun::create([
            'tenant_id' => $tenant1->id,
            'month_year' => 'January 2026',
            'total_employees' => 128,
            'paid_count' => 98,
            'unpaid_count' => 30,
            'total_payroll' => 512872.92,
            'paid_amount' => 321272.90,
            'unpaid_amount' => 191600.02,
            'status' => 'processing',
        ]);

        $i = 0;
        foreach ($employees as $emp) {
            $i++;
            $basic = $emp->salary * 0.50; // 50% basic (Bangladesh standard)
            $houseRent = $emp->salary * 0.25; // 25% house rent
            $medical = 4000.00;
            $conveyance = 3000.00;
            $bonus = $i % 3 === 0 ? 5000.00 : 0.00;
            $gross = $basic + $houseRent + $medical + $conveyance + $bonus;

            $tax = $gross > 50000 ? $gross * 0.05 : 0;
            $pf = $basic * 0.08; // 8% PF
            $deductions = $tax + $pf;
            $net = $gross - $deductions;

            PayrollItem::create([
                'tenant_id' => $tenant1->id,
                'payroll_run_id' => $payrollRun->id,
                'employee_id' => $emp->id,
                'basic_salary' => $basic,
                'house_rent' => $houseRent,
                'medical_allowance' => $medical,
                'conveyance' => $conveyance,
                'festival_bonus' => $bonus,
                'gross_earnings' => $gross,
                'tax_deduction' => $tax,
                'provident_fund' => $pf,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                'payment_status' => $i < 10 ? 'paid' : 'unpaid',
                'payment_method' => 'bank_transfer',
                'transaction_ref' => 'BEFTN-202601-' . str_pad($emp->id, 5, '0', STR_PAD_LEFT),
                'paid_at' => $i < 10 ? now() : null,
            ]);
        }
    }
}

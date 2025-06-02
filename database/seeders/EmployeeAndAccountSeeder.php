<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeAndAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeNames = [
            // Format: ['FirstName', 'MiddleName', 'LastName'] for Head and Staff
            'PFMO' => [
                'head' => ['Ricardo', 'Santos', 'Cruz'],
                'staff' => ['Manuel', 'Garcia', 'Reyes']
            ],
            'REG' => [
                'head' => ['Maria', 'Lourdes', 'Santos'],
                'staff' => ['Angela', 'Cruz', 'Mendoza']
            ],
            'FIN' => [
                'head' => ['Roberto', 'Martinez', 'Tan'],
                'staff' => ['Christina', 'Santos', 'Lim']
            ],
            'PROP' => [
                'head' => ['Eduardo', 'Reyes', 'Garcia'],
                'staff' => ['Patricia', 'Luna', 'Torres']
            ],
            'ITM' => [
                'head' => ['Michael', 'David', 'Chen'],
                'staff' => ['Jennifer', 'Cruz', 'Wong']
            ],
            'HR' => [
                'head' => ['Victoria', 'Santos', 'Reyes'],
                'staff' => ['Ramon', 'Cruz', 'Gonzales']
            ],
            'LIB' => [
                'head' => ['Carmen', 'Luna', 'Santos'],
                'staff' => ['Francisco', 'Reyes', 'dela Cruz']
            ],
            'GUID' => [
                'head' => ['Beatrice', 'Santos', 'Aquino'],
                'staff' => ['Joseph', 'Cruz', 'Villanueva']
            ],
            'MDO' => [
                'head' => ['Dr. Antonio', 'Rivera', 'Santos'],
                'staff' => ['Nurse Maria', 'Cruz', 'Lim']
            ],
            'SEC' => [
                'head' => ['Roberto', 'Cruz', 'Mendoza'],
                'staff' => ['Juan', 'Santos', 'dela Paz']
            ],
            'CCS' => [
                'head' => ['Dr. Carlos', 'Martinez', 'Lim'],
                'staff' => ['Grace', 'Santos', 'Tan']
            ],
            'COE' => [
                'head' => ['Engr. Ramon', 'Cruz', 'Santos'],
                'staff' => ['Elena', 'Reyes', 'Garcia']
            ],
            'COED' => [
                'head' => ['Dr. Teresa', 'Santos', 'Cruz'],
                'staff' => ['Catherine', 'Luna', 'Reyes']
            ],
            'CAS' => [
                'head' => ['Dr. Manuel', 'Cruz', 'Reyes'],
                'staff' => ['Andrea', 'Santos', 'Luna']
            ],
            'CBA' => [
                'head' => ['Dr. Roberto', 'Tan', 'Lim'],
                'staff' => ['Michelle', 'Cruz', 'Chen']
            ],
            'CON' => [
                'head' => ['Dr. Maria', 'Santos', 'Flores'],
                'staff' => ['Nurse Joy', 'Cruz', 'Santos']
            ],
            'COA' => [
                'head' => ['Arch. Jose', 'Reyes', 'Santos'],
                'staff' => ['Daniel', 'Cruz', 'Mendoza']
            ],
            'GENED' => [
                'head' => ['Dr. Felipe', 'Santos', 'Cruz'],
                'staff' => ['Isabella', 'Luna', 'Reyes']
            ]
        ];

        // Get all departments from DB (assuming DepartmentSeeder ran first)
        $departments = DB::table('tb_department')->get();
        $empNoCounter = 0; // Simple counter for Emp_No generation
        
        foreach ($departments as $department) {
            $deptCode = $department->dept_code;
            if (!isset($employeeNames[$deptCode])) {
                continue; 
            }

            // Create Head Employee and Account
            $empNoCounter++;
            $headEmpNo = date('Y') . '-' . str_pad($empNoCounter, 4, '0', STR_PAD_LEFT);
            $headNames = $employeeNames[$deptCode]['head'];
            
            DB::table('tb_employeeinfo')->insert([
                'Emp_No' => $headEmpNo,
                'LastName' => $headNames[2],
                'FirstName' => $headNames[0],
                'MiddleName' => $headNames[1],
                'Email' => strtolower(str_replace(' ', '.', $headNames[0])) . '.' . strtolower(str_replace(' ', '.', $headNames[2])) . '@school.edu',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('tb_account')->insert([
                'Emp_No' => $headEmpNo,
                'department_id' => $department->department_id, // Added department_id
                'username' => $department->dept_code . '-' . $headEmpNo,
                'password' => Hash::make('password123'),
                'position' => 'Head',
                'accessRole' => 'Approver',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Staff Employee and Account
            $empNoCounter++;
            $staffEmpNo = date('Y') . '-' . str_pad($empNoCounter, 4, '0', STR_PAD_LEFT);
            $staffNames = $employeeNames[$deptCode]['staff'];
            
            DB::table('tb_employeeinfo')->insert([
                'Emp_No' => $staffEmpNo,
                'LastName' => $staffNames[2],
                'FirstName' => $staffNames[0],
                'MiddleName' => $staffNames[1],
                'Email' => strtolower(str_replace(' ', '.', $staffNames[0])) . '.' . strtolower(str_replace(' ', '.', $staffNames[2])) . '@school.edu',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('tb_account')->insert([
                'Emp_No' => $staffEmpNo,
                'department_id' => $department->department_id, // Added department_id
                'username' => $department->dept_code . '-' . $staffEmpNo,
                'password' => Hash::make('password123'),
                'position' => 'Staff',
                'accessRole' => 'Viewer',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
} 
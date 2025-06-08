<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            // Non-Teaching Departments
            ['dept_name' => 'Physical Facilities Management Office', 'category' => 'Non-teaching', 'dept_code' => 'PFMO'],
            ['dept_name' => 'Registrar Office', 'category' => 'Non-teaching', 'dept_code' => 'REG'],
            ['dept_name' => 'Finance Office', 'category' => 'Non-teaching', 'dept_code' => 'FIN'], // Existing, matches EmployeeMasterSeeder's 'Finance'
            ['dept_name' => 'Property Office', 'category' => 'Non-teaching', 'dept_code' => 'PROP'],
            ['dept_name' => 'Information Technology Management', 'category' => 'Non-teaching', 'dept_code' => 'ITM'],
            ['dept_name' => 'Human Resource Office', 'category' => 'Non-teaching', 'dept_code' => 'HR'],
            ['dept_name' => 'Library', 'category' => 'Non-teaching', 'dept_code' => 'LIB'],
            ['dept_name' => 'Guidance Office', 'category' => 'Non-teaching', 'dept_code' => 'GUID'],
            ['dept_name' => 'Medical and Dental Office', 'category' => 'Non-teaching', 'dept_code' => 'MDO'],
            ['dept_name' => 'Security Office', 'category' => 'Non-teaching', 'dept_code' => 'SEC'],
            // New Non-Teaching Departments from EmployeeMasterSeeder / initial request
            ['dept_name' => 'Clinic', 'category' => 'Non-teaching', 'dept_code' => 'CLN'],
            ['dept_name' => 'Vice President for Academic Affairs', 'category' => 'Non-teaching', 'dept_code' => 'VPAA'],
            ['dept_name' => 'President', 'category' => 'Non-teaching', 'dept_code' => 'PRES'],

            // Teaching Departments
            ['dept_name' => 'College of Computer Studies', 'category' => 'Teaching', 'dept_code' => 'CCS'],
            ['dept_name' => 'College of Engineering', 'category' => 'Teaching', 'dept_code' => 'COE'],
            ['dept_name' => 'College of Education', 'category' => 'Teaching', 'dept_code' => 'COED'],
            ['dept_name' => 'College of Arts and Sciences', 'category' => 'Teaching', 'dept_code' => 'CAS'], // Note: EmployeeMasterSeeder has 'College of Arts and Science'
            ['dept_name' => 'College of Business Administration', 'category' => 'Teaching', 'dept_code' => 'CBA'],
            ['dept_name' => 'College of Nursing', 'category' => 'Teaching', 'dept_code' => 'CON'],
            ['dept_name' => 'College of Architecture', 'category' => 'Teaching', 'dept_code' => 'COA'],
            ['dept_name' => 'General Education', 'category' => 'Teaching', 'dept_code' => 'GENED'],
            // New Teaching Departments from EmployeeMasterSeeder / initial request
            ['dept_name' => 'College of Business Management Education', 'category' => 'Teaching', 'dept_code' => 'CBME'],
            ['dept_name' => 'College of Tourism and Hospitality Management', 'category' => 'Teaching', 'dept_code' => 'CTHM'],
            ['dept_name' => 'College of Psychology', 'category' => 'Teaching', 'dept_code' => 'CP'],
            ['dept_name' => 'College of Criminal Justice', 'category' => 'Teaching', 'dept_code' => 'COCJ'],
            ['dept_name' => 'College of Real Estate Management', 'category' => 'Teaching', 'dept_code' => 'CREM'],
        ];

        foreach ($departments as $department) {
            DB::table('tb_department')->insert([
                'dept_name' => $department['dept_name'],
                'category' => $department['category'],
                'dept_code' => $department['dept_code'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
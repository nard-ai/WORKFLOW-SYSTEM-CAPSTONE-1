<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Department; // Added for Eloquent operations
use App\Models\EmployeeInfo; // Added for Eloquent operations
use App\Models\User; // Added for Eloquent operations (assuming User model is for tb_account)
use Faker\Factory as Faker; // Added for generating fake data

class EmployeeAndAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DB::transaction(function () use ($faker) {
            // Departments to ensure exist (from your list, including "Other Colleges")
            $departmentsData = [
                // Teaching
                ['dept_name' => 'College of Computer Studies', 'category' => 'Teaching', 'dept_code' => 'CCS'],
                ['dept_name' => 'College of Business Administration', 'category' => 'Teaching', 'dept_code' => 'CBA'],
                ['dept_name' => 'College of Business Management Education', 'category' => 'Teaching', 'dept_code' => 'CBME'],
                ['dept_name' => 'College of Tourism and Hospitality Management', 'category' => 'Teaching', 'dept_code' => 'CTHM'],
                ['dept_name' => 'College of Psychology', 'category' => 'Teaching', 'dept_code' => 'CP'],
                ['dept_name' => 'College of Engineering', 'category' => 'Teaching', 'dept_code' => 'COE'],
                ['dept_name' => 'College of Criminal Justice', 'category' => 'Teaching', 'dept_code' => 'COCJ'],
                ['dept_name' => 'College of Education', 'category' => 'Teaching', 'dept_code' => 'COED'],
                ['dept_name' => 'College of Arts and Science', 'category' => 'Teaching', 'dept_code' => 'CAS'],
                ['dept_name' => 'College of Real Estate Management', 'category' => 'Teaching', 'dept_code' => 'CREM'],
                // Non-teaching
                ['dept_name' => 'Finance', 'category' => 'Non-teaching', 'dept_code' => 'FIN'],
                ['dept_name' => 'Clinic', 'category' => 'Non-teaching', 'dept_code' => 'CLN'],
                ['dept_name' => 'Vice President for Academic Affairs', 'category' => 'Non-teaching', 'dept_code' => 'VPAA'],
                ['dept_name' => 'President', 'category' => 'Non-teaching', 'dept_code' => 'PRES'],
            ];

            $this->command->info('Ensuring specified departments exist...');
            foreach ($departmentsData as $deptData) {
                Department::updateOrCreate(
                    ['dept_code' => $deptData['dept_code']], // Check by dept_code to be safe
                    $deptData
                );
            }
            $this->command->info('Departments check/creation complete.');

            $departmentHeads = []; // Tracks department_id that has a head from the specific list

            // Employee and Account Data from your provided list
            // Structure: [Emp_No, LastName, FirstName, MiddleName, Email, DepartmentName, Position (Head/Staff)]
            $employees = [
                // CCS
                ['2025-0001', 'Ellana', 'Regie', 'Camba', 'regie@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Head'],
                ['2025-0002', 'Banag', 'Andro Philip', 'Gampay', 'Andro_Philip_Banag@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0003', 'Pecho', 'Julie', 'Laude', 'JULIE_PECHO@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0004', 'Alamo', 'Nino Francisco', 'Calzada', 'nino_francisco_alamo@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0005', 'Escudero', 'Rosalyn', 'Medina', 'Rosalyn_Escudero_sh@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0006', 'Deladia', 'Bobby', 'Cabanillas', 'BOBBY_DELADIA@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0007', 'Mascarinas', 'Moises', 'Abarientos', 'MOISES_MASCARINAS@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0008', 'Laureano', 'Michael Eugene', 'Borja', 'michaeleugene_laureano@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0009', 'Malig-on', 'Rodolfo', 'Benavidez', 'RODOLFO_MALIG-ON@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0010', 'Hagupit', 'Erika', 'Bonajos', 'erika_hagupit@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0011', 'Barrantes', 'Jino', 'Anday', 'jino_barrantes@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],
                ['2025-0012', 'Ayap', 'Jeffersonseph', 'Navarro', 'jeffersonseph_ayap@itmlyceumalabang.onmicrosoft.com', 'College of Computer Studies', 'Staff'],

                // CBA
                ['2025-0013', 'Mendoza', 'Rolly', null, 'rolly_mendoza@itmlyceumalabang.onmicrosoft.com', 'College of Business Administration', 'Head'],
                ['2025-0014', 'Malanog', 'Ramir', 'Dela Rosa', 'RAMIR_MALANOG@itmlyceumalabang.onmicrosoft.com', 'College of Business Administration', 'Staff'],
                ['2025-0015', 'Malanog', 'Ma. Marla', 'Buendia', 'MARLA_MALANOG@itmlyceumalabang.onmicrosoft.com', 'College of Business Administration', 'Staff'],
                ['2025-0016', 'Manaois', 'Tony', 'Estrada', 'TONY_MANAOIS@itmlyceumalabang.onmicrosoft.com', 'College of Business Administration', 'Staff'],
                ['2025-0017', 'Almodal', 'Jullie Ann', 'Dela Cruz', 'Jullie_Almodal@itmlyceumalabang.onmicrosoft.com', 'College of Business Administration', 'Staff'],
                ['2025-0018', 'Arciaga', 'Kristofferson', 'Ortega', 'Kristofferson_Arciaga@itmlyceumalabang.onmicrosoft.com', 'College of Business Administration', 'Staff'],

                // CBME
                ['2025-0019', 'Garcia Jr.', 'Michael', 'Riñon', 'MICHAEL_GARCIA_JR@itmlyceumalabang.onmicrosoft.com', 'College of Business Management Education', 'Staff'],
                ['2025-0020', 'Ramirez', 'Cesar', 'Victorio', 'CESAR_RAMIREZ@itmlyceumalabang.onmicrosoft.com', 'College of Business Management Education', 'Staff'],
                ['2025-0021', 'Titan', 'John', 'Custodio', 'John_Titan@itmlyceumalabang.onmicrosoft.com', 'College of Business Management Education', 'Staff'],
                ['2025-0022', 'Pasion', 'Arlyn', 'Castillo', 'ARLYN_PASION@itmlyceumalabang.onmicrosoft.com', 'College of Business Management Education', 'Staff'],
                ['2025-0023', 'Salviejo', 'Perla', 'Melegrito', 'PERLA_SALVIEJO@itmlyceumalabang.onmicrosoft.com', 'College of Business Management Education', 'Staff'],

                // CTHM
                ['2025-0025', 'Necor', 'Rudy', 'Billiones', 'RUDY_NECOR@itmlyceumalabang.onmicrosoft.com', 'College of Tourism and Hospitality Management', 'Head'],
                ['2025-0026', 'Pocdolacion', 'Ivan', 'Zalzos', 'IVAN_POCDOLACION@itmlyceumalabang.onmicrosoft.com', 'College of Tourism and Hospitality Management', 'Staff'], // Was Head, demoted as Necor is Head
                ['2025-0027', 'Odquier', 'Caitlin Dianne', 'Que', 'Caitlin_Dianne_Odquier@itmlyceumalabang.onmicrosoft.com', 'College of Tourism and Hospitality Management', 'Staff'],
                ['2025-0028', 'Lim', 'Vea Katherine', 'Ambos', 'Veakatherine_lim@itmlyceumalabang.onmicrosoft.com', 'College of Tourism and Hospitality Management', 'Staff'],
                ['2025-0029', 'Ammugauan', 'Lope', 'Butac', 'Ammugauan_Lope@itmlyceumalabang.onmicrosoft.com', 'College of Tourism and Hospitality Management', 'Staff'],
                ['2025-0024', 'Vernier', 'Miranda', 'Cordova', 'VERNIER_MIRANDA@itmlyceumalabang.onmicrosoft.com', 'College of Tourism and Hospitality Management', 'Staff'],

                // College of Psychology
                ['2025-0030', 'Lopez', 'Lois Eunice Jemima', 'Lapira', 'LOISEUNICEJEMIMA_LOPEZ@itmlyceumalabang.onmicrosoft.com', 'College of Psychology', 'Head'],
                ['2025-0031', 'De Leon', 'Juvy', 'Abundo', 'JUVY_DELEON@itmlyceumalabang.onmicrosoft.com', 'College of Psychology', 'Staff'],
                ['2025-0032', 'Callos', 'Jovilyn', null, 'jovilyn_callos@itmlyceumalabang.onmicrosoft.com', 'College of Psychology', 'Staff'],
                ['2025-0033', 'Bismar', 'Jerwin', 'Anchoriz', 'JERWIN_BISMAR@itmlyceumalabang.onmicrosoft.com', 'College of Psychology', 'Staff'],
                ['2025-0034', 'Austria', 'Jonnamel', null, 'Jonnamel_Austria@itmlyceumalabang.onmicrosoft.com', 'College of Psychology', 'Staff'],

                // COE
                ['2025-0035', 'Briones', 'Anthony Ralf', null, 'anthonyralf_briones@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Head'],
                ['2025-0036', 'Uy', 'Teresita', null, 'teresita_uy@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Staff'],
                ['2025-0037', 'Viterbo', 'Teresita', 'Licuanan', 'TERESITA_VITERBO@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Staff'],
                ['2025-0038', 'Arriola', 'Hernando', 'Espiritu', 'HERNANDO_ARRIOLA@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Staff'],
                ['2025-0039', 'Borero', 'Kris Andrew', null, 'krisandrew_borero@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Staff'],
                // Emp_No conflict resolution:
                ['2025-0040A', 'Dones', 'Danilo', 'Del Pilar', 'danilo_dones@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Staff'],
                ['2025-0041A', 'Calitina Jr.', 'Cris', null, 'CRIS_CALITINA_JR@itmlyceumalabang.onmicrosoft.com', 'College of Engineering', 'Staff'],

                // COCJ
                ['2025-0040B', 'Francisco', 'Camille', 'Cipres', 'Camille_Francisco@itmlyceumalabang.onmicrosoft.com', 'College of Criminal Justice', 'Head'],

                // COED
                ['2025-0041B', 'Dunham', 'Teresita', 'Aryap', 'TERESITA_DUNHAM@itmlyceumalabang.onmicrosoft.com', 'College of Education', 'Head'],

                // Finance
                ['2025-0042', 'Ellana', 'Deborah', 'Mallari', 'DEBORAH_ELLANA@itmlyceumalabang.onmicrosoft.com', 'Finance', 'Head'],

                // Clinic
                ['2025-0043', 'Riego', 'Maria Kristina', 'M', null, 'Clinic', 'Head'],
                ['2025-0044', 'Pelisco', 'Mariza Monida', 'M', null, 'Clinic', 'Staff'],
                ['2025-0045', 'Alquillera', 'Ailleen', 'A', null, 'Clinic', 'Staff'],
                ['2025-0046', 'Pasaforte', 'Marites', 'R', null, 'Clinic', 'Staff'],
                ['2025-0047', 'Ayap', 'Analyn', 'S', null, 'Clinic', 'Staff'],
                ['2025-0048', 'Antonio', 'Jinny', 'D', null, 'Clinic', 'Staff'],
                ['2025-0049', 'Guna', 'Eloisa Joy', 'F', null, 'Clinic', 'Staff'],
            ];

            $this->command->info('Seeding employees and accounts from the provided list...');
            $processedEmpNos = []; // To keep track of Emp_No to avoid duplicates from this list

            foreach ($employees as $empData) {
                $empNo = $empData[0];
                $lastName = $empData[1];
                $firstName = $empData[2];
                $middleName = $empData[3];
                $email = $empData[4];
                $deptName = $empData[5];
                $position = $empData[6]; // Intended position

                if (in_array($empNo, $processedEmpNos)) {
                    $this->command->warn("Duplicate Emp_No {$empNo} in provided data. Skipping subsequent entry for {$firstName} {$lastName}.");
                    continue;
                }
                $processedEmpNos[] = $empNo;

                $department = Department::where('dept_name', $deptName)->first();
                if (!$department) {
                    // Attempt to find by dept_code if name is slightly different (e.g. 'Finance' vs 'Finance Office')
                    $deptFromCode = collect($departmentsData)->firstWhere('dept_name', $deptName);
                    if ($deptFromCode) {
                        $department = Department::where('dept_code', $deptFromCode['dept_code'])->first();
                    }
                    if (!$department) {
                        $this->command->error("Department '{$deptName}' not found for employee {$firstName} {$lastName} ({$empNo}). Skipping.");
                        continue;
                    }
                }

                $currentEmail = $email;
                if (empty($currentEmail)) {
                    $currentEmail = $faker->unique()->safeEmail;
                }

                // Check for email uniqueness before creating/updating EmployeeInfo
                while (EmployeeInfo::where('Email', $currentEmail)->where('Emp_No', '!=', $empNo)->exists()) {
                    $currentEmail = $faker->unique()->safeEmail;
                }

                EmployeeInfo::updateOrCreate(
                    ['Emp_No' => $empNo],
                    [
                        'LastName' => $lastName,
                        'FirstName' => $firstName,
                        'MiddleName' => $middleName,
                        'Email' => $currentEmail,
                    ]
                );

                $actualPosition = $position;
                if ($actualPosition === 'Head') {
                    if (isset($departmentHeads[$department->department_id])) {
                        $this->command->info("Department '{$department->dept_name}' (ID: {$department->department_id}) already has a Head ({$departmentHeads[$department->department_id]}). Assigning {$firstName} {$lastName} ({$empNo}) as Staff.");
                        $actualPosition = 'Staff';
                    } else {
                        $departmentHeads[$department->department_id] = $empNo;
                    }
                }

                User::updateOrCreate(
                    ['Emp_No' => $empNo],
                    [
                        'department_id' => $department->department_id,
                        'username' => $department->dept_code . '-' . $empNo,
                        'password' => Hash::make('password123'),
                        'position' => $actualPosition,
                        'accessRole' => ($actualPosition === 'Head') ? 'Approver' : 'Viewer',
                        'status' => 'active',
                    ]
                );
            }
            $this->command->info('Specific employees and accounts seeded.');

            // Ensure every department (from DB, including those from DepartmentSeeder) has one Head and one Staff
            $this->command->info('Ensuring all departments have at least one Head and one Staff...');
            $allDepartmentsInDB = Department::all();
            $existingEmpNos = EmployeeInfo::pluck('Emp_No')->toArray(); // Get all Emp_Nos to avoid collision

            // Initialize a single counter for all numerically generated Emp_Nos (placeholders and additional random)
            // Starts from 50, so Emp_No will be 2025-0050, 2025-0051, etc.
            $numericEmpNoCounter = 50;

            foreach ($allDepartmentsInDB as $dept) {
                $hasHead = User::where('department_id', $dept->department_id)->where('position', 'Head')->exists();
                $hasStaff = User::where('department_id', $dept->department_id)->where('position', 'Staff')->exists();

                if (!$hasHead) {
                    $placeholderEmpNo = $this->generateUniqueNumericEmpNo($numericEmpNoCounter, $existingEmpNos);
                    // $existingEmpNos is already updated by generateUniqueNumericEmpNo
                    $placeholderEmail = $faker->unique()->safeEmail;
                    while (EmployeeInfo::where('Email', $placeholderEmail)->exists()) {
                        $placeholderEmail = $faker->unique()->safeEmail;
                    }

                    EmployeeInfo::create([
                        'Emp_No' => $placeholderEmpNo,
                        'LastName' => $faker->lastName,
                        'FirstName' => $faker->firstName,
                        'MiddleName' => strtoupper($faker->randomLetter()),
                        'Email' => $placeholderEmail,
                    ]);
                    User::create([
                        'Emp_No' => $placeholderEmpNo,
                        'department_id' => $dept->department_id,
                        'username' => $dept->dept_code . '-' . $placeholderEmpNo,
                        'password' => Hash::make('password123'),
                        'position' => 'Head',
                        'accessRole' => 'Approver',
                        'status' => 'active',
                    ]);
                    $this->command->info("Added placeholder Head for department: {$dept->dept_name} (Emp_No: {$placeholderEmpNo})");
                }

                if (!$hasStaff) {
                    $placeholderEmpNo = $this->generateUniqueNumericEmpNo($numericEmpNoCounter, $existingEmpNos);
                    // $existingEmpNos is already updated by generateUniqueNumericEmpNo
                    $placeholderEmail = $faker->unique()->safeEmail;
                    while (EmployeeInfo::where('Email', $placeholderEmail)->exists()) {
                        $placeholderEmail = $faker->unique()->safeEmail;
                    }

                    EmployeeInfo::create([
                        'Emp_No' => $placeholderEmpNo,
                        'LastName' => $faker->lastName,
                        'FirstName' => $faker->firstName,
                        'MiddleName' => strtoupper($faker->randomLetter()),
                        'Email' => $placeholderEmail,
                    ]);
                    User::create([
                        'Emp_No' => $placeholderEmpNo,
                        'department_id' => $dept->department_id,
                        'username' => $dept->dept_code . '-' . $placeholderEmpNo,
                        'password' => Hash::make('password123'),
                        'position' => 'Staff',
                        'accessRole' => 'Viewer',
                        'status' => 'active',
                    ]);
                    $this->command->info("Added placeholder Staff for department: {$dept->dept_name} (Emp_No: {$placeholderEmpNo})");
                }
            }
            $this->command->info('Placeholder Head/Staff check complete.');

            // Add a few additional random users
            $numberOfRandomUsers = 3;
            $this->command->info("Adding {$numberOfRandomUsers} additional random employees using numeric Emp_No sequence...");

            for ($i = 0; $i < $numberOfRandomUsers; $i++) {
                $randomDept = $allDepartmentsInDB->isEmpty() ? null : $allDepartmentsInDB->random();
                if (!$randomDept) {
                    $this->command->warn("No departments available to assign random user. Skipping.");
                    break;
                }

                $randomEmpNo = $this->generateUniqueNumericEmpNo($numericEmpNoCounter, $existingEmpNos);
                // $existingEmpNos is already updated by generateUniqueNumericEmpNo
                $randomEmail = $faker->unique()->safeEmail;
                while (EmployeeInfo::where('Email', $randomEmail)->exists()) {
                    $randomEmail = $faker->unique()->safeEmail;
                }

                EmployeeInfo::create([
                    'Emp_No' => $randomEmpNo,
                    'LastName' => $faker->lastName,
                    'FirstName' => $faker->firstName,
                    'MiddleName' => strtoupper($faker->randomLetter),
                    'Email' => $randomEmail,
                ]);
                User::create([
                    'Emp_No' => $randomEmpNo,
                    'department_id' => $randomDept->department_id,
                    'username' => $randomDept->dept_code . '-' . $randomEmpNo,
                    'password' => Hash::make('password123'),
                    'position' => 'Staff', // Default random users to Staff
                    'accessRole' => 'Viewer',
                    'status' => 'active',
                ]);
            }
            $this->command->info("Added {$numberOfRandomUsers} additional random employees.");

            $this->command->info('EmployeeAndAccountSeeder completed successfully.');

        }); // End DB::transaction

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Generates a unique Emp_No in the format 2025-XXXX.
     * Ensures the generated Emp_No does not already exist in the provided array.
     * The $counter is passed by reference and incremented.
     * The $existingEmpNos array is also updated by reference.
     */
    private function generateUniqueNumericEmpNo(int &$counter, array &$existingEmpNos): string
    {
        $newEmpNo = '';
        do {
            $newEmpNo = '2025-' . sprintf('%04d', $counter++);
        } while (in_array($newEmpNo, $existingEmpNos));
        $existingEmpNos[] = $newEmpNo; // Add the newly generated unique Emp_No to the list
        return $newEmpNo;
    }
}
<?php

namespace Database\Seeders;

// use App\Models\User; // Commented out or remove if not needed elsewhere
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            EmployeeAndAccountSeeder::class,
            SignatureStyleSeeder::class,
            // You can add other general seeders here if needed

        ]);
    }
}

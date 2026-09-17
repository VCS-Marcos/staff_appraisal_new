<?php

namespace Database\Seeders;

use App\Enums\AppraisalStatus;
use App\Enums\TargetType;
use App\Enums\UserRole;
use App\Models\Appraisal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::create([
            'name' => 'Alex Admin',
            'email' => 'admin@school.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'position' => 'Head of School',
            'email_verified_at' => now(),
        ]);

        $reviewer = User::create([
            'name' => 'Robin Reviewer',
            'email' => 'reviewer@school.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Reviewer,
            'position' => 'Head of Department',
            'line_manager_id' => $admin->id,
            'email_verified_at' => now(),
        ]);

        $employee = User::create([
            'name' => 'Emery Employee',
            'email' => 'employee@school.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'position' => 'Class Teacher',
            'line_manager_id' => $reviewer->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jamie Junior',
            'email' => 'employee2@school.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'position' => 'Teaching Assistant',
            'line_manager_id' => $reviewer->id,
            'email_verified_at' => now(),
        ]);

        $appraisal = Appraisal::create([
            'user_id' => $employee->id,
            'reviewer_id' => $reviewer->id,
            'year' => now()->year,
            'status' => AppraisalStatus::PendingEmployee,
        ]);

        $sampleTargets = [
            1 => 'Improve student engagement in Year 5 Maths through weekly group activities.',
            2 => 'Complete the TES "Assessment for Learning" certification by December.',
            3 => 'Lead one cross-department collaboration project this year.',
            4 => 'Maintain consistent parent communication via the fortnightly newsletter.',
        ];

        foreach ($sampleTargets as $number => $text) {
            $appraisal->targets()->create([
                'target_type' => TargetType::Current,
                'target_number' => $number,
                'target_text' => $text,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Category & SubCategory
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Questions
            'questions.view',
            'questions.create',
            'questions.edit',
            'questions.delete',

            // Exams
            'exams.view',
            'exams.create',
            'exams.edit',
            'exams.delete',

            // Exam Sessions
            'exam-sessions.view',
            'exam-sessions.create',
            'exam-sessions.edit',
            'exam-sessions.delete',
            'exam-sessions.monitor',

            // Exam Packages
            'exam-packages.view',
            'exam-packages.create',
            'exam-packages.edit',
            'exam-packages.delete',

            // Students
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',

            // Classes
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',

            // Rooms
            'rooms.view',
            'rooms.create',
            'rooms.edit',
            'rooms.delete',

            // Settings
            'settings.view',
            'settings.edit',

            // Reports
            'reports.view',
            'reports.grade',

            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'categories.view',
            'categories.create',
            'categories.edit',
            'questions.view',
            'questions.create',
            'questions.edit',
            'questions.delete',
            'exams.view',
            'exams.create',
            'exams.edit',
            'exam-sessions.view',
            'exam-sessions.create',
            'exam-sessions.edit',
            'exam-sessions.monitor',
            'students.view',
            'classes.view',
            'rooms.view',
            'reports.view',
            'reports.grade',
        ]);

        Role::firstOrCreate(['name' => 'student']);
    }
}

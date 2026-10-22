<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Idempotent role seeder.
 *
 * Seeds the protected General Manager system role and starter custom roles
 * using firstOrCreate so existing roles (including any produced in production)
 * are NEVER overwritten. The 5-custom-role maximum is respected.
 */
class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Full access key list granted to the General Manager system role.
     */
    protected function gmPermissions(): array
    {
        return [
            'dashboard',
            'members',
            'savings',
            'sharecapitals',
            'lendings',
            'payments',
            'finance',
            'reports',
            'seminars',
            'officers-committees',
            'settings',
        ];
    }

    protected function starterRoles(): array
    {
        return [
            'secretary' => [
                'name' => 'Secretary',
                'description' => 'Handles correspondence, records and meeting coordination.',
                'sidebar_permissions' => [
                    'dashboard', 'members', 'reports', 'seminars', 'officers-committees',
                ],
            ],
            'treasurer' => [
                'name' => 'Treasurer',
                'description' => 'Oversees collections, payments and financial reporting.',
                'sidebar_permissions' => [
                    'dashboard', 'members', 'payments', 'finance', 'reports',
                ],
            ],
        ];
    }

    /**
     * Run the seeder (safe to call multiple times).
     */
    public function run(): void
    {
        // General Manager — system role, full access. Never overwritten if present.
        Role::firstOrCreate(
            ['slug' => 'general-manager'],
            [
                'name' => 'General Manager',
                'description' => 'Top authority with full, permanent access across all modules.',
                'is_system' => true,
                'sidebar_permissions' => $this->gmPermissions(),
            ]
        );

        $customCount = Role::where('is_system', false)->count();

        foreach ($this->starterRoles() as $slug => $data) {
            if ($customCount >= Role::MAX_CUSTOM_ROLES) {
                break;
            }

            $exists = Role::where('slug', $slug)->exists();

            if (! $exists) {
                Role::create([
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'],
                    'is_system' => false,
                    'sidebar_permissions' => $data['sidebar_permissions'],
                ]);
                $customCount++;
            }
        }
    }
}
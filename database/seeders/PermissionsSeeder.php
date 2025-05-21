<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    private array $modules = [
        'permission' => 'Permission',
        'project' => 'Project',
        'project status' => 'Project',
        'role' => 'Role',
        'ticket' => 'Ticket',
        'ticket priority' => 'Ticket',
        'ticket status' => 'Ticket',
        'ticket type' => 'Ticket',
        'user' => 'User',
        'activity' => 'Activity',
        'sprint' => 'Sprint'
    ];

    private array $pluralActions = [
        'List'
    ];

    private array $singularActions = [
        'View',
        'Create',
        'Update',
        'Delete'
    ];

    private array $extraPermissions = [
        'Manage general settings' => 'General',
        'Import from Jira' => 'General',
        'List timesheet data' => 'Timesheet',
        'View timesheet dashboard' => 'Timesheet'
    ];

    private string $defaultRole = 'Default role';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create profiles
        foreach ($this->modules as $module => $type) {
            $plural = Str::plural($module);
            $singular = $module;
            foreach ($this->pluralActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . ' ' . $plural,
                    'type' => $type
                ]);
            }
            foreach ($this->singularActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . ' ' . $singular,
                    'type' => $type
                ]);
            }
        }
        Permission::firstOrCreate([
            'name' => 'create-backup',
            'type' => 'General'
        ]);
        foreach ($this->extraPermissions as $permission => $type) {
            Permission::firstOrCreate([
                'name' => $permission,
                'type' => $type
            ]);
        }

        // Create default role
        $role = Role::firstOrCreate([
            'name' => $this->defaultRole
        ]);
        $settings = app(GeneralSettings::class);
        $settings->default_role = $role->id;
        $settings->save();

        // Add all permissions to default role
        $role->syncPermissions(Permission::all()->pluck('name')->toArray());

        // Assign default role to first database user
        if ($user = User::first()) {
            $user->syncRoles([$this->defaultRole]);
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;

class SyncPermission extends Command
{
    /**
     * Siguraduhing ito ang itatype mo sa terminal.
     */
    protected $signature = 'app:sync-permission';

    /**
     * Description para alam mo kung para saan ito.
     */
    protected $description = 'Sync all Enums in Enums/Permissions to the database permissions table';

    /**
     * Dito mangyayari ang magic.
     */
   public function handle()
    {
        $path = app_path('Enums/Permissions');
        $files = File::allFiles($path);

        $this->info("Files found in folder: " . count($files));

        // 1. Loop para i-sync ang bawat Permission mula sa Enums
        foreach ($files as $file) {
            $className = $file->getFilenameWithoutExtension();
            $fullPath = 'App\\Enums\\Permissions\\' . $className;

            $this->comment("🔍 Checking file: {$className}");

            if (!enum_exists($fullPath)) {
                $this->error("❌ Enum class not found or invalid: {$fullPath}");
                continue;
            }

            $this->info("✅ Processing Enum: {$className}");

            foreach ($fullPath::cases() as $case) {
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => $case->value,
                    'guard_name' => 'web'
                ]);
                $this->line("   - Synced: {$case->value}");
            }
        }

        $this->newLine();
        $this->info("🛡️ Setting up Admin Role & User ID 1...");

        // 2. Siguraduhing may 'admin' role
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        // 3. Ibigay ang LAHAT ng permissions sa 'admin' role
        $allPermissions = \Spatie\Permission\Models\Permission::all();
        $adminRole->syncPermissions($allPermissions);

        // 4. Hanapin ang User ID 1 at i-assign ang 'admin' role
        $adminUser = \App\Models\User::find(1);
        if ($adminUser) {
            $adminUser->assignRole($adminRole);
            $this->info("👤 User ID 1 ({$adminUser->email}) has been assigned as Admin.");
        } else {
            $this->warn("⚠️ User with ID 1 not found. Make sure your admin user exists.");
        }

        // 5. Clear Spatie's cache para mag-reflect agad sa UI/Sidebar
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("🚀 Admin Role synced with " . $allPermissions->count() . " permissions.");
        $this->info("✅ All systems go!");
    }
}
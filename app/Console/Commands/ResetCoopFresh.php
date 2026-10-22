<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Users_tbl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;

class ResetCoopFresh extends Command
{
    protected $signature = 'coop:fresh';

    protected $description = 'Safely rebuild the development database from migrations and recreate the two canonical accounts (Rogelio Amoyan, Ronald Sales)';

    private const DEFAULT_PASSWORD = '12345678';

    public function handle()
    {
        $this->line('========================================');
        $this->info(' Cooperative Database Fresh Reset');
        $this->line('========================================');
        $this->newLine();

        // ---- 1. Production protection (no bypass flag) --------------------
        if (app()->environment('production')) {
            $this->error('ABORTED — APP_ENV is production. This command is intentionally disabled in production.');
            $this->error('No database reset was performed.');

            return Command::FAILURE;
        }
        $this->info('✓ Environment check passed');

        // ---- 2. Explicit confirmation -------------------------------------
        $confirmation = (string) $this->ask(
            "WARNING: This DESTROYS ALL existing tables/data and rebuilds them from migrations.\n".
            'Type RESET to continue, or anything else to cancel.'
        );
        if ($confirmation !== 'RESET') {
            $this->warn('Cancelled — confirmation did not match RESET.');
            $this->warn('No database reset was performed.');

            return Command::FAILURE;
        }
        $this->info('✓ RESET confirmation received');
        $this->newLine();

        // ---- 3. Mandatory backup (abort on any failure) -------------------
        $backupPath = $this->createBackup();
        if ($backupPath === null) {
            $this->error('ABORTED — database backup failed.');
            $this->error('No database reset was performed.');

            return Command::FAILURE;
        }
        $this->info('✓ Database backup created');
        $this->info('  '.$backupPath);
        $this->newLine();

        // ---- 4. Fresh migration (NO --seed) -------------------------------
        try {
            $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
            $output = trim(Artisan::output());
            if ($exitCode !== 0 || str_contains(strtolower($output), 'error')) {
                $this->error('MIGRATION FAILED.');
                $this->error('Backup available at: '.$backupPath);
                $this->error('Accounts were not created.');
                $this->line($output);

                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('MIGRATION FAILED.');
            $this->error('Backup available at: '.$backupPath);
            $this->error($e->getMessage());
            $this->error('Accounts were not created.');

            return Command::FAILURE;
        }
        $this->info('✓ Fresh migrations completed');
        $this->newLine();

        // ---- 5. Account 1 — Rogelio Amoyan (id=1, Member) -----------------
        $rogelio = $this->createUser([
            'first_name' => 'Rogelio',
            'last_name' => 'Amoyan',
            'username' => 'rogelio',
            'email' => 'rogelioamoyan768@gmail.com',
            'role' => 'Member',
            'base_role' => 'member',
        ]);
        if (! $rogelio) {
            $this->line(Artisan::output());

            return Command::FAILURE;
        }
        if ((int) $rogelio->id !== 1) {
            $this->error('Rogelio was expected to receive ID 1 but got ID '.$rogelio->id.'.');
            $this->error('Aborting. Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $this->info('✓ Rogelio Amoyan created');
        $this->info('  ID: '.$rogelio->id);
        $this->info('  Role: '.$rogelio->role);
        $this->info('  Email: '.$rogelio->email);
        $this->info('  Password verification: '.($this->passwordPasses($rogelio) ? 'PASS' : 'FAIL'));

        // Create Rogelio's otherinfo record (required for member login).
        $otherInfo = $this->createOtherInfo($rogelio);
        if (! $otherInfo) {
            $this->error('Rogelio otherinfo record creation FAILED.');
            $this->error('Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $this->info('✓ Rogelio otherinfo record created');
        $this->info('  Approval status: '.$otherInfo->approval_status);
        $this->info('  Login eligibility: '.($this->otherInfoAllowsLogin($otherInfo) ? 'PASS' : 'FAIL'));
        $this->newLine();

        // ---- 6. Account 2 — Ronald Sales (id=2, General Manager, main admin) ----
        $ronald = $this->createUser([
            'first_name' => 'Ronald',
            'last_name' => 'Sales',
            'username' => 'ronald',
            'email' => 'ronald@coop.com',
            'role' => 'general-manager',
            'base_role' => null,
        ]);
        if (! $ronald) {
            $this->line(Artisan::output());

            return Command::FAILURE;
        }
        if ((int) $ronald->id !== 2) {
            $this->error('Ronald was expected to receive ID 2 but got ID '.$ronald->id.'.');
            $this->error('Aborting. Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $isMainAdmin = $ronald->isMainAdmin();
        $this->info('✓ Ronald Sales created');
        $this->info('  ID: '.$ronald->id);
        $this->info('  Role: '.$ronald->role);
        $this->info('  Email: '.$ronald->email);
        $this->info('  Password verification: '.($this->passwordPasses($ronald) ? 'PASS' : 'FAIL'));
        $this->info('  Main admin verification: '.($isMainAdmin ? 'PASS' : 'FAIL'));

        if (! $isMainAdmin) {
            $this->error('Ronald (General Manager) does NOT satisfy isMainAdmin().');
            $this->error('The GM holds the main-admin authority, so isMainAdmin() must be true.');
            $this->error('Check role value (must be exact lowercase "general-manager") or the isMainAdmin() definition.');
            $this->error('Aborting. Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $this->newLine();

        // ---- 7. Verify roles ----------------------------------------------
        $roles = Role::pluck('slug')->map(fn ($s) => strtolower((string) $s))->all();
        $expectedRoles = ['general-manager'];
        foreach ($expectedRoles as $slug) {
            if (! in_array($slug, $roles, true)) {
                $this->error("Required role '{$slug}' was not created by migrations.");
                $this->error('Aborting. Backup available at: '.$backupPath);

                return Command::FAILURE;
            }
            $this->info('✓ '.ucfirst(str_replace('-', ' ', $slug)).' role verified');
        }
        $this->newLine();

        // ---- 8. Verify users count = 2 -------------------------------------
        $usersCount = Users_tbl::count();
        if ($usersCount !== 2) {
            $this->error('Expected exactly 2 users after reset but found '.$usersCount.'.');
            $this->error('Aborting. Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $this->info('✓ Users count: '.$usersCount);

        // ---- 9. Schema verification ---------------------------------------
        $schemaOk = true;
        foreach (['users_tbls', 'otherinfo_tbls', 'roles'] as $table) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
                $schemaOk = false;
                $this->error('Expected schema table missing: '.$table);
            }
        }
        if (! $schemaOk) {
            $this->error('Aborting. Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $otherInfoStatus = DB::table('otherinfo_tbls')->where('user_id', $rogelio->id)->value('approval_status');
        if ($otherInfoStatus !== 'Approved') {
            $this->error("Rogelio's otherinfo approval_status is '{$otherInfoStatus}', expected 'Approved'.");
            $this->error('Aborting. Backup available at: '.$backupPath);

            return Command::FAILURE;
        }
        $this->info('✓ Schema verification passed');
        $this->newLine();

        $this->line('========================================');
        $this->info(' RESET COMPLETE');
        $this->line('========================================');

        return Command::SUCCESS;
    }

    private function createUser(array $attrs): ?Users_tbl
    {
        try {
            return Users_tbl::create([
                'first_name' => $attrs['first_name'],
                'last_name' => $attrs['last_name'],
                'username' => $attrs['username'],
                'email' => $attrs['email'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => $attrs['role'],
                'base_role' => $attrs['base_role'],
                'status' => 'active',
                'sidebar_permissions' => null,
            ]);
        } catch (\Throwable $e) {
            $this->error($attrs['first_name'].' '.$attrs['last_name'].' creation FAILED: '.$e->getMessage());

            return null;
        }
    }

    private function createOtherInfo(Users_tbl $user)
    {
        try {
            // otherinfo_tbls: only user_id is NOT NULL. approval_status enum
            // Pending/Approved/Declined (default Pending); membership_status
            // default "Unofficial". Approved value used across the app is "Approved".
            $id = DB::table('otherinfo_tbls')->insertGetId([
                'user_id' => $user->id,
                'email_verified' => true,
                'approval_status' => 'Approved',
                'membership_status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('otherinfo_tbls')->where('id', $id)->first();
        } catch (\Throwable $e) {
            $this->error('Rogelio otherinfo_tbls creation FAILED: '.$e->getMessage());
            $this->error('Check the otherinfo_tbls columns and their allowed values (see create_otherinfo_tbls_table migration).');

            return null;
        }
    }

    private function passwordPasses(Users_tbl $user): bool
    {
        return Hash::check(self::DEFAULT_PASSWORD, $user->password);
    }

    private function otherInfoAllowsLogin($otherInfo): bool
    {
        if (! $otherInfo || ! isset($otherInfo->approval_status)) {
            return false;
        }

        return $otherInfo->approval_status !== 'Pending'
            && $otherInfo->approval_status !== 'Declined';
    }

    private function createBackup(): ?string
    {
        $mysqldump = $this->resolveMysqldump();
        if (! $mysqldump) {
            $this->error('mysqldump could not be located. Set MYSQLDUMP_BIN env var or install MySQL client tools.');

            return null;
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $path = $dir.'/coop_before_fresh_'.now()->format('Ymd_His').'.sql';
        $database = config('database.connections.'.config('database.default').'.database');
        $username = config('database.connections.'.config('database.default').'.username');
        $password = config('database.connections.'.config('database.default').'.password');
        $host = config('database.connections.'.config('database.default').'.host');
        $port = config('database.connections.'.config('database.default').'.port');

        $passwordArg = $password ? '--password='.$password : '';
        $parts = array_filter([$mysqldump, '--user='.$username, $passwordArg, '--host='.$host, '--port='.$port, '--single-transaction', '--skip-comments', $database]);

        try {
            $process = new Process(array_values($parts), null, null, null, 300);
            $process->mustRun();
            File::put($path, $process->getOutput());
        } catch (\Throwable $e) {
            $this->error('mysqldump failed: '.$e->getMessage());
            $this->error('Backup error output: '.(method_exists($process ?? null, 'getErrorOutput') ? $process->getErrorOutput() : ''));

            return null;
        }

        if (! file_exists($path) || filesize($path) === 0) {
            $this->error('mysqldump produced an empty/missing dump file.');

            return null;
        }

        return $path;
    }

    private function resolveMysqldump(): ?string
    {
        $fromEnv = getenv('MYSQLDUMP_BIN');
        if ($fromEnv && is_file($fromEnv)) {
            return $fromEnv;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $wampMysql = glob('C:\\wamp64\\bin\\mysql\\*\\bin\\mysqldump.exe');
            if ($wampMysql) {
                return $wampMysql[0];
            }
        }

        $candidates = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump'];
        foreach ($candidates as $candidate) {
            if ($this->commandExists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function commandExists(string $command): bool
    {
        try {
            if (DIRECTORY_SEPARATOR === '\\') {
                $process = new Process(['where', $command]);
                $process->run();

                return $process->isSuccessful();
            }

            $process = new Process(['sh', '-c', 'command -v '.$command]);
            $process->run();

            return $process->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }
}

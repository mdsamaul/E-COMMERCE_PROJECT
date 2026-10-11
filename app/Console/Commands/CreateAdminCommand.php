<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Rules\BangladeshPhone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopbd:create-admin 
                            {--name= : The full name of the administrator}
                            {--email= : The email address of the administrator}
                            {--phone= : The Bangladesh mobile number (e.g. 017XXXXXXXX)}
                            {--password= : The secure password (optional, prompted securely if omitted)}
                            {--super : Grant root Super Administrator privileges}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely create a new ShopBD administrator account without hardcoding credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== ShopBD Admin Account Creator ===');

        $isSuper = (bool) $this->option('super');

        // Gather Name
        $name = $this->option('name') ?: $this->ask('Administrator Full Name');
        if (empty($name)) {
            $this->error('Name cannot be empty.');
            return self::FAILURE;
        }

        // Gather Email
        $email = $this->option('email') ?: $this->ask('Administrator Email');
        $email = strtolower(trim($email));

        // Gather Phone
        $phone = $this->option('phone') ?: $this->ask('Administrator Mobile Number (e.g., 017XXXXXXXX)');
        $normalizedPhone = BangladeshPhone::normalize($phone);

        // Gather Password (secret input unless option provided)
        $password = $this->option('password') ?: $this->secret('Administrator Password (minimum 8 characters)');

        // Validate
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'phone' => $normalizedPhone,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', new BangladeshPhone(), 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line(" - {$error}");
            }
            return self::FAILURE;
        }

        // Create Admin User
        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $normalizedPhone,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_super_admin' => $isSuper,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $roleSlug = $isSuper ? 'super-admin' : 'admin';
        $role = \App\Models\Role::where('slug', $roleSlug)->first();
        if ($role) {
            $admin->roles()->sync([$role->id]);
        }

        $this->newLine();
        $type = $isSuper ? 'Super Administrator' : 'Administrator';
        $loginUrl = $isSuper ? url('/superadmin/login') : url('/admin/login');

        $this->info("✓ {$type} [{$admin->name}] ({$admin->email}) created successfully!");
        $this->comment("You can now log in at: " . $loginUrl);

        return self::SUCCESS;
    }
}

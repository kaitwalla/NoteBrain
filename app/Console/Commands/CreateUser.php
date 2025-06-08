<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user interactively';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Let\'s create a new user!');
        $this->newLine();

        // Get user details
        $name = $this->getUserName();
        $email = $this->getUserEmail();
        $password = $this->getUserPassword();

        // Confirm details
        $this->newLine();
        $this->info('Please confirm the following details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Password', '********'],
            ]
        );

        if (!$this->confirm('Do you want to create this user?')) {
            $this->error('User creation cancelled.');
            return 1;
        }

        // Create user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->newLine();
            $this->info('✅ User created successfully!');
            $this->table(
                ['Name', 'Email'],
                [[$user->name, $user->email]]
            );
        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Get the user's name with validation
     */
    private function getUserName(): string
    {
        while (true) {
            $name = $this->ask('What is the user\'s name?');

            $validator = Validator::make(
                ['name' => $name],
                ['name' => ['required', 'string', 'max:255']]
            );

            if ($validator->fails()) {
                $this->error($validator->errors()->first('name'));
                continue;
            }

            return $name;
        }
    }

    /**
     * Get the user's email with validation
     */
    private function getUserEmail(): string
    {
        while (true) {
            $email = $this->ask('What is the user\'s email?');

            $validator = Validator::make(
                ['email' => $email],
                ['email' => ['required', 'string', 'email', 'max:255', 'unique:users']]
            );

            if ($validator->fails()) {
                $this->error($validator->errors()->first('email'));
                continue;
            }

            return $email;
        }
    }

    /**
     * Get the user's password with validation
     */
    private function getUserPassword(): string
    {
        while (true) {
            $password = $this->secret('What is the user\'s password?');
            $passwordConfirmation = $this->secret('Please confirm the password:');

            if ($password !== $passwordConfirmation) {
                $this->error('Passwords do not match. Please try again.');
                continue;
            }

            $validator = Validator::make(
                ['password' => $password],
                ['password' => ['required', 'string', 'min:8']]
            );

            if ($validator->fails()) {
                $this->error($validator->errors()->first('password'));
                continue;
            }

            return $password;
        }
    }
}

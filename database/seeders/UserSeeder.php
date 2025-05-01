<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
/**
* Run the database seeds.
*/
public function run(): void
{
// Create or update test user as admin
$user = User::firstOrCreate(
['email' => 'test@example.com'],
[
'name' => 'کاربر تستی',
'password' => Hash::make('password'),
'email_verified_at' => now(),
'role' => 'admin',
]
);

// If user already existed, update role to admin
if ($user->wasRecentlyCreated == false) {
$user->update(['role' => 'admin']);
}

$this->command->info('کاربر تستی با موفقیت به عنوان مدیر ایجاد یا به‌روزرسانی شد.');
$this->command->info('ایمیل: test@example.com');
$this->command->info('رمز عبور: password');
}
}

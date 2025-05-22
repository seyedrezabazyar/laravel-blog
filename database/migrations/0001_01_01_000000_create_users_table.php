<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 100)->charset('utf8mb4');
            $table->string('email', 100)->unique()->charset('ascii');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 60)->charset('ascii');
            $table->enum('role', ['user', 'admin'])->default('user')->index();
            $table->string('remember_token', 100)->nullable()->charset('ascii');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('email');
            $table->index(['role', 'created_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 100)->primary()->charset('ascii');
            $table->string('token', 255)->charset('ascii');
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 40)->primary()->charset('ascii');
            $table->unsignedMediumInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->charset('ascii');
            $table->text('user_agent')->nullable()->charset('ascii');
            $table->longText('payload')->charset('ascii');
            $table->unsignedInteger('last_activity')->index();

            $table->index(['user_id', 'last_activity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

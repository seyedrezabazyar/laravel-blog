<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('user_id')->nullable()->index();
            $table->string('activity_type', 50)->charset('ascii'); // search, view, download
            $table->unsignedInteger('post_id')->nullable()->index();
            $table->string('search_query', 200)->nullable()->charset('utf8mb4');
            $table->json('metadata')->nullable(); // browser, location, etc
            $table->string('ip_address', 45)->nullable()->charset('ascii');
            $table->timestamp('created_at')->useCurrent();

            // ایندکس‌های بهینه برای آمارگیری
            $table->index(['activity_type', 'created_at']);
            $table->index(['user_id', 'activity_type']);
            $table->index(['post_id', 'activity_type']);
            $table->index('created_at');

            // کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('post_id')->references('id')->on('posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

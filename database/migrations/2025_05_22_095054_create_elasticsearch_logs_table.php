<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elasticsearch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('index_name', 100)->charset('ascii');
            $table->unsignedInteger('post_id')->nullable();
            $table->enum('action', ['index', 'update', 'delete', 'bulk_index', 'search']);
            $table->enum('status', ['success', 'failed', 'partial']);
            $table->text('message')->nullable()->charset('utf8mb4');
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('execution_time_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // ایندکس‌های بهینه
            $table->index(['index_name', 'action']);
            $table->index(['post_id', 'action']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elasticsearch_logs');
    }
};

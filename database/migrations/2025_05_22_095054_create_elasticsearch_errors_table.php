<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elasticsearch_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable()->index();
            $table->enum('action', ['index', 'update', 'delete', 'search']);
            $table->text('error_message')->charset('utf8mb4');
            $table->json('error_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elasticsearch_errors');
    }
};

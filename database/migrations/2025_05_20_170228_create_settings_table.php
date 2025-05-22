<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('key', 100)->unique()->charset('ascii');
            $table->text('value')->nullable()->charset('utf8mb4');
            $table->string('description', 300)->nullable()->charset('utf8mb4');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

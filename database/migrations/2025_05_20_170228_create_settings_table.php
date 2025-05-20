<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // فقط در صورتی که این جدول از قبل وجود نداشته باشد، آن را ایجاد کنید
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // جدول را حذف نمی‌کنیم چون ممکن است برای قابلیت‌های دیگر استفاده شود
        // در عوض، رکوردهای مرتبط با فیلتر محتوا را حذف می‌کنیم
        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'content_filters')->delete();
        }
    }
};

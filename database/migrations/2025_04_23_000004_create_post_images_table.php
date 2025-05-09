<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('caption', 1500)->nullable(); // Longitud 1500 caracteres

            // Enum con tres estados: NULL, visible, hidden. Default NULL
            $table->enum('hide_image', ['visible', 'hidden'])->nullable()->default(null);

            $table->integer('sort_order')->default(0);

            // Índices optimizados
            $table->index(['post_id', 'sort_order']); // Índice para ordenar imágenes
            $table->index(['post_id', 'hide_image']); // Índice combinado para consultas frecuentes
            $table->index('hide_image'); // Índice para filtrar imágenes ocultas

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};

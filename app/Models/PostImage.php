<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PostImage extends Model
{
    protected $fillable = [
        'post_id',
        'image_path',
        'caption',
        'hide_image',
        'sort_order',
    ];

    // TTL de caché para URLs de imágenes - 7 días
    protected $imageCacheTtl = 604800;

    /**
     * Relación con el post - optimizada
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Verificar si la imagen está oculta
     */
    public function isHidden()
    {
        return $this->hide_image === 'hidden';
    }

    /**
     * Verificar si la imagen es visible
     */
    public function isVisible()
    {
        return $this->hide_image === 'visible';
    }

    /**
     * Obtener URL de imagen en caché
     *
     * Esto reduce el procesamiento necesario para cada solicitud de imagen
     */
    public function getImageUrlAttribute()
    {
        $cacheKey = "post_image_{$this->id}_url";

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () {
            if (empty($this->image_path)) {
                return asset('images/default-book.png');
            }

            // URL directa para rutas HTTP/HTTPS
            if (strpos($this->image_path, 'http://') === 0 || strpos($this->image_path, 'https://') === 0) {
                return $this->image_path;
            }

            // Manejar dominio images.balyan.ir
            if (strpos($this->image_path, 'images.balyan.ir/') !== false) {
                return 'https://' . $this->image_path;
            }

            // Manejar imágenes del host de descarga
            if (strpos($this->image_path, 'post_images/') === 0 || strpos($this->image_path, 'posts/') === 0) {
                return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $this->image_path;
            }

            // Fallback al almacenamiento local
            return asset('storage/' . $this->image_path);
        });
    }

    /**
     * Obtener URL para mostrar la imagen
     *
     * Tiene en cuenta los permisos de usuario y la visibilidad de la imagen
     */
    public function getDisplayUrlAttribute()
    {
        // Generar clave de caché incluyendo el estado de admin
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $cacheKey = "post_image_{$this->id}_display_url_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, $this->imageCacheTtl, function () use ($isAdmin) {
            // Imagen predeterminada
            $defaultImage = asset('images/default-book.png');

            // Siempre mostrar la imagen real a los administradores
            if ($isAdmin) {
                return $this->image_url;
            }

            // Mostrar imagen predeterminada si está oculta o vacía
            if ($this->hide_image === 'hidden' || empty($this->image_path)) {
                return $defaultImage;
            }

            return $this->image_url;
        });
    }
}

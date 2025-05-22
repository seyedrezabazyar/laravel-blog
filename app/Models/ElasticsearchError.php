<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElasticsearchError extends Model
{
    public $timestamps = false; // فقط created_at داریم

    protected $fillable = [
        'post_id',
        'action',
        'error_message'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * رابطه با Post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * پاکسازی خطاهای قدیمی
     */
    public static function cleanOld(int $daysToKeep = 7)
    {
        return static::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }
}

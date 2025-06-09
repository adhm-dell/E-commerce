<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'image', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Clean up old image when updating
        static::updating(function ($brand) {
            if ($brand->isDirty('image') && $brand->getOriginal('image')) {
                Storage::disk('public')->delete($brand->getOriginal('image'));
            }
        });

        // Clean up image when deleting
        static::deleting(function ($brand) {
            if ($brand->image) {
                Storage::disk('public')->delete($brand->image);
            }
        });
    }
}

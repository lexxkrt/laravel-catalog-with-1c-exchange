<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasImage
{
    public static function bootHasImage()
    {
        static::deleting(function (Model $model) {
            $model->deleteImage();
        });
    }

    public function imageSmallUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->imageUrl('small', quality: 40)
        );
    }

    public function imageMediumUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->imageUrl('medium', quality: 60)
        );
    }

    public function imageLargeUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->imageUrl('large', quality: 90)
        );
    }

    public function imageUrl($size = 'original', $quality = 60)
    {
        if (empty($this->image)) {
            return asset('images/no_img.jpg');
        }

        if (Str::startsWith($this->image, 'http')) {
            return $this->image;
        }

        $file_path = Storage::disk("local")->path('images/' . $this->image);

        if (!File::exists($file_path)) {
            return asset('images/no_img.jpg');
        }

        if (!in_array($size, ['original', 'large', 'medium', 'small'])) {
            return abort(403);
        }

        $cache_path = Storage::disk('public')->path("images/cache/{$size}/");

        if (
            !File::exists($cache_path . $this->image)
            || File::lastModified($cache_path . $this->image) < File::lastModified($file_path)
        ) {
            if (!File::exists(dirname($cache_path . $this->image))) {
                File::makeDirectory(dirname($cache_path . $this->image), 0755, true);
            }

            $image = @Image::read($file_path);

            [$width, $height] = match ($size) {
                'small' => [100, 100],
                'medium' => [300, 300],
                'large' => [800, 800],
                default => [$image->width(), $image->height()]
            };

            $image->scale($width, $height)->resizeCanvas($width, $height)->save($cache_path . $this->image, quality: $quality);
        }

        return Storage::disk('public')->url("images/cache/{$size}/" . $this->image);
    }

    public function deleteImage($image = null)
    {
        is_null($image) and $image = $this->image;

        Storage::disk('local')->delete("images/{$image}");
        foreach (['original', 'small', 'medium', 'large'] as $size) {
            Storage::disk('public')->delete("images/cache/{$size}/{$image}");
        }
    }

}

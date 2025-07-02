<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function optimizeAndStore(UploadedFile $file, string $path, array $sizes = [])
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;

        // Crear directorio si no existe
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }

        // Procesar imagen principal
        $image = Image::make($file);
        
        // Optimizar calidad
        $image->encode('jpg', 85);
        
        // Guardar imagen original
        Storage::disk('public')->put($fullPath, $image->stream());

        // Crear versiones en diferentes tamaños
        $variants = [];
        foreach ($sizes as $size => $dimensions) {
            $variantPath = $path . '/' . $size . '_' . $filename;
            $variant = Image::make($file);
            
            if (isset($dimensions['width']) && isset($dimensions['height'])) {
                $variant->fit($dimensions['width'], $dimensions['height']);
            } elseif (isset($dimensions['width'])) {
                $variant->resize($dimensions['width'], null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            $variant->encode('jpg', 85);
            Storage::disk('public')->put($variantPath, $variant->stream());
            $variants[$size] = $variantPath;
        }

        return [
            'original' => $fullPath,
            'variants' => $variants
        ];
    }

    public function deleteImage(string $path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Eliminar variantes si existen
        $pathInfo = pathinfo($path);
        $files = Storage::disk('public')->files($pathInfo['dirname']);
        
        foreach ($files as $file) {
            if (str_contains($file, $pathInfo['filename'])) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    public function generateThumbnail(string $path, int $width = 150, int $height = 150)
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        $image = Image::make(Storage::disk('public')->path($path));
        $image->fit($width, $height);
        
        $thumbnailPath = str_replace('.', '_thumb.', $path);
        Storage::disk('public')->put($thumbnailPath, $image->stream());

        return $thumbnailPath;
    }
} 
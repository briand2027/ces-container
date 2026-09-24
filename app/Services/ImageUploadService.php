<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    public function storePrincipal(?UploadedFile $file, string $type = 'standard'): ?string
    {
        if (!$file) return null;
        $this->validate($file);
        return 'storage/'.$file->store('containers/'.$this->slug($type).'/principales', 'public');
    }

    public function storeSecondaries(array $files, string $type = 'standard'): array
    {
        $paths = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            $this->validate($file);
            $paths[] = 'storage/'.$file->store('containers/'.$this->slug($type).'/secondaires', 'public');
        }
        return $paths;
    }

    public function delete(?string $path): void
    {
        if (!$path) return;
        $path = preg_replace('#^storage/#', '', $path);
        if (Storage::disk('public')->exists($path)) Storage::disk('public')->delete($path);
    }

    private function validate(UploadedFile $file): void
    {
        abort_unless(in_array($file->getMimeType(), ['image/jpeg','image/png','image/webp','image/avif'], true), 422, 'Format image non autorisé.');
        abort_unless($file->getSize() <= 5 * 1024 * 1024, 422, 'Image trop volumineuse (5 Mo maximum).');
    }

    private function slug(string $type): string
    {
        return Str::slug($type) ?: 'standard';
    }
}

<?php

namespace App\Observers;

use App\Exceptions\BusinessException;
use App\Models\Media\News;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NewsObserver
{

    public function __construct(
        private MediaService $mediaService
    ) {}
    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        //
    }

    /**
     * Handle the News "updated" event.
     */
    public function updated(News $news): void
    {
        //
    }

    /**
     * Handle the News "deleted" event.
     */
    public function deleted(News $news): void
    {
        //
    }
    public function deleting(News $news): void
    {
        $pathArray = [$news->preview_picture, $news->detail_picture];

        foreach ($pathArray as $filePath) {
            if(!isset($filePath)) {
                continue;
            }
            $deleted = Storage::disk('public')->delete($filePath);
            if (!$deleted || Storage::disk('public')->exists($filePath)) {
                Log::channel('image')->error("Не удалось удалить файл {$filePath} у новости {$news->id}");
                throw new BusinessException("Failed to delete file: {$filePath}");
            }

        }

        if ($news->images->isNotEmpty()) {
            app(MediaService::class)->deleteImage($news->images);
        }
    }

    /**
     * Handle the News "restored" event.
     */
    public function restored(News $news): void
    {
        //
    }

    /**
     * Handle the News "force deleted" event.
     */
    public function forceDeleted(News $news): void
    {
        //
    }
}

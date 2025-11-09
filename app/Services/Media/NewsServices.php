<?php

namespace App\Services\Media;

use App\Dto\News\NewsDto;
use App\Exceptions\BusinessException;
use App\Models\Media\News;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NewsServices
{

    public function __construct(
        private MediaService $mediaService,
    )
    {}

    public function create (NewsDto $dto, $userId): News
    {
        return DB::transaction(function () use ($dto, $userId) {
            $new = new News();
            $new = $this->setParamsToNews($new, $dto, $userId);
            return $new;
        });
    }

    public function update(News $new, NewsDto $dto, $userId): News
    {
        return DB::transaction(function () use ($new, $dto, $userId) {
            $new = $this->setParamsToNews($new, $dto, $userId);
            Cache::tags('news')->flush();
            return $new;
        });
    }
    private function setParamsToNews(News $new,NewsDto $dto, $userId): News
    {
            $data = $this->processedData($dto);
            $tags = $dto->tags;

            $data['author_id'] = $userId;
            $data['active'] = true;

            $new->fill($data);
            $new->save();

            if ($tags) {
                $new->addTagBySlug($tags);
            }

            $this->setAllPicturesToNew($new, $dto);

            $new->load(['tags','author', 'images']);
            $new->save();

            return $new;
    }

    private function processedData(NewsDto $dto): array
    {
        $data = [];
        if (isset($dto->title)) {
            $data['title'] = $dto->title;
        }
        if (isset($dto->content)) {
            $data['content'] = $dto->content;
        }
        return $data;
    }

    protected function setAllPicturesToNew(News $new, NewsDto $dto): void
    {
        if ($dto->detailPicture) {
            $detailPicturePath = $dto->detailPicture->store('news/'.$new->id.'/detail', 'public');
            if (!isset($new->detail_picture) || !Storage::disk('public')->exists($new->detail_picture)) {
                $new->detail_picture = $detailPicturePath;
            } else {
                $this->deletePicture($new->detail_picture);
                $new->detail_picture = $detailPicturePath;
            }
        }

        if (isset($dto->previewPicture)) {
            $previewPicturePath = $dto->previewPicture->store('news/'.$new->id.'/preview', 'public');
            if (!isset($new->preview_picture) || !Storage::disk('public')->exists($new->preview_picture)) {
                $new->preview_picture = $previewPicturePath;
            } else {
                $this->deletePicture($new->preview_picture);
                $new->preview_picture = $previewPicturePath;
            }
        }
        if (!empty($dto->images)) {
            foreach ($dto->images as $image) {
                if ($image instanceof UploadedFile) {
                $this->mediaService->uploadFile($new,$image,'news_image');
                }
            }
        }
    }

    protected function deletePicture($filePath)
    {
        $deleted = Storage::disk('public')->delete($filePath);
        if (!$deleted || Storage::disk('public')->exists($filePath)) {
            Log::channel('image')->error("Failed to delete the preview image, file {$filePath}");
            throw new BusinessException("Failed to delete file: {$filePath}");
        }
    }
}

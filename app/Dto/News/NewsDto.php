<?php

namespace App\Dto\News;

use Illuminate\Http\UploadedFile;

class NewsDto
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $content,
        public readonly ?UploadedFile $detailPicture,
        public readonly ?UploadedFile $previewPicture,
        public readonly array $images,
        public readonly array $tags
    )
    {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            detailPicture: $data['detail_picture'] ?? null,
            previewPicture: $data['preview_picture'] ?? null,
            images: $data['images'] ?? [],
            tags: explode(',',$data['tags']) ?? []
        );
    }
}

<?php

namespace App\Dto\News;

class FilterForNewsListDto
{
    public function __construct(
        public int $page,
        public int $limit,
        public string $sort,
        public string $direction,
        public array|bool $tags,
        public bool $tagsFlag,
        public string|bool $userEmail,
        public string|bool $dateFrom,
        public string|bool $dateTo
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            page: $data['page'],
            limit: $data['limit'],
            sort: $data['sort'],
            direction: $data['direction'],
            tags: $data['tags'],
            tagsFlag: $data['tags_flag'],
            userEmail: $data['user_email'],
            dateFrom: $data['date_from'],
            dateTo: $data['date_to']
        );
    }

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();

        $result = [];
        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }

        return $result;
    }
}

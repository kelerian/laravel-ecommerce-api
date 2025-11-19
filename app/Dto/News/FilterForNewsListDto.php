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
            page: $data['page'] ?? 1,
            limit: $data['limit'] ?? 10,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
            tags: $data['tags'] ?? false,
            tagsFlag: filter_var($data['tags_flag'] ?? false, FILTER_VALIDATE_BOOLEAN),
            userEmail: $data['user_email'] ?? false,
            dateFrom: $data['date_from'] ?? false,
            dateTo: $data['date_to'] ?? false
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

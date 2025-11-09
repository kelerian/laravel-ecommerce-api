<?php

namespace App\Dto\Products;

class FilterForListDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly int $limit,
        public readonly int $page,
        public readonly string $direction,
        public readonly string $sort,
    )
    {}

    public static function fromArray(array $validated)
    {
        return new self(
            limit: $validated['limit'] ?? 10,
            page: $validated['page'] ?? 1,
            direction: $validated['direction'] ?? 'desc',
            sort: $validated['sort'] ?? 'date',
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

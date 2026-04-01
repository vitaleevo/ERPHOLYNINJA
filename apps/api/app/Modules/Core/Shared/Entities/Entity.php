<?php

namespace App\Modules\Core\Shared\Entities;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class Entity
{
    public function __construct(
        protected ?UuidInterface $id = null
    ) {
        $this->id = $id ?? Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function equals(self $other): bool
    {
        if (static::class !== get_class($other)) {
            return false;
        }

        return $this->id->equals($other->id);
    }

    public function __toString(): string
    {
        return $this->id->toString();
    }
}

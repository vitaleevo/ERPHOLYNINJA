<?php

namespace App\Modules\Core\Shared\DomainEvent;

use DateTimeImmutable;

interface DomainEvent
{
    public function occurredOn(): DateTimeImmutable;
    
    public function payload(): array;
}

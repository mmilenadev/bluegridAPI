<?php

namespace App\Message;

class DataTransferMessage
{
    private array $structuredData;

    public function __construct(array $structuredData)
    {
        $this->structuredData = $structuredData;
    }

    public function getStructuredData(): array
    {
        return $this->structuredData;
    }
}

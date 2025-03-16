<?php

namespace App\MessageHandler;
use App\Message\DataTransferMessage;
use App\Service\SaveDataToDatabase;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DataTransferHandler
{
    private SaveDataToDatabase $saveDataToDatabase;

    public function __construct(SaveDataToDatabase $saveDataToDatabase)
    {
        $this->saveDataToDatabase = $saveDataToDatabase;
    }

    public function __invoke(DataTransferMessage $message): void
    {
        $this->saveDataToDatabase->saveStructuredDataToDatabase($message->getStructuredData());
        gc_collect_cycles();
    }

}
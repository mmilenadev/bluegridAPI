<?php

namespace App\Service;

use App\Entity\Directory\Directory;

class DirectoryDataFormatterService
{
    public function formatDirectoryData(Directory $directory): array
    {
        return [
            $directory->getName(),
        ];
    }

    public function formatFileData($file): array
    {
        return [
            $file->getName(),
        ];
    }

    public function formatDirectoryAndFileData(Directory $directory): array
    {
        $formattedData = [];
        foreach ($directory->getSubDirectories() as $subdir) {
            $formattedData[$subdir->getName()] = $this->formatDirectoryAndFileData($subdir);
        }
        $files = array_unique(array_map(fn($file) => $file->getName(), $directory->getFiles()->toArray()));
        if (!empty($files)) {
            $formattedData = array_merge($formattedData, $files);
        }

        return $formattedData;
    }


}
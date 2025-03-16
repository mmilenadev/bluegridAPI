<?php

namespace App\Service;

use App\Entity\Directory\Directory;
use App\Entity\File\File;
use Doctrine\ORM\EntityManagerInterface;

 class SaveDataToDatabase
{
    private int $batchSize = 5000;
    private int $currentBatchCount = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

     public function saveStructuredDataToDatabase(array $structuredData): void
     {
         foreach ($structuredData as $ip => $directories) {
             $rootDirectory = $this->findOrCreateDirectory($ip);
             $this->processDirectoryStructure($directories, $rootDirectory);
         }
         $this->entityManager->flush();
     }

     private function findOrCreateDirectory(string $name, ?Directory $parent = null): Directory
     {
         $existingDirectory = $this->entityManager->getRepository(Directory::class)
             ->findOneBy(['name' => $name, 'parent' => $parent]);

         if ($existingDirectory) {
             return $existingDirectory;
         }

         $directory = new Directory();
         $directory->setName($name);
         $directory->setParent($parent);

         $this->entityManager->persist($directory);
         $this->batchProcess();

         return $directory;
     }

     private function processDirectoryStructure(mixed $directories, Directory $parentDirectory): void
     {
         foreach ($directories as $directoryName => $contents) {
             if (is_array($contents)) {
                 $subdirectory = $this->findOrCreateDirectory($directoryName, $parentDirectory);
                 foreach ($contents as $subItem) {
                     if (is_array($subItem)) {
                         foreach ($subItem as $subDirName => $files) {
                             $subDir = $this->findOrCreateDirectory($subDirName, $subdirectory);
                             foreach ($files as $fileName) {
                                 $this->createFile($subDir, $fileName);
                             }
                         }
                     } else {
                         $this->createFile($subdirectory, $subItem);
                     }
                 }
             } else {
                 $this->createFile($parentDirectory, $contents);
             }
         }
     }

     private function createFile(Directory $directory, string $fileName): void
     {
         $file = new File();
         $file->setName($fileName);
         $file->setDirectory($directory);

         $this->entityManager->persist($file);
         $this->batchProcess(); // Process in batches
     }

     private function batchProcess(): void
     {
         $this->currentBatchCount++;
         if ($this->currentBatchCount >= $this->batchSize) {
             $this->entityManager->flush();
             $this->entityManager->clear();
             $this->currentBatchCount = 0;
         }
     }
 }

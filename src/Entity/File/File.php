<?php

namespace App\Entity\File;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\GetFilesAction;
use App\Entity\Directory\Directory;
use App\Entity\Traits\TimestampableEntity;
use App\Repository\File\FileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            controller: GetFilesAction::class,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
    ],
    paginationMaximumItemsPerPage: 100,
)]
#[ORM\HasLifecycleCallbacks]
class File
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Directory::class, cascade: ['persist'], inversedBy: 'files')]
    private ?Directory $directory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDirectory(): ?Directory
    {
        return $this->directory;
    }

    public function setDirectory(?Directory $directory): static
    {
        $this->directory = $directory;

        return $this;
    }
}

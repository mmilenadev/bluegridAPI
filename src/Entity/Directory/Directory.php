<?php

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\GetDirectoriesAction;
use App\Controller\GetFilesAndDirectoriesAction;
use App\Entity\File\File;
use App\Entity\Traits\TimestampableEntity;
use App\Repository\Directory\DirectoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DirectoryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/files-and-directories',
            controller: GetFilesAndDirectoriesAction::class,
            security: "is_granted('PUBLIC_ACCESS')"),
        new GetCollection(
            controller: GetDirectoriesAction::class,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
    ],
    paginationMaximumItemsPerPage: 100,
)]
#[ORM\HasLifecycleCallbacks]
class Directory
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: self::class, cascade: ['persist'], inversedBy: 'subdirectories')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', cascade: ['persist'])]
    private Collection $subDirectories;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'directory', cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->subDirectories = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubDirectories(): Collection
    {
        return $this->subDirectories;
    }

    public function addSubDirectory(self $subDirectory): static
    {
        if (!$this->subDirectories->contains($subDirectory)) {
            $this->subDirectories->add($subDirectory);
            $subDirectory->setParent($this);
        }

        return $this;
    }

    public function removeSubDirectory(self $subDirectory): static
    {
        if ($this->subDirectories->removeElement($subDirectory)) {
            // set the owning side to null (unless already changed)
            if ($subDirectory->getParent() === $this) {
                $subDirectory->setParent(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setDirectory($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getDirectory() === $this) {
                $file->setDirectory(null);
            }
        }

        return $this;
    }
}

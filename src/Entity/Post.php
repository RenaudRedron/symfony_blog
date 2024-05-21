<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // *******************
    // Title
    // *******************

    #[Assert\NotBlank(
        message: "Le titre de l'article est obligatoire."
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le titre ne peu pas avoir plus de {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    // *******************
    // Slug
    // *******************
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    // *******************
    // Category
    // *******************

    #[Assert\NotBlank(
        message: "Le choix d'une catégorie d'article est obligatoire."
    )]
    #[Assert\Type(
        type: Category::class,
        message: 'Cette catégorie est invalide.',
    )]
    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    // *******************
    // User
    // *******************

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?User $user = null;

    // *******************
    // Image
    // *******************

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Assert\File(
        maxSize: '5120k',
        extensions: ['png', 'jpg', 'jpeg', 'webp'],
        extensionsMessage: 'Seuls les images en formats .png, .jpg, .jpeg, où .webp sont autorisés',
    )]
    #[Vich\UploadableField(mapping: 'posts', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $image = null;

    // *******************
    // Content
    // *******************

    #[Assert\NotBlank(
        message: "L'article doit obligatoirement possèdé un contenu."
    )]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Le contenu ne peu pas avoir plus de {{ limit }} caractères',
    )]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    // *******************
    // isPublished
    // *******************

    #[ORM\Column]
    private ?bool $isPublished = null;
    // private ?bool $isPublished = false; <<-- on peu faire comme cela pour initialisé a false isPublished mais il est plus convenable d'initialisé dans le __construct

    // *******************
    // Created At
    // *******************

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    // *******************
    // Updated At
    // *******************

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // *******************
    // Published At
    // *******************

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    public function __construct()
    {
        $this->isPublished = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}

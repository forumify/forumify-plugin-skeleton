<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton\Entity;

// skeleton:if api
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
// skeleton:endif
use Doctrine\ORM\Mapping as ORM;
use Forumify\PluginSkeleton\Repository\ExampleRepository;
use Gedmo\Mapping\Annotation as Gedmo;
// skeleton:if api
use Symfony\Component\Serializer\Attribute\Groups;
// skeleton:endif
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An example entity to build on. Rename it, or delete it and add your own.
 *
 * The table is mapped automatically: forumify registers src/Entity for every plugin, so
 * you never have to point doctrine at your own vendor path.
 */
#[ORM\Entity(repositoryClass: ExampleRepository::class)]
#[ORM\Table('plugin_skeleton_example')]
// skeleton:if api
#[ApiResource(
    shortName: 'Example',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['example:read']],
    denormalizationContext: ['groups' => ['example:write']],
)]
// skeleton:endif
class Example
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    // skeleton:if api
    #[Groups(['example:read', 'example:write'])]
    // skeleton:endif
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    // skeleton:if api
    #[Groups(['example:read', 'example:write'])]
    // skeleton:endif
    private ?string $content = null;

    #[ORM\Column]
    // skeleton:if api
    #[Groups(['example:read'])]
    // skeleton:endif
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTime $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}

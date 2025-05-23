<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;



#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'userid')]
    private ?int $userid = null;

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(int $userid): self
    {
        $this->userid = $userid;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $username = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        // Don't hash here - let the controller handle hashing
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false, name: 'phonenumber')]
    private ?string $phonenumber = null;

    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(string $phonenumber): self
    {
        $this->phonenumber = $phonenumber;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'user')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        if (!$this->events instanceof Collection) {
            $this->events = new ArrayCollection();
        }
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
        }
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        $this->getEvents()->removeElement($event);
        return $this;
    }
    public function getRoles(): array
    {
        // Return an array of roles with ROLE_ prefix
        $roles = [];
        if ($this->role) {
            $roles[] = 'ROLE_' . strtoupper($this->role);
        }

        // Every user must have at least ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary sensitive data
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }


    #[Vich\UploadableField(mapping: 'user_profile_image', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // ... existing methods ...

    

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @internal
     */
    public function serialize(): string
    {
        return serialize([
            $this->userid,
            $this->username,
            $this->password,
            $this->imageName,
            // Add other properties needed for serialization
        ]);
    }

    /**
     * @internal
     */
    public function unserialize($serialized): void
    {
        [
            $this->userid,
            $this->username,
            $this->password,
            $this->imageName,
            // Match the order from serialize()
        ] = unserialize($serialized);
    }

    public function __sleep()
    {
        return ['userid', 'username', 'password', 'email', 'name', 'phonenumber', 'status', 'role', 'imageName', 'updatedAt'];
    }

    // VichUploader methods
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

}

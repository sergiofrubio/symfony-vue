<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class)]
    private Collection $roles;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'users')]
    private Collection $companies;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Company $defaultCompany = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_active = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $last_login = null;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->is_active = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password ?? '');

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $last_login): static
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles->map(function (Role $r) {
            return $r->getSlug() ?? $r->getName();
        })->toArray();

        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_values(array_unique(array_filter($roles)));
    }

    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        $this->companies->removeElement($company);

        return $this;
    }

    public function getDefaultCompany(): ?Company
    {
        return $this->defaultCompany;
    }

    public function setDefaultCompany(?Company $defaultCompany): static
    {
        $this->defaultCompany = $defaultCompany;

        return $this;
    }

    /**
     * Obtiene todos los códigos de permiso asignados a través de sus roles
     * @return string[]
     */
    public function getPermissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                if ($code = $permission->getCode()) {
                    $permissions[] = $code;
                }
            }
        }
        return array_values(array_unique($permissions));
    }
}

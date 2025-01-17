<?php

namespace User\Model;

use Decision\Model\Member as MemberModel;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection,
};
use Doctrine\ORM\Mapping\{Column,
    Entity,
    Id,
    JoinColumn,
    OneToMany,
    OneToOne,
};
use Laminas\Permissions\Acl\{
    Resource\ResourceInterface,
    Role\RoleInterface,
};
use RuntimeException;

/**
 * User model.
 */
#[Entity]
class User implements RoleInterface, ResourceInterface
{
    /**
     * The membership number.
     */
    #[Id]
    #[Column(type: "integer")]
    protected int $lidnr;

    /**
     * The user's email address.
     * Deprecated.
     */
    #[Column(type: "string")]
    protected string $email;

    /**
     * The user's password.
     */
    #[Column(type: "string")]
    protected string $password;

    /**
     * User roles.
     */
    #[OneToMany(
        targetEntity: UserRole::class,
        mappedBy: "lidnr",
    )]
    protected Collection $roles;

    /**
     * The corresponding member for this user.
     */
    #[OneToOne(
        targetEntity: MemberModel::class,
        fetch: "EAGER",
    )]
    #[JoinColumn(
        name: "lidnr",
        referencedColumnName: "lidnr",
        nullable: false,
    )]
    protected MemberModel $member;

    /**
     * Constructor.
     *
     * @param NewUser|null $newUser
     */
    public function __construct(NewUser $newUser = null)
    {
        $this->roles = new ArrayCollection();

        if (null !== $newUser) {
            $this->lidnr = $newUser->getLidnr();
            $this->email = $newUser->getEmail();
            $this->member = $newUser->getMember();
        }
    }

    /**
     * Get the membership number.
     *
     * @return int
     */
    public function getLidnr(): int
    {
        return $this->lidnr;
    }

    /**
     * Get the user's email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->member->getEmail();
    }

    /**
     * Get the password hash.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the password hash.
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Get the user's roles.
     *
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * Get the member information of this user.
     *
     * @return MemberModel
     */
    public function getMember(): MemberModel
    {
        return $this->member;
    }

    /**
     * Get the user's role names.
     *
     * @return array Role names
     */
    public function getRoleNames(): array
    {
        $names = [];

        foreach ($this->getRoles() as $role) {
            $names[] = $role->getRole();
        }

        return $names;
    }

    /**
     * @return string
     */
    public function getRoleId(): string
    {
        $roleNames = $this->getRoleNames();
        if (in_array('admin', $roleNames) || $this->getMember()->isBoardMember()) {
            return 'admin';
        }

        if (in_array('company_admin', $roleNames)) {
            return 'company_admin';
        }

        if (count($this->getMember()->getCurrentOrganInstallations()) > 0) {
            return 'active_member';
        }

        if (empty($roleNames)) {
            return 'user';
        }

        if (in_array('photo_guest', $roleNames)) {
            return 'photo_guest';
        }

        throw new RuntimeException(
            sprintf('Could not determine user role unambiguously for user %s', $this->getLidnr())
        );
    }

    /**
     * @param ArrayCollection $roles
     */
    public function setRoles(ArrayCollection $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param int $lidnr
     */
    public function setLidnr(int $lidnr): void
    {
        $this->lidnr = $lidnr;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param MemberModel $member
     */
    public function setMember(MemberModel $member): void
    {
        $this->member = $member;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lidnr' => $this->getLidnr(),
            'email' => $this->getEmail(),
            'member' => $this->getMember()->toArray(),
        ];
    }

    /**
     * Get the user's resource ID.
     *
     * @return string
     */
    public function getResourceId(): string
    {
        return 'user';
    }
}

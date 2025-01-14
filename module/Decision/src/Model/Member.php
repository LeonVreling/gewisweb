<?php

namespace Decision\Model;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection,
};
use Doctrine\ORM\Mapping\{
    Column,
    Entity,
    Id,
    InverseJoinColumn,
    JoinColumn,
    JoinTable,
    ManyToMany,
    OneToMany,
    OneToOne,
};
use Decision\Model\SubDecision\Installation;
use InvalidArgumentException;
use User\Model\User as UserModel;

/**
 * Member model.
 */
#[Entity]
class Member
{
    public const GENDER_MALE = 'm';
    public const GENDER_FEMALE = 'f';
    public const GENDER_OTHER = 'o';

    public const TYPE_ORDINARY = 'ordinary';
    public const TYPE_PROLONGED = 'prolonged';
    public const TYPE_EXTERNAL = 'external';
    public const TYPE_EXTRAORDINARY = 'extraordinary';
    public const TYPE_HONORARY = 'honorary';

    /**
     * The user.
     */
    #[Id]
    #[Column(type: "integer")]
    #[OneToOne(targetEntity: UserModel::class)]
    #[JoinColumn(
        name: "lidnr",
        referencedColumnName: "lidnr",
    )]
    protected int $lidnr;

    /**
     * Member's email address.
     */
    #[Column(type: "string")]
    protected string $email;

    /**
     * Member's last name.
     */
    #[Column(type: "string")]
    protected string $lastName;

    /**
     * Middle name.
     */
    #[Column(type: "string")]
    protected string $middleName;

    /**
     * Initials.
     */
    #[Column(type: "string")]
    protected string $initials;

    /**
     * First name.
     */
    #[Column(type: "string")]
    protected string $firstName;

    /**
     * Gender of the member.
     *
     * Either one of:
     * - m
     * - f
     */
    #[Column(
        type: "string",
        length: 1,
    )]
    protected string $gender;

    /**
     * Generation.
     *
     * This is the year that this member became a GEWIS member. This is not
     * a academic year, but rather a calendar year.
     */
    #[Column(type: "integer")]
    protected int $generation;

    /**
     * Member type.
     *
     * This can be one of the following, as defined by the GEWIS statuten:
     *
     * - ordinary
     * - prolonged
     * - external
     * - extraordinary
     * - honorary
     *
     * You can find the GEWIS Statuten here:
     *
     * http://gewis.nl/vereniging/statuten/statuten.php
     *
     * Zie artikel 7 lid 1 en 2.
     */
    #[Column(type: "string")]
    protected string $type;

    /**
     * Last changed date of membership.
     */
    #[Column(type: "date")]
    protected DateTime $changedOn;

    /**
     * Member birth date.
     */
    #[Column(type: "date")]
    protected DateTime $birth;

    /**
     * Member expiration date.
     */
    #[Column(type: "date")]
    protected DateTime $expiration;

    /**
     * How much the member has paid for membership. 0 by default.
     */
    #[Column(type: "integer")]
    protected int $paid = 0;

    /**
     * Iban number.
     */
    #[Column(
        type: "string",
        nullable: true,
    )]
    protected ?string $iban = null;

    /**
     * If the member receives a 'supremum'.
     */
    #[Column(
        type: "string",
        nullable: true,
    )]
    protected ?string $supremum = null;

    /**
     * Addresses of this member.
     */
    #[OneToMany(
        targetEntity: Address::class,
        mappedBy: "member",
        cascade: ["persist"],
    )]
    protected Collection $addresses;

    /**
     * Installations of this member.
     */
    #[OneToMany(
        targetEntity: Installation::class,
        mappedBy: "member",
    )]
    protected Collection $installations;

    /**
     * Memberships of mailing lists.
     */
    #[ManyToMany(
        targetEntity: MailingList::class,
        inversedBy: "members",
    )]
    #[JoinTable(name: "members_mailinglists")]
    #[JoinColumn(
        name: "lidnr",
        referencedColumnName: "lidnr"
    )]
    #[InverseJoinColumn(
        name: "name",
        referencedColumnName: "name",
    )]
    protected Collection $lists;

    /**
     * Organ memberships.
     */
    #[OneToMany(
        targetEntity: OrganMember::class,
        mappedBy: "member",
    )]
    protected Collection $organInstallations;

    /**
     * Board memberships.
     */
    #[OneToMany(
        targetEntity: BoardMember::class,
        mappedBy: "member",
    )]
    protected Collection $boardInstallations;

    /**
     * Static method to get available genders.
     *
     * @return array
     */
    protected static function getGenders(): array
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
            self::GENDER_OTHER,
        ];
    }

    /**
     * Static method to get available member types.
     *
     * @return array
     */
    protected static function getTypes(): array
    {
        return [
            self::TYPE_ORDINARY,
            self::TYPE_PROLONGED,
            self::TYPE_EXTERNAL,
            self::TYPE_EXTRAORDINARY,
            self::TYPE_HONORARY,
        ];
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->installations = new ArrayCollection();
        $this->organInstallations = new ArrayCollection();
        $this->boardInstallations = new ArrayCollection();
        $this->lists = new ArrayCollection();
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
     * Get the member's email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the member's last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Get the member's middle name.
     *
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * Get the member's initials.
     *
     * @return string
     */
    public function getInitials(): string
    {
        return $this->initials;
    }

    /**
     * Get the member's first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set the lidnr.
     *
     * @param int $lidnr
     */
    public function setLidnr(int $lidnr): void
    {
        $this->lidnr = $lidnr;
    }

    /**
     * Set the member's email address.
     *
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Set the member's last name.
     *
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Set the member's middle name.
     *
     * @param string $middleName
     */
    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * Set the member's initials.
     *
     * @param string $initials
     */
    public function setInitials(string $initials): void
    {
        $this->initials = $initials;
    }

    /**
     * Set the member's first name.
     *
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Assemble the member's full name.
     *
     * @return string
     */
    public function getFullName(): string
    {
        $name = $this->getFirstName() . ' ';

        $middle = $this->getMiddleName();
        if (!empty($middle)) {
            $name .= $middle . ' ';
        }

        return $name . $this->getLastName();
    }

    /**
     * Get the member's gender.
     *
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * Set the member's gender.
     *
     * @param string $gender
     *
     * @throws InvalidArgumentException when the gender does not have correct value
     */
    public function setGender(string $gender): void
    {
        if (!in_array($gender, self::getGenders())) {
            throw new InvalidArgumentException('Invalid gender value');
        }
        $this->gender = $gender;
    }

    /**
     * Get the generation.
     *
     * @return int
     */
    public function getGeneration(): int
    {
        return $this->generation;
    }

    /**
     * Set the generation.
     *
     * @param int $generation
     */
    public function setGeneration(int $generation): void
    {
        $this->generation = $generation;
    }

    /**
     * Get the member type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the member type.
     *
     * @param string $type
     *
     * @throws InvalidArgumentException when the type is incorrect
     */
    public function setType(string $type): void
    {
        if (!in_array($type, self::getTypes())) {
            throw new InvalidArgumentException('Nonexisting type given.');
        }
        $this->type = $type;
    }

    /**
     * Get the expiration date.
     *
     * The information comes from the statuten and HR.
     *
     * @return DateTime
     */
    public function getExpiration(): DateTime
    {
        return $this->expiration;
    }

    /**
     * Set the expiration date.
     *
     * @param DateTime $expiration
     */
    public function setExpiration(DateTime $expiration): void
    {
        $this->expiration = $expiration;
    }

    /**
     * Get the birth date.
     *
     * @return DateTime
     */
    public function getBirth(): DateTime
    {
        return $this->birth;
    }

    /**
     * Set the birthdate.
     *
     * @param DateTime $birth
     */
    public function setBirth(DateTime $birth): void
    {
        $this->birth = $birth;
    }

    /**
     * Get the date of the last membership change.
     *
     * @return DateTime
     */
    public function getChangedOn(): DateTime
    {
        return $this->changedOn;
    }

    /**
     * Set the date of the last membership change.
     *
     * @param DateTime $changedOn
     */
    public function setChangedOn(DateTime $changedOn): void
    {
        $this->changedOn = $changedOn;
    }

    /**
     * Get how much has been paid.
     *
     * @return int
     */
    public function getPaid(): int
    {
        return $this->paid;
    }

    /**
     * Set how much has been paid.
     *
     * @param int $paid
     */
    public function setPaid(int $paid): void
    {
        $this->paid = $paid;
    }

    /**
     * Get the IBAN.
     *
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * Set the IBAN.
     *
     * @param string|null $iban
     */
    public function setIban(?string $iban): void
    {
        $this->iban = $iban;
    }

    /**
     * Get if the member wants a supremum.
     *
     * @return string|null
     */
    public function getSupremum(): ?string
    {
        return $this->supremum;
    }

    /**
     * Set if the member wants a supremum.
     *
     * @param string|null $supremum
     */
    public function setSupremum(?string $supremum): void
    {
        $this->supremum = $supremum;
    }

    /**
     * Get the installations.
     *
     * @return Collection
     */
    public function getInstallations(): Collection
    {
        return $this->installations;
    }

    /**
     * Get the organ installations.
     *
     * @return Collection
     */
    public function getOrganInstallations(): Collection
    {
        return $this->organInstallations;
    }

    /**
     * Get the organ installations of organs that the member is currently part of.
     *
     * @return Collection
     */
    public function getCurrentOrganInstallations(): Collection
    {
        if ($this->getOrganInstallations()->isEmpty()) {
            return new ArrayCollection();
        }

        // Filter out past installations
        $today = new DateTime();

        return $this->getOrganInstallations()->filter(
            function (OrganMember $organ) use ($today) {
                $dischargeDate = $organ->getDischargeDate();

                // Keep installation if not discharged or discharged in the future
                return is_null($dischargeDate) || $dischargeDate >= $today;
            }
        );
    }

    /**
     * Returns whether the member is currently part of any organs.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->getCurrentOrganInstallations()->isEmpty();
    }

    /**
     * Get the board installations.
     *
     * @return Collection
     */
    public function getBoardInstallations(): Collection
    {
        return $this->boardInstallations;
    }

    /**
     * Get the current board the member is part of.
     *
     * @return BoardMember|null
     */
    public function getCurrentBoardInstallation(): ?BoardMember
    {
        // Filter out past board installations
        $today = new DateTime();

        $boards = $this->getBoardInstallations()->filter(
            function (BoardMember $boardMember) use ($today) {
                $dischargeDate = $boardMember->getDischargeDate();

                // Keep installation if not discharged or discharged in the future
                return is_null($dischargeDate) || $dischargeDate >= $today;
            }
        );

        if ($boards->isEmpty()) {
            return null;
        }

        // Assume a member has a single board installation at a time
        return $boards[0];
    }

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lidnr' => $this->getLidnr(),
            'email' => $this->getEmail(),
            'fullName' => $this->getFullName(),
            'lastName' => $this->getLastName(),
            'middleName' => $this->getMiddleName(),
            'initials' => $this->getInitials(),
            'firstName' => $this->getFirstName(),
            'generation' => $this->getGeneration(),
            'expiration' => $this->getExpiration()->format('l j F Y'),
        ];
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'lidnr' => $this->getLidnr(),
            'email' => $this->getEmail(),
            'fullName' => $this->getFullName(),
            'initials' => $this->getInitials(),
            'firstName' => $this->getFirstName(),
            'middleName' => $this->getMiddleName(),
            'lastName' => $this->getLastName(),
            'birth' => $this->getBirth()->format(DateTimeInterface::ISO8601),
            'generation' => $this->getGeneration(),
            'expiration' => $this->getExpiration()->format(DateTimeInterface::ISO8601),
        ];
    }

    /**
     * Get all addresses.
     *
     * @return Collection all addresses
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * Clear all addresses.
     */
    public function clearAddresses(): void
    {
        $this->addresses = new ArrayCollection();
    }

    /**
     * Add multiple addresses.
     *
     * @param array $addresses
     */
    public function addAddresses(array $addresses): void
    {
        foreach ($addresses as $address) {
            $this->addAddress($address);
        }
    }

    /**
     * Add an address.
     *
     * @param Address $address
     */
    public function addAddress(Address $address): void
    {
        $address->setMember($this);
        $this->addresses[] = $address;
    }

    /**
     * Get mailing list subscriptions.
     *
     * @return Collection
     */
    public function getLists(): Collection
    {
        return $this->lists;
    }

    /**
     * Add a mailing list subscription.
     *
     * Note that this is the owning side.
     *
     * @param MailingList $list
     */
    public function addList(MailingList $list): void
    {
        $list->addMember($this);
        $this->lists[] = $list;
    }

    /**
     * Add multiple mailing lists.
     *
     * @param array $lists
     */
    public function addLists(array $lists): void
    {
        foreach ($lists as $list) {
            $this->addList($list);
        }
    }

    /**
     * Clear the lists.
     */
    public function clearLists(): void
    {
        $this->lists = new ArrayCollection();
    }

    /**
     * Returns true the member is currently installed as a board member and false otherwise.
     *
     * @return bool
     */
    public function isBoardMember(): bool
    {
        foreach ($this->getBoardInstallations() as $boardInstall) {
            if ($this->isCurrentBoard($boardInstall)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this is a current board member.
     *
     * @param BoardMember $boardMember
     *
     * @return bool
     */
    protected function isCurrentBoard(BoardMember $boardMember): bool
    {
        $now = new DateTime();
        $currentAssociationYear = AssociationYear::fromDate($now);
        $installDate = $boardMember->getInstallDate();
        $dischargeDate = $boardMember->getDischargeDate();

        if ($installDate <= $now) {
            // Installation was (before) today.
            if (
                null === $dischargeDate
                || $dischargeDate >= $now
            ) {
                // Not yet discharged or the discharge is the in the future.
                if ($installDate->format('Y') === $now->format('Y')) {
                    if ($installDate >= $currentAssociationYear->getEndDate()) {
                        // It is the calendar year of the installation and after July 1, hence this person is in the
                        // current board.
                        return true;
                    }
                } else {
                    // It is not the same calendar year as the installation, so we need to check if it is before July 1.
                    // Create a new DateTime from the installation date to be July 1 the following year.
                    $newDate = DateTime::createFromFormat('Y-m-d', sprintf('%s-07-01', $installDate->format('Y')))
                        ->add(new DateInterval('P1Y'));

                    if ($now->format('Ymd') < $newDate->format('Ymd')) {
                        // Year following the installation but before July 1, hence this person is in the current board.
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function is18Plus(): bool
    {
        return (18 <= (new DateTime('now'))->diff($this->getBirth())->y);
    }
}

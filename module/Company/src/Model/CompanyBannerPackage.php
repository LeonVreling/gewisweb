<?php

namespace Company\Model;

use Doctrine\ORM\Mapping\{
    Column,
    Entity,
};

/**
 * CompanyBannerPackage model.
 */
#[Entity]
class CompanyBannerPackage extends CompanyPackage
{
    /**
     * The banner's image URL.
     */
    #[Column(
        type: "string",
        nullable: true,
    )]
    protected ?string $image = null;

    /**
     * Get the banner's image URL.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the banner's image URL.
     *
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'banner';
    }
}

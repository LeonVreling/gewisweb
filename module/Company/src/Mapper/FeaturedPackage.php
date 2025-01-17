<?php

namespace Company\Mapper;

use Company\Model\CompanyFeaturedPackage as CompanyFeaturedPackageModel;

/**
 * Mappers for package.
 *
 * NOTE: Packages will be modified externally by a script. Modifications will be
 * overwritten.
 */
class FeaturedPackage extends Package
{
    /**
     * Returns a random featured package from the active featured packages,
     * and null when there is no featured package.
     *
     * @return CompanyFeaturedPackageModel|null
     */
    public function getFeaturedPackage(): ?CompanyFeaturedPackageModel
    {
        $featuredPackages = $this->findVisiblePackages();

        if (!empty($featuredPackages)) {
            return $featuredPackages[array_rand($featuredPackages)];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getRepositoryName(): string
    {
        return CompanyFeaturedPackageModel::class;
    }
}

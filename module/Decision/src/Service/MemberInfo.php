<?php

namespace Decision\Service;

use Decision\Mapper\Member as MemberMapper;
use Decision\Model\Member as MemberModel;
use Exception;
use Laminas\Mvc\I18n\Translator;
use Photo\Service\Photo as PhotoService;
use User\Mapper\ApiAppAuthentication as ApiAppAuthenticationMapper;
use User\Permissions\NotAllowedException;

/**
 * Member service.
 */
class MemberInfo
{
    /**
     * @var AclService
     */
    private AclService $aclService;

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var PhotoService
     */
    private PhotoService $photoService;

    /**
     * @var MemberMapper
     */
    private MemberMapper $memberMapper;

    private ApiAppAuthenticationMapper $apiAppAuthenticationMapper;

    /**
     * @var array
     */
    private array $photoConfig;

    /**
     * @param AclService $aclService
     * @param Translator $translator
     * @param PhotoService $photoService
     * @param MemberMapper $memberMapper
     * @param ApiAppAuthenticationMapper $apiAppAuthenticationMapper
     * @param array $photoConfig
     */
    public function __construct(
        AclService $aclService,
        Translator $translator,
        PhotoService $photoService,
        MemberMapper $memberMapper,
        ApiAppAuthenticationMapper $apiAppAuthenticationMapper,
        array $photoConfig,
    ) {
        $this->aclService = $aclService;
        $this->translator = $translator;
        $this->photoService = $photoService;
        $this->memberMapper = $memberMapper;
        $this->apiAppAuthenticationMapper = $apiAppAuthenticationMapper;
        $this->photoConfig = $photoConfig;
    }

    /**
     * Obtain information about the current user.
     *
     * @param int|null $lidnr
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function getMembershipInfo(?int $lidnr = null): ?array
    {
        if (null === $lidnr && !$this->aclService->isAllowed('view_self', 'member')) {
            throw new NotAllowedException($this->translator->translate('You are not allowed to view membership info.'));
        } elseif (null !== $lidnr && !$this->aclService->isAllowed('view', 'member')) {
            throw new NotAllowedException($this->translator->translate('You are not allowed to view members.'));
        }

        if (null === $lidnr) {
            $lidnr = $this->aclService->getIdentityOrThrowException()->getLidnr();
        }

        $member = $this->memberMapper->findByLidnr($lidnr);

        if (null === $member) {
            return null;
        }

        $memberships = $this->getOrganMemberships($member);

        $tags = $this->photoService->getTagsForMember($member);

        // Base directory for retrieving photos
        $basedir = $this->photoService->getBaseDirectory();

        $profilePhoto = $this->photoService->getProfilePhoto($lidnr);
        $isExplicitProfilePhoto = $this->photoService->hasExplicitProfilePhoto($lidnr);

        return [
            'member' => $member,
            'memberships' => $memberships,
            'tags' => $tags,
            'profilePhoto' => $profilePhoto,
            'isExplicitProfilePhoto' => $isExplicitProfilePhoto,
            'basedir' => $basedir,
            'photoConfig' => $this->photoConfig,
            'apps' => $this->apiAppAuthenticationMapper->getFirstAndLastAuthenticationPerApiApp($member),
        ];
    }

    /**
     * Gets a list of all organs which the member currently is part of.
     *
     * @param MemberModel $member
     *
     * @return array
     */
    public function getOrganMemberships(MemberModel $member): array
    {
        $memberships = [];
        foreach ($member->getOrganInstallations() as $install) {
            if (null !== $install->getDischargeDate()) {
                continue;
            }

            if (!isset($memberships[$install->getOrgan()->getAbbr()])) {
                $memberships[$install->getOrgan()->getAbbr()] = [];
                $memberships[$install->getOrgan()->getAbbr()]['organ'] = $install->getOrgan();
            }

            if ('Lid' != $install->getFunction()) {
                $function = $this->translator->translate($install->getFunction());
                $memberships[$install->getOrgan()->getAbbr()]['functions'] = $function;
            }
        }

        return $memberships;
    }
}

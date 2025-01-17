<?php

namespace Decision\Controller;

use Decision\Service\{
    AclService,
    Decision as DecisionService,
    Member as MemberService,
    MemberInfo as MemberInfoService,
};
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\{
    JsonModel,
    ViewModel,
};
use Laminas\Mvc\I18n\Translator;
use User\Permissions\NotAllowedException;

class MemberController extends AbstractActionController
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
     * @var MemberService
     */
    private MemberService $memberService;

    /**
     * @var MemberInfoService
     */
    private MemberInfoService $memberInfoService;

    /**
     * @var DecisionService
     */
    private DecisionService $decisionService;

    /**
     * @var array
     */
    private array $regulationsConfig;

    /**
     * MemberController constructor.
     *
     * @param AclService $aclService
     * @param MemberService $memberService
     * @param MemberInfoService $memberInfoService
     * @param DecisionService $decisionService
     * @param array $regulationsConfig
     */
    public function __construct(
        AclService $aclService,
        Translator $translator,
        MemberService $memberService,
        MemberInfoService $memberInfoService,
        DecisionService $decisionService,
        array $regulationsConfig,
    ) {
        $this->aclService = $aclService;
        $this->translator = $translator;
        $this->memberService = $memberService;
        $this->memberInfoService = $memberInfoService;
        $this->decisionService = $decisionService;
        $this->regulationsConfig = $regulationsConfig;
    }

    public function indexAction(): ViewModel
    {
        // Get the latest 3 meetings of each type and flatten result
        $meetingsCollection = [
            'AV' => array_column($this->decisionService->getPastMeetings(3, 'AV'), 0),
            'BV' => array_column($this->decisionService->getPastMeetings(3, 'BV'), 0),
            'VV' => array_column($this->decisionService->getPastMeetings(3, 'VV'), 0),
        ];

        $member = $this->aclService->getIdentityOrThrowException()->getMember();

        return new ViewModel(
            [
                'member' => $member,
                'isActive' => $this->memberService->isActiveMember(),
                'upcoming' => $this->decisionService->getUpcomingMeeting(),
                'meetingsCollection' => $meetingsCollection,
            ]
        );
    }

    /**
     * Shown own information.
     */
    public function selfAction(): ViewModel
    {
        return new ViewModel($this->memberInfoService->getMembershipInfo());
    }

    /**
     * View information about a member.
     */
    public function viewAction(): ViewModel
    {
        $info = $this->memberInfoService->getMembershipInfo($this->params()->fromRoute('lidnr'));

        if (null === $info) {
            return $this->notFoundAction();
        }

        return new ViewModel($info);
    }

    /**
     * Search action, allows searching for members.
     */
    public function searchAction(): JsonModel|ViewModel
    {
        if (!$this->aclService->isAllowed('search', 'member')) {
            throw new NotAllowedException($this->translator->translate('Not allowed to search for members.'));
        }

        $name = $this->params()->fromQuery('q');

        if (!empty($name)) {
            return new JsonModel(
                [
                    'members' => $this->memberService->searchMembersByName($name),
                ]
            );
        }

        return new ViewModel([]);
    }

    /**
     * Determinues whether a member can be authorized without additional confirmation.
     */
    public function canAuthorizeAction(): JsonModel|ViewModel
    {
        $lidnr = $this->params()->fromQuery('q');
        $meeting = $this->decisionService->getLatestAV();

        if (!empty($lidnr) && !empty($meeting)) {
            $member = $this->memberService->findMemberByLidNr($lidnr);
            $canAuthorize = $this->memberService->canAuthorize($member, $meeting);

            if ($canAuthorize) {
                return new JsonModel(
                    [
                        'value' => true,
                    ]
                );
            }

            return new JsonModel(
                [
                    'value' => false,
                ]
            );
        }

        return new ViewModel([]);
    }

    /**
     * Show birthdays of members.
     */
    public function birthdaysAction(): ViewModel
    {
        return new ViewModel(
            [
                'members' => $this->memberService->getBirthdayMembers(7),
            ]
        );
    }

    /**
     * Action to download regulations.
     */
    public function downloadRegulationAction(): Response
    {
        $regulation = $this->params('regulation');
        if (isset($this->regulationsConfig['regulation'])) {
            $this->getResponse()->setStatusCode(404);
        }
        $path = $this->regulationsConfig[$regulation];

        return $this->redirect()->toUrl($this->url()->fromRoute('decision/files', ['path' => '']) . $path);
    }
}

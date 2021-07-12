<?php

namespace Activity\Controller;

use Activity\Form\ModifyRequest as RequestForm;
use Activity\Model\Activity;
use Activity\Service\ActivityQuery;
use Activity\Service\Signup;
use Activity\Service\SignupListQuery;
use DateTime;
use Laminas\Mvc\I18n\Translator;
use User\Permissions\NotAllowedException;
use Laminas\Form\FormInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Paginator\Paginator;
use Laminas\Session\Container as SessionContainer;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;

/**
 * Controller that gives some additional details for activities, such as a list of email adresses
 * or an export function specially tailored for the organizer.
 */
class AdminController extends AbstractActionController
{
    /**
     * @var \Activity\Service\Activity
     */
    private $activityService;

    /**
     * @var ActivityQuery
     */
    private $activityQueryService;

    /**
     * @var Signup
     */
    private $signupService;

    /**
     * @var SignupListQuery
     */
    private $signupListQueryService;
    private Translator $translator;

    public function __construct(Translator $translator, \Activity\Service\Activity $activityService, ActivityQuery $activityQueryService, Signup $signupService, SignupListQuery $signupListQueryService)
    {
        $this->activityService = $activityService;
        $this->activityQueryService = $activityQueryService;
        $this->signupService = $signupService;
        $this->signupListQueryService = $signupListQueryService;
        $this->translator = $translator;
    }

    public function updateAction()
    {
        $activityId = (int) $this->params('id');

        $activity = $this->activityQueryService->getActivityWithDetails($activityId);
        $translator = $this->translator;

        if (is_null($activity)) {
            return $this->notFoundAction();
        }

        $acl = $this->getAcl();
        $identity = $this->getIdentity();

        if (!$acl->isAllowed($identity, $activity, 'update')) {
            throw new NotAllowedException(
                $translator->translate('You are not allowed to update this activity')
            );
        }

        if ($activity->getSignupLists()->count() !== 0) {
            $openingDates = [];
            $participants = 0;

            foreach ($activity->getSignupLists() as $signupList) {
                $openingDates[] = $signupList->getOpenDate();
                $participants += $signupList->getSignups()->count();
            }

            if (min($openingDates) < new DateTime() || $activity->getStatus() === Activity::STATUS_APPROVED) {
                $message = $translator->translate('Activities that have sign-up lists which are open or approved cannot be updated.');

                $this->redirectActivityAdmin(false, $message);
            }
        }

        // Can also be `elseif` as SignupLists are guaranteed to be before the
        // Activity begin date and time.
        if ($activity->getBeginTime() < new DateTime()) {
            $message = $translator->translate('This activity has already started/ended and can no longer be updated.');

            $this->redirectActivityAdmin(false, $message);
        }

        $form = $this->activityService->getActivityForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->activityService->createUpdateProposal($activity, $request->getPost())) {
                $message = $translator->translate('You have successfully created an update proposal for the activity! If the activity was already approved, the proposal will be applied after it has been approved by the board. Otherwise, the update has already been applied to the activity.');

                $this->redirectActivityAdmin(true, $message);
            }
        }

        $updateProposal = $activity->getUpdateProposal();

        if ($updateProposal->count() !== 0) {
            // If there already is an update proposal for this activity, show that instead of the original activity.
            $activity = $updateProposal->first()->getNew();
        }

        $activityData = $activity->toArray();
        unset($activityData['id']);

        $languages = $this->activityQueryService->getAvailableLanguages($activity);
        $activityData['language_dutch'] = $languages['nl'];
        $activityData['language_english'] = $languages['en'];

        $allowSignupList = true;
        if ($activity->getStatus() === Activity::STATUS_APPROVED || (isset($participants) && $participants !== 0)) {
            $allowSignupList = false;
            unset($activityData['signupLists']);
        }

        $form->setData($activityData);

        $viewModel = new ViewModel(
            [
            'form' => $form,
            'action' => $translator->translate('Update Activity'),
            'allowSignupList' => $allowSignupList,
            ]
        );
        $viewModel->setTemplate('activity/activity/create.phtml');

        return $viewModel;
    }

    protected function getAcl()
    {
        return $this->getServiceLocator()->get('activity_acl');
    }

    protected function getIdentity()
    {
        return $this->getServiceLocator()->get('user_service_user')->getIdentity();
    }

    protected function redirectActivityAdmin($success, $message)
    {
        $activityAdminSession = new SessionContainer('activityAdmin');
        $activityAdminSession->success = $success;
        $activityAdminSession->message = $message;
        $this->redirect()->toRoute('activity_admin');
    }

    /**
     * Return the data of the activity participants
     *
     * @return array
     */
    public function participantsAction()
    {
        $activityId = (int) $this->params('id');
        $signupListId = (int) $this->params('signupList');

        $acl = $this->getAcl();
        $identity = $this->getIdentity();

        if ($signupListId === 0) {
            $activity = $this->activityQueryService->getActivity($activityId);

            if (is_null($activity)) {
                return $this->notFoundAction();
            }

            // If the activity does not have any sign-up lists there is no need
            // to check the participants or any sign-up lists.
            if ($activity->getSignupLists()->count() === 0) {
                return $this->notFoundAction();
            }

            if (!$acl->isAllowed($identity, $activity, 'viewParticipants')) {
                $translator = $this->translator;
                throw new NotAllowedException(
                    $translator->translate('You are not allowed to view the participants of this activity')
                );
            }
        } else {
            $signupList = $this->signupListQueryService->getSignupListByActivity($signupListId, $activityId);

            if (is_null($signupList)) {
                return $this->notFoundAction();
            }

            if (!$acl->isAllowed($identity, $signupList, 'viewParticipants')) {
                $translator = $this->translator;
                throw new NotAllowedException(
                    $translator->translate('You are not allowed to view the participants of this activity')
                );
            }

            $activity = $this->activityQueryService->getActivity($activityId);
        }

        $result = [
            'activity' => $activity,
        ];

        if (isset($signupList)) {
            $result['signupList'] = $signupList;
            $activityAdminSession = new SessionContainer('activityAdminRequest');
            $externalSignupForm = $this->signupService->getExternalAdminForm($signupList);

            if (isset($activityAdminSession->signupData)) {
                $externalSignupForm->setData(new Parameters($activityAdminSession->signupData));
                $externalSignupForm->isValid();
                unset($activityAdminSession->signupData);
            }

            $result['externalSignupForm'] = $externalSignupForm;
            $result['externalSignoffForm'] = new RequestForm(
                'activityExternalSignoff',
                $this->translator->translate('Remove')
            );
        }

        $signupLists = [];

        foreach ($activity->getSignupLists()->getValues() as $signupList) {
            $signupLists[] = [
                'id' => $signupList->getId(),
                'name' => $signupList->getName(),
            ];
        }

        $result['signupLists'] = $signupLists;

        // Retrieve and clear the request status from the session, if it exists.
        if (isset($activityAdminSession->success)) {
            $result['success'] = $activityAdminSession->success;
            unset($activityAdminSession->success);
            $result['message'] = $activityAdminSession->message;
            unset($activityAdminSession->message);
        }

        return $result;
    }

    public function externalSignupAction()
    {
        $activityId = (int) $this->params('id');
        $signupListId = (int) $this->params('signupList');
        $signupList = $this->signupListQueryService->getSignupListByActivity($signupListId, $activityId);

        if (is_null($signupList)) {
            return $this->notFoundAction();
        }

        $translator = $this->translator;
        $acl = $this->getAcl();
        $identity = $this->getIdentity();

        if (!$acl->isAllowed($identity, $signupList, 'adminSignup')) {
            throw new NotAllowedException(
                $translator->translate('You are not allowed to use this form')
            );
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form = $this->signupService->getExternalAdminForm($signupList);
            $postData = $request->getPost();
            $form->setData($postData);

            // Check if the form is valid
            if (!$form->isValid()) {
                $error = $translator->translate('Invalid form');
                $activityAdminSession = new SessionContainer('activityAdminRequest');
                $activityAdminSession->signupData = $postData->toArray();
                $this->redirectActivityAdminRequest($activityId, $signupListId, false, $error, $activityAdminSession);
                return $this->getResponse();
            }

            $formData = $form->getData(FormInterface::VALUES_AS_ARRAY);
            $fullName = $formData['fullName'];
            unset($formData['fullName']);
            $email = $formData['email'];
            unset($formData['email']);
            $this->signupService->adminSignUp($signupList, $fullName, $email, $formData);
            $message = $translator->translate('Successfully subscribed external participant');
            $this->redirectActivityAdminRequest($activityId, $signupListId, true, $message);
            return $this->getResponse();
        }

        $error = $translator->translate('Use the form to subscribe');
        $this->redirectActivityAdminRequest($activityId, $signupListId, false, $error);
        return $this->getResponse();
    }

    /**
     * Redirects to the view of the activity with the given $id, where the
     * $error message can be displayed if the request was unsuccesful (i.e.
     * $success was false)
     *
     * @param int $id
     * @param boolean $success Whether the request was successful
     * @param string $message
     */
    protected function redirectActivityAdminRequest($activityId, $signupListId, $success, $message, $session = null)
    {
        if (is_null($session)) {
            $session = new SessionContainer('activityAdminRequest');
        }
        $session->success = $success;
        $session->message = $message;
        $this->redirect()->toRoute(
            'activity_admin/participants',
            [
            'id' => $activityId,
            'signupList' => $signupListId,
            ]
        );
    }

    public function externalSignoffAction()
    {
        $signupId = (int) $this->params('id');
        $signupMapper = $this->getServiceLocator()->get('activity_mapper_signup');

        $signup = $signupMapper->getSignupById($signupId);

        if (is_null($signup)) {
            return $this->notFoundAction();
        }

        $signupList = $signup->getSignupList();
        $translator = $this->translator;
        $acl = $this->getAcl();
        $identity = $this->getIdentity();

        if (!$acl->isAllowed($identity, $signupList, 'adminSignup')) {
            throw new NotAllowedException(
                $translator->translate('You are not allowed to use this form')
            );
        }

        $request = $this->getRequest();

        //Assure a form is used
        if ($request->isPost()) {
            $form = new RequestForm('activityExternalSignoff', $translator->translate('Remove'));
            $form->setData($request->getPost());

            //Assure the form is valid
            if (!$form->isValid()) {
                $message = $translator->translate('Invalid form');
                $this->redirectActivityAdminRequest(
                    $signupList->getActivity()->getId(),
                    $signupList->getId(),
                    false,
                    $message
                );
                return $this->getResponse();
            }

            $this->signupService->externalSignOff($signup);
            $message = $translator->translate('Successfully removed external participant');
            $this->redirectActivityAdminRequest(
                $signupList->getActivity()->getId(),
                $signupList->getId(),
                true,
                $message
            );
            return $this->getResponse();
        }

        $message = $translator->translate('Use the form to unsubscribe an external participant');
        $this->redirectActivityAdminRequest(
            $signupList->getActivity()->getId(),
            $signupList->getId(),
            false,
            $message
        );
        return $this->getResponse();
    }

    /**
     * Show a list of all activities this user can manage.
     */
    public function viewAction()
    {
        $admin = false;
        $acl = $this->getAcl();
        $identity = $this->getIdentity();

        if (!$acl->isAllowed($identity, 'activity', 'viewAdmin')) {
            $translator = $this->translator;
            throw new NotAllowedException(
                $translator->translate('You are not allowed to administer activities')
            );
        }

        $disapprovedActivities = null;
        $unapprovedActivities = null;
        $approvedActivities = null;

        if ($acl->isAllowed($identity, 'activity', 'approval')) {
            $admin = true;
            $disapprovedActivities = $this->activityQueryService->getDisapprovedActivities();
            $unapprovedActivities = $this->activityQueryService->getUnapprovedActivities();
            $approvedActivities = $this->activityQueryService->getApprovedActivities();
        }

        $paginator = new Paginator($this->activityQueryService->getOldCreatedActivitiesPaginator($identity));
        $paginator->setDefaultItemCountPerPage(15);
        $page = $this->params()->fromRoute('page');
        if ($page && $paginator->count() !== 0) {
            $paginator->setCurrentPageNumber($paginator->normalizePageNumber($page));
        }

        $result = [
            'upcomingActivities' => $this->activityQueryService->getUpcomingCreatedActivities($identity),
            'disapprovedActivities' => $disapprovedActivities,
            'unapprovedActivities' => $unapprovedActivities,
            'approvedActivities' => $approvedActivities,
            'oldActivityPaginator' => $paginator,
            'admin' => $admin,
        ];

        $activityAdminSession = new SessionContainer('activityAdmin');
        if (isset($activityAdminSession->success)) {
            $result['success'] = $activityAdminSession->success;
            unset($activityAdminSession->success);
            $result['message'] = $activityAdminSession->message;
            unset($activityAdminSession->message);
        }

        return $result;
    }
}

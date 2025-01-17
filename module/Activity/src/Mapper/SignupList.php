<?php

namespace Activity\Mapper;

use Activity\Model\SignupList as SignupListModel;
use Application\Mapper\BaseMapper;

class SignupList extends BaseMapper
{
    /**
     * @param int $signupListId
     * @param int $activityId
     *
     * @return SignupListModel|null
     */
    public function getSignupListByIdAndActivity(
        int $signupListId,
        int $activityId,
    ): ?SignupListModel {
        return $this->findOneBy([
            'id' => $signupListId,
            'activity' => $activityId,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getRepositoryName(): string
    {
        return SignupListModel::class;
    }
}

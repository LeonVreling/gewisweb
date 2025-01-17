<?php

namespace Frontpage\Service;

class AclService extends \User\Service\AclService
{
    /**
     * @param array $pages
     */
    public function setPages(array $pages): void
    {
        foreach ($pages as $page) {
            $requiredRole = $page->getRequiredRole();
            $this->acl->addResource($page);
            $this->acl->allow($requiredRole, $page, 'view');
        }
    }

    protected function createAcl(): void
    {
        parent::createAcl();

        $this->acl->addResource('page');
        $this->acl->addResource('poll');
        $this->acl->addResource('poll_comment');
        $this->acl->addResource('news_item');
        $this->acl->addResource('infimum');

        $this->acl->allow('user', 'infimum', 'view');
        $this->acl->allow('user', 'poll', ['vote', 'request']);
        $this->acl->allow('user', 'poll_comment', ['view', 'create', 'list']);
    }
}

<?php

namespace Frontpage\Service;

use DateTime;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Frontpage\Form\NewsItem as NewsItemForm;
use Frontpage\Mapper\NewsItem as NewsItemMapper;
use Frontpage\Model\NewsItem as NewsItemModel;
use Laminas\Mvc\I18n\Translator;
use User\Permissions\NotAllowedException;
use User\Service\AclService;

/**
 * News service.
 */
class News
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
     * @var NewsItemMapper
     */
    private NewsItemMapper $newsItemMapper;

    /**
     * @var NewsItemForm
     */
    private NewsItemForm $newsItemForm;

    /**
     * @param AclService $aclService
     * @param Translator $translator
     * @param NewsItemMapper $newsItemMapper
     * @param NewsItemForm $newsItemForm
     */
    public function __construct(
        AclService $aclService,
        Translator $translator,
        NewsItemMapper $newsItemMapper,
        NewsItemForm $newsItemForm,
    ) {
        $this->aclService = $aclService;
        $this->translator = $translator;
        $this->newsItemMapper = $newsItemMapper;
        $this->newsItemForm = $newsItemForm;
    }

    /**
     * Returns a single NewsItem by its id.
     *
     * @param int $newsItem
     *
     * @return NewsItemModel|null
     */
    public function getNewsItemById(int $newsItem): ?NewsItemModel
    {
        return $this->newsItemMapper->find($newsItem);
    }

    /**
     * Returns a paginator adapter for paging through news items.
     *
     * @return DoctrinePaginator
     */
    public function getPaginatorAdapter(): DoctrinePaginator
    {
        if (!$this->aclService->isAllowed('list', 'news_item')) {
            throw new NotAllowedException($this->translator->translate('You are not allowed to list all news items.'));
        }

        return $this->newsItemMapper->getPaginatorAdapter();
    }

    /**
     * Retrieves a certain number of news items sorted descending by their date.
     *
     * @param int $count
     *
     * @return array
     */
    public function getLatestNewsItems(int $count): array
    {
        return $this->newsItemMapper->getLatestNewsItems($count);
    }

    /**
     * Creates a news item.
     *
     * @param array $data form post data
     *
     * @return bool
     */
    public function createNewsItem(array $data): bool
    {
        $newsItem = new NewsItemModel();
        $newsItem->setDate(new DateTime());

        $this->updateNewsItem($newsItem, $data);

        return true;
    }

    /**
     * @param NewsItemModel $newsItem
     * @param array $data form post data
     *
     * @return bool
     */
    public function updateNewsItem(
        NewsItemModel $newsItem,
        array $data,
    ): bool {
        $newsItem->setEnglishContent($data['englishContent']);
        $newsItem->setEnglishTitle($data['englishTitle']);
        $newsItem->setDutchContent($data['dutchContent']);
        $newsItem->setDutchTitle($data['dutchTitle']);
        $newsItem->setPinned($data['pinned']);

        $this->newsItemMapper->persist($newsItem);

        return true;
    }

    /**
     * Removes a news item.
     *
     * @param NewsItemModel $newsItem the id of the news item to remove
     */
    public function deleteNewsItem(NewsItemModel $newsItem): void
    {
        $this->newsItemMapper->remove($newsItem);
    }

    /**
     * Get the NewsItem form.
     **
     * @return NewsItemForm
     */
    public function getNewsItemForm(): NewsItemForm
    {
        return $this->newsItemForm;
    }
}

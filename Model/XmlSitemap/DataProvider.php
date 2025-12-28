<?php

declare(strict_types=1);

namespace AumTechnolabs\AmastyURLAliasToSiteMap\Model\XmlSitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Amasty_XmlSitemap entity provider
 */
class DataProvider
{
    public const ENTITY_CODE = 'filter_item_urls';

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    public $generateUrlsFactory;
    public function __construct(
        StoreManagerInterface $storeManager,
        \AumTechnolabs\AmastyURLAliasToSiteMap\Model\GenerateUrlsFactory $generateUrlsFactory
    ) {
        $this->storeManager = $storeManager;
        $this->generateUrlsFactory = $generateUrlsFactory;
    }

    public function getData(SitemapInterface $sitemap): \Generator
    {
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());

        foreach ($this->getCollection($sitemap->getStoreId()) as $url) {

            yield [
                [
                    'loc'       => $url,
                    'frequency' => $sitemapEntityData->getFrequency(),
                    'priority'  => $sitemapEntityData->getPriority()
                ]
            ];
        }
    }

    private function getCollection(int $storeId): array
    {
        return $this->generateUrlsFactory->create()->getFilterItemUrls($storeId);
    }

    public function getEntityCode(): string
    {
        return self::ENTITY_CODE;
    }

    public function getEntityLabel(): Phrase
    {
        return __('Filter Item Urls');
    }
}

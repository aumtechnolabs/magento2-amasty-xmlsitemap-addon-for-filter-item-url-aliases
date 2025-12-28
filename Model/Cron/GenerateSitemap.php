<?php

declare(strict_types=1);

namespace AumTechnolabs\AmastyURLAliasToSiteMap\Model\Cron;

use Amasty\XmlSitemap\Model\GenerateAndSaveFactory;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory as SitemapCollectionFactory;

class GenerateSitemap extends \Amasty\XmlSitemap\Model\Cron\GenerateSitemap
{
    /**
     * @var SitemapCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GenerateAndSaveFactory
     */
    private $generateAndSave;

    public function __construct(
        SitemapCollectionFactory $collectionFactory,
        GenerateAndSaveFactory $generateAndSave
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->generateAndSave = $generateAndSave;
    }

    public function execute(): void
    {
        /** Return statement is added because we're generating the sitemaps by generateSitemapIdWise as our customization for custom url is not working with cron. I'd tried debugging the core issue but it was not working so set the root file path with the url hitting by cron.
         *  Also in this module the amasty's crontab.xml is disabled.. look at etc/crontab.xml file..
         * Look at this cron command.
         * 50 0 * * * /usr/bin/curl https://www.some-site.com/AMASTY_XML_SITEMAP_GENERATOR_CRON.php
         * 
         */
        return;
        $collection = $this->collectionFactory->create();
        $sitemapIds = $collection->getColumnValues('sitemap_id');
        foreach ($sitemapIds as $sitemapId) {
            $sitemap = $this->collectionFactory->create()->addFieldToFilter('sitemap_id', $sitemapId)->getFirstItem();
            $this->generateAndSave->create()->execute($sitemap);
        }
    }
    public function generateSitemapIdWise($sitemapId): void
    {
        $sitemap = $this->collectionFactory->create()->addFieldToFilter('sitemap_id', $sitemapId)->getFirstItem();
        $this->generateAndSave->create()->execute($sitemap);
    }
}

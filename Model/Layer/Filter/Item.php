<?php

namespace AumTechnolabs\AmastyURLAliasToSiteMap\Model\Layer\Filter;

use AumTechnolabs\AmastyURLAliasToSiteMap\Helper\UrlBuilder;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var  UrlBuilder
     */
    private $urlBuilderHelper;

    /**
     * @var FilterSettingResolver
     */
    private $filterSettingResolver;

    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock,
        UrlBuilder $urlBuilderHelper,
        FilterSettingResolver $filterSettingResolver,
        array $data = []
    ) {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->urlBuilderHelper = $urlBuilderHelper;
        $this->filterSettingResolver = $filterSettingResolver;
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        try {
            return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue(), $this->getFilter()->getLayer()->getCurrentCategory()->getId());
        } catch (\Exception $e) {
            return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
        }
    }

    /**
     * Get url for remove item from filter
     * @param mixed $value
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemoveUrl($value = null)
    {
        $value = $value ?? $this->getValue();

        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $value);
    }

    /**
     * @return bool
     */
    public function isAddNofollow()
    {
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this->getFilter());

        if ($this->getFilter() instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            return $filterSetting->isMultiselect() && $filterSetting->isAddNofollow();
        }

        return $filterSetting->isAddNofollow();
    }

    /**
     * @return string
     */
    public function getOptionLabel()
    {
        return $this->getData('label');
    }
}

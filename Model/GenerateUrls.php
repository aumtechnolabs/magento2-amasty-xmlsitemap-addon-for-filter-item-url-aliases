<?php

namespace AumTechnolabs\AmastyURLAliasToSiteMap\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Layer\Resolver;

class GenerateUrls
{
    public $storeManager;
    public $catalogLayerFactory;
    public $filterListFactory;
    public $storeRepository;
    public $categoryCollectionFactory;
    public $logger;
    public $categoryRepository;
    protected $_catalogSession;
    protected $_coreRegistry;
    protected $layerResolver;
    protected $catalogLayer;
    protected $isCatalogLayerCreated;
    protected $optionSettingRepository;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\CategoryFactory $catalogLayerFactory,
        \AumTechnolabs\AmastyURLAliasToSiteMap\Model\Layer\FilterListFactory $filterListFactory,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        CategoryRepositoryInterface $categoryRepository,
        Session $catalogSession,
        Registry $coreRegistry,
        Resolver $layerResolver,
        \Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface $optionSettingRepository
    ) {
        $this->storeManager = $storeManager;
        $this->catalogLayerFactory = $catalogLayerFactory;
        $this->filterListFactory = $filterListFactory;
        $this->storeRepository = $storeRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->layerResolver = $layerResolver;
        $this->catalogLayer = null;
        $this->isCatalogLayerCreated = false;
        $this->optionSettingRepository = $optionSettingRepository;
    }
    public function getFilterItemUrls($storeId)
    {
        $urls = [];
        try {
            $store = $this->storeRepository->getById($storeId);
            $rootCategoryId = $store->getRootCategoryId();
            $this->storeManager->setCurrentStore($store);
            $categories = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToSelect('*')
                ->addFieldToFilter('path', ['like' => "1/$rootCategoryId/%"])
                ->setStoreId($storeId)
                ->setStore($store);
            $childrenCategories = [];
            foreach ($categories as $category) {
                if (!$category->getChildrenCount()) {
                    $childrenCategories[] = $category;
                }
            }
            foreach ($childrenCategories as $category) {
                $category = $this->categoryRepository->get($category->getId(), $storeId);
                $this->_catalogSession->setLastVisitedCategoryId($category->getId());
                $this->_coreRegistry->unregister('current_category');
                $this->_coreRegistry->register('current_category', $category);
                $this->catalogLayer = $this->catalogLayerFactory->create()->setCurrentCategory($category)->setCurrentStore($store);
                if (!$this->isCatalogLayerCreated) {
                    $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
                    $this->isCatalogLayerCreated = true;
                }
                $filters = $this->filterListFactory->create()->getFilters($this->catalogLayer);
                $filtersWithResultCount = [];
                foreach ($filters as $key => $filter) {
                    if ($filter->getItemsCount()) {
                        if (empty($filtersWithResultCount[$filter->getName()])) {
                            $filtersWithResultCount[$filter->getName()] = [];
                        }
                        $filtersWithResultCount[$filter->getName()] += [$key => $filter->getItemsCount()];
                    }
                }

                foreach ($filters as $filter) {
                    $shouldRender = (bool)$filter->getItemsCount();
                    if ($shouldRender) {
                        $category = $this->catalogLayer->getCurrentCategory();
                        $filterItems = $filter->getItems();
                        if ($filterItems) {
                            foreach ($filter->getItems() as $filterItem) {
                                $optionSetting = $this->optionSettingRepository->getByParams(
                                    $filterItem->getFilter()->getRequestVar(),
                                    $filterItem->getValue(),
                                    $storeId
                                );
                                if (!empty($optionSetting->getUrlAlias())) {
                                    $parsedUrl = parse_url($filterItem->getUrl(), PHP_URL_PATH);
                                    $parsedUrl = explode('/', $parsedUrl);
                                    $parsedUrl = "/" .  end($parsedUrl);
                                    if (!empty($parsedUrl) && $parsedUrl != "/" && $parsedUrl != "//") {
                                        $filterItemShortUrl = $parsedUrl . ".html";
                                        $filterItemFullUrl = $store->getBaseUrl() . $category->getUrlPath() . $filterItemShortUrl;
                                        $urls[] = $filterItemFullUrl;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical("Error Occured While Generating Filter Item Urls : " . $e->getMessage());
        }
        return $urls;
    }
}

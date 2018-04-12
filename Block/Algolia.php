<?php

namespace Algolia\AlgoliaSearch\Block;

use Algolia\AlgoliaSearch\Helper\AlgoliaHelper;
use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\Data as CoreHelper;
use Algolia\AlgoliaSearch\Helper\Entity\CategoryHelper;
use Algolia\AlgoliaSearch\Helper\Entity\ProductHelper;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\Currency;
use Magento\Framework\Registry;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Search\Helper\Data as CatalogSearchHelper;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

class Algolia extends Template implements CollectionDataSourceInterface
{
    private $config;
    private $catalogSearchHelper;
    private $registry;
    private $productHelper;
    private $currency;
    private $algoliaHelper;
    private $urlHelper;
    private $formKey;
    private $httpContext;
    private $coreHelper;
    private $categoryHelper;
    private $cart;

    private $priceKey;

    public function __construct(
        Template\Context $context,
        ConfigHelper $config,
        CatalogSearchHelper $catalogSearchHelper,
        ProductHelper $productHelper,
        Currency $currency,
        Registry $registry,
        AlgoliaHelper $algoliaHelper,
        Data $urlHelper,
        FormKey $formKey,
        HttpContext $httpContext,
        CoreHelper $coreHelper,
        CategoryHelper $categoryHelper,
        Cart $cart,
        array $data = []
    ) {
        $this->config = $config;
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->productHelper = $productHelper;
        $this->currency = $currency;
        $this->registry = $registry;
        $this->algoliaHelper = $algoliaHelper;
        $this->urlHelper = $urlHelper;
        $this->formKey = $formKey;
        $this->httpContext = $httpContext;
        $this->coreHelper = $coreHelper;
        $this->categoryHelper = $categoryHelper;
        $this->cart = $cart;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore();

        return $store;
    }

    public function getConfigHelper()
    {
        return $this->config;
    }

    public function getCoreHelper()
    {
        return $this->coreHelper;
    }

    public function getProductHelper()
    {
        return $this->productHelper;
    }

    public function getCategoryHelper()
    {
        return $this->categoryHelper;
    }

    public function getCatalogSearchHelper()
    {
        return $this->catalogSearchHelper;
    }

    public function getAlgoliaHelper()
    {
        return $this->algoliaHelper;
    }

    public function getCart()
    {
        return $this->cart;
    }

    public function getCurrencySymbol()
    {
        return $this->currency->getCurrency($this->getCurrencyCode())->getSymbol();
    }
    public function getCurrencyCode()
    {
        return $this->getStore()->getCurrentCurrencyCode();
    }

    public function getGroupId()
    {
        return $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
    }

    public function getPriceKey()
    {
        if ($this->priceKey === null) {
            $currencyCode = $this->getCurrencyCode();

            $this->priceKey = '.' . $currencyCode . '.default';

            if ($this->config->isCustomerGroupsEnabled($this->getStore()->getStoreId())) {
                $groupId = $this->getGroupId();
                $this->priceKey = '.' . $currencyCode . '.group_' . $groupId;
            }
        }

        return $this->priceKey;
    }

    public function getStoreId()
    {
        return $this->getStore()->getStoreId();
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /** @return \Magento\Catalog\Model\Product */
    public function getCurrentProduct()
    {
        return $this->registry->registry('product');
    }

    public function getAddToCartParams()
    {
        $url = $this->getAddToCartUrl();

        return [
            'action' => $url,
            'formKey' => $this->formKey->getFormKey(),
        ];
    }

    private function getAddToCartUrl($additional = [])
    {
        $continueUrl = $this->urlHelper->getEncodedUrl($this->_urlBuilder->getCurrentUrl());
        $urlParamName = ActionInterface::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $continueUrl,
            '_secure' => $this->algoliaHelper->getRequest()->isSecure()
        ];

        if ($additional !== []) {
            $routeParams = array_merge($routeParams, $additional);
        }

        return $this->_urlBuilder->getUrl('checkout/cart/add', $routeParams);
    }
}

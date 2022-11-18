<?php
    /**
     * @Thomas-Athanasiou
     *
     * @author Thomas Athanasiou {thomas@hippiemonkeys.com}
     * @link https://hippiemonkeys.com
     * @link https://github.com/Thomas-Athanasiou
     * @copyright Copyright (c) 2022 Hippiemonkeys Web Inteligence EE All Rights Reserved.
     * @license http://www.gnu.org/licenses/ GNU General Public License, version 3
     * @package Hippiemonkeys_ModificationAitocProductDesigner
     */

    namespace Hippiemonkeys\ModificationAitocProductDesigner\Block\Product;

    use Aitoc\CustomProductDesigner\Api\CustomerDataRepositoryInterface as CustomerDataRepository,
        Aitoc\CustomProductDesigner\Api\ProductOptionsRepositoryInterface as ProductOptionsRepository,
        Aitoc\CustomProductDesigner\Helper\Data,
        Aitoc\CustomProductDesigner\Model\ResourceModel\Clipart\CollectionFactory as ClipartsCollection,
        Aitoc\CustomProductDesigner\Model\ResourceModel\ClipartCategory\CollectionFactory as ClipartCategoryCollection,
        Aitoc\CustomProductDesigner\Model\ResourceModel\CustomerDesigns\CollectionFactory as CustomerDesignsCollectionFactory,
        Aitoc\CustomProductDesigner\Model\ResourceModel\Font\CollectionFactory as FontsCollection,
        Aitoc\CustomProductDesigner\Model\ResourceModel\FontFamily\CollectionFactory as FontFamilyCollection,
        Magento\Catalog\Api\Data\ProductInterfaceFactory,
        Magento\Catalog\Helper\Product as ProductHelper,
        Magento\Catalog\Api\Data\ProductInterface,
        Magento\Catalog\Model\ProductFactory,
        Magento\Catalog\Model\ProductRepository,
        Magento\ConfigurableProduct\Helper\Data as ConfigurableProductHelper,
        Magento\Customer\Model\Session as CustomerSession,
        Magento\Framework\Api\DataObjectHelper,
        Magento\Framework\View\Element\Template\Context,
        Magento\Store\Model\StoreManagerInterface,
        Aitoc\CustomProductDesigner\Block\Product\RenderProduct as ParentRenderProduct,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface as ConfigInterface,
        Magento\Framework\Api\SimpleDataObjectConverter;

    class RenderProduct
    extends ParentRenderProduct
    {
        protected const
            PATH_PRODUCT_ATTRIBUTE_CODE = 'renderproduct_attribute_code';

        /**
         * Constructor
         *
         * @access public
         *
         * @param \Magento\Framework\View\Element\Template\Context $context
         * @param \Aitoc\CustomProductDesigner\Helper\Data $helper
         * @param \Aitoc\CustomProductDesigner\Api\ProductOptionsRepositoryInterface $productOptionsRepository
         * @param \Aitoc\CustomProductDesigner\Api\CustomerDataRepositoryInterface $customerDataRepository
         * @param \Magento\Catalog\Model\ProductRepository $productRepository
         * @param \Aitoc\CustomProductDesigner\Model\ResourceModel\CustomerDesigns\CollectionFactory $customerDesignsCollectionFactory
         * @param \Aitoc\CustomProductDesigner\Model\ResourceModel\Clipart\CollectionFactory $clipartsCollection
         * @param \Aitoc\CustomProductDesigner\Model\ResourceModel\Font\CollectionFactory $fontsCollection
         * @param \Aitoc\CustomProductDesigner\Model\ResourceModel\ClipartCategory\CollectionFactory $clipartCategoryCollection
         * @param \Aitoc\CustomProductDesigner\Model\ResourceModel\FontFamily\CollectionFactory $fontFamilyCollection
         * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
         * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
         * @param \Magento\Customer\Model\Session $cuctomerSession
         * @param \Magento\Catalog\Helper\Product $catalogProduct
         * @param \Magento\ConfigurableProduct\Helper\Data $configurableProductHelper
         * @param \Magento\Store\Model\StoreManagerInterface $storeManager
         * @param \Magento\Catalog\Model\ProductFactory $prodFactory
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         * @param array $data
         */
        public function __construct(
            Context $context,
            Data $helper,
            ProductOptionsRepository $productOptionsRepository,
            CustomerDataRepository $customerDataRepository,
            ProductRepository $productRepository,
            CustomerDesignsCollectionFactory $customerDesignsCollectionFactory,
            ClipartsCollection $clipartsCollection,
            FontsCollection $fontsCollection,
            ClipartCategoryCollection $clipartCategoryCollection,
            FontFamilyCollection $fontFamilyCollection,
            ProductInterfaceFactory $productFactory,
            DataObjectHelper $dataObjectHelper,
            CustomerSession $cuctomerSession,
            ProductHelper $catalogProduct,
            ConfigurableProductHelper $configurableProductHelper,
            StoreManagerInterface $storeManager,
            ProductFactory $prodFactory,
            ConfigInterface $config,
            array $data = []
        )
        {
            parent::__construct(
                $context,
                $helper,
                $productOptionsRepository,
                $customerDataRepository,
                $productRepository,
                $customerDesignsCollectionFactory,
                $clipartsCollection,
                $fontsCollection,
                $clipartCategoryCollection,
                $fontFamilyCollection,
                $productFactory,
                $dataObjectHelper,
                $cuctomerSession,
                $catalogProduct,
                $configurableProductHelper,
                $storeManager,
                $prodFactory,
                $data
            );
            $this->_config = $config;
        }

        /**
         * @inheritdoc
         */
        public function renderProductJSON()
        {
            $json = \json_encode(null);

            if($this->getIsModificationActive())
            {
                try
                {
                    $product = $this->getProductRepository()->getById($this->getProductId());
                    if(!$this->getVerifyActiveProduct($product))
                    {
                        $json = parent::renderProductJSON();
                    }
                }
                catch (\Exception $e)
                {
                    $json = parent::renderProductJSON();
                }
            }
            else
            {
                $json = parent::renderProductJSON();
            }

            return $json;
        }

        /**
         * Gets Product Repository
         *
         * @return \Magento\Catalog\Model\ProductRepository
         */
        protected function getProductRepository(): ProductRepository
        {
            return $this->productRepository;
        }

        /**
         * Gets Verify Active Product
         *
         * @return bool
         */
        protected function getVerifyActiveProduct(ProductInterface $product): bool
        {
            return (bool) $product->{
                $this->getGetterMethodName(
                    $this->getVerifyActiveProductAttributeCode()
                )
            }();
        }

        /**
         * Get getter name based on field name
         *
         * @param string $fieldName
         *
         * @return string
         */
        private function getGetterMethodName(string $fieldName): string
        {
            return 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($fieldName);
        }

        /**
         * Gets Active Product Attribute Code
         *
         * @access protected
         *
         * @return string
         */
        protected function getVerifyActiveProductAttributeCode(): string
        {
            return $this->getConfig()->getData(static::PATH_PRODUCT_ATTRIBUTE_CODE);
        }

        /**
         * Gets wether the indexer modification is active or not.
         *
         * @access protected
         *
         * @return bool
         */
        protected function getIsModificationActive(): bool
        {
            return $this->getConfig()->getIsActive();
        }

        /**
         * Config property
         *
         * @access private
         *
         * @var \Hippiemonkeys\Core\Api\Helper\ConfigInterface $_config
         */
        private $_config;

        /**
         * Gets Config
         *
         * @access protected
         *
         * @return \Hippiemonkeys\Core\Api\Helper\ConfigInterface
         */
        protected function getConfig(): ConfigInterface
        {
            return $this->_config;
        }
    }
?>
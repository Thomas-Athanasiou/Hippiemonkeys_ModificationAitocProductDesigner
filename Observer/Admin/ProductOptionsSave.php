<?php
    /**
     * @Thomas-Athanasiou
     *
     * @author Thomas Athanasiou {thomas@hippiemonkeys.com}
     * @link https://hippiemonkeys.com
     * @link https://github.com/Thomas-Athanasiou
     * @copyright Copyright (c) 2023 Hippiemonkeys Web Inteligence EE All Rights Reserved.
     * @license http://www.gnu.org/licenses/ GNU General Public License, version 3
     * @package Hippiemonkeys_ModificationAitocProductDesigner
     */

    declare(strict_types=1);

    namespace Hippiemonkeys\ModificationAitocProductDesigner\Observer\Admin;

    use Aitoc\CustomProductDesigner\Helper\Data,
        Aitoc\CustomProductDesigner\Model\Product\OptionsRepository,
        Magento\Framework\App\Cache\Frontend\Pool,
        Magento\Framework\App\Cache\TypeListInterface,
        Magento\Framework\Event\Observer,
        Aitoc\CustomProductDesigner\Observer\Admin\ProductOptionsSave as ParentProductOptionsSave,
        Hippiemonkeys\Core\Api\Helper\ConfigInterface;

    class ProductOptionsSave
    extends ParentProductOptionsSave
    {
        /**
         * Constructor
         *
         * @access public
         *
         * @param \Aitoc\CustomProductDesigner\Helper\Data $helper
         * @param \Aitoc\CustomProductDesigner\Model\Product\OptionsRepository $productOptionsRepository
         * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
         * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
         * @param \Hippiemonkeys\Core\Api\Helper\ConfigInterface $config
         */
        public function __construct(
            Data $helper,
            OptionsRepository $productOptionsRepository,
            TypeListInterface $cacheTypeList,
            Pool $cacheFrontendPool,
            ConfigInterface $config
        )
        {
            parent::__construct($helper, $productOptionsRepository, $cacheTypeList, $cacheFrontendPool);
            $this->_config = $config;
        }

        /**
         * @inheritdoc
         */
        public function execute(Observer $observer)
        {
            if($this->getIsActive())
            {
                if ($observer->getEvent()->getObject()->getEventPrefix() == 'catalog_product')
                {
                    $data = $observer->getEvent()->getObject()->getData();

                    $error = $this->CPDFiledsValidation($data);
                    if ($error)
                    {
                        throw new \Magento\Framework\Exception\LocalizedException(__($error));
                    }

                    if (isset($data['entity_id']) && !empty($data['images_area']))
                    {
                        $productOptionsRepository = $this->getProductOptionsRepository();

                        $productId   = $data['entity_id'];
                        $currentItem = $productOptionsRepository->getByProductId($productId);
                        $currentItem->setData('product_id', $data['entity_id']);

                        foreach ($this->getHelper()->getFields() as $field)
                        {
                            if (array_key_exists($field, $data))
                            {
                                $currentItem->setData($field, $data[$field]);
                            }
                        }

                        $productOptionsRepository->save($currentItem);
                    }
                    elseif (isset($data['entity_id']) && empty($data['images_area']) && isset($data['enable_cpd']) && $data['enable_cpd'] === "1")
                    {
                        $error = "To enable 'Custom Product Designer' feature, please select an image area.";
                        throw new \Magento\Framework\Exception\LocalizedException(__($error));
                    }
                }
            }
            else
            {
                return parent::execute($observer);
            }
        }

        /**
         * Gets Helper
         *
         * @access protected
         *
         * @return \Aitoc\CustomProductDesigner\Helper\Data
         */
        protected function getHelper(): Data
        {
            return $this->helper;
        }

        /**
         * Gets Product Options Repository
         *
         * @access protected
         *
         * @return \Aitoc\CustomProductDesigner\Model\Product\OptionsRepository
         */
        protected function getProductOptionsRepository(): OptionsRepository
        {
            return $this->productOptionsRepository;
        }

        /**
         * Gets wether the modification is active or not.
         *
         * @access protected
         *
         * @return bool
         */
        protected function getIsActive(): bool
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
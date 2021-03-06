<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysley
 * @copyright   Copyright (c) 2020 Paysley
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Paysley\Paysley\Block\Adminhtml\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var \Paysley\Paysley\Helper\Core
     */
    private $helperCore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Paysley\Paysley\Helper\Core $helperCore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Paysley\Paysley\Helper\Core $helperCore,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->helperCore = $helperCore;
    }

    /**
     * get additional information by name
     * @param string $name
     * @return string
     */
    public function getAdditionalInfo($name)
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation($name);
    }

    /**
     * get all additional informations
     * @return array
     */
    public function getAllAdditionalInfo()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation();
    }

    /**
     * get country name by country iso code
     * @param string $countryIsoCode
     * @return string
     */
    public function getCountryNameByIsoCode($countryIsoCode)
    {
        return $this->helperCore->getCountryName($countryIsoCode);
    }
}

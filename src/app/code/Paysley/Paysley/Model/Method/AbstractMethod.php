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
namespace Paysley\Paysley\Model\Method;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * @var string
     */
    public $_code= 'paysley_abstract';

    /**
     * @var string
     */
    public $brand = '';

    /**
     * @var string
     */
    public $methodTitle = '';

    /**
     * @var string
     */
    public $logo = '';

    /**
     * @var string
     */
    protected $storeManager;

    const PENDING = 'pending';
    const PAYMENT_PA = 'payment_pa';
    const PAYMENT_ACCEPTED = 'payment_accepted';

    const PROCESSED_STATUS = '2';
    const PENDING_STATUS = '0';
    const FAILED_STATUS = '-2';
    const REFUNDED_STATUS = '-4';
    const REFUNDFAILED_STATUS = '-5';
    const REFUNDPENDING_STATUS = '-6';
    const FRAUD_STATUS = '-7';
    const INVALIDCREDENTIAL_STATUS = '-8';
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Paysley\Paysley\Helper\Logger $paysleyLogger
     * @param \Paysley\Paysley\Helper\Core $helperCore
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Magento\Framework\Locale\ResolverInterface $localResolver
     * @param \Magento\Framework\Url $url
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Paysley\Paysley\Helper\Logger $paysleyLogger,
        \Paysley\Paysley\Helper\Core $helperCore,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Locale\ResolverInterface $localResolver,
        \Magento\Framework\Url $url,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->paysleyLogger = $paysleyLogger;
        $this->helperCore = $helperCore;
        $this->storeManager = $storeManager;
        $this->encryptorInterface = $encryptorInterface;
        $this->localResolver = $localResolver;
        $this->url = $url;
        $this->httpHeader = $httpHeader;
    }

    /**
     * get Helper Core
     * @return \Paysley\Paysley\Helper\Core
     */
    public function getHelperCore()
    {
        return $this->helperCore;
    }

    /**
     *
     * @param  string $paymentAction
     * @param  object $stateObject
     * @return object
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case self::ACTION_ORDER:
                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus($this->getConfigData('order_status'));
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * check if method active or not
     *
     * @param string $methodCard
     * @return boolean
     */
    public function isMethodActive($methodCard)
    {
        $active = $this->getSpecificConfig('payment/'.$methodCard.'/active');
        
        if ($active) {
            return true;
        }
        return false;
    }

    /**
     * get a quote
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote(\Magento\Checkout\Model\Session $checkoutSession)
    {
        return $checkoutSession->getQuote();
    }

    /**
     * get an order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        $infoInstance = $this->getInfoInstance();

        return $infoInstance->getOrder();
    }

    /**
     * get a title
     * @return string
     */
    public function getTitle()
    {
        return __($this->getGeneralConfig("payment_method_title"));
    }

    /**
     * get a payment method description
     * @return string
     */
    public function getPaymentMethodDescription()
    {
        return __($this->getGeneralConfig("payment_method_description"));
    }

    /**
     * get a logo
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * get an order place redirect URL
     * @return boolean | string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return true;
    }

    /**
     * get the general configuration
     * @param  string $field
     * @param  string $storeId
     * @return string
     */
    public function getGeneralConfig($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        $path = 'general/paysley_settings/' . $field;
        
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * get the specific configuration
     * @param  string $field
     * @param  string $storeId
     * @return string
     */
    public function getSpecificConfig($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        return $this->_scopeConfig->getValue($field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

     /**
      * Retrieve paysley settings
      *
      * @return array
      */
    public function getPaysleySettings($currency = false)
    {

        if (!$currency) {
            $currency = strtolower($this->storeManager->getStore()->getCurrentCurrencyCode());
        }
        
        $settings = $this->getPaysleyGeneralSettings();
        return $settings;
    }

    /**
     * Retrieve the general paysley settings
     *
     * @return array
     */
    public function getPaysleyGeneralSettings()
    {
        $settings = [
            'payment_method_title'  => $this->getGeneralConfig('payment_method_title'),
            'payment_method_description'  => $this->getGeneralConfig('payment_method_description'),
            'paysley_log'  => $this->getGeneralConfig('paysley_log'),
            'access_key'  => $this->encryptorInterface->decrypt($this->getGeneralConfig('access_key'))
        ];

        return $settings;
    }

    /**
     * get a brand
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     *
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  float $amount
     * @return $this
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * get a language code
     * @return string
     */
    public function getLangCode()
    {
        $locale = explode('_', $this->localResolver->getLocale());
        if (isset($locale[0])) {
            return strtoupper($locale[0]);
        }
        return 'EN';
    }

    /**
     * capture a payment
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  float $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $payment->setStatus('APPROVED')
                ->setTransactionId($payment->getAdditionalInformation('paysley_transaction_id'))
                ->setIsTransactionClosed(0)->save();

        return $this;
    }

    /**
     * get refund status url
     * @param  string $orderId
     * @return string
     */
    protected function getRefundStatusUrl($orderId)
    {

        if (version_compare($this->helperCore->getShopVersion(), '2.3.0', '<')) {
            $refundUrl = "handlerefund";
        } else {
            $refundUrl = "handlerefundcsrf";
        }

        $refundStatusUrl = $this->url->getUrl(
            'paysley/payment/' . $refundUrl,
            [
                'orderId' => $orderId,
                '_secure' => true
            ]
        );

        return $refundStatusUrl;
    }

    /**
     * refund a payment
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  float $amount
     * @return $this
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->paysleyLogger->info('process refund online');
        $orderId = $payment->getOrder()->getIncrementId();
        $paysleySettings = $this->getPaysleySettings();
        $parameters['email'] = $payment->getOrder()->getCustomerEmail();
        $parameters['amount'] = (float)$payment->getAdditionalInformation('paysley_amount');
        $paymentId = $payment->getAdditionalInformation('paysley_payment_id');

        $this->getHelperCore()->accessKey = $paysleySettings['access_key'];
        $refundResponse = $this->getHelperCore()->doRefund($paymentId, $parameters);
        $this->paysleyLogger->info('response from gateway : '.json_encode($refundResponse));

        if (isset($refundResponse["result"]) && $refundResponse["result"] == "failed") {
            $this->paysleyLogger->info('refund error message : '.$refundResponse["message"]);
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed to do refund'));
        } else {
            $this->paysleyLogger->info('process refund online with status processed');
            $payment->setAdditionalInformation('paysley_refund_status', $refundResponse["status"]);
            $payment->setAdditionalInformation('paysley_ref_number', $refundResponse["ref_number"]);

            $payment->setTransactionId($refundResponse["ref_number"])
                    ->setIsTransactionClosed(0)->save();
            $response['status'] = $refundResponse["status"];
            $comment = $this->getHelperCore()->getComment($response);
            $payment->getOrder()->addStatusHistoryComment($comment, false)->save();
        }

        return $this;
    }

    /**
     * get shop name
     *
     * @return string
     */
    public function getShopName()
    {
        $shopName = $this->getSpecificConfig('general/store_information/name');
        if (empty($shopName)) {
            return $this->httpHeader->getHttpHost();
        }
        return $shopName;
    }
}

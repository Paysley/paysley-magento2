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
namespace Paysley\Paysley\Controller\Payment;

class Index extends \Magento\Framework\App\Action\Action
{
    public $modelQuote = false;
    public $salesOrder = false;
    public $checkoutHelper;
    public $localeResolver;
    public $resultPageFactory;
    public $logger;
    public $helperCore;
    public $method;
    public $invoiceService;
    public $dbTransaction;
    public $salesEmailInvoice;
    public $salesEmailOrder;
    public $catalogSession;
    public $paymentType   = 'DB';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Paysley\Paysley\Helper\Logger $logger
     * @param \Paysley\Paysley\Helper\Core $helperCore
     * @param \Magento\Sales\Model\Order $salesOrder
     * @param \Magento\Quote\Model\Quote $modelQuote
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $salesEmailInvoice
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $salesEmailOrder
     * @param \Magento\Catalog\Model\Session $catalogSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Paysley\Paysley\Helper\Logger $logger,
        \Paysley\Paysley\Helper\Core $helperCore,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Quote\Model\Quote $modelQuote,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $salesEmailInvoice,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $salesEmailOrder,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        parent::__construct($context);
        $this->checkoutHelper = $checkoutHelper;
        $this->localeResolver = $localeResolver;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->helperCore = $helperCore;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->modelQuote = $modelQuote;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->salesEmailInvoice = $salesEmailInvoice;
        $this->salesEmailOrder = $salesEmailOrder;
        $this->catalogSession = $catalogSession;
    }

    /**
     * get checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function _getCheckoutSession()
    {
        return $this->checkoutHelper->getCheckout();
    }

    /**
     * get Quote from checkout session
     *
     * @return \Magento\Sales\Model\Quote
     *
     */
    public function _getQuote()
    {
        if (!$this->modelQuote) {
            $this->modelQuote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->modelQuote;
    }

    /**
     * get last order object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function _getOrder()
    {
        $order = $this->salesOrder;
        $order->load($this->_getCheckoutSession()->getLastOrderId());

        return $order;
    }

    /**
     * get an order based on increment id
     * @param  string $incrementId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderByIncerementId($incrementId)
    {
        $order = $this->salesOrder;
        $order->loadByIncrementId($incrementId);

        return $order;
    }

    /**
     * execute Payment
     */
    public function execute()
    {
        $this->logger->info('process generate payment form');
        $this->order = $this->_getOrder();
        $this->method = $this->order->getPayment()->getMethodInstance();
        $methodTitle = $this->method->getTitle();

        if ($this->order->getPayment()->getAdditionalInformation('is_payment_processed')) {
            $this->_redirect(
                'paysley/payment/handlereturn',
                [
                    'orderId' => $this->order->getIncrementId(),
                    '_secure' => true
                ]
            );
        }

        $paymentUrl = $this->catalogSession->getPaymentUrl();
        
        if (isset($responseStatus['status'])) {
            if ($responseStatus['status'] == \Paysley\Paysley\Model\Method\AbstractMethod::FAILED_STATUS) {
                $failedReasonCode = '';
                if (isset($responseStatus['message'])) {
                    $failedReasonCode = $responseStatus['message'];
                }
                $this->redirectError(__($failedReasonCode));
            } else {
                $this->_redirect(
                    'paysley/payment/handlereturn',
                    [
                        'orderId' => $this->order->getIncrementId(),
                        '_secure' => true
                    ]
                );
            }
        }
        
        $this->_redirect($paymentUrl);
    }

    /**
     * Add breadcrumbs
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     */
    public function addBreadCrumbs($resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb(
            $this->method->getCode(),
            [
                'label' => $this->method->getTitle(),
                'title' => $this->method->getTitle()
            ]
        );
    }

    /**
     * redirect to checkout page when error or warning happen
     *
     * @param string $errorIdentifier
     * @param string $url
     * @return void
     *
     */
    public function redirectError($errorIdentifier, $url = 'checkout/cart')
    {
        $this->messageManager->addError(__($errorIdentifier));
        $this->_redirect($url, ['_secure' => true]);
    }

    /**
     * deactive quote
     *
     * @return void
     */
    public function deactiveQuote()
    {
        $quote = $this->modelQuote;
        $quote->loadActive($this->_getCheckoutSession()->getLastQuoteId());
        $quote->setReservedOrderId($this->salesOrder->getIncrementId());
        $quote->setIsActive(false)->save();
    }

    /**
     * get payment parameters
     *
     * @return array
     */
    public function getPaymentParameters()
    {
        $parameters = [];
        $settings = $this->method->getPaysleySettings();
        $billingAddress = $this->order->getBillingAddress();
        $currency = $this->checkoutSession->getQuote()->getQuoteCurrencyCode();

        $parameters = [
            'reference'    => $this->order->getIncrementId().time(),
            'payment_type' => $this->paymentType,
            'currency'     => $currency,
            'amount'       => (float)$this->setFormatNumber($this->checkoutSession->getQuote()->getGrandTotal()),
            'cart_items'   => $this->getCartItemsParameters()
        ];

         $parameters['cancel_url'] = $this->_url->getUrl(
             'paysley/payment/handlecancel',
             [
                'orderId' => $this->order->getIncrementId(),
                'trn_id' => $parameters['reference'],
                '_secure' => true
             ]
         );
        $parameters['return_url'] = $this->_url->getUrl(
            'paysley/payment/handlereturn',
            [
                'orderId' => $this->order->getIncrementId(),
                '_secure' => true
            ]
        );

        if (version_compare($this->helperCore->getShopVersion(), '2.3.0', '<')) {
            $statusUrl = "handlestatus";
        } else {
            $statusUrl = "handlestatuscsrf";
        }

        $parameters['response_url'] = $this->_url->getUrl(
            'paysley/payment/' . $statusUrl,
            [
                'orderId' => $this->order->getIncrementId(),
                'securePayment' => $this->generatePaymentKey($parameters),
                '_secure' => true
            ]
        );

        return $parameters;
    }

     /**
      * get cart items parameters
      * @return array
      */
    protected function getCartItemsParameters()
    {
        $cartItems = [];
        $count = 0;
        $orderAllItems = $this->order->getAllItems();

        foreach ($orderAllItems as $orderItem) {
            $product = $orderItem->getProduct();
            $finalPrice = (float)$product->getFinalPrice();
            $discountAmount = (float)$orderItem->getDiscountAmount();
            $taxAmount = (float)$orderItem->getTaxAmount();
            $price = (float)$product->getPrice();
            $priceIncludeTax = $price + $taxAmount;
            $cartItems[$count]['qty'] = (int)$orderItem->getQtyOrdered();
            ;
            $cartItems[$count]['name'] = $orderItem->getName();
            $cartItems[$count]['unit_price'] = $priceIncludeTax;
            $cartItems[$count]['sku'] = $orderItem->getSku();

            $count++;
        }
        
        return $cartItems;
    }

    /**
     * set formated number with 2 digits
     * @param string $number
     */
    public function setFormatNumber($number)
    {
        $number = (float) str_replace(',', '.', $number);
        return number_format($number, 2, '.', '');
    }

    /**
     * generate payment key
     * @param  array $parameters
     * @return string
     */
    public function generatePaymentKey($parameters)
    {
        $string = $parameters['currency'].$parameters['amount'];
        $encryptionMethod = "md5";

        return strtoupper($encryptionMethod($string));
    }

    /**
     * is payment signature equals generated signature
     * @param string $paymentKey
     * @param string $generatedKey
     * @return boolean
     */
    public function isPaymentKeyEqualsGeneratedKey($paymentKey, $generatedKey)
    {
        if ($paymentKey == $paymentKey) {
            $this->logger->info('Your payment data is authorized');
            return true;
        }
        $this->logger->info('Your payment data cannot be authorized');
        return false;
    }

    /**
     * create invoice
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     *
     */
    public function createInvoice($order)
    {
        $invoiceService = $this->invoiceService;

        $invoice = $invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = $this->dbTransaction;
        $transactionSave->addObject($invoice)->addObject($invoice->getOrder())->save();

        $invoiceSender = $this->salesEmailInvoice;
        $invoiceSender->send($invoice);
    }

    /**
     * add order additional information
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $responseStatus
     * @return void
     */
    public function saveAdditionalInformation($order, $responseStatus)
    {
        $payment = $order->getPayment();
        if (isset($responseStatus['payment_id'])) {
            $payment->setAdditionalInformation('paysley_payment_id', $responseStatus['payment_id']);
        }
        if (isset($responseStatus['transaction_id'])) {
            $payment->setAdditionalInformation('paysley_transaction_id', $responseStatus['transaction_id']);
        }
        if (isset($responseStatus['amount'])) {
            $payment->setAdditionalInformation('paysley_amount', $responseStatus['amount']);
        }
        if (isset($responseStatus['result'])) {
            $payment->setAdditionalInformation('paysley_result', $responseStatus['result']);
        }
        if (isset($responseStatus['currency'])) {
            $payment->setAdditionalInformation('paysley_currency', $responseStatus['currency']);
        }
        if (isset($responseStatus['result_code'])) {
            $payment->setAdditionalInformation('paysley_result_code', $responseStatus['result_code']);
        }
        if (isset($responseStatus['status'])) {
            $payment->setAdditionalInformation('paysley_status', $responseStatus['status']);
        }
        
        $payment->save();
    }

    /**
     * validate payment
     * @param  \Magento\Sales\Model\Order $order
     * @param  array $responseStatus
     * @return void
     */
    public function validatePayment($order, $responseStatus)
    {

        $this->logger->info('validate payment');

        $currentStatus = $order->getPayment()->getAdditionalInformation('paysley_status');

        if ($responseStatus['result'] == 'ACK' && empty($currentStatus)) {
            $responseStatus['status'] = 'payment_accepted';
        } else {
            $responseStatus['status'] = 'canceled';
        }

        $this->saveAdditionalInformation($order, $responseStatus);

        if (!isset($currentStatus)) {
            $this->logger->info('processing payment');
            $this->processPayment($order, $responseStatus);
        } else {
            if ($currentStatus == "pending") {
                $this->logger->info('processing payment');
                $this->updateOrderStatus($order, $responseStatus);
            }
        }
    }

    /**
     * update order status
     * @param  \Magento\Sales\Model\Order $order
     * @param  array $responseStatus
     * @return void
     */
    public function updateOrderStatus($order, $responseStatus)
    {
        $this->logger->info('update order status');

        if ($responseStatus['status'] == "payment_accepted") {
            $this->createInvoice($order);
            $comment = $this->helperCore->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, 'payment_accepted')->save();
            $this->logger->info('update order status to processed');
        } elseif ($responseStatus['status'] == "canceled") {
            if (isset($responseStatus['message'])) {
                $order->getPayment()->setAdditionalInformation(
                    'failed_reason_code',
                    $responseStatus['message']
                );
            }
            $comment = $this->helperCore->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, false);
            $order->cancel();
            $this->logger->info('update order status to failed');
        }
    }

    /**
     * process payment
     * @param  \Magento\Sales\Model\Order $order
     * @param  array $responseStatus
     * @return void
     */
    public function processPayment($order, $responseStatus)
    {
        $this->logger->info('process payment');
        $comment = $this->helperCore->getComment($responseStatus);
        $orderSender = $this->salesEmailOrder;
        $order->getPayment()->setAdditionalInformation('is_payment_processed', true)->save();
        
        if ($responseStatus['status'] == "payment_accepted") {
            $this->logger->info('process payment with status payment_accepted');
            $orderSender->send($order);
            $this->createInvoice($order);
            $order->addStatusHistoryComment($comment, false)->save();
        } else {
            $this->logger->info('process payment with status failed');
            if (isset($responseStatus['message'])) {
                $order->getPayment()->setAdditionalInformation(
                    'failed_reason_code',
                    $responseStatus['message']
                )->save();
            }
            $order->addStatusHistoryComment($comment, false)->save();
            $order->cancel()->save();
        }
    }
}

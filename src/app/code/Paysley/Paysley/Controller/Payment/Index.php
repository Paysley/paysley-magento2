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
    public $categoryRepository;
    public $checkoutCart;
    public $order;

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
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Checkout\Model\Cart $checkoutCart
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
        $this->categoryRepository = $categoryRepository;
        $this->checkoutCart = $checkoutCart;
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
        $paymentMethod = $this->order->getPayment()->getMethod();
        $this->method = $this->order->getPayment()->getMethodInstance();
        $settings = $this->method->getPaysleySettings();
        if (empty($paymentMethod) || empty($settings['access_key'])) {
            $this->redirectError(__('Error while Processing Request: please try again.'));
            return false;
        }
        $this->helperCore->accessKey = $settings['access_key'];
        $this->helperCore->isTestMode = str_contains($settings['access_key'], "py_test_");
        $paymentParameters = $this->getPaymentParameters();
        $createPaymentResponse = $this->helperCore->getPaymentUrl($paymentParameters);
        if (!isset($createPaymentResponse['result']) || $createPaymentResponse['result'] != 'success') {
            $errorMessage = empty($createPaymentResponse['error_message']) ? 'Error while Processing Request: please try again.' : $createPaymentResponse['error_message'];
            $this->redirectError($errorMessage);
            return false;
        }
        $this->catalogSession->setTransactionId($createPaymentResponse['transaction_id']);
        $this->_redirect($createPaymentResponse['long_url']);
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
        $this->order = $this->_getOrder();
        $parameters = [];
        $billingAddress = $this->order->getBillingAddress();
        $currency = $this->checkoutSession->getQuote()->getQuoteCurrencyCode();
        $this->createOrUpdateCustomerOnPaysley($billingAddress);
        $parameters = [
            'reference_number'          => $this->order->getIncrementId().time(),
            'payment_type'              => $this->paymentType,
            'request_methods'           => ["WEB"],
            'email'                     => $billingAddress->getEmail() ?? "",
            'mobile_number'             => !empty($billingAddress->getTelephone()) ? $this->helperCore->getCountryPhoneCode($billingAddress->getCountryId()).$billingAddress->getTelephone() : "",
            'customer_first_name'       => $billingAddress->getFirstname() ?? "",
            'customer_last_name'        => $billingAddress->getFirstname() ?? "",
            'currency'                  => $currency,
            'amount'                    => (float)$this->setFormatNumber($this->checkoutSession->getQuote()->getGrandTotal()),
            // 'shipping_enabled'       => true,
            'cart_items'                => $this->getCartItemsParameters(),
            'fixed_amount'              => true,
        ];
        $parameters['cancel_url'] = $this->_url->getUrl('checkout');
        if (version_compare($this->helperCore->getShopVersion(), '2.3.0', '<')) {
            $statusUrl = "handlestatus";
        } else {
            $statusUrl = "handlestatuscsrf";
        }
        $parameters['redirect_url'] = $this->_url->getUrl(
            'paysley/payment/' . $statusUrl,
            [
                'orderId' => $this->order->getIncrementId(),
                'securePayment' => $this->generatePaymentKey($parameters),
                'key'=>  base64_encode($this->helperCore->accessKey),
                '_secure' => true,

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
        if (!empty($orderAllItems)) {
            foreach ($orderAllItems as $orderItem) {
                $product = $orderItem->getProduct();
                $discountAmount = (float)$orderItem->getDiscountAmount();
                $taxAmount = (float)$orderItem->getTaxAmount();
                $price = (float)!empty($product->getSpecialPrice()) ? $product->getSpecialPrice() : $product->getPrice();
                $priceIncludeTax = (float)$this->setFormatNumber(($price + $taxAmount) - $discountAmount);
                $cartItems[$count]['qty'] = (int)$orderItem->getQtyOrdered();
                $cartItems[$count]['name'] = $orderItem->getName();
                $cartItems[$count]['sku'] = $orderItem->getSku();
                $cartItems[$count]['sales_price'] = $priceIncludeTax;
                $cartItems[$count]['unit'] = ['pc'];
                $cartItems[$count]['product_service_id'] = $this->createOrUpdateProductOnPaysley($product);
                $count++;
            }
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
        $invoice->getOrder()->setCustomerNoteNotify(true);
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
        $responseStatus['status'] = $responseStatus['result'] == 'success' ? 'payment_accepted' : 'canceled';
        $this->saveAdditionalInformation($order, $responseStatus);
        $this->updateOrderStatus($order, $responseStatus);
    }

    /**
     * update order status
     * @param  \Magento\Sales\Model\Order $order
     * @param  array $responseStatus
     * @return void
     */
    public function updateOrderStatus($order, $responseStatus)
    {
        $orderId = $this->getRequest()->getParam('orderId');
        $this->logger->info('update order status');
        if ($responseStatus['status'] == "payment_accepted") {
            $this->createInvoice($order);
            $comment = $this->helperCore->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, 'payment_accepted')->save();
            $this->logger->info('update order status to processed');
            $this->removeAllItemsFromCart();
            $this->_redirect($this->_url->getUrl('checkout/onepage/success'));            
        } elseif ($responseStatus['status'] == "canceled") {
            if (!empty($responseStatus['message'])) {
                $order->getPayment()->setAdditionalInformation(
                    'failed_reason_code',
                    $responseStatus['message']
                );
            }
            $comment = $this->helperCore->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, "canceled")->save();
            $order->cancel()->save();
            $this->logger->info('update order status to canceled');
            $this->_redirect($this->_url->getUrl('checkout/onepage/failure', ['order_id' => $orderId, '_secure' => true]));
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

    /**
	 * Create/Update product on paysley
     * @param array $product
     * @return  int $paysleyProductId
	 */
	protected function createOrUpdateProductOnPaysley($product = [])
	{
		$paysleyProductId = null;
		if (empty($product)) {
			return $paysleyProductId;
		}
		$data = [];
		$data['name'] = $product->getName();
		$data['description'] = $product->getDescription();
		$data['sku'] = $product->getSku();
		$data['category_id'] = $this->checkAndCreateProductCategoryOnPaysley($product->getCategoryIds());
		$data['type'] = 'product';
		$data['manage_inventory'] = $this->getProductQuantity($product->getQuantityAndStockStatus());
		$data['unit_in_stock'] = !empty($product->getQuantityAndStockStatus()) ? $product->getQuantityAndStockStatus()['qty'] : 0;
		$data['unit_low_stock'] = 2;
		$data['unit_type'] = 'flat-rate';
		$data['cost'] = (float)$this->setFormatNumber($product->getPrice());
		$data['sales_price'] = (float)$this->setFormatNumber($product->getPrice());
		$existingProducts = $this->helperCore->getProducts($product->getName());
		if (!empty($existingProducts['result']) && $existingProducts['result'] === "success" && !empty($existingProducts['product_services'])) {
            $data['id'] = $existingProducts['product_services'][0]['id'];
			$productResult = $this->helperCore->updateProduct($data);
		} else {
			$productResult = $this->helperCore->createProduct($data);
		}
		if (!empty($productResult['result']) && 'success' === $productResult['result']) {
			$paysleyProductId = !empty($productResult['product_and_service']) ? $productResult['product_and_service']['id'] : $productResult['id'];
		}
		return $paysleyProductId;
	}

    /**
     * Function to checkAndCreateProductCategory if category already exists then return the category else create category on paysley
     * @param int $product_id
     * @return $categoryid if data exists else null
     */
    protected function checkAndCreateProductCategoryOnPaysley ($categoryIds = [])
    {
        $category = $this->getCategoryData($categoryIds[0] ?? "");
		if (!empty($category)) {
			$categoryResult = $this->helperCore->categoryList($category['category_name']);
			if (!empty($categoryResult['result']) && 'success' === $categoryResult['result'] && !empty($categoryResult['categories'])) {
				return $categoryResult['categories'][0]['id'];
			}
            $categoryCreateResult = $this->helperCore->createCategory(['name' => $category['category_name']]);
            if (!empty($categoryCreateResult)) {
                return $categoryCreateResult['id'];
            }
		}
		return null;
    }

    /**
     * Function to get the product quantity
     * @param arrray $quantityArray 
     * @return enum(1, 0) $product quantity 
     */

    protected function getProductQuantity($quantityArray = [])
    {
        if (empty($quantityArray)) {
            return 0;
        }
        return $quantityArray['is_in_stock'] && $quantityArray['qty'] ? 1 : 0;
    } 

    /**
     * Function to get the category data of specified data
     * @param int $categoryId 
     * @return array $categoryData
     */

    protected function getCategoryData($categoryId)
    {
        try {
            $categoryDetails = [
                "category_id" => "", 
                "category_name" => "No Category"
            ];
            if (empty($categoryId)) {
                return $categoryDetails;
            }
            $categoryData = $this->categoryRepository->get($categoryId);
            $categoryDetails["category_id"] = $categoryData->getEntityId();
            $categoryDetails["category_name"] = $categoryData->getName();
            return $categoryDetails;
        } catch (\Exception $e) {
            $this->logger->error('Exception occurred while getting category data: ' . $e->getMessage());
            return $categoryDetails;
        }
    }

    /**
	 * Create/Update Customer on paysley
     * @param $billingAddress
     * @return int $customer_paysley_id
	 */
	protected function createOrUpdateCustomerOnPaysley($billingAddress)
	{
		$customerPaysleyId = null;
        //Get the exists customer lists
		$checkIfCustomerExistOnPaysleyResult = $this->helperCore->customerList($billingAddress->getEmail());
		if (!empty($checkIfCustomerExistOnPaysleyResult['result']) && 'success' === $checkIfCustomerExistOnPaysleyResult['result']) {
			$customerDataToUpdate = [];
            $address_line1 = !empty($billingAddress->getStreet()[0]) ? $billingAddress->getStreet()[0] : "";
            $address_line2 = !empty($billingAddress->getStreet()[1]) ? $billingAddress->getStreet()[1] : "";
            $mobile_number = !empty($billingAddress->getTelephone()) ? $this->helperCore->getCountryPhoneCode($billingAddress->getCountryId()).$billingAddress->getTelephone() : "";
			// Customer billing information details
			$customerDataToUpdate['email']         = $billingAddress->getEmail() ?? "";
			$customerDataToUpdate['mobile_no']     = $mobile_number;
			$customerDataToUpdate['first_name']    = $billingAddress->getFirstname() ?? "";
			$customerDataToUpdate['last_name']     = $billingAddress->getLastname() ?? "";
			$customerDataToUpdate['company_name']  = $billingAddress->getCompany() ?? "";
			$customerDataToUpdate['listing_type']  = 'individual';
			$customerDataToUpdate['address_line1'] = $address_line1;
			$customerDataToUpdate['address_line2'] = $address_line2;
			$customerDataToUpdate['city'] 		  = $billingAddress->getCity() ?? "";
			$customerDataToUpdate['state'] 		  = $billingAddress->getRegion() ?? "";
			$customerDataToUpdate['postal_code']   = $billingAddress->getPostcode() ?? "";
			$customerDataToUpdate['country_iso']   = $billingAddress->getCountryId() ?? "";
            //Check customers exists if exists then get set customer paysley id
			if (!empty($checkIfCustomerExistOnPaysleyResult['customers'])) {
				$customerDataIndex = array_search($billingAddress->getEmail(), array_column($checkIfCustomerExistOnPaysleyResult['customers'], 'email'));			
				$customerDataToUpdate['customer_id'] = $customerPaysleyId = $checkIfCustomerExistOnPaysleyResult['customers'][$customerDataIndex]['customer_id'] ?? null;
			}
			if (!empty($customerPaysleyId)) {
                //Update customer
                $updateCustomerOnPaysleyResult = $this->helperCore->updateCustomer($customerDataToUpdate);
				if (!empty($updateCustomerOnPaysleyResult['result']) && 'success' === $updateCustomerOnPaysleyResult['result']) {
				}
			} else {
                //Create customer
				$createCustomerOnPaysleyResult = $this->helperCore->createCustomer($customerDataToUpdate);
				if (!empty($createCustomerOnPaysleyResult['result']) && 'success' === $createCustomerOnPaysleyResult['result']) {
					$customerPaysleyId = $createCustomerOnPaysleyResult['customer_id'];
				}
			}
		}
		return $customerPaysleyId;
	}

    /** 
     * Function to remove all items from cart
     * @return void 
     */
    public function removeAllItemsFromCart ()
    {
        $this->checkoutCart->truncate()->save();
    }
}

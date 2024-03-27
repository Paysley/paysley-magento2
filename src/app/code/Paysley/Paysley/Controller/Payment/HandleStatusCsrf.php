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

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class HandleStatusCsrf extends \Paysley\Paysley\Controller\Payment\Index implements CsrfAwareActionInterface
{

    protected $_order;
    /**
     * execute payment handlestatus
     */
    public function execute()
    {
        $this->logger->info('process response url with csrf : ');        
        $this->helperCore->accessKey = base64_decode($this->getRequest()->getParam('key'));
        $transactionId = $this->catalogSession->getTransactionId();
        $orderId = $this->getRequest()->getParam('orderId');
        if (empty($transactionId) || empty($orderId)) {
            $this->redirectError(__('Error while Processing Request: please try again.'));
            return false;
        }
        $paymentResponse = $this->helperCore->getPaymentDetails($transactionId);
        if (empty($paymentResponse['transaction'])) {
            $this->redirectError(__('Error while Processing Request: please try again.'));
            return false;    
        }
        $paymentKey = $this->getRequest()->getParam('securePayment');
        $this->logger->info('Payment response : '.json_encode($paymentResponse));
        $paymentResult = [];
        $paymentResult['payment_id'] = $paymentResponse['transaction']['payment_id'] ?? '';
        $paymentResult['transaction_id'] = $paymentResponse['transaction']['transaction_id'] ?? '';
        $paymentResult['amount'] = $paymentResponse['transaction']['amount'] ?? '';
        $paymentResult['result'] = $paymentResponse['transaction']['status'] ?? '';
        $paymentResult['currency'] = $paymentResponse['transaction']['currency'] ?? '';
        $paymentResult['message'] = $paymentResponse['transaction']['status_description'] ?? '';
        $generatedKey = $this->generatePaymentKey($paymentResult);
        $this->_order = $this->getOrderByIncerementId($orderId);
        if ($paymentResult && $this->isPaymentKeyEqualsGeneratedKey($paymentKey, $generatedKey)) {
            $this->logger->info("Payment status url response : ".json_encode($paymentResult));
            $this->method = $this->_order->getPayment()->getMethodInstance();
            $this->validatePayment($this->_order, $paymentResult);
        } else {
            $paymentResult['result'] = 'canceled';
            $comment = "Your payment data cannot be authorized";
            $this->saveAdditionalInformation($this->_order, $paymentResult);
            $this->_order->addStatusHistoryComment($comment, "canceled")->save();
            $this->_order->cancel()->save();
            $this->_redirect($this->_url->getUrl('checkout/onepage/failure', ['order_id' => $orderId, '_secure' => true]));
        }
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}

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
    /**
     * execute payment handlestatus
     */
    public function execute()
    {
        $this->logger->info('process response url with csrf : ');

        $paymentResponse = json_decode($this->getRequest()->getParam('response'), 1);
        $paymentKey = $this->getRequest()->getParam('paymentKey');
        
        $this->logger->info('process response url with csrf : '.json_encode($paymentResponse));

        $result = isset($paymentResponse['result']) ? $paymentResponse['result'] : null;
        $status = isset($paymentResponse['status']) ? $paymentResponse['status'] : null;

        if ($status) {
            $paymentResult = [];
            $paymentResult['payment_id'] =
            isset($paymentResponse['response']['id']) ? $paymentResponse['response']['id'] : '';
            $paymentResult['transaction_id'] =
            isset($paymentResponse['customParameters']['transaction_id']) ?
            $paymentResponse['customParameters']['transaction_id'] : '';
            $paymentResult['amount'] =  isset($paymentResponse['amount']) ? $paymentResponse['amount'] : '';
            $paymentResult['result'] =  isset($paymentResponse['status']) ? $paymentResponse['status'] : '';
            $paymentResult['currency'] =
            isset($paymentResponse['response']['currency']) ? $paymentResponse['response']['currency'] : '';
            $paymentResult['result_code'] =
            isset($paymentResponse['result_code']) ? $paymentResponse['result_code'] : '';
        } elseif ($result) {
            $paymentResult = $paymentResponse;
        }
        $generatedKey = $this->generatePaymentKey($paymentResult);
        if ($paymentResult && $this->isPaymentKeyEqualsGeneratedKey($paymentKey, $paymentResult)) {
            $this->logger->info("status url response : ".
                json_encode($paymentResult));

            $orderId = $this->getRequest()->getParam('orderId');
            $this->_order = $this->getOrderByIncerementId($orderId);
            $this->method = $this->_order->getPayment()->getMethodInstance();

            $this->validatePayment($this->_order, $paymentResult);
        } else {
            $comment = "Your payment data cannot be authorized";
            $order->addStatusHistoryComment($comment, false)->save();
            $order->cancel()->save();
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

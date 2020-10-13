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

class HandleReturn extends \Paysley\Paysley\Controller\Payment\Index
{
    /**
     * execute payment handlereturn
     */
    public function execute()
    {
        $this->logger->info('process return url');

        $orderId = $this->getRequest()->getParam('orderId');
        $this->_order = $this->getOrderByIncerementId($orderId);

        $additionalInformation = $this->_order->getPayment()->getAdditionalInformation();
        $this->logger->info('payment additional information : ', $additionalInformation);

        if (isset($additionalInformation['paysley_status'])) {
            if ($additionalInformation['paysley_status'] == "canceled") {
                $this->logger->info('process return url : failed payment');
                $this->redirectError("order already canceled");
            } else {
                $this->logger->info('process return url : success payment');
                $this->deactiveQuote();
                $this->_getCheckoutSession()->setLastRealOrderId($this->_order->getIncrementId());
                $this->_redirect('checkout/onepage/success', ['_secure' => true]);
            }
        } else {
            $this->logger->info('process return url : late status url');
            $payment = $this->_order->getPayment();
            $paymentMethod = $this->_order->getPayment()->getMethod();
            $this->method = $this->_order->getPayment()->getMethodInstance();

            $transaction_id = $this->getRequest()->getParam('transaction_id');
            $payment->setAdditionalInformation('paysley_transaction_id', $transaction_id);
            $payment->setAdditionalInformation(
                'paysley_status',
                'pending'
            );
            $payment->save();

            $this->deactiveQuote();
            $this->_getCheckoutSession()->setLastRealOrderId($this->_order->getIncrementId());
            $message = __('Your order on').' '.
                $this->method->getShopName().' '.
                __('is in the process').' '.
                __('Please back again after a minutes and check your order history"
                    , "Please back again after a minutes and check your order history');
            $this->messageManager->addWarning($message);
            $this->_redirect('checkout/onepage/success', ['_secure' => true]);
        }
    }
}

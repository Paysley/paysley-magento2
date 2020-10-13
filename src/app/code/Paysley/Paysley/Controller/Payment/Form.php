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

class Form extends \Paysley\Paysley\Controller\Payment\Index
{
    /**
     * execute payment form
     */
    public function execute()
    {
        $this->order = $this->_getOrder();

        $paymentMethod = $this->order->getPayment()->getMethod();
        $this->method = $this->order->getPayment()->getMethodInstance();

        $settings = $this->method->getPaysleySettings();

        if (empty($paymentMethod) || empty($settings['access_key'])) {
            $this->redirectError('Error while Processing Request: please try again.');
            return false;
        }

        $paymentParameters = $this->getPaymentParameters();
        
        $this->helperCore->accessKey = $settings['access_key'];
        
        $paymentUrl = $this->helperCore->getPaymentUrl($paymentParameters);
        
        if (!isset($paymentUrl['result']) || $paymentUrl['result'] != 'success') {
            $this->redirectError('Error while Processing Request: please try again.');
            return false;
        }

        $this->catalogSession->setPaymentUrl($paymentUrl['long_url']);
        
        $this->_redirect(
            'paysley/payment',
            [
                'trn_id'    => $paymentParameters['reference'],
                '_secure' => true
            ]
        );
    }
}

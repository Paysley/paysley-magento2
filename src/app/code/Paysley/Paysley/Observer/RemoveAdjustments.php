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
 * @copyright   Copyright (c) 2015 Paysley
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Paysley\Paysley\Observer;

use Magento\Framework\Event\ObserverInterface;

class RemoveAdjustments implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * @var \Paysley\Paysley\Helper\Logger
     */
    public $paysleyLogger;

    /**
     * @var \Magento\Sales\Model\Order
     */
    public $salesOrder = false;
   
    /**
     * Constructor
     * @param \Magento\Framework\App\RequestInterface        $request
     * @param \Paysley\Paysley\Helper\Logger                   $paysleyLogger
     * @param \Magento\Sales\Model\Order                     $salesOrder
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Paysley\Paysley\Helper\Logger $paysleyLogger,
        \Magento\Sales\Model\Order $salesOrder
    ) {
        $this->request = $request;
        $this->paysleyLogger = $paysleyLogger;
        $this->salesOrder = $salesOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fullActionName = $observer->getEvent()->getFullActionName();

        if ($fullActionName == 'sales_order_creditmemo_new') {
            $requestParams = $this->request->getParams();
            $orderId = $requestParams['order_id'];
            $order = $this->getOrderByOrderId($orderId);
            
            if ($order->getId()) {
                $paymentMethod = $order->getPayment()->getMethod();
                $method = $order->getPayment()->getMethodInstance();

                if (strpos($paymentMethod, 'paysley') !== false &&
                    !$method->canRefundPartialPerInvoice()
                ) {
                    $observer->getEvent()->getLayout()->getUpdate()->addHandle(
                        $fullActionName . '_remove_adjustments'
                    );
                }
            }
        }
    }

    /**
     * get an order based on order id
     * @param  string $orderId
     * @return \Magento\Sales\Model\Order
     */
    private function getOrderByOrderId($orderId)
    {
        $order = $this->salesOrder;
        $order->load($orderId);

        return $order;
    }
}

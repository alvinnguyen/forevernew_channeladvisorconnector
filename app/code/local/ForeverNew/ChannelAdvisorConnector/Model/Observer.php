<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento
 *
 * DISCLAIMER
 *
 * This custom module is owned by Forever New and is a private intellectual property
 * Please do not modify this file because you will lose the modification when upgrading it
 *
 * @category  ForeverNew
 * @package   ChannelAdvisorConnector
 * @author    Alvin Nguyen <alvin.nguyen@forevernew.com.au>
 */

class ForeverNew_ChannelAdvisorConnector_Model_Observer {

    /**
     * Adjust the shipping description so that middleware can recognise the shipping method
     * Defaulting to Standard Shipping and update to express shipping once found the magic word
     *
     * @param $observer
     */
    public function adjustShippingDescription($observer) {
        $order = $observer->getEvent()->getOrder();
        if (preg_match('/channeladvisorshipping/i', $order->getShippingMethod())) {
            $currentShippingDescription = $order->getShippingDescription(); // expecting "*standard*" "*express*"
            $newShippingDescription = 'Standard Shipping';
            $newShippingPrice = 7.95;
            if (preg_match('/express/i', $currentShippingDescription)) {
                $newShippingDescription = 'Express Shipping';
                $newShippingPrice = 9.95;
            }
            $order->setShippingDescription($newShippingDescription);
            $order->setShippingAmount($newShippingPrice);
            $order->setBaseShippingAmount($newShippingPrice);
            $order->save();
        }
    }

    /**
     * Running through the cart to ensure availability > purchase quantity
     * The CA connector already checked for out of stock scenario, we are just checking for ENOUGH stock
     *
     * @param $observer
     * @throws Magento_Exception
     */
    public function preventNegativeStock($observer) {
        $ca_check = false;
        foreach ($observer->getEvent()->getItems() as $quoteItem) {
            if (!$ca_check) {
                if (!$quoteItem->getQuote()->getData('channeladvisor_order')) {
                    // Should only apply for CA quote
                    break;
                }
                $ca_check = true;
            }
            $product = $quoteItem->getProduct();
            $availableQty = $product->getStockItem()->getQty();
            $requestedQty = $quoteItem->getQty();
            if ($availableQty < $requestedQty) {
                throw new Magento_Exception('Insufficient stock');
            }
        }
    }
}
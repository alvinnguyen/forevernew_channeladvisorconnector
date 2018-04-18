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

class ForeverNew_ChannelAdvisorConnector_Model_Export_Product_Incremental extends ChannelAdvisor_ChannelAdvisorConnector_Model_Export_Product_Incremental
{
    /**
     * Override to add stock_id = 1 for the export to export only DC3000 stock
     *
     * @param array $productIds
     * @return array
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function _prepareCatalogInventory(array $productIds)
    {
        if (empty($productIds)) {
            return array();
        }
        $select = $this->_connection->select()
            ->from(Mage::getResourceModel('cataloginventory/stock_item')->getMainTable())
            ->where('product_id IN (?) AND stock_id = 1', $productIds);

        $stmt = $this->_connection->query($select);
        $stockItemRows = array();
        while ($stockItemRow = $stmt->fetch()) {
            $productId = $stockItemRow['product_id'];
            unset(
                $stockItemRow['item_id'], $stockItemRow['product_id'], $stockItemRow['low_stock_date'],
                $stockItemRow['stock_id'], $stockItemRow['stock_status_changed_automatically']
            );
            $stockItemRows[$productId] = $stockItemRow;
        }
        return $stockItemRows;
    }
}
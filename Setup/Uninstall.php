<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
 
namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
	private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
	
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
		$installer->startSetup();
		
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists('tax_class') == true)
			$setup->getConnection()->dropColumn($setup->getTable('tax_class'), 'avatax_code');
		if ($setup->getConnection()->isTableExists('sales_creditmemo') == true)
			$setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'avatax_is_unbalanced');
		if ($setup->getConnection()->isTableExists('sales_creditmemo') == true)
			$setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'base_avatax_tax_amount');
		if ($setup->getConnection()->isTableExists('sales_invoice') == true)
			$setup->getConnection()->dropColumn($setup->getTable('sales_invoice'), 'avatax_is_unbalanced');
		if ($setup->getConnection()->isTableExists('sales_invoice') == true)
			$setup->getConnection()->dropColumn($setup->getTable('sales_creditmemo'), 'base_avatax_tax_amount');
		if ($setup->getConnection()->isTableExists('quote_item') == true)
			$setup->getConnection()->dropColumn($setup->getTable('quote_item'), 'ava_vatcode');
		if ($setup->getConnection()->isTableExists('sales_order_item') == true)
			$setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), 'ava_vatcode');		
		if ($setup->getConnection()->isTableExists('quote_address') == true)
			$setup->getConnection()->dropColumn($setup->getTable('quote_address'), 'avatax_messages');	
		
		if ($setup->getConnection()->isTableExists('avatax_queue') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_queue'));
		if ($setup->getConnection()->isTableExists('avatax_log') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_log'));
		if ($setup->getConnection()->isTableExists('baseprovider_queue_job') == true)
			$installer->getConnection()->dropTable($installer->getTable('baseprovider_queue_job'));
		if ($setup->getConnection()->isTableExists('avatax_sales_creditmemo') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_sales_creditmemo'));
		if ($setup->getConnection()->isTableExists('avatax_sales_invoice') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_sales_invoice'));
		if ($setup->getConnection()->isTableExists('avatax_sales_invoice_item') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_sales_invoice_item'));
		if ($setup->getConnection()->isTableExists('avatax_batch_queue') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_batch_queue'));
		if ($setup->getConnection()->isTableExists('avatax_cross_border_class') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_cross_border_class'));
		if ($setup->getConnection()->isTableExists('avatax_cross_border_class_country') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_cross_border_class_country'));
		if ($setup->getConnection()->isTableExists('avatax_products_sync') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_products_sync'));
		if ($setup->getConnection()->isTableExists('avatax_quote_item') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_quote_item'));
		if ($setup->getConnection()->isTableExists('avatax_sales_creditmemo_item') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_sales_creditmemo_item'));
		if ($setup->getConnection()->isTableExists('avatax_sales_order') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_sales_order'));
		if ($setup->getConnection()->isTableExists('avatax_sales_order_item') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_sales_order_item'));
		if ($setup->getConnection()->isTableExists('avatax_tax_code_sync') == true)
			$installer->getConnection()->dropTable($installer->getTable('avatax_tax_code_sync'));
		if ($setup->getConnection()->isTableExists('classyllama_avatax_crossbordertype') == true)
			$installer->getConnection()->dropTable($installer->getTable('classyllama_avatax_crossbordertype'));
		if ($setup->getConnection()->isTableExists('baseprovider_logs') == true)
			$installer->getConnection()->dropTable($installer->getTable('baseprovider_logs'));
		
		$eavSetup = $this->eavSetupFactory->create();
		$eavSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'override_importer_of_record');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ava_vatcode');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'avatax_cross_border_type');
		
		$core_config_data_table = $setup->getConnection()->getTableName('core_config_data');
		$sql4 = "DELETE FROM $core_config_data_table WHERE path LIKE '%tax/avatax%';";         
		$installer->getConnection()->query($sql4);
		
		$patch_list_table = $setup->getConnection()->getTableName('patch_list');
		$sql5 = "DELETE FROM $patch_list_table WHERE patch_name LIKE '%ClassyLlama%';";         
		$installer->getConnection()->query($sql5);

		$installer->endSetup();
    }
}

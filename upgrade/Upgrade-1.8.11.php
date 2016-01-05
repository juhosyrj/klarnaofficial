<?php
/*
* File: /upgrade/Upgrade-1.8.11.php
*/
function upgrade_module_1_8_11($module)
{
    // Process Module upgrade to 1.8.11
    $update_sql = 'ALTER TABLE `'._DB_PREFIX_.'klarna_orders` ADD';
    $update_sql .= ' COLUMN `id_cart` INT(10) UNSIGNED NOT NULL AFTER `id`;';
    return Db::getInstance()->execute($update_sql);
}

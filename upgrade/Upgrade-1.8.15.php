<?php
/*
* File: /upgrade/Upgrade-1.8.11.php
*/
function upgrade_module_1_8_15($module)
{
    // Process Module upgrade to 1.8.15
    $update_sql = 'ALTER TABLE `'._DB_PREFIX_.'kpmpclasses` MODIFY';
    $update_sql .= ' `expire` VARCHAR(20) NOT NULL;';
    return Db::getInstance()->execute($update_sql);
}

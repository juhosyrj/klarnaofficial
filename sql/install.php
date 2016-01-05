<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE `'._DB_PREFIX_.'klarna_orders` (
		  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		  `id_cart` INTEGER UNSIGNED NOT NULL,
		  `id_order` INTEGER UNSIGNED NOT NULL,
		  `id_shop` INTEGER UNSIGNED NOT NULL,
		  `ssn` VARCHAR(20) NOT NULL,
		  `invoicenumber` VARCHAR(256) NOT NULL,
		  `eid` VARCHAR(100) NOT NULL,
		  `reservation` VARCHAR(256) NOT NULL,
		  `risk_status` VARCHAR(10) NOT NULL,
		  PRIMARY KEY (`id`)
		)
		ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        
$sql[] = 'CREATE TABLE `'._DB_PREFIX_.'klarna_errors` (
		  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		  `id_order` INTEGER UNSIGNED NOT NULL,
		  `error_message` VARCHAR(256) NOT NULL,
		  PRIMARY KEY (`id`)
		)
		ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'kpmpclasses` (
                `eid` int(10) unsigned NOT NULL,
                `id` int(10) unsigned NOT NULL,
                `type` tinyint(4) NOT NULL,
                `description` varchar(255) NOT NULL,
                `months` int(11) NOT NULL,
                `interestrate` decimal(11,2) NOT NULL,
                `invoicefee` decimal(11,2) NOT NULL,
                `startfee` decimal(11,2) NOT NULL,
                `minamount` decimal(11,2) NOT NULL,
                `country` int(11) NOT NULL,
                `expire` int(11) NOT NULL,
                KEY `id` (`id`)
            )
            ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

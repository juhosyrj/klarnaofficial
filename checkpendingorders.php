<?php
require_once(dirname(__FILE__). '/../../config/config.inc.php');
require_once(_PS_ROOT_DIR_.'/init.php');
require_once(dirname(__FILE__).'/klarnaofficial.php');

$shops = Shop::getShops(true,null,true);
$order_status_ids = "";
foreach ($shops as $shop) {
    $order_status_ids .= Configuration::get('KPM_PENDING_PP', null, null, $shop).",";
    $order_status_ids .= Configuration::get('KPM_PENDING_INVOICE', null, null, $shop).",";
}
$order_status_ids = rtrim($order_status_ids, ",");
$order_status_ids = pSQL($order_status_ids);
$sql = "SELECT id_order FROM "._DB_PREFIX_."orders WHERE current_state IN ($order_status_ids);";
$result = Db::getInstance()->executeS($sql);

foreach ($result as $row) {
    $id_order = (int)$row["id_order"];
    try {
        $klarnaofficial = new KlarnaOfficial();
        $klarnaofficial->checkPendingStatus($id_order);
    } catch (Exception $e) {
        $msg = "Check pending: $id_order " . $e->getMessage();
        Logger::addLog($msg, 1, null, 'klarnaofficial', $id_order, true);
    }
}
echo "Done";
?>
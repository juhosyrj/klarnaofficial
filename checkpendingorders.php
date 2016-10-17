<?php
require_once(dirname(__FILE__). '/../../config/config.inc.php');
require_once(_PS_ROOT_DIR_.'/init.php');
require_once(dirname(__FILE__).'/klarnaofficial.php');

$klarnaofficial = new KlarnaOfficial();
$shops = Shop::getShops(true, null, true);

$risk_status = $klarnaofficial->Pending_risk;
$sql = "SELECT id_order FROM "._DB_PREFIX_."klarna_orders WHERE risk_status='$risk_status';";

$result = Db::getInstance()->executeS($sql);

foreach ($result as $row) {
    $id_order = (int)$row["id_order"];
    echo "CHECKING ORDER $id_order";
    try {
        $klarnaofficial->checkPendingStatus($id_order, true);
    } catch (Exception $e) {
        $msg = "Check pending: $id_order " . $e->getMessage();
        Logger::addLog($msg, 1, null, 'klarnaofficial', $id_order, true);
    }
}
echo "<br />Done checking orders";

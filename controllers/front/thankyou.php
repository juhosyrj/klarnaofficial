<?php
/**
 * 2015 Prestaworks AB.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@prestaworks.se so we can send you a copy immediately.
 *
 * @author Prestaworks AB <info@prestaworks.se>
 * @category PrestaShop
 * @category  Module
 * @copyright 2015 Prestaworks AB
 * @license     see file: docs/LICENSE.txt
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of Prestaworks AB
 */
 
class KlarnaOfficialThankYouModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->addCSS(_MODULE_DIR_.'klarnaofficial/views/css/klarnacheckout.css', 'all');
    }

    public function initContent()
    {
        parent::initContent();
        require_once dirname(__FILE__).'/../../libraries/Checkout.php';

        session_start();
        if (!isset($_SESSION['klarna_checkout'])) {
            Tools::redirect('index.php');
        }
        try {
            /*
             * Fetch the checkout resource.
             */
            Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
            $sid = Tools::getValue('sid');
            if ($sid == 'se') {
                $secret = Configuration::get('KCO_SWEDEN_SECRET');
            }
            if ($sid == 'de') {
                $secret = Configuration::get('KCO_GERMANY_SECRET');
            }
            if ($sid == 'at') {
                $secret = Configuration::get('KCO_AUSTRIA_SECRET');
            }
            if ($sid == 'fi') {
                $secret = Configuration::get('KCO_FINLAND_SECRET');
            }
            if ($sid == 'no') {
                $secret = Configuration::get('KCO_NORWAY_SECRET');
            }
            $connector = Klarna_Checkout_Connector::create($secret);

            $checkoutId = $_SESSION['klarna_checkout'];
            $klarnaorder = new Klarna_Checkout_Order($connector, $checkoutId);
            $klarnaorder->fetch();

            if ($klarnaorder['status'] == 'checkout_incomplete') {
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }

            $snippet = $klarnaorder['gui']['snippet'];

            $order_id2 = (int)($klarnaorder['merchant_reference']['orderid2']);
            $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart='.$order_id2;
            $result = Db::getInstance()->getRow($sql);
            if (!isset($result['id_order'])) {
                //Give push a few extra seconds
                sleep(2);
                $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart='.$order_id2;
                $result = Db::getInstance()->getRow($sql);
                if (!isset($result['id_order'])) {
                    sleep(3);
                    $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart='.$order_id2;
                    $result = Db::getInstance()->getRow($sql);
                }
            }
            $this->context->smarty->assign(array(
                    'klarna_html' => $snippet,
                    'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation((int) ($result['id_order'])),
                ));

            unset($_SESSION['klarna_checkout']);
        } catch (Klarna_Exception $e) {
            $this->context->smarty->assign('klarna_error', $e->getMessage());
        }
        $this->setTemplate('kco_thankyoupage.tpl');
    }

    public function displayOrderConfirmation($id_order)
    {
        if (Validate::isUnsignedId($id_order)) {
            $params = array();
            $order = new Order($id_order);
            $currency = new Currency($order->id_currency);

            if (Validate::isLoadedObject($order)) {
                $params['total_to_pay'] = $order->getOrdersTotalPaid();
                $params['currency'] = $currency->sign;
                $params['objOrder'] = $order;
                $params['currencyObj'] = $currency;

                return Hook::exec('displayOrderConfirmation', $params);
            }
        }

        return false;
    }
}

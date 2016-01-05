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
 *  @author    Prestaworks AB <info@prestaworks.se>
 *  @copyright 2015 Prestaworks AB
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Prestaworks AB
 */
 
class KlarnaOfficialThankYouUkModuleFrontController extends ModuleFrontController
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
        require_once dirname(__FILE__).'/../../libraries/KCOUK/autoload.php';

        session_start();
        if (!Tools::getIsset('klarna_order_id')) {
            Tools::redirect('index.php');
        }
        try {
            /*
             * Fetch the checkout resource.
             */

            $sid = Tools::getValue('sid');
            if ($sid == 'gb') {
                $sharedSecret = Configuration::get('KCO_UK_SECRET');
                $merchantId = Configuration::get('KCO_UK_EID');
            }

            if ((int) (Configuration::get('KCO_TESTMODE')) == 1) {
                $connector = \Klarna\Rest\Transport\Connector::create(
                    $merchantId,
                    $sharedSecret,
                    \Klarna\Rest\Transport\ConnectorInterface::EU_TEST_BASE_URL
                );

           
                $orderId = Tools::getValue('klarna_order_id');

                $checkout = new \Klarna\Rest\Checkout\Order($connector, $orderId);
                $checkout->fetch();
            } else {
                $connector = \Klarna\Rest\Transport\Connector::create(
                    $merchantId,
                    $sharedSecret,
                    \Klarna\Rest\Transport\ConnectorInterface::EU_BASE_URL
                );
              
                $orderId = Tools::getValue('klarna_order_id');
                $checkout = new \Klarna\Rest\Checkout\Order($connector, $orderId);
                $checkout->fetch();
            }

            $snippet = $checkout['html_snippet'];

            if ($checkout['status'] == 'checkout_incomplete') {
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkout_klarna');
            }
            //var_dump($checkout);

            $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders '.
            'WHERE id_cart='.(int) ($checkout['merchant_reference2']);
            $result = Db::getInstance()->getRow($sql);
            
            if (!isset($result['id_order'])) {
                //Give push a few extra seconds
                sleep(2);
                
                $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders '.
                'WHERE id_cart='.(int) ($checkout['merchant_reference2']);
                
                $result = Db::getInstance()->getRow($sql);
                
                if (!isset($result['id_order'])) {
                    sleep(3);
                    $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders '.
                    'WHERE id_cart='.(int) ($checkout['merchant_reference2']);
                    $result = Db::getInstance()->getRow($sql);
                }
            }
            $this->context->smarty->assign(array(
                    'klarna_html' => $snippet,
                    'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation((int) ($result['id_order'])),
                ));
            unset($_SESSION['klarna_checkout_uk']);
        } catch (Exception $e) {
            //var_dump($e);
            echo $orderId;
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

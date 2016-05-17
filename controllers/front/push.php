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
 
class KlarnaOfficialPushModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function postProcess()
    {
        //$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        //Logger::addLog($url, 1, null, null, null, true);

        require_once dirname(__FILE__).'/../../libraries/Checkout.php';
        //Klarna uses iso 3166-1 alpha 3, prestashop uses different iso so we need to convert this.
        $country_iso_codes = array(
        'SWE' => 'SE',
        'NOR' => 'NO',
        'FIN' => 'FI',
        'DNK' => 'DK',
        'DEU' => 'DE',
        'NLD' => 'NL',
        'se' => 'SE',
        'no' => 'NO',
        'fi' => 'FI',
        'dk' => 'DK',
        'de' => 'DE',
        'nl' => 'NL',
        'gb' => 'GB',
        );

        try {
            $sid = Tools::getValue('sid');
            if ($sid == 'se') {
                $conf = Configuration::getMultiple(array('KCO_SWEDEN_EID', 'KCO_SWEDEN_SECRET'));
                $eid = $conf['KCO_SWEDEN_EID'];
                $secret = $conf['KCO_SWEDEN_SECRET'];
            }
            if ($sid == 'de') {
                $conf = Configuration::getMultiple(array('KCO_GERMANY_EID', 'KCO_GERMANY_SECRET'));
                $eid = $conf['KCO_GERMANY_EID'];
                $secret = $conf['KCO_GERMANY_SECRET'];
            }
            if ($sid == 'fi') {
                $conf = Configuration::getMultiple(array('KCO_FINLAND_EID', 'KCO_FINLAND_SECRET'));
                $eid = $conf['KCO_FINLAND_EID'];
                $secret = $conf['KCO_FINLAND_SECRET'];
            }
            if ($sid == 'no') {
                $conf = Configuration::getMultiple(array('KCO_NORWAY_EID', 'KCO_NORWAY_SECRET'));
                $eid = $conf['KCO_NORWAY_EID'];
                $secret = $conf['KCO_NORWAY_SECRET'];
            }

            session_start();
            Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
            $connector = Klarna_Checkout_Connector::create($secret);

            @$checkoutId = Tools::getValue('klarna_order');
            $update = array();
            $klarnaorder = new Klarna_Checkout_Order($connector, $checkoutId);
            $klarnaorder->fetch();
            if ($klarnaorder['status'] == 'checkout_complete') {
                $id_cart = $klarnaorder['merchant_reference']['orderid2'];
                $cart = new Cart((int) ($id_cart));

                Context::getContext()->currency = new Currency((int) $cart->id_currency);

                //Check and handle errors
                if ($cart->OrderExists()) {
                    $klarna_reservation = $klarnaorder['reservation'];
                    
                    $sql = 'SELECT * FROM `'._DB_PREFIX_.'message` m '.
                    'LEFT JOIN `'._DB_PREFIX_.'orders` o ON m.id_order=o.id_order '.
                    'WHERE o.id_cart='.(int) ($id_cart);
                    
                    $messages = Db::getInstance()->ExecuteS($sql);
                    foreach ($messages as $message) {
                        //Check if reference matches
                        if (strpos($message['message'], $klarna_reservation) !== false) {
                            //Already created, send create
                            $update['status'] = 'created';
                            $update['merchant_reference'] = array(
                                'orderid1' => ''.$message['id_order'],
                                'orderid2' => ''.$cart->id,
                            );
                            $klarnaorder->update($update);
                            Logger::addLog(
                                'KCO: created sent: '.$id_cart.' res:'.$klarna_reservation,
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                            die;
                        }
                    }
                    Logger::addLog(
                        'KCO: cancel cart: '.$id_cart.' res:'.$klarna_reservation,
                        1,
                        null,
                        null,
                        null,
                        true
                    );

                    //Duplicate reservation, cancel reservation.

                    $md5key = base64_encode(pack('H*', hash('md5', $eid.':'.$klarna_reservation.':'.$secret)));
                    
                    $params = "<param><value><string>4.1</string></value></param><param><value>".
                        "<string>php:xmlrpc:1.0:test</string></value></param><param><value>".
                        "<string>$klarna_reservation</string></value></param><param><value>".
                        "<int>$eid</int></value></param><param><value><string>$md5key</string>".
                        "</value></param>";
                        
                    $request = $this->buildRequest($params, 'cancel_reservation');
                    $response = $this->sendToKlarna($request, $this->getKlarnaURL());
                    if (Tools::strlen(stristr($response, 'faultcode')) > 0) {
                        Logger::addLog(
                            'KCO: cancel reservation failed: '.$klarnaorder['reservation'],
                            1,
                            null,
                            null,
                            null,
                            true
                        );
                    }
                    die;
                }//Check and handle errors
                $shipping = $klarnaorder['shipping_address'];
                $billing = $klarnaorder['billing_address'];
                //$reference = $klarnaorder['reference'];
                $reference = $klarnaorder['reservation'];
                if (!Validate::isEmail($shipping['email'])) {
                    $shipping['email'] = 'ingen_mejl_'.$id_cart.'@ingendoman.cc';
                }
                $newsletter = 0;
                $newsletter_setting = (int)Configuration::get('KCO_ADD_NEWSLETTERBOX', null, $cart->id_shop);
                if ($newsletter_setting == 0 || $newsletter_setting == 1) {
                    if (
                            isset($klarnaorder['merchant_requested']) &&
                            isset($klarnaorder['merchant_requested']['additional_checkbox']) &&
                            $klarnaorder['merchant_requested']['additional_checkbox'] == true
                        ) {
                        $newsletter = 1;
                    }
                } elseif ($newsletter_setting == 2) {
                    $newsletter = 1;
                }
                $id_customer = (int) (Customer::customerExists($shipping['email'], true, true));
                if ($id_customer > 0) {
                    $customer = new Customer($id_customer);
                    if ($newsletter == 1) {
                        $sql_update_customer = "UPDATE "._DB_PREFIX_."customer SET newsletter=1".
                        " WHERE id_customer=$id_customer;";
                        Db::getInstance()->execute(pSQL($sql_update_customer));
                    }
                } else {
                    //add customer
                    $password = Tools::passwdGen(8);
                    $customer = new Customer();
                    $customer->firstname = $this->module->truncateValue($shipping['given_name'], 32, true);
                    $customer->lastname = $this->module->truncateValue($shipping['family_name'], 32, true);
                    $customer->email = $shipping['email'];
                    $customer->passwd = Tools::encrypt($password);
                    $customer->is_guest = 0;
                    $customer->id_default_group = (int) (Configuration::get('PS_CUSTOMER_GROUP', null, $cart->id_shop));
                    //$customer->id_default_group = (int)(Configuration::get('PS_GUEST_GROUP', null, $cart->id_shop));
                    $customer->newsletter = $newsletter;
                    $customer->optin = 0;
                    $customer->active = 1;
                    $customer->id_gender = 9;
                    $customer->add();
                    if (!$this->sendConfirmationMail($customer, $cart->id_lang, $password)) {
                        Logger::addLog(
                            'KCO: Failed sending welcome mail to: '.$shipping['email'],
                            1,
                            null,
                            null,
                            null,
                            true
                        );
                    }
                }
                //Check if address already exists, if not, add
                $delivery_address_id = 0;
                $invoice_address_id = 0;
                $shipping_iso = $country_iso_codes[$shipping['country']];
                $invocie_iso = $country_iso_codes[$billing['country']];
                $shipping_country_id = Country::getByIso($shipping_iso);
                $invocie_country_id = Country::getByIso($invocie_iso);
                if ($shipping['country'] == 'de') {
                    $shipping['street_address'] = $shipping['street_name'].' '.$shipping['street_number'];
                    $billing['street_address'] = $billing['street_name'].' '.$billing['street_number'];
                }
                
                if (!isset($shipping['care_of'])) {
                    $shipping['care_of'] = "";
                }
                if (!isset($billing['care_of'])) {
                    $billing['care_of'] = "";
                }

                foreach ($customer->getAddresses($cart->id_lang) as $address) {
                    if ($address['firstname'] == $shipping['given_name']
                    and $address['lastname'] == $shipping['family_name']
                    and $address['city'] == $shipping['city']
                    and $address['address2'] == $shipping['care_of']
                    and $address['address1'] == $shipping['street_address']
                    and $address['postcode'] == $shipping['postal_code']
                    and $address['phone_mobile'] == $shipping['phone']
                    and $address['id_country'] == $shipping_country_id) {
                        //LOAD SHIPPING ADDRESS
                        $cart->id_address_delivery = $address['id_address'];
                        $delivery_address_id = $address['id_address'];
                    }
                    if ($address['firstname'] == $billing['given_name']
                    and $address['lastname'] == $billing['family_name']
                    and $address['city'] == $billing['city']
                    and $address['address2'] == $billing['care_of']
                    and $address['address1'] == $billing['street_address']
                    and $address['postcode'] == $billing['postal_code']
                    and $address['phone_mobile'] == $billing['phone']
                    and $address['id_country'] == $invocie_country_id) {
                        //LOAD SHIPPING ADDRESS
                        $cart->id_address_invoice = $address['id_address'];
                        $invoice_address_id = $address['id_address'];
                    }
                }
                if ($invoice_address_id == 0) {
                    //Create address
                    $address = new Address();
                    $address->firstname = $this->module->truncateValue($billing['given_name'], 32, true);
                    $address->lastname = $this->module->truncateValue($billing['family_name'], 32, true);
                    if (isset($billing['care_of']) && Tools::strlen($billing['care_of']) > 0) {
                        $address->address1 = $billing['care_of'];
                        $address->address2 = $billing['street_address'];
                    } else {
                        $address->address1 = $billing['street_address'];
                    }

                    $address->postcode = $billing['postal_code'];
                    $address->phone = $billing['phone'];
                    $address->phone_mobile = $billing['phone'];
                    $address->city = $billing['city'];
                    $address->id_country = $invocie_country_id;
                    $address->id_customer = $customer->id;
                    $address->alias = 'Klarna Address';
                    $address->add();
                    $cart->id_address_invoice = $address->id;
                    $invoice_address_id = $address->id;
                }
                if ($delivery_address_id == 0) {
                    //Create address
                    $address = new Address();
                    $address->firstname = $this->module->truncateValue($shipping['given_name'], 32, true);
                    $address->lastname = $this->module->truncateValue($shipping['family_name'], 32, true);

                    if (isset($shipping['care_of']) && Tools::strlen($shipping['care_of']) > 0) {
                        $address->address1 = $shipping['care_of'];
                        $address->address2 = $shipping['street_address'];
                    } else {
                        $address->address1 = $shipping['street_address'];
                    }

                    $address->city = $shipping['city'];
                    $address->postcode = $shipping['postal_code'];
                    $address->phone = $shipping['phone'];
                    $address->phone_mobile = $shipping['phone'];
                    $address->id_country = $shipping_country_id;
                    $address->id_customer = $customer->id;
                    $address->alias = 'Klarna Address';
                    $address->add();
                    $cart->id_address_delivery = $address->id;
                    $delivery_address_id = $address->id;
                }

                $new_delivery_options = array();
                $new_delivery_options[(int) ($delivery_address_id)] = $cart->id_carrier.',';
                $new_delivery_options_serialized = serialize($new_delivery_options);
                
                $update_sql = 'UPDATE '._DB_PREFIX_.'cart '.
                        'SET delivery_option=\''.
                        pSQL($new_delivery_options_serialized).
                        '\' WHERE id_cart='.
                        (int) $cart->id;
                
                Db::getInstance()->execute($update_sql);
                
                if ($cart->id_carrier > 0) {
                    $cart->delivery_option = $new_delivery_options_serialized;
                } else {
                    $cart->delivery_option = '';
                }
                $update_sql = 'UPDATE '._DB_PREFIX_.'cart_product '.
                    'SET id_address_delivery='.(int) $delivery_address_id.
                    ' WHERE id_cart='.(int) $cart->id;
                    
                Db::getInstance()->execute($update_sql);

                $update_sql = 'UPDATE '._DB_PREFIX_.'customization '.
                    'SET id_address_delivery='.(int) $delivery_address_id.
                    ' WHERE id_cart='.(int) $cart->id;
                    
                Db::getInstance()->execute($update_sql);

                $cart->getPackageList(true);
                $cart->getDeliveryOptionList(null, true);

                $amount = (int) ($klarnaorder['cart']['total_price_including_tax']);
                $amount = (float) ($amount / 100);

                $cart->id_customer = $customer->id;
                $cart->secure_key = $customer->secure_key;
                //$cart->setNoMultishipping();
                $cart->save();

                $update_sql = 'UPDATE '._DB_PREFIX_.'cart '.
                    'SET id_customer='.(int) $customer->id.
                    ', secure_key=\''.pSQL($customer->secure_key).
                    '\' WHERE id_cart='.(int) $cart->id;
                    
                Db::getInstance()->execute($update_sql);

                if (Configuration::get('KCO_ROUNDOFF') == 1) {
                    $total_cart_price_before_round = $cart->getOrderTotal(true, Cart::BOTH);
                    $total_cart_price_after_round = round($total_cart_price_before_round);
                    $diff = abs($total_cart_price_after_round - $total_cart_price_before_round);
                    if ($diff > 0) {
                        $amount = $total_cart_price_before_round;
                    }
                }
                
                $eid = pSQL($eid);
                $reference = pSQL($reference);

                $extra = array();
                $extra['transaction_id'] = $reference;
                $cache_id = 'objectmodel_cart_'.$cart->id.'*';
                Cache::clean($cache_id);
                $cart = new Cart($cart->id);

                $id_shop = (int) $cart->id_shop;
                
                $sql = 'INSERT INTO `'._DB_PREFIX_."klarna_orders`".
                    "(eid, id_order, id_cart, id_shop, ssn, invoicenumber,risk_status ,reservation) ".
                    "VALUES('$eid', 0, ".(int) $cart->id.
                    ", $id_shop, '', '', '','$reference');";
                Db::getInstance()->execute($sql);
                    
                $this->module->validateOrder(
                    $cart->id,
                    Configuration::get('PS_OS_PAYMENT'),
                    number_format($amount, 2, '.', ''),
                    $this->module->displayName,
                    $reference,
                    $extra,
                    $cart->id_currency,
                    false,
                    $customer->secure_key
                );

                $order_reference = $this->module->currentOrder;
                if (Configuration::get('KCO_ORDERID') == 1) {
                    $order = new Order($this->module->currentOrder);
                    $order_reference = $order->reference;
                }
                $update['status'] = 'created';
                $update['merchant_reference'] = array(
                    'orderid1' => ''.$order_reference,
                    'orderid2' => ''.$cart->id,
                );
                $klarnaorder->update($update);
                
                $sql = 'UPDATE `'._DB_PREFIX_.
                        "klarna_orders` SET id_order=".
                        (int) $this->module->currentOrder.
                        " WHERE id_order=0 AND id_cart=".
                        (int) $cart->id;
                    
                Db::getInstance()->execute($sql);
            }
        } catch (Exception $e) {
            Logger::addLog('Klarna Checkout: '.htmlspecialchars($e->getMessage()), 1, null, null, null, true);
        }
    }

    //FOR CANCEL RESERVATION
    public function buildRequest($params, $function)
    {
        $request = '<?xml version="1.0" encoding="ISO-8859-1"?>'.PHP_EOL.'<methodCall>'.
        PHP_EOL.'<methodName>'.$function.'</methodName>'.
        PHP_EOL.'<params>'.PHP_EOL;
        
        $request .= $params;
        $request .= '</params>'.PHP_EOL.'</methodCall>';

        return $request;
    }
    
    public function sendToKlarna($data, $url)
    {
        $headers = array(
        'Content-Type: text/xml',
        'User-Agent: PHPRPC/1.0',
        'Content-length: '.Tools::strlen($data),
        'Connection: Close',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = utf8_encode($response);

        return $response;
    }
    public function getKlarnaURL()
    {
        if ((int)(Configuration::get('KCO_TESTMODE')) == 1) {
            $returnValue = 'https://payment.testdrive.klarna.com/';
        } else {
            $returnValue = 'https://payment.klarna.com/';
        }
        return $returnValue;
    }
    //FOR CANCEL RESERVATION

    protected function sendConfirmationMail($customer, $id_lang, $psw)
    {
        if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) {
            return true;
        }
        try {
            return Mail::Send(
                $id_lang,
                'account',
                Mail::l('Welcome!', $id_lang),
                array(
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{email}' => $customer->email,
                    '{passwd}' => $psw, ),
                $customer->email,
                $customer->firstname.' '.$customer->lastname
            );
        } catch (Exception $e) {
            Logger::addLog('Klarna Checkout: '.htmlspecialchars($e->getMessage()), 1, null, null, null, true);

            return false;
        }
    }
}

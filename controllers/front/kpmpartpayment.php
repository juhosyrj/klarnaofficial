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

class KlarnaOfficialKpmPartPaymentModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->addCSS(_MODULE_DIR_.'klarnaofficial/views/css/kpm_css.css', 'all');
        $this->addJS(_MODULE_DIR_.'klarnaofficial/views/js/kpm_common.js');
    }

    public function initContent()
    {
        parent::initContent();

        //create klarna invoice order

        $deliveryAddress = new Address($this->context->cart->id_address_delivery);
        $deliveryCountry = new Country($deliveryAddress->id_country);
        $currency = new Currency($this->context->cart->id_currency);
        $language = new Language($this->context->cookie->id_lang);
        if ($deliveryCountry->iso_code == 'se' || $deliveryCountry->iso_code == 'SE') {
            $eid = Configuration::get('KPM_SV_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_SV_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = 'SV';
            $klarnaCountry = 'SE';
        }
        if ($deliveryCountry->iso_code == 'no' || $deliveryCountry->iso_code == 'NO') {
            $eid = Configuration::get('KPM_NO_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_NO_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = 'nb';
            $klarnaCountry = 'NO';
        }
        if ($deliveryCountry->iso_code == 'de' || $deliveryCountry->iso_code == 'DE') {
            $eid = Configuration::get('KPM_DE_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_DE_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = $language->iso_code;
            $klarnaCountry = 'DE';
        }
        if ($deliveryCountry->iso_code == 'dk' || $deliveryCountry->iso_code == 'DK') {
            $eid = Configuration::get('KPM_DA_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_DA_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = $language->iso_code;
            $klarnaCountry = 'DK';
        }
        if ($deliveryCountry->iso_code == 'fi' || $deliveryCountry->iso_code == 'FI') {
            $eid = Configuration::get('KPM_FI_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_FI_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = $language->iso_code;
            $klarnaCountry = 'FI';
        }
        if ($deliveryCountry->iso_code == 'nl' || $deliveryCountry->iso_code == 'NL') {
            $eid = Configuration::get('KPM_NL_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_NL_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = $language->iso_code;
            $klarnaCountry = 'NL';
        }
        if ($deliveryCountry->iso_code == 'at' || $deliveryCountry->iso_code == 'AT') {
            $eid = Configuration::get('KPM_AT_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_AT_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $currencyIso = $currency->iso_code;
            $languageIso = $language->iso_code;
            $klarnaCountry = 'AT';
        }

        $k = $this->module->initKlarnaAPI(
            $eid,
            $sharedSecret,
            Tools::strtolower($countryIso),
            Tools::strtolower($languageIso),
            Tools::strtolower($currencyIso),
            $this->context->shop->id
        );

        if (Tools::getIsset('confirmkpm')) {
            if ($deliveryCountry->iso_code == 'se' || $deliveryCountry->iso_code == 'SE') {
                $klarnaCountry = KlarnaCountry::SE;
            }
            if ($deliveryCountry->iso_code == 'no' || $deliveryCountry->iso_code == 'NO') {
                $klarnaCountry = KlarnaCountry::NO;
            }
            if ($deliveryCountry->iso_code == 'de' || $deliveryCountry->iso_code == 'DE') {
                $klarnaCountry = KlarnaCountry::DE;
            }
            if ($deliveryCountry->iso_code == 'da' || $deliveryCountry->iso_code == 'DA') {
                $klarnaCountry = KlarnaCountry::DA;
            }
            if ($deliveryCountry->iso_code == 'nl' || $deliveryCountry->iso_code == 'NL') {
                $klarnaCountry = KlarnaCountry::NL;
            }
            if ($deliveryCountry->iso_code == 'fi' || $deliveryCountry->iso_code == 'FI') {
                $klarnaCountry = KlarnaCountry::FI;
            }
            if ($deliveryCountry->iso_code == 'at' || $deliveryCountry->iso_code == 'AT') {
                $klarnaCountry = KlarnaCountry::AT;
            }

            if (Configuration::get('KPM_INVOICEFEE', null, null, $this->context->shop->id) != ''
            && (int) Tools::getValue('kpm_pclass') == -1) {
                $invoicefee = $this->module->getByReference(
                    Configuration::get(
                        'KPM_INVOICEFEE',
                        null,
                        null,
                        $this->context->shop->id
                    )
                );
                if (Validate::isLoadedObject($invoicefee)) {
                    $insert_sql = 'INSERT INTO '._DB_PREFIX_.'cart_product 
                    (id_address_delivery, id_shop, id_cart, id_product, id_product_attribute, quantity, date_add) 
                    VALUES('.(int) $this->context->cart->id_address_delivery.','.
                    (int) $this->context->cart->id_shop.','.
                    (int) $this->context->cookie->id_cart.','.
                    (int) $invoicefee->id.',0,1,\''.pSql(date('Y-m-d h:i:s')).'\')';
                    Db::getInstance()->Execute($insert_sql);
                    $this->context->cart->update(true);
                    $this->context->cart->getPackageList(true);
                } else {
                    Logger::addLog('Klarna Invoice fee product not found', 1, null, null, null, true);
                }
            }

            $total_shipping_wt = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
            $total_shipping = (float) $this->context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING);

            $total_discounts_wt = 0;
            $total_discounts = 0;
            $cart_rules = $this->context->cart->getCartRules(CartRule::FILTER_ACTION_ALL);
            foreach ($cart_rules as $cart_rule) {
                if ($cart_rule['obj']->free_shipping) {
                    $total_shipping_wt = 0;
                } else {
                    $total_discounts_wt = $total_discounts_wt + $cart_rule['value_real'];
                    $total_discounts = $total_discounts + $cart_rule['value_tax_exc'];
                }
            }

            if ($total_shipping_wt > 0) {
                $flags = KlarnaFlags::INC_VAT | KlarnaFlags::IS_SHIPMENT;
                $shipping_tax = (float) (($total_shipping_wt - $total_shipping) / $total_shipping) * 100;
                $k->addArticle(
                    1,
                    'shipping',
                    ''.utf8_decode(pSQL(Tools::getValue('kpmshipping'))),
                    $total_shipping_wt,
                    $shipping_tax,
                    0,
                    $flags
                );
            }
            if ($this->context->cart->gift) {
                $total_wrapping_cost_wt = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
                $total_wrapping_cost = $this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING);
                $tmp = $total_wrapping_cost_wt - $total_wrapping_cost;
                $wrapping_fees_tax = (float)(($tmp) / $total_wrapping_cost) * 100;
                $flags = KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING;
                $k->addArticle(
                    1,
                    'wrapping',
                    ''.utf8_decode(pSQL(Tools::getValue('kpmwrapping'))),
                    $total_wrapping_cost_wt,
                    $wrapping_fees_tax,
                    0,
                    $flags
                );
            }
            $products = $this->context->cart->getProducts();
            $kmp_invoicefee = Configuration::get('KPM_INVOICEFEE', null, null, $this->context->shop->id);
            foreach ($products as $product) {
                if ($product['reference'] == $kmp_invoicefee && $kmp_invoicefee != '') {
                    $flags = KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING;
                } else {
                    $flags = KlarnaFlags::INC_VAT;
                }

                $name = $product['name'];

                if (isset($product['attributes'])) {
                    $name .= ' - '.$product['attributes'];
                }
                $name = strip_tags($name);
                $k->addArticle(
                    $product['cart_quantity'],
                    ''.$product['reference'],
                    ''.utf8_decode($name),
                    (float)$product['price_wt'],
                    $product['rate'],
                    0,
                    $flags
                );
            }

            if ($total_discounts_wt > 0) {
                $vatrate = (float) (($total_discounts_wt - $total_discounts) / $total_discounts) * 100;
                $flags = KlarnaFlags::INC_VAT;
                $k->addArticle(
                    1,
                    '',
                    $this->module->getL('Discount'),
                    -(float)$total_discounts_wt,
                    $vatrate,
                    0,
                    $flags
                );
            }

            $housenumber = null;
            $houseext = null;

            if (Tools::getIsset('kpm_housenumber')) {
                $housenumber = Tools::getValue('kpm_housenumber');
            }
            if (Tools::getIsset('kpm_housenumberext')) {
                $houseext = Tools::getValue('kpm_housenumberext');
            }

            //set address
            $addr = new KlarnaAddr(
                ''.utf8_decode(Tools::getValue('kpm_email')),
                ''.utf8_decode(Tools::getValue('kpm_phone')),
                ''.utf8_decode(Tools::getValue('kpm_mobilephone')),
                ''.utf8_decode(Tools::getValue('kpm_firstname')),
                ''.utf8_decode(Tools::getValue('kpm_lastname')),
                ''.utf8_decode(Tools::getValue('kpm_coname')),
                ''.utf8_decode(Tools::getValue('kpm_streetname')),
                ''.utf8_decode(Tools::getValue('kpm_zipcode')),
                ''.utf8_decode(Tools::getValue('kpm_city')),
                $klarnaCountry,
                $housenumber,
                $houseext
            );
            
            if (Tools::getValue('kpm_company') != "") {
                $addr->setCompanyName(utf8_decode(Tools::getValue('kpm_company')));
            }
            
            $k->setAddress(KlarnaFlags::IS_BILLING, $addr);
            $k->setAddress(KlarnaFlags::IS_SHIPPING, $addr);

            try {
                $gender = null;
                if (Tools::getIsset('kpm_gender')) {
                    if ((int) Tools::getValue('kpm_gender') == 1) {
                        $gender = KlarnaFlags::MALE;
                    } else {
                        $gender = KlarnaFlags::FEMALE;
                    }
                }
                $k->setEstoreInfo('', $this->context->cart->id, '');
                $payed_amount = number_format($this->context->cart->getOrderTotal(true, 3), 2, '.', '');
                $result = $k->reserveAmount(
                    ''.Tools::getValue('kpm_ssn', Tools::getValue('kpm_birthdate')),
                    $gender,
                    -1,
                    KlarnaFlags::NO_FLAG,
                    (int) Tools::getValue('kpm_pclass')
                );

                $order_status = _PS_OS_CANCELED_;
                if ((int) Tools::getValue('kpm_pclass') > 0) {
                    if ((int) $result[1] == 1) {
                        $order_status = Configuration::get('KPM_ACCEPTED_PP', null, null, $this->context->shop->id);
                    }
                    if ((int) $result[1] == 2) {
                        $order_status = Configuration::get('KPM_PENDING_PP', null, null, $this->context->shop->id);
                    }
                    $reservation_number = $result[0];
                } else {
                    if ((int) $result[1] == 1) {
                        $order_status = Configuration::get(
                            'KPM_ACCEPTED_INVOICE',
                            null,
                            null,
                            $this->context->shop->id
                        );
                    }
                    if ((int) $result[1] == 2) {
                        $order_status = Configuration::get('KPM_PENDING_INVOICE', null, null, $this->context->shop->id);
                    }
                    $reservation_number = $result[0];
                }
                $customer = new Customer((int) $this->context->cart->id_customer);
                $extra_vars = array();
                $extra_vars['transaction_id'] = $reservation_number;

                $kpm_phone = Tools::getValue('kpm_phone');
                $kpm_mobilephone = Tools::getValue('kpm_mobilephone');
                $klarna_phone = ($kpm_phone != '' ? $kpm_phone : $kpm_mobilephone);
                $this->module->changeAddressOnCart(
                    Tools::getValue('kpm_firstname'),
                    Tools::getValue('kpm_lastname'),
                    Tools::getValue('kpm_streetname'),
                    Tools::getValue('kpm_coname'),
                    Tools::getValue('kpm_company'),
                    Tools::getValue('kpm_zipcode'),
                    Tools::getValue('kpm_city'),
                    $deliveryAddress,
                    $klarna_phone
                );

                $this->context->cart->update(true);
                $this->context->cart->getPackageList(true);
                
                $id_shop = (int) $this->context->shop->id;
                $ssn = pSQL(Tools::getValue('kpm_ssn', Tools::getValue('kpm_birthdate')));
                $reservation = pSQL($reservation_number);
                $eid = pSQL($eid);
                $id_cart = (int) $this->context->cart->id;
                $sql = "INSERT INTO `"._DB_PREFIX_."klarna_orders` ".
                "(`eid`,`id_order`,`id_cart`,`id_shop`,`ssn`,`reservation`)";
                $sql .= "VALUES('$eid', 0, $id_cart, $id_shop, '$ssn', '$reservation');";
                Db::getInstance()->Execute($sql);
                
                $this->module->validateOrder(
                    $this->context->cart->id,
                    $order_status,
                    $payed_amount,
                    $this->module->displayName,
                    $reservation_number,
                    $extra_vars,
                    null,
                    false,
                    $customer->secure_key
                );

                $id_order = (int) $this->module->currentOrder;
                
                $sql = 'UPDATE `'._DB_PREFIX_.
                        "klarna_orders` SET id_order=".
                        (int) $id_order.
                        " WHERE id_order=0 AND id_cart=".
                        (int) $this->context->cart->id;
                Db::getInstance()->Execute($sql);

                $k->setEstoreInfo($id_order, $this->context->cart->id, '');
                $k->update($reservation_number);

                Tools::redirect(
                    'order-confirmation.php?key='.
                    $customer->secure_key.
                    '&id_cart='.
                    $this->context->cart->id.
                    '&id_module='.
                    $this->module->id
                );
                
            } catch (Exception $e) {
                $kpminvoicefee = Configuration::get('KPM_INVOICEFEE', null, null, $this->context->shop->id);
                if ($kpminvoicefee != '' && (int) Tools::getValue('kpm_pclass') == -1) {
                    $invoicefee = $this->module->getByReference($kpminvoicefee);
                    if (!Validate::isLoadedObject($invoicefee)) {
                        Logger::addLog('Klarna Invoice fee product not found', 1, null, null, null, true);
                    } else {
                        $sql_delete_from_cart = 'DELETE FROM '._DB_PREFIX_.'cart_product WHERE id_cart=';
                        $sql_delete_from_cart .= (int) $this->context->cookie->id_cart.' AND id_product=';
                        $sql_delete_from_cart .= (int) $invoicefee->id;
                        Db::getInstance()->Execute($sql_delete_from_cart);
                        $this->context->cart->update(true);
                        $this->context->cart->getPackageList(true);
                    }
                }
                $this->context->smarty->assign('errormsg', utf8_encode($e->getMessage()));
            }
        }

        if (isset($_COOKIE['kpm_ssn'])) {
            $kpm_ssn = $_COOKIE['kpm_ssn'];
        } else {
            $kpm_ssn = Tools::getValue('kpm_ssn', '');
        }

        $address_delivery = new Address($this->context->cart->id_address_delivery);
        $country = new Country($address_delivery->id_country);
        $customer = new Customer($this->context->cart->id_customer);
        $kpm_fields = $this->module->getRequiredKPMFields($country->iso_code);

        $kpm_gender = Tools::getValue('kpm_gender', '');
        $kpm_firstname = Tools::getValue('kpm_firstname', $address_delivery->firstname);
        $kpm_lastname = Tools::getValue('kpm_lastname', $address_delivery->lastname);
        $kpm_company = Tools::getValue('kpm_company', $address_delivery->company);
        $kpm_streetname = Tools::getValue('kpm_streetname', $address_delivery->address1);
        $kpm_coname = Tools::getValue('kpm_coname', $address_delivery->address2);
        $kpm_housenumber = Tools::getValue('kpm_housenumber', '');
        $kpm_housenumberext = Tools::getValue('kpm_housenumberext', '');
        $kpm_zipcode = Tools::getValue('kpm_zipcode', $address_delivery->postcode);
        $kpm_city = Tools::getValue('kpm_city', $address_delivery->city);
        $kpm_country = Tools::getValue('kpm_country', $country->name[$this->context->cart->id_lang]);
        $kpm_phone = Tools::getValue('kpm_phone', $address_delivery->phone);
        $kpm_mobilephone = Tools::getValue('kpm_mobilephone', $address_delivery->phone_mobile);
        $kpm_email = Tools::getValue('kpm_email', $customer->email);

        $layout = 'desktop';
        require_once _PS_TOOL_DIR_.'mobile_Detect/Mobile_Detect.php';
        $mobile_detect_class = new Mobile_Detect();
        if ($mobile_detect_class->isMobile() or $mobile_detect_class->isMobile()) {
            $layout = 'mobile';
        }

        if (configuration::get('KPM_INVOICEFEE') != '') {
            $invoicefee = $this->module->getByReference(Configuration::get('KPM_INVOICEFEE'));
            if (Validate::isLoadedObject($invoicefee)) {
                $klarna_invoice_fee = $invoicefee->getPrice();
            } else {
                $klarna_invoice_fee = 0;
            }
        } else {
            $klarna_invoice_fee = 0;
        }

        $kpm_total_order_value = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $response = $k->checkoutService(
            $kpm_total_order_value,
            $currencyIso,
            $languageIso.'_'.$countryIso
        );
        $data = $response->getData();
        if (!isset($data['payment_methods']) && !in_array($klarnaCountry, array('SE', 'NO'))) {
            
            $logourl = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.
            Tools::strtolower($languageIso).'_'.
            Tools::strtolower($countryIso).
            "/basic/blue-black.png?width=100&eid=$eid";
            
            $termsuri = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            Tools::strtolower($languageIso).'_'.
            Tools::strtolower($countryIso).'/account';

            $kpm_specials = $k->getPClasses(KlarnaPClass::SPECIAL);
            $kpm_account = $k->getPClasses(KlarnaPClass::ACCOUNT);
            $kpm_fixed = $k->getPClasses(KlarnaPClass::FIXED);
            $kpm_delay = $k->getPClasses(KlarnaPClass::DELAY);
            $kpm_invoice = $k->getPClasses(KlarnaPClass::INVOICE);
            $kpm_campaign = $k->getPClasses(KlarnaPClass::CAMPAIGN);

            $newPclass = array();
            $newPclass['title'] = $this->module->getL('Invoice');
            $newPclass['pclass_id'] = -1;
            $newPclass['use_case'] = '';
            $newPclass['extra_info'] = '';
            
            $newPclass['terms']['uri'] = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            Tools::strtolower($languageIso).'_'.
            Tools::strtolower($countryIso).'/invoice?fee='.$klarna_invoice_fee;
            
            $newPclass['logo']['uri'] = $logourl;
            $data['payment_methods'][] = $newPclass;
            $use_case = '';
            if ($languageIso == 'de') {
                $use_case = file_get_contents(dirname(__FILE__)."/../../libraries/germanterms.txt");
                $use_case = str_replace("(eid)", $eid, $use_case);
            }
            if ($languageIso == 'nl') {
                $use_case = file_get_contents(dirname(__FILE__)."/../../libraries/netherlandsterms.txt");
                $use_case = str_replace("(url)", $this->context->shop->virtual_uri, $use_case);
            }
            foreach ($kpm_invoice as $pclass) {
                $newPclass = array();
                KlarnaCalc::calc_apr(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                $monthlycost = KlarnaCalc::calc_monthly_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                KlarnaCalc::total_credit_purchase_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );

                $newPclass['pclass_id'] = $pclass->id;
                $newPclass['title'] = $pclass->description;
                $newPclass['use_case'] = $use_case;
                
                $newPclass['extra_info'] = $this->module->getL('extra_info').' '.
                $this->module->getL('interestRate').' '.
                $pclass->interestRate.'%'.' '.
                $this->module->getL('monthlyFee').' '.
                $pclass->invoiceFee.' '.$this->module->getL('monthlyCost').' '.
                $monthlycost;
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }

            foreach ($kpm_account as $pclass) {
                $newPclass = array();
                KlarnaCalc::calc_apr($kpm_total_order_value, $pclass, KlarnaFlags::CHECKOUT_PAGE);
                $monthlycost = KlarnaCalc::calc_monthly_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                KlarnaCalc::total_credit_purchase_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );

                $newPclass['pclass_id'] = $pclass->id;
                $newPclass['title'] = $pclass->description;
                $newPclass['use_case'] = $use_case;
                
                $newPclass['extra_info'] = $this->module->getL('extra_info').' '.
                $this->module->getL('interestRate').' '.
                $pclass->interestRate.'%'.' '.$this->module->getL('monthlyFee').' '.
                $pclass->invoiceFee.' '.$this->module->getL('monthlyCost').' '.
                $monthlycost;
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
            foreach ($kpm_campaign as $pclass) {
                $newPclass = array();
                KlarnaCalc::calc_apr($kpm_total_order_value, $pclass, KlarnaFlags::CHECKOUT_PAGE);
                $monthlycost = KlarnaCalc::calc_monthly_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                KlarnaCalc::total_credit_purchase_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );

                $newPclass['pclass_id'] = $pclass->id;
                $newPclass['title'] = $pclass->description;
                $newPclass['use_case'] = $use_case;
                
                $newPclass['extra_info'] = $this->module->getL('extra_info').' '.
                $this->module->getL('interestRate').' '.$pclass->interestRate.'%'.' '.
                $this->module->getL('monthlyFee').' '.$pclass->invoiceFee.' '.
                $this->module->getL('monthlyCost').' '.$monthlycost;
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
            foreach ($kpm_specials as $pclass) {
                $newPclass = array();
                KlarnaCalc::calc_apr($kpm_total_order_value, $pclass, KlarnaFlags::CHECKOUT_PAGE);
                $monthlycost = KlarnaCalc::calc_monthly_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                KlarnaCalc::total_credit_purchase_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );

                $newPclass['pclass_id'] = $pclass->id;
                $newPclass['title'] = $pclass->description;
                $newPclass['use_case'] = $use_case;
                
                $newPclass['extra_info'] = $this->module->getL('extra_info').' '.
                $this->module->getL('interestRate').' '.$pclass->interestRate.'%'.' '.
                $this->module->getL('monthlyFee').' '.$pclass->invoiceFee.' '.
                $this->module->getL('monthlyCost').' '.$monthlycost;
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
            foreach ($kpm_fixed as $pclass) {
                $newPclass = array();
                KlarnaCalc::calc_apr($kpm_total_order_value, $pclass, KlarnaFlags::CHECKOUT_PAGE);
                $monthlycost = KlarnaCalc::calc_monthly_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                KlarnaCalc::total_credit_purchase_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );

                $newPclass['pclass_id'] = $pclass->id;
                $newPclass['title'] = $pclass->description;
                $newPclass['use_case'] = $use_case;
                
                $newPclass['extra_info'] = $this->module->getL('extra_info').' '.
                $this->module->getL('interestRate').' '.
                $pclass->interestRate.'%'.' '.
                $this->module->getL('monthlyFee').' '.
                $pclass->invoiceFee.' '.
                $this->module->getL('monthlyCost').' '.$monthlycost;
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
            foreach ($kpm_delay as $pclass) {
                $newPclass = array();
                KlarnaCalc::calc_apr($kpm_total_order_value, $pclass, KlarnaFlags::CHECKOUT_PAGE);
                $monthlycost = KlarnaCalc::calc_monthly_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );
                KlarnaCalc::total_credit_purchase_cost(
                    $kpm_total_order_value,
                    $pclass,
                    KlarnaFlags::CHECKOUT_PAGE
                );

                $newPclass['pclass_id'] = $pclass->id;
                $newPclass['title'] = $pclass->description;
                $newPclass['use_case'] = $use_case;
                
                $newPclass['extra_info'] = $this->module->getL('extra_info').' '.
                $this->module->getL('interestRate').' '.
                $pclass->interestRate.'%'.' '.$this->module->getL('monthlyFee').' '.
                $pclass->invoiceFee.' '.$this->module->getL('monthlyCost').' '.
                $monthlycost;
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
        }

        $kpm_pclasses = $data['payment_methods'];

        $kpm_pclass = -1;
        $kpm_pclass = Tools::getValue('kpm_pclass', Tools::getValue('pclass', $kpm_pclass));

        if (Tools::strtolower($countryIso) == 'se' && Tools::strtolower($languageIso) != 'sv') {
            $languageIso = 'en';
        }
        if (Tools::strtolower($countryIso) == 'no') {
            if (Tools::strtolower($languageIso) == 'nn' || Tools::strtolower($languageIso) == 'no') {
                $languageIso = 'nb';
            } else {
                $languageIso = 'en';
            }
        }

        $kpm_birthdate = '';
        if ($customer->birthday != null && $customer->birthday != '' & $customer->birthday != '0000-00-00') {
            $kpm_birthdate = $customer->birthday;
        }

        if ($kpm_birthdate != "" && ($country->iso_code == "DE" || $country->iso_code == "NL")) {
            $birthday_segments = explode('-', $kpm_birthdate);
            if (count($birthday_segments) !== 3) {
                $kpm_birthdate = "";
            } else {
                list($year, $month, $day) = $birthday_segments;
                $kpm_birthdate = "$day$month$year";
            }
        }
        $this->context->smarty->assign(array(
            'kpm_md5key' => Tools::encrypt($sharedSecret),
            'kpm_birthdate' => $kpm_birthdate,
            'klarna_invoice_fee' => $klarna_invoice_fee,
            'kpm_pclass' => $kpm_pclass,
            'kpm_pclasses' => $kpm_pclasses,
            'kpm_total_order_value' => $kpm_total_order_value,
            'kpm_company' => $kpm_company,
            'kpm_gender' => $kpm_gender,
            'kpm_coname' => $kpm_coname,
            'kpm_firstname' => $kpm_firstname,
            'kpm_lastname' => $kpm_lastname,
            'kpm_streetname' => $kpm_streetname,
            'kpm_housenumber' => $kpm_housenumber,
            'kpm_housenumberext' => $kpm_housenumberext,
            'kpm_zipcode' => $kpm_zipcode,
            'kpm_city' => $kpm_city,
            'kpm_country' => $kpm_country,
            'kpm_phone' => $kpm_phone,
            'kpm_mobilephone' => $kpm_mobilephone,
            'kpm_email' => $kpm_email,
            'kpm_fields' => $kpm_fields,
            'kpm_ssn' => $kpm_ssn,
            'kpm_iso_code_terms' => $languageIso.'_'.$countryIso,
            'kpm_eid' => $eid,
            'kpm_terms_layout' => $layout,
            'kpm_iso_code' => $country->iso_code,
            'kpm_postback_url' => $this->context->link->getModuleLink('klarnaofficial', 'kpmpartpayment'),
            'kpm_getaddress_url' => $this->context->link->getModuleLink('klarnaofficial', 'kpmgetaddress'),
        ));

        $this->setTemplate('kpm_partpayment.tpl');
    }
}

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
        $currencyIso = $currency->iso_code;
        $languageIso = $language->iso_code;
            
        if ($deliveryCountry->iso_code == 'se' || $deliveryCountry->iso_code == 'SE') {
            $eid = Configuration::get('KPM_SV_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_SV_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $languageIso = 'sv';
        }
        if ($deliveryCountry->iso_code == 'no' || $deliveryCountry->iso_code == 'NO') {
            $eid = Configuration::get('KPM_NO_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_NO_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $languageIso = 'nb';
        }
        if ($deliveryCountry->iso_code == 'de' || $deliveryCountry->iso_code == 'DE') {
            $eid = Configuration::get('KPM_DE_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_DE_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $languageIso = 'de';
        }
        if ($deliveryCountry->iso_code == 'dk' || $deliveryCountry->iso_code == 'DK') {
            $eid = Configuration::get('KPM_DA_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_DA_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $languageIso = 'da';
        }
        if ($deliveryCountry->iso_code == 'fi' || $deliveryCountry->iso_code == 'FI') {
            $eid = Configuration::get('KPM_FI_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_FI_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            if ($languageIso != 'sv' && $languageIso != 'fi') {
                $languageIso = 'fi';
            }
        }
        if ($deliveryCountry->iso_code == 'nl' || $deliveryCountry->iso_code == 'NL') {
            $eid = Configuration::get('KPM_NL_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_NL_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $languageIso = 'nl';
        }
        if ($deliveryCountry->iso_code == 'at' || $deliveryCountry->iso_code == 'AT') {
            $eid = Configuration::get('KPM_AT_EID', null, null, $this->context->shop->id);
            $sharedSecret = Configuration::get('KPM_AT_SECRET', null, null, $this->context->shop->id);
            $countryIso = $deliveryCountry->iso_code;
            $languageIso = 'de';
        }

        $k = $this->module->initKlarnaAPI(
            $eid,
            $sharedSecret,
            Tools::strtolower($countryIso),
            Tools::strtolower($languageIso),
            Tools::strtolower($currencyIso),
            $this->context->shop->id
        );

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
        
        if (Tools::getIsset('confirmkpm')) {
            $ssn = ''.Tools::getValue('kpm_ssn', Tools::getValue('kpm_birthdate'));
        }
        
        if (Tools::getIsset('confirmkpm')) {
            if ($ssn != '') {
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
                    $carrier = new Carrier($this->context->cart->id_carrier);
                    $carrieraddress = new Address($this->context->cart->id_address_delivery);
                    $carriertaxrate = $carrier->getTaxesRate($carrieraddress);
                    $shippingReference = $this->module->shippingreferences[$languageIso];
                    
                    $k->addArticle(
                        1,
                        $shippingReference,
                        ''.utf8_decode(pSQL(Tools::getValue('kpmshipping'))),
                        $total_shipping_wt,
                        (int) ($carriertaxrate),
                        0,
                        $flags
                    );
                }
                if ($this->context->cart->gift) {
                    $wrappingreference = $this->module->wrappingreferences[$languageIso];
                    $total_wrapping_cost_wt = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
                    $total_wrapping_cost = $this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING);
                    $tmp = $total_wrapping_cost_wt - $total_wrapping_cost;
                    $wrapping_fees_tax = (float)(($tmp) / $total_wrapping_cost) * 100;
                    $wrapping_fees_tax = $this->cutNum($wrapping_fees_tax);
                    $wrapping_fees_tax = Tools::ps_round($wrapping_fees_tax, 2);
                    $flags = KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING;
                    $k->addArticle(
                        1,
                        $wrappingreference,
                        ''.utf8_decode(pSQL(Tools::getValue('kpmwrapping'))),
                        $total_wrapping_cost_wt,
                        $wrapping_fees_tax,
                        0,
                        $flags
                    );
                }
                $lastrate = "notset";
                $has_different_rates = false;
                $products = $this->context->cart->getProducts();
                $kmp_invoicefee = Configuration::get('KPM_INVOICEFEE', null, null, $this->context->shop->id);
                foreach ($products as $product) {
                    if ($product['reference'] == $kmp_invoicefee && $kmp_invoicefee != '') {
                        $flags = KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING;
                    } else {
                        $flags = KlarnaFlags::INC_VAT;
                        if ($lastrate == "notset") {
                            $lastrate = $product['rate'];
                        } elseif ($lastrate != $product['rate']) {
                            $has_different_rates = true;
                        }
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
                    if ($has_different_rates == false) {
                        $discount_tax_rate = Tools::ps_round($lastrate, 2);
                    } else {
                        $discount_tax_rate = (($total_discounts_wt / $total_discounts) - 1) * 100;
                        $discount_tax_rate = $this->cutNum($discount_tax_rate);
                        $discount_tax_rate = Tools::ps_round($discount_tax_rate, 2);
                    }
                        
                    //$vatrate = (float) (($total_discounts_wt - $total_discounts) / $total_discounts) * 100;
                    $flags = KlarnaFlags::INC_VAT;
                    $k->addArticle(
                        1,
                        '',
                        $this->module->l('Discount', 'kpmpartpayment'),
                        -(float)$total_discounts_wt,
                        $discount_tax_rate,
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
                
                if (Tools::getValue('kpm_company')!="") {
                    $addr->setCompanyName(utf8_decode(Tools::getValue('kpm_company')));
                    $addr->isCompany = true;
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
                    
                    
                    if ($klarnaCountry == KlarnaCountry::DK) {
                        $ssn = str_replace("-", "", $ssn);
                    }
                    $result = $k->reserveAmount(
                        $ssn,
                        $gender,
                        -1,
                        KlarnaFlags::NO_FLAG,
                        (int) Tools::getValue('kpm_pclass')
                    );

                    $order_status = _PS_OS_CANCELED_;
                    $risk_status = '';
                    
                    if ((int) Tools::getValue('kpm_pclass') > 0) {
                        if ((int) $result[1] == 1) {
                            $order_status = Configuration::get('KPM_ACCEPTED_PP', null, null, $this->context->shop->id);
                        }
                        if ((int) $result[1] == 2) {
                            $order_status = Configuration::get('KPM_PENDING_PP', null, null, $this->context->shop->id);
                            $risk_status = $this->module->Pending_risk;
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
                            $order_status = Configuration::get(
                                'KPM_PENDING_INVOICE',
                                null,
                                null,
                                $this->context->shop->id
                            );
                            $risk_status = $this->module->Pending_risk;
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
                    "(`eid`,`id_order`,`id_cart`,`id_shop`,`ssn`,`reservation`, `risk_status`)";
                    $sql .= "VALUES('$eid', 0, $id_cart, $id_shop, '$ssn', '$reservation', '$risk_status');";
                    Db::getInstance()->Execute($sql);
                    
                    //Cache::clean('*');
                    /*Try to move this to a new controller or some kind of reload,
                    that should in theory fix all cache issue with prestashop cart.*/
                    
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
            } else {
                $this->context->smarty->assign('errormsg', $this->module->l('Missing field SSN', 'kpmpartpayment'));
            }
        }

        if (isset(Context::getContext()->cookie->kpm_ssn)) {
            $kpm_ssn = Context::getContext()->cookie->kpm_ssn;
        } else {
            $kpm_ssn = Tools::getValue('kpm_ssn', '');
        }

        $sameAddress = true;
        $companyAddress = false;
        if ($this->context->cart->id_address_delivery != $this->context->cart->id_address_invoice) {
            $sameAddress = false;
        }
        $address_delivery = new Address($this->context->cart->id_address_delivery);
        
        if ($address_delivery->company != "") {
            $companyAddress = true;
        }
        $country = new Country($address_delivery->id_country);
        $customer = new Customer($this->context->cart->id_customer);
        $kpm_fields = $this->module->getRequiredKPMFields($country->iso_code);

        $preselgender = "";
        if ($customer->id_gender>0) {
            $gender = new Gender($customer->id_gender);
            if ((int)$gender->type == 1) {
                $preselgender = 2;
            } elseif ((int)$gender->type == 0) {
                $preselgender = 1;
            }
        }
        $kpm_gender = Tools::getValue('kpm_gender', $preselgender);
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
                $klarna_invoice_fee = Tools::ps_round($invoicefee->getPrice(), 2);
            } else {
                $klarna_invoice_fee = 0;
            }
        } else {
            $klarna_invoice_fee = 0;
        }

        
        $logourl = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.
            Tools::strtolower($languageIso).'_'.
            Tools::strtolower($countryIso).
            "/basic/blue-black.png?width=100&eid=$eid";
            
        $termsuri = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            Tools::strtolower($languageIso).'_'.
            Tools::strtolower($countryIso).'/account';
            
        $kpm_total_order_value = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $kpm_expected_currency = "";
        $kpm_expected_language = array("");
        if ($klarnaCountry == KlarnaCountry::NL) {
            $data = array();
            $kpm_expected_currency = "EUR";
            $kpm_expected_language_display = "Nederlands";
            $kpm_expected_language = array("nl");
            
            $logourl = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.
            "nl_nl/basic/blue-black.png?width=100&eid=$eid";
            
            $termsuri = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            "nl_nl/account";
            
            if ($currencyIso == $kpm_expected_currency) {
                $kpm_account = $k->getPClasses(KlarnaPClass::ACCOUNT);
            } else {
                $kpm_account = array();
            }
            
            $newPclass = array();
            $newPclass['title'] = 'Binnen 14 dagen';
            $newPclass['group']['title'] = 'Achteraf betalen';
            $newPclass['pclass_id'] = -1;
            $newPclass['use_case'] = '';
            $newPclass['extra_info'] = '';
            
            if ($klarna_invoice_fee>0) {
                $invoicefeestring = '?fee='.$klarna_invoice_fee;
            } else {
                $invoicefeestring = '';
            }
            
            $klarna_locale = $this->module->getKlarnaLocale();
            $newPclass['terms']['uri'] = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            $klarna_locale.'/invoice'.$invoicefeestring;
            
            $newPclass['logo']['uri'] = $logourl;
            $data['payment_methods'][] = $newPclass;
            
            $use_case = '';
            $use_case = Tools::file_get_contents(dirname(__FILE__)."/../../libraries/netherlandsterms.txt");
            $use_case = str_replace("(url)", $this->context->shop->virtual_uri, $use_case);
            $this->context->smarty->assign('special_usecase', $use_case);
            
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
                $newPclass['group']['title'] = 'Gespreid betalen';
                $newPclass['title'] = 'Flexibel, in uw eigen tempo betalen';
                
                $newPclass['extra_info'] = 'Jaarlijkse rente '.
                $pclass->interestRate.'%'.' Factuurkosten '.
                Tools::displayPrice($pclass->invoiceFee).' Maandelijkse betaling vanaf '.
                Tools::displayPrice($monthlycost);
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
            
            $this->context->smarty->assign('terms_account', "Lees meer!");
            $this->context->smarty->assign('terms_invoice', "Factuurvoorwaarden");
            
            
        } elseif ($klarnaCountry == KlarnaCountry::FI) {
            $kpm_expected_language = array("fi", "sv");
            $kpm_expected_language_display = "Sumoi, Svenska";
            $kpm_expected_currency = "EUR";
            
            if ($currencyIso == $kpm_expected_currency) {
                $kpm_account = $k->getPClasses(KlarnaPClass::ACCOUNT);
            } else {
                $kpm_account = array();
            }
            
            $newPclass = array();
            $newPclass['title'] = 'Lasku';
            $newPclass['group']['title'] = 'Maksa 14 pälvän kuluessa';
            $newPclass['pclass_id'] = -1;
            $newPclass['use_case'] = '';
            $newPclass['extra_info'] = '';
            
            if ($klarna_invoice_fee>0) {
                $invoicefeestring = '?fee='.$klarna_invoice_fee;
            } else {
                $invoicefeestring = '';
            }
            
            $klarna_locale = $this->module->getKlarnaLocale();
            $newPclass['terms']['uri'] = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            $klarna_locale.'/invoice'.$invoicefeestring;
            
            $newPclass['logo']['uri'] = $logourl;
            $data['payment_methods'][] = $newPclass;
            
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
                $newPclass['group']['title'] = 'Erämaksu';
                $newPclass['extra_info'] = 'Vuosikorko '.
                $pclass->interestRate.'%'.' Hallinnointimaksu '.
                Tools::displayPrice($pclass->invoiceFee).' Kuukausikustannus '.
                Tools::displayPrice($monthlycost);
                
                $newPclass['terms']['uri'] = $termsuri;
                $newPclass['logo']['uri'] = $logourl;
                $data['payment_methods'][] = $newPclass;
            }
            
            $this->context->smarty->assign('terms_account', "Lule lisää");
            $this->context->smarty->assign('terms_invoice', "Lule lisää");
            
            
        } elseif ($klarnaCountry == KlarnaCountry::AT) {
            $kpm_expected_language = array("de");
            $kpm_expected_language_display = "Deutch";
            $kpm_expected_currency = "EUR";
            
            $logourl = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.
            "de_at/basic/blue-black.png?width=100&eid=$eid";
            
            $termsuri = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            "de_at/account";
            
            $newPclass = array();
            $newPclass['title'] = 'Rechnung';
            $newPclass['group']['title'] = 'In 14 Tagen bezahlen';
            $newPclass['pclass_id'] = -1;
            $newPclass['use_case'] = '';
            $newPclass['extra_info'] = '';
            
            if ($klarna_invoice_fee>0) {
                $invoicefeestring = '?fee='.$klarna_invoice_fee;
            } else {
                $invoicefeestring = '';
            }
            
            $klarna_locale = $this->module->getKlarnaLocale();
            $newPclass['terms']['uri'] = "https://cdn.klarna.com/1.0/shared/content/legal/terms/$eid/".
            $klarna_locale.'/invoice'.$invoicefeestring;
            
            $newPclass['logo']['uri'] = $logourl;
            $data['payment_methods'][] = $newPclass;

            $use_case = Tools::file_get_contents(dirname(__FILE__)."/../../libraries/austriaterms.txt");
            $cms = new CMS(
                (int) (Configuration::get('PS_CONDITIONS_CMS_ID')),
                (int) ($this->context->cookie->id_lang)
            );
            
            
            $link_conditions = $this->context->link->getCMSLink(
                $cms,
                $cms->link_rewrite,
                Configuration::get('PS_SSL_ENABLED')
            );
            if (!strpos($link_conditions, '?')) {
                $link_conditions .= '?content_only=1';
            } else {
                $link_conditions .= '&content_only=1';
            }
            
            $use_case = str_replace("(storeterms)", $link_conditions, $use_case);
            $use_case = str_replace("(eid)", $eid, $use_case);
            $this->context->smarty->assign('special_usecase', $use_case);

            $this->context->smarty->assign('terms_invoice', "Rechnungsbedingungen");
            
        } elseif (in_array($klarnaCountry, array(KlarnaCountry::SE, KlarnaCountry::NO, KlarnaCountry::DE))) {
            if ($klarnaCountry == KlarnaCountry::DE) {
                $kpm_expected_currency = "EUR";
                $pclassiso = "de_de";
                $kpm_expected_language = array("de");
                $kpm_expected_language_display = "Deutch";
            } elseif ($klarnaCountry == KlarnaCountry::NO) {
                $kpm_expected_currency = "NOK";
                $pclassiso = "nb_no";
                $kpm_expected_language = array("nb", "no");
                $kpm_expected_language_display = "Norsk";
            } elseif ($klarnaCountry == KlarnaCountry::SE) {
                $kpm_expected_currency = "SEK";
                $pclassiso = "sv_se";
                $kpm_expected_language = array("sv");
                $kpm_expected_language_display = "Svenska";
            } else {
                $pclassiso = $languageIso.'_'.$countryIso;
            }
            $response = $k->checkoutService(
                $kpm_total_order_value,
                $currencyIso,
                $pclassiso
            );
            $data = $response->getData();
            
            /*Add a check here too see if pclass is active in shop*/
            $sql_to_check = "SELECT GROUP_CONCAT(id) FROM `"._DB_PREFIX_."kpmpclasses`".
                "WHERE eid=$eid AND country=$klarnaCountry";
                
            $active_pclasses_string = Db::getInstance()->getValue($sql_to_check);
            $active_pclasses = explode(",", $active_pclasses_string);
            $active_pclasses[] = -1;
            foreach ($data['payment_methods'] as $pclasskey => $pclass) {
                if (!in_array($pclass["pclass_id"], $active_pclasses)) {
                    unset($data['payment_methods'][$pclasskey]);
                }
            }

            if ($klarnaCountry == KlarnaCountry::DE) {
                $use_case = Tools::file_get_contents(dirname(__FILE__)."/../../libraries/germanterms.txt");
                $use_case = str_replace("(eid)", $eid, $use_case);
                $this->context->smarty->assign('special_usecase', $use_case);
                //$this->context->smarty->assign('terms_account', "Weitere Informationen");
                $this->context->smarty->assign('terms_account', "Lesen Sie mehr");
                $this->context->smarty->assign('terms_invoice', "Bedingungen");
            }
        }

        $kpm_pclasses = $data['payment_methods'];

        $kpm_pclass = -1;
        $kpm_pclass = Tools::getValue('kpm_pclass', Tools::getValue('pclass', $kpm_pclass));

        /*if (Tools::strtolower($countryIso) == 'se' && Tools::strtolower($languageIso) != 'sv') {
            $languageIso = 'en';
        }
        if (Tools::strtolower($countryIso) == 'no') {
            if (Tools::strtolower($languageIso) == 'nn' || Tools::strtolower($languageIso) == 'no') {
                $languageIso = 'nb';
            } else {
                $languageIso = 'en';
            }
        }*/

        $kpm_birthdate = '';
        if ($customer->birthday != null &&
            $customer->birthday != '' &&
            $customer->birthday != '0000-00-00'
        ) {
            $kpm_birthdate = $customer->birthday;
        }

        if ($kpm_birthdate != "" &&
            ($country->iso_code == "DE" || $country->iso_code == "NL" || $country->iso_code == "AT")
        ) {
            $birthday_segments = explode('-', $kpm_birthdate);
            if (count($birthday_segments) !== 3) {
                $kpm_birthdate = "";
            } else {
                list($year, $month, $day) = $birthday_segments;
                $kpm_birthdate = "$day$month$year";
            }
        }

        $invoiceIsdisabled = Configuration::get('KPM_DISABLE_INVOICE', null, null, $this->context->shop->id);
        
        if ($country->iso_code == "DE" || $country->iso_code == "NL") {
            if ($kpm_housenumber == "" || $kpm_housenumberext == "") {
                $streetarray = $this->splitStreet($kpm_streetname);
                $kpm_housenumberext = $streetarray["numberAddition"];
                $kpm_housenumber = $streetarray["number"];
                $kpm_streetname = $streetarray["street"];
            }
        }
        
        if ($currencyIso != $kpm_expected_currency) {
            $currencymismatch = true;
        } else {
            $currencymismatch = false;
        }
        if (!in_array(Tools::strtolower($languageIso), $kpm_expected_language)) {
            $languagemismatch = true;
        } else {
            $languagemismatch = false;
        }
        $kpm_postback_url = $this->context->link->getModuleLink('klarnaofficial', 'kpmpartpayment', array(), true);
        $kpm_getaddress_url = $this->context->link->getModuleLink('klarnaofficial', 'kpmgetaddress', array(), true);
        
        $this->context->smarty->assign(array(
            'kpm_md5key' => Tools::encrypt($sharedSecret),
            'kpm_birthdate' => $kpm_birthdate,
            'currencymismatch' => $currencymismatch,
            'kpm_expected_language_display' => $kpm_expected_language_display,
            'languagemismatch' => $languagemismatch,
            'sameAddress' => $sameAddress,
            'companyAddress' => $companyAddress,
            'invoiceIsdisabled' => $invoiceIsdisabled,
            'klarna_invoice_fee' => $klarna_invoice_fee,
            'kpm_pclass' => $kpm_pclass,
            'kpm_expected_currency' => $kpm_expected_currency,
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
            'kpm_iso_code' => Tools::strtolower($country->iso_code),
            'kpm_postback_url' => $kpm_postback_url,
            'kpm_getaddress_url' => $kpm_getaddress_url,
        ));

        $this->setTemplate('kpm_partpayment.tpl');
    }
    public function cutNum($num, $precision = 2)
    {
        return floor($num).Tools::substr($num-floor($num), 1, $precision+1);
    }
    
    public function splitStreet($streetStr)
    {
        $aMatch         = array();
        $pattern        = '#^([\w[:punct:] ]+) ([0-9]{1,5})([ \w[:punct:]\-/]*)$#';
        $matchResult    = preg_match($pattern, $streetStr, $aMatch);
        if ($matchResult == 0) {
            $pattern = '/([^\d]+)\s?(.+)/i';
            $matchResult = preg_match($pattern, $streetStr, $aMatch);
        }

        $street         = (isset($aMatch[1])) ? $aMatch[1] : $streetStr;
        $number         = (isset($aMatch[2])) ? $aMatch[2] : '';
        $numberAddition = (isset($aMatch[3])) ? $aMatch[3] : '';
        return array('street' => $street, 'number' => $number, 'numberAddition' => $numberAddition);
    }
}

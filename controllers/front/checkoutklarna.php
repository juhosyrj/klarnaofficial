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
 
class KlarnaOfficialCheckoutKlarnaModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function setMedia()
    {
        parent::setMedia();
        //if ($this->context->getMobileDevice() == false)
        //	$this->addJqueryPlugin(array('fancybox'));
        $this->context->controller->addCSS(_MODULE_DIR_.'klarnaofficial/views/css/klarnacheckout.css', 'all');
        $this->addJS(_MODULE_DIR_.'klarnaofficial/views/js/klarna_checkout.js');
    }

	public function pushPostcode($postcode = "-1")
	{
		
		if ($postcode == "-1") {
			if (isset($_GET['zip'])) {
				$postcode = $_GET['zip'];
			} else {
				return false;
			}
		}
		
		$currency = new Currency($this->context->cart->id_currency);
		$language = new Language($this->context->cart->id_lang);
		$country_information = $this->getKlarnaCountryInformation($currency->iso_code, $language->iso_code);

        if ($country_information === false) {
            return false;
        }
		
		$alias = 'KCO_UNDEFINED';
				
		if ($country_information['purchase_country'] == 'SE') { $alias = 'KCO_SVERIGE_DEFAULT'; }
		elseif ($country_information['purchase_country'] == 'FI') { $alias = 'KCO_FINLAND_DEFAULT'; }
        elseif ($country_information['purchase_country'] == 'NO') { $alias = 'KCO_NORGE_DEFAULT'; }
        elseif ($country_information['purchase_country'] == 'DE') { $alias = 'KCO_GERMANY_DEFAULT'; }

        $update_sql = 'UPDATE '._DB_PREFIX_."address ".
			"SET postcode='" . $postcode . "'".
			"WHERE alias='" . $alias . "'";

        Db::getInstance()->execute($update_sql);
	}
	
    public function postProcess()
    {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        } else {
            if (session_id() === '') {
                session_start();
            }
        }
		
        if (Tools::isSubmit('savepostcodebutton')) {
            $postcode = strip_tags(Tools::getValue('postcode'));
			$this->pushPostcode($postcode);

			if ($this->validateDeliveryOption(Tools::getValue('delivery_option'))) {
                $this->context->cart->setDeliveryOption(Tools::getValue('delivery_option'));
            }

            if (!$this->context->cart->update()) {
                $this->context->smarty->assign(array(
                    'vouchererrors' => Tools::displayError('Could not save carrier selection'),
                ));
            }
			
			// Carrier has changed, so we check if the cart rules still apply
            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);
			
			Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna&zip=' . $postcode . '#KCO_shipping_start');
		}
		
        if (Tools::isSubmit('kco_change_country')) {
            $id_lang = 0;
            $id_currency = 0;
            if (Tools::getValue('kco_change_country') == 'gb') {
                $id_lang = Language::getIdByIso('en');
                $id_currency = Currency::getIdByIsoCode('GBP');
                $id_tmp_address = Configuration::get('KCO_UK_ADDR');
				
				$_GET['id_lang'] = $id_lang;
                $_POST['id_lang'] = $id_lang;
                $_POST['id_currency'] = $id_currency;
                $_POST['SubmitCurrency'] = $id_currency;
                Tools::switchLanguage();
                Tools::setCurrency($this->context->cookie);
                $this->context->cart->id_lang = $id_lang;
                $this->context->cart->id_currency = $id_currency;
                $this->context->cart->id_address_delivery = $id_tmp_address;
                $this->context->cart->update();
				
                if (isset($_SESSION['klarna_checkout'])) {
                    unset($_SESSION['klarna_checkout']);
                }

                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarnauk');
            }
            if (Tools::getValue('kco_change_country') == 'sv') {
				$this->context->smarty->assign('show_postcode_field', false);
                $id_lang = Language::getIdByIso('sv');
                if ((int) ($id_lang) == 0) { $id_lang = Language::getIdByIso('en'); }
                $id_currency = Currency::getIdByIsoCode('SEK');
                $id_tmp_address = Configuration::get('KCO_SWEDEN_ADDR');
            }
            if (Tools::getValue('kco_change_country') == 'fi') {
				$this->context->smarty->assign('show_postcode_field', true);
                $id_lang = Language::getIdByIso('fi');
                if ((int) ($id_lang) == 0) { $id_lang = Language::getIdByIso('en'); }
                $id_currency = Currency::getIdByIsoCode('EUR');
                $id_tmp_address = Configuration::get('KCO_FINLAND_ADDR');
            }
            if (Tools::getValue('kco_change_country') == 'de') {
				$this->context->smarty->assign('show_postcode_field', false);
                $id_lang = Language::getIdByIso('de');
                if ((int) ($id_lang) == 0) { $id_lang = Language::getIdByIso('en'); }
                $id_currency = Currency::getIdByIsoCode('EUR');
                $id_tmp_address = Configuration::get('KCO_GERMANY_ADDR');
            }
            if (Tools::getValue('kco_change_country') == 'at') {
				$this->context->smarty->assign('show_postcode_field', false);
                $id_lang = Language::getIdByIso('de');
                if ((int) ($id_lang) == 0) { $id_lang = Language::getIdByIso('en'); }
                $id_currency = Currency::getIdByIsoCode('EUR');
                $id_tmp_address = Configuration::get('KCO_AUSTRIA_ADDR');
            }
            if (Tools::getValue('kco_change_country') == 'no') {
				$this->context->smarty->assign('show_postcode_field', false);
                $id_lang = Language::getIdByIso('no');
                if ((int) $id_lang == 0) { $id_lang = Language::getIdByIso('nb'); }
                if ((int) $id_lang == 0) { $id_lang = Language::getIdByIso('nn'); }
                if ((int) ($id_lang) == 0) { $id_lang = Language::getIdByIso('en'); }
                $id_currency = Currency::getIdByIsoCode('NOK');
                $id_tmp_address = Configuration::get('KCO_NORWAY_ADDR');
            }
            
            if ($id_lang > 0 and $id_currency > 0) {
                $_GET['id_lang'] = $id_lang;
                $_POST['id_lang'] = $id_lang;
                $_POST['id_currency'] = $id_currency;
                $_POST['SubmitCurrency'] = $id_currency;
                Tools::switchLanguage();
                Tools::setCurrency($this->context->cookie);
                $this->context->cart->id_lang = $id_lang;
                $this->context->cart->id_currency = $id_currency;
                $this->context->cart->id_address_delivery = $id_tmp_address;
                $this->context->cart->update();
                //KILL THE SESSION TO START A NEW
                if (isset($_SESSION['klarna_checkout'])) {
                    unset($_SESSION['klarna_checkout']);
                }

				// reset carrier info
				$this->context->cart->setDeliveryOption(null);

				if (!$this->context->cart->update()) {
					$this->context->smarty->assign(array(
						'vouchererrors' => Tools::displayError('Could not save carrier selection'),
					));
				}
				
				// Carrier has changed, so we check if the cart rules still apply
				CartRule::autoRemoveFromCart($this->context);
				CartRule::autoAddToCart($this->context);
				
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        }
        if (Tools::isSubmit('savemessagebutton')) {
            $messageContent = Tools::getValue('message');
            $message_result = $this->updateMessage($messageContent, $this->context->cart);
            if (!$message_result) {
                $this->context->smarty->assign('gift_error', Tools::displayError('Invalid message'));
            }
        }
        if (Tools::isSubmit('savegift')) {
            $this->context->cart->gift = (int) (Tools::getValue('gift'));
            $gift_error = '';
            if (!Validate::isMessage($_POST['gift_message'])) {
                $gift_error = Tools::displayError('Invalid gift message');
            } else {
                $this->context->cart->gift_message = strip_tags(Tools::getValue('gift_message'));
            }
            $this->context->cart->update();
            $this->context->smarty->assign('gift_error', $gift_error);
        }
        if (CartRule::isFeatureActive()) {
            $vouchererrors = '';
            if (Tools::isSubmit('submitAddDiscount')) {
                $code = trim(Tools::getValue('discount_name'));
                $code = Tools::purifyHTML($code);
                if (!($code)) {
                    $vouchererrors = Tools::displayError('You must enter a voucher code');
                } elseif (!Validate::isCleanHtml($code)) {
                    $vouchererrors = Tools::displayError('Voucher code invalid');
                } else {
                    if (($cartRule = new CartRule(CartRule::getIdByCode($code))) &&
                        Validate::isLoadedObject($cartRule)) {
                        if ($error = $cartRule->checkValidity($this->context, false, true)) {
                            $vouchererrors = $error;
                        } else {
                            $this->context->cart->addCartRule($cartRule->id);
                            Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
                        }
                    } else {
                        $vouchererrors = Tools::displayError('This voucher does not exists');
                    }
                }
                //FORCE html_entity_decode SINCE PRESTASHOP Demand escape:html
                //in tpl files but already does this on displayError..
                $this->context->smarty->assign(array(
                    'vouchererrors' => html_entity_decode($vouchererrors),
                    'discount_name' => Tools::safeOutput($code),
                ));
            } elseif (($id_cart_rule = (int) Tools::getValue('deleteDiscount')) &&
            Validate::isUnsignedId($id_cart_rule)) {
                $this->context->cart->removeCartRule($id_cart_rule);
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        }

        if (Tools::getIsset('delivery_option')) {
            if ($this->validateDeliveryOption(Tools::getValue('delivery_option'))) {
                $this->context->cart->setDeliveryOption(Tools::getValue('delivery_option'));
            }

            if (!$this->context->cart->update()) {
                $this->context->smarty->assign(array(
                    'vouchererrors' => Tools::displayError('Could not save carrier selection'),
                ));
            }

			// get the zip code
			$postcode = strip_tags(Tools::getValue('postcode'));
			$this->pushPostcode($postcode);
			
            // Carrier has changed, so we check if the cart rules still apply
            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);
			
			// refresh with zip
			Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna&zip=' . $postcode . '#KCO_shipping_start');
        }
    }

    public function initContent()
    {
        parent::initContent();

		// lets assume that the customer is Finnish
		$this->context->smarty->assign('show_postcode_field', true);
		
        //Make a check on reload
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);
            
        $checkoutcart = array();
        $update = array();
        $create  = array();
        
        if (Tools::getIsset('kco_update') and Tools::getValue('kco_update') == '1') {
            if ($this->context->cart->nbProducts() < 1) {
                die;
            }
        }

        if (!isset($this->context->cart->id)) {
            Tools::redirect('index.php');
        }

        $currency = new Currency($this->context->cart->id_currency);
        $language = new Language($this->context->cart->id_lang);

        $country_information = $this->getKlarnaCountryInformation($currency->iso_code, $language->iso_code);

        if ($country_information === false) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $tmp_address = new Address((int) ($this->context->cart->id_address_delivery));
        $country = new Country($tmp_address->id_country);
        if ($country_information['purchase_country'] == 'SE') {
			$this->context->smarty->assign('show_postcode_field', false);
            $eid = (int) (Configuration::get('KCO_SWEDEN_EID'));
            $sharedSecret = Configuration::get('KCO_SWEDEN_SECRET');
            $ssid = 'se';
            if ($country->iso_code != 'SE') {
                if ($this->context->cart->id_address_delivery==Configuration::get('KCO_SWEDEN_ADDR')) {
                    $this->module->createAddress(
                        'SE',
                        'KCO_SWEDEN_ADDR',
                        'Stockholm',
                        'Sverige',
                        'KCO_SVERIGE_DEFAULT'
                    );
                }
                $this->context->cart->id_address_delivery = Configuration::get('KCO_SWEDEN_ADDR');
                $this->context->cart->update();
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        } elseif ($country_information['purchase_country'] == 'FI') {
			$this->context->smarty->assign('show_postcode_field', true);
            $eid = (int) (Configuration::get('KCO_FINLAND_EID'));
            $sharedSecret = Configuration::get('KCO_FINLAND_SECRET');
            $ssid = 'fi';
            if ($country->iso_code != 'FI') {
                if ($this->context->cart->id_address_delivery==Configuration::get('KCO_FINLAND_ADDR')) {
                    $this->module->createAddress(
                        'FI',
                        'KCO_FINLAND_ADDR',
                        'Helsinki',
                        'Finland',
                        'KCO_FINLAND_DEFAULT'
                    );
                }
                $this->context->cart->id_address_delivery = Configuration::get('KCO_FINLAND_ADDR');
                $this->context->cart->update();
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        } elseif ($country_information['purchase_country'] == 'NO') {
			$this->context->smarty->assign('show_postcode_field', false);
            $eid = (int) (Configuration::get('KCO_NORWAY_EID'));
            $sharedSecret = Configuration::get('KCO_NORWAY_SECRET');
            $ssid = 'no';
            if ($country->iso_code != 'NO') {
                
                if ($this->context->cart->id_address_delivery==Configuration::get('KCO_NORWAY_ADDR')) {
                    $this->module->createAddress(
                        'NO',
                        'KCO_NORWAY_ADDR',
                        'Oslo',
                        'Norge',
                        'KCO_NORGE_DEFAULT'
                    );
                }
                
                $this->context->cart->id_address_delivery = Configuration::get('KCO_NORWAY_ADDR');
                $this->context->cart->update();
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        } elseif ($country_information['purchase_country'] == 'DE') {
			$this->context->smarty->assign('show_postcode_field', false);
            $eid = (int) (Configuration::get('KCO_GERMANY_EID'));
            $sharedSecret = Configuration::get('KCO_GERMANY_SECRET');
            $ssid = 'de';
            if ($country->iso_code != 'DE') {
                
                if ($this->context->cart->id_address_delivery==Configuration::get('KCO_GERMANY_ADDR')) {
                    $this->module->createAddress(
                        'DE',
                        'KCO_GERMANY_ADDR',
                        'Berlin',
                        'Germany',
                        'KCO_GERMANY_DEFAULT'
                    );
                }
                
                $this->context->cart->id_address_delivery = Configuration::get('KCO_GERMANY_ADDR');
                $this->context->cart->update();
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        } elseif ($country_information['purchase_country'] == 'AT') {
			$this->context->smarty->assign('show_postcode_field', false);
            $eid = (int) (Configuration::get('KCO_AUSTRIA_EID'));
            $sharedSecret = Configuration::get('KCO_AUSTRIA_SECRET');
            $ssid = 'at';
            if ($country->iso_code != 'AT') {
                
                if ($this->context->cart->id_address_delivery==Configuration::get('KCO_AUSTRIA_ADDR')) {
                    $this->module->createAddress(
                        'AT',
                        'KCO_AUSTRIA_ADDR',
                        'Vienna',
                        'Austria',
                        'KCO_AUSTRIA_DEFAULT'
                    );
                }
                
                $this->context->cart->id_address_delivery = Configuration::get('KCO_AUSTRIA_ADDR');
                $this->context->cart->update();
                Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarna');
            }
        } elseif ($country_information['purchase_country'] == 'GB') {
			$this->context->smarty->assign('show_postcode_field', false);
            $eid = Configuration::get('KCO_UK_EID');
            $sharedSecret = Configuration::get('KCO_UK_SECRET');
            $ssid = 'gb';
            if ($country->iso_code != 'GB') {
                
                if ($this->context->cart->id_address_delivery==Configuration::get('KCO_UK_ADDR')) {
                    $this->module->createAddress(
                        'GB',
                        'KCO_UK_ADDR',
                        'London',
                        'United Kingdom',
                        'KCO_UK_DEFAULT'
                    );
                }
                
                $this->context->cart->id_address_delivery = Configuration::get('KCO_UK_ADDR');
                $this->context->cart->update();
            }
            Tools::redirect('index.php?fc=module&module=klarnaofficial&controller=checkoutklarnauk');
        }

		$new_address = new Address((int)$this->context->cart->id_address_delivery);
		
		// force carrier change if not initialized & validation fails
		if ($this->context->cart->id_carrier == 0 && isset($_GET['pickupalert']))
		{
			// hardcoded carrier id (matkahuolto / smartpost)
			$this->context->cart->id_carrier = 4;
		}
		
		
		if (isset($_GET['zip'])) {
			$new_address->postcode = $_GET['zip'];
			
            $this->context->cart->id_address_delivery = $new_address->id;
            $this->context->cart->update();
		}
		
		// get carrier hooks
        $displayCarrierListHook = Hook::exec(
			'displayCarrierList',
			array('address' => $new_address));
			
		if($displayCarrierListHook != "")  {
			$this->context->smarty->assign('displayCarrierListHook', $displayCarrierListHook);
		}
		
        $layout = 'desktop';
        //if($this->context->getMobileDevice())
        //	$layout = 'mobile';
        require_once _PS_TOOL_DIR_.'mobile_Detect/Mobile_Detect.php';
        $mobile_detect_class = new Mobile_Detect();
        if ($mobile_detect_class->isMobile() or $mobile_detect_class->isMobile()) {
            $layout = 'mobile';
        }

        $totalCartValue = 0;
        $round_diff = 0;

        if (Configuration::get('KCO_ROUNDOFF') == 1) {
            $total_cart_price_before_round = $this->context->cart->getOrderTotal(true, Cart::BOTH);
            $total_cart_price_after_round = round($total_cart_price_before_round);
            $round_diff = $total_cart_price_after_round - $total_cart_price_before_round;
        }

        if (isset($this->context->cart) and $this->context->cart->nbProducts() > 0) {
            if (!$this->context->cart->checkQuantities()) {
                Tools::redirect('index.php?controller=order&step=1');
            } else {
                $minimal_purchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
                if ($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimal_purchase) {
                    Tools::redirect('index.php?controller=order&step=1');
                }
                require_once dirname(__FILE__).'/../../libraries/Checkout.php';
                if (version_compare(phpversion(), '5.4.0', '>=')) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                } else {
                    if (session_id() === '') {
                        session_start();
                    }
                }
                $lastrate = "notset";
                $has_different_rates = false;
                foreach ($this->context->cart->getProducts() as $product) {
                    if ($lastrate == "notset") {
                        $lastrate = $product['rate'];
                    } elseif ($lastrate != $product['rate']) {
                        $has_different_rates = true;
                    }
                    $price = Tools::ps_round($product['price_wt'], 2);
                    $totalCartValue += ($price * (int) ($product['cart_quantity']));

                    $price = ($price * 100);
                    
                    $product_reference = $product['id_product'];
                    if (isset($product['reference']) &&
                    $product['reference'] != '') {
                        $product_reference = $product['reference'];
                    }
                    
                    $attributes = "";
                    if (isset($product['attributes']) &&
                    $product['attributes'] != '') {
                        $attributes = " - ".$product['attributes'];
                    }
                    
                    $instructions = "";
                    if (isset($product['instructions']) &&
                    $product['instructions'] != '') {
                        $instructions = " - ".$product['instructions'];
                    }
                    
                    $product_name = strip_tags(
                        $product['name'].$attributes.$instructions
                    );
                    $checkoutcart[] = array(
                    'reference' => $product_reference,
                    'name' => $product_name,
                    'quantity' => (int) ($product['cart_quantity']),
                    'unit_price' => $price,
                    'discount_rate' => 0,
                    'tax_rate' => (int) ($product['rate']) * 100,
                    );
                }

                $shipping_cost_with_tax = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
                $shipping_cost_without_tax = $this->context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING);

                if ($shipping_cost_without_tax > 0) {
                    /*$shipping_tax_rate = ($shipping_cost_with_tax / $shipping_cost_without_tax) - 1;*/
                    $totalCartValue += $shipping_cost_with_tax;
                    
                    $carrier = new Carrier($this->context->cart->id_carrier);
                    $carrieraddress = new Address($this->context->cart->id_address_delivery);
                    $carriertaxrate = $carrier->getTaxesRate($carrieraddress);

                    $shippingReference = $this->module->shippingreferences[$language->iso_code];

                    $checkoutcart[] = array(
                        'type' => 'shipping_fee',
                        'reference' => $shippingReference,
                        'name' => strip_tags($carrier->name),
                        'quantity' => 1,
                        'unit_price' => ($shipping_cost_with_tax * 100),
                        'tax_rate' => (int) ($carriertaxrate * 100),
                    );
                }
                if ($this->context->cart->gift == 1) {
                    $cart_wrapping = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
                    if ($cart_wrapping > 0) {
                        //$wrapping_cost_excl = $this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING);
                        //$wrapping_cost_incl = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
                        /*$wrapping_vat = (($wrapping_cost_incl / $wrapping_cost_excl) - 1) * 100;*/
                        
                        if (!is_object($carrieraddress)) {
                            $carrieraddress = new Address($this->context->cart->id_address_delivery);
                        }
                        $PS_GIFT_WRAPPING_TAX_RULES_GROUP = Configuration::get('PS_GIFT_WRAPPING_TAX_RULES_GROUP');
                        if ($PS_GIFT_WRAPPING_TAX_RULES_GROUP > 0) {
                            $tax_manager = TaxManagerFactory::getManager(
                                $carrieraddress,
                                $PS_GIFT_WRAPPING_TAX_RULES_GROUP
                            );
                            $tax_calculator = $tax_manager->getTaxCalculator();
                            $wrapping_vat = $tax_calculator->getTotalRate();
                        } else {
                            $wrapping_vat = 0;
                        }
                        
                        $cart_wrapping = Tools::ps_round($cart_wrapping, 2);
                        $totalCartValue += $cart_wrapping;
                        
                        $wrappingreference = $this->module->wrappingreferences[$language->iso_code];
                        
                        $checkoutcart[] = array(
                            'reference' => $wrappingreference,
                            'name' =>  $this->module->getL('Inslagning'),
                            'quantity' => 1,
                            'unit_price' => ($cart_wrapping * 100),
                            'tax_rate' => (int) ($wrapping_vat * 100),
                        );
                    }
                }

                //DISCOUNTS
                
                foreach ($this->context->cart->getCartRules() as $cart_rule) {
                    $value_real = $cart_rule["value_real"];
                    $value_tax_exc = $cart_rule["value_tax_exc"];
                    
                    if ($has_different_rates == false) {
                        $discount_tax_rate = Tools::ps_round($lastrate, 2);
                    } else {
                        $discount_tax_rate = (($value_real / $value_tax_exc) - 1) * 100;
                        //$discount_tax_rate = number_format($discount_tax_rate, 2);
                        $discount_tax_rate = $this->cutNum($discount_tax_rate);
                        //echo "<br>(($value_real / $value_tax_exc) - 1) * 100 = $discount_tax_rate<br>";
                        $discount_tax_rate = Tools::ps_round($discount_tax_rate, 2);
                    }
                    
                    
                    $checkoutcart[] = array(
                            'type' => 'discount',
                            'reference' => '',
                            'name' => $cart_rule["description"],
                            'quantity' => 1,
                            'unit_price' => -(Tools::ps_round($value_real, 2) * 100),
                            'tax_rate' => (int) ($discount_tax_rate * 100),
                        );
                }

                if ($round_diff != 0) {
                    $checkoutcart[] = array(
                        'reference' => '',
                        'name' => 'Avrundning',
                        'quantity' => 1,
                        'unit_price' => round(($round_diff * 100), 0),
                        'discount_rate' => 0,
                        'tax_rate' => 0,
                    );
                }

                $callbackPage = $this->context->link->getModuleLink(
                    'klarnaofficial',
                    'thankyou',
                    array('sid' => $ssid)
                );
                
                $pushPage = $this->context->link->getModuleLink('klarnaofficial', 'push', array('sid' => $ssid));
                $pushPage .= '&klarna_order={checkout.order.uri}';
				
				// custom validation for carriers with pickup points
                $validationPage = $this->context->link->getModuleLink('klarnaofficial', 'validate', array('cartid' => $this->context->cart->id));

                $checkout = $this->context->link->getModuleLink('klarnaofficial', 'checkoutklarna', array(), true);
                $back_to_store_uri = $this->context->link->getPageLink('index');
                
                $cms = new CMS(
                    (int) (Configuration::get('PS_CONDITIONS_CMS_ID')),
                    (int) ($this->context->cookie->id_lang)
                );
                
                $link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, true);
                $termsPage = $link_conditions;
                try {
                    if ((int) (Configuration::get('KCO_TESTMODE')) == 1) {
                        Klarna_Checkout_Order::$baseUri = 'https://checkout.testdrive.klarna.com/checkout/orders';
                    } else {
                        Klarna_Checkout_Order::$baseUri = 'https://checkout.klarna.com/checkout/orders';
                    }
                    Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';

                    $connector = Klarna_Checkout_Connector::create($sharedSecret);
                    $klarnaorder = null;
                    if (array_key_exists('klarna_checkout', $_SESSION)) {
                        // Resume session
                        $klarnaorder = new Klarna_Checkout_Order(
                            $connector,
                            $_SESSION['klarna_checkout']
                        );
                        try {
                            $klarnaorder->fetch();

                            // Reset cart
                            $update['cart']['items'] = array();
                            foreach ($checkoutcart as $item) {
                                $update['cart']['items'][] = $item;
                            }

							// prepopulate zip if we have it
							if (isset($_GET['zip'])) {
								$update['shipping_address']['postal_code'] = $_GET['zip'];
							}
							
                            $update['purchase_country'] = $country_information['purchase_country'];
                            $update['purchase_currency'] = $country_information['purchase_currency'];
                            $update['locale'] = $country_information['locale'];
                            $update['merchant_reference']['orderid2'] = ''.(int) ($this->context->cart->id);
                            $klarnaorder->update($update);
                        } catch (Exception $e) {
                            // Reset session
                            $klarnaorder = null;
                            unset($_SESSION['klarna_checkout']);
                        }
                    }
                    if ($klarnaorder == null) {
                        $klarnaorder = new Klarna_Checkout_Order($connector);

                        $create['purchase_country'] = $country_information['purchase_country'];
                        $create['purchase_currency'] = $country_information['purchase_currency'];
                        $create['locale'] = $country_information['locale'];
                        if (Configuration::get('KCO_AUTOFOCUS') == 0) {
                            $create['gui']['options'] = array('disable_autofocus');
                        }
                        $create['gui']['layout'] = $layout;
                        $create['merchant']['id'] = ''.$eid;
                        $create['merchant']['terms_uri'] = $termsPage;
                        $create['merchant']['checkout_uri'] = $checkout;
                        $create['merchant']['back_to_store_uri'] = $back_to_store_uri;
                        $create['merchant']['confirmation_uri'] = $callbackPage;
                        $create['merchant']['push_uri'] = $pushPage;
						
						
                        //$create['merchant']['validation_uri'] = $validationPage;
						
						/*
                        if (Configuration::get('KCO_CALLBACK_CHECK') == 1) {
                            $create['merchant']['validation_uri'] = ''.
                            $this->context->link->getModuleLink(
                                $this->module->name,
                                'callbackvalidation',
                                array(),
                                true
                            );
                        }
						*/
                        
                        $create['merchant_reference']['orderid2'] = ''.(int) ($this->context->cart->id);
                        
                        if ($country_information['purchase_country'] == "SE") {
                            $allowB2B = (bool)Configuration::get('KCO_SWEDEN_B2B');
                        } elseif ($country_information['purchase_country'] == "NO") {
                            $allowB2B = (bool)Configuration::get('KCO_NORWAY_B2B');
                        } elseif ($country_information['purchase_country'] == "FI") {
                            $allowB2B = (bool)Configuration::get('KCO_FINLAND_B2B');
                        } else {
                            $allowB2B = false;
                        }
                        if ($allowB2B == true) {
                            $create['options']['allowed_customer_types'] = array("person", "organization");
                        }
                        $forcePhone = false;
                        if ($forcePhone == true) {
                            $create['options']['phone_mandatory'] = true;
                        }
                        
                        if ((int)Configuration::get('KCO_ADD_NEWSLETTERBOX') == 0) {
                            $create['options']['additional_checkbox']['text'] = ''.
                            $this->module->getL('Subscribe to our newsletter.');
                            $create['options']['additional_checkbox']['checked'] = false;
                            $create['options']['additional_checkbox']['required'] = false;
                        } elseif ((int)Configuration::get('KCO_ADD_NEWSLETTERBOX') == 1) {
                            $create['options']['additional_checkbox']['text'] = ''.
                            $this->module->getL('Subscribe to our newsletter.');
                            $create['options']['additional_checkbox']['checked'] = true;
                            $create['options']['additional_checkbox']['required'] = false;
                        }
                        if (Configuration::get('KCO_COLORBUTTON') != '') {
                            $create['options']['color_button'] = ''.
                            Configuration::get('KCO_COLORBUTTON');
                        }
                        if (Configuration::get('KCO_COLORBUTTONTEXT') != '') {
                            $create['options']['color_button_text'] = ''.
                            Configuration::get('KCO_COLORBUTTONTEXT');
                        }
                        if (Configuration::get('KCO_COLORCHECKBOX') != '') {
                            $create['options']['color_checkbox'] = ''.
                            Configuration::get('KCO_COLORCHECKBOX');
                        }
                        if (Configuration::get('KCO_COLORCHECKBOXMARK') != '') {
                            $create['options']['color_checkbox_checkmark'] = ''.
                            Configuration::get('KCO_COLORCHECKBOXMARK');
                        }
                        if (Configuration::get('KCO_COLORHEADER') != '') {
                            $create['options']['color_header'] = ''.
                            Configuration::get('KCO_COLORHEADER');
                        }
                        if (Configuration::get('KCO_COLORLINK') != '') {
                            $create['options']['color_link'] = ''.
                            Configuration::get('KCO_COLORLINK');
                        }

                        foreach ($checkoutcart as $item) {
                            $create['cart']['items'][] = $item;
                        }
                        $klarnaorder->create($create);
                        $klarnaorder->fetch();
                        $_SESSION['klarna_checkout'] = $klarnaorder->getLocation();
                    }

                    $id_country = 0;
                    if ($country_information['purchase_country'] == 'SV') {
                        $id_country = Country::getByIso('se');
                    }
                    if ($country_information['purchase_country'] == 'FI') {
                        $id_country = Country::getByIso('fi');
                    }
                    if ($country_information['purchase_country'] == 'NO') {
                        $id_country = Country::getByIso('no');
                    }
                    if ($country_information['purchase_country'] == 'DE') {
                        $id_country = Country::getByIso('de');
                    }
                    if ($country_information['purchase_country'] == 'AT') {
                        $id_country = Country::getByIso('at');
                    }

                    $this->context->cart->getSummaryDetails();

                    if ($klarnaorder != null) {
                        $snippet = $klarnaorder['gui']['snippet'];
                        if (Tools::getIsset('kco_update') and Tools::getValue('kco_update') == '1') {
                            die($snippet);
                        }

                        if (Tools::getIsset('changed')) {
                            $this->context->smarty->assign('klarna_checkout_cart_changed', true);
                        }
                        $this->context->smarty->assign('klarna_checkout', $snippet);

                        $wrapping_fees_tax_inc = $this->context->cart->getGiftWrappingPrice(true);

                        $this->context->smarty->assign('discounts', $this->context->cart->getCartRules());
                        $this->context->smarty->assign('cart_is_empty', false);
                        $this->context->smarty->assign('gift', $this->context->cart->gift);
                        $this->context->smarty->assign('gift_message', $this->context->cart->gift_message);
                        $this->context->smarty->assign('giftAllowed', (int) (Configuration::get('PS_GIFT_WRAPPING')));
                        $this->context->smarty->assign(
                            'gift_wrapping_price',
                            Tools::convertPrice(
                                $wrapping_fees_tax_inc,
                                new Currency($this->context->cart->id_currency)
                            )
                        );
                        $this->context->smarty->assign(
                            'message',
                            Message::getMessageByCartId((int) ($this->context->cart->id))
                        );
                    }

                    if ($id_country > 0) {
                        $delivery_option_list = $this->context->cart->getDeliveryOptionList(
                            new Country($id_country),
                            true
                        );
                    } else {
                        $delivery_option_list = $this->context->cart->getDeliveryOptionList();
                    }

                    $free_shipping = false;
                    foreach ($this->context->cart->getCartRules() as $rule) {
                        if ($rule['free_shipping']) {
                            $free_shipping = true;
                            break;
                        }
                    }
                    $free_fees_price = 0;
                    $configuration = Configuration::getMultiple(
                        array(
                            'PS_SHIPPING_FREE_PRICE',
                            'PS_SHIPPING_FREE_WEIGHT'
                        )
                    );
                    if (isset($configuration['PS_SHIPPING_FREE_PRICE']) &&
                    $configuration['PS_SHIPPING_FREE_PRICE'] > 0) {
                        $free_fees_price = Tools::convertPrice(
                            (float) $configuration['PS_SHIPPING_FREE_PRICE'],
                            Currency::getCurrencyInstance((int) $this->context->cart->id_currency)
                        );
                        $orderTotalwithDiscounts = $this->context->cart->getOrderTotal(
                            true,
                            Cart::BOTH_WITHOUT_SHIPPING,
                            null,
                            null,
                            false
                        );
                        $left_to_get_free_shipping = $free_fees_price - $orderTotalwithDiscounts;
                        $this->context->smarty->assign('left_to_get_free_shipping', $left_to_get_free_shipping);
                    }
                    if (isset($configuration['PS_SHIPPING_FREE_WEIGHT']) &&
                    $configuration['PS_SHIPPING_FREE_WEIGHT'] > 0) {
                        $free_fees_weight = $configuration['PS_SHIPPING_FREE_WEIGHT'];
                        $total_weight = $this->context->cart->getTotalWeight();
                        $left_to_get_free_shipping_weight = $free_fees_weight - $total_weight;
                        $this->context->smarty->assign(
                            'left_to_get_free_shipping_weight',
                            $left_to_get_free_shipping_weight
                        );
                    }

                    $delivery_option = $this->context->cart->getDeliveryOption(
                        new Country($id_country),
                        false,
                        false
                    );

                    $no_active_countries = 0;
                    $show_sweden = false;
                    $show_norway = false;
                    $show_finland = false;
                    $show_germany = false;
                    $show_austria = false;
                    $show_uk = false;
                    if ((int) (Configuration::get('KCO_SWEDEN')) == 1) {
                        ++$no_active_countries;
                        $show_sweden = true;
                    }
                    if ((int) (Configuration::get('KCO_FINLAND')) == 1) {
                        ++$no_active_countries;
                        $show_finland = true;
                    }
                    if ((int) (Configuration::get('KCO_NORWAY')) == 1) {
                        ++$no_active_countries;
                        $show_norway = true;
                    }
                    if ((int) (Configuration::get('KCO_GERMANY')) == 1) {
                        ++$no_active_countries;
                        $show_germany = true;
                    }
                    if ((int) (Configuration::get('KCO_AUSTRIA')) == 1) {
                        ++$no_active_countries;
                        $show_austria = true;
                    }
                    if ((int) (Configuration::get('KCO_UK')) == 1) {
                        ++$no_active_countries;
                        $show_uk = true;
                    }
                    $this->assignSummaryInformations();
                    $this->context->smarty->assign(array(
                        'no_active_countries' => $no_active_countries,
                        'show_austria' => $show_austria,
                        'show_germany' => $show_germany,
                        'show_norway' => $show_norway,
                        'show_uk' => $show_uk,
                        'show_finland' => $show_finland,
                        'show_sweden' => $show_sweden,
                        'kco_selected_country' => $country_information['purchase_country'],
                        'klarna_checkout' => $snippet,
                        'controllername' => 'checkoutklarna',
                        'free_shipping' => $free_shipping,
                        'token_cart' => $this->context->cart->secure_key,
                        'delivery_option_list' => $delivery_option_list,
                        'delivery_option' => $delivery_option,
                        'KCO_SHOWLINK' => (int) Configuration::get('KCO_SHOWLINK'),
                        'layout' => $layout,
                        'kcourl' => $checkout,
                        'back' => ''
                    ));
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    if ($message == "Connection to 'https://checkout.klarna.com/checkout/orders' failed.") {
                        $connectionerror = true;
                    } else {
                        $connectionerror = false;
                    }
                    $this->context->smarty->assign('connectionerror', $connectionerror);
                    $this->context->smarty->assign('klarna_error', $message);
                }
            }
        } else {
            $this->context->smarty->assign('klarna_error', 'empty_cart');
        }
        if (Configuration::get('KCO_LAYOUT') == 1) {
            $this->setTemplate('kco_twocolumns.tpl');
        } else {
            $this->setTemplate('kco_height.tpl');
        }
    }

    protected function validateDeliveryOption($delivery_option)
    {
        if (!is_array($delivery_option)) {
            return false;
        }

        foreach ($delivery_option as $option) {
            if (!preg_match('/(\d+,)?\d+/', $option)) {
                return false;
            }
        }

        return true;
    }

    protected function updateMessage($messageContent, $cart)
    {
        if ($messageContent) {
            if (!Validate::isMessage($messageContent)) {
                return false;
            } elseif ($oldMessage = Message::getMessageByCartId((int) ($cart->id))) {
                $message = new Message((int) ($oldMessage['id_message']));
                $message->message = $messageContent;
                $message->update();
            } else {
                $message = new Message();
                $message->message = $messageContent;
                $message->id_cart = (int) ($cart->id);
                $message->id_customer = (int) ($cart->id_customer);
                $message->add();
            }
        } else {
            if ($oldMessage = Message::getMessageByCartId((int) ($cart->id))) {
                $message = new Message((int) ($oldMessage['id_message']));
                $message->delete();
            }
        }

        return true;
    }

	protected function ForceEnglish($language_iso_code)
	{
		if ($_SERVER["REQUEST_URI"]) {
			if( strpos( $_SERVER["REQUEST_URI"], '/en/' ) !== false ) {
				return true;
			}
		}

		return false;
	}
	
	protected function IsKlarnaLocale($language_iso_code)
	{
		if ($language_iso_code == 'en' || 
			$language_iso_code == 'gb' || 
			$language_iso_code == 'fi' || 
			$language_iso_code == 'sv' || 
			$language_iso_code == 'no' || 
			$language_iso_code == 'nb' || 
			$language_iso_code == 'nn' || 
			$language_iso_code == 'de')
		{
			return true;	
		}
		
		return false;
	}
	
    protected function getKlarnaCountryInformation($currency_iso_code, $language_iso_code)
    {
		if (!$this->IsKlarnaLocale($language_iso_code)) {
			return false;
		}
		
		if ($currency_iso_code == 'GBP' && Configuration::get('KCO_UK') == 1) {
            return array('locale' => 'en-gb', 'purchase_currency' => 'GBP', 'purchase_country' => 'GB');
		}
		elseif ($currency_iso_code == 'SEK' && Configuration::get('KCO_SWEDEN') == 1) {
			if ($this->ForceEnglish($language_iso_code)){
				return array('locale' => 'en-gb', 'purchase_currency' => 'SEK', 'purchase_country' => 'SE'); }
            return array('locale' => 'sv-se', 'purchase_currency' => 'SEK', 'purchase_country' => 'SE');
		}
		elseif ($currency_iso_code == 'NOK' && Configuration::get('KCO_NORWAY') == 1) {
			if ($this->ForceEnglish($language_iso_code)){
				return array('locale' => 'en-gb', 'purchase_currency' => 'NOK', 'purchase_country' => 'NO'); }
            return array('locale' => 'nb-no', 'purchase_currency' => 'NOK', 'purchase_country' => 'NO');
		}
		elseif ($currency_iso_code == 'GBP' && Configuration::get('KCO_UK') == 1) {
			return array('locale' => 'en-gb', 'purchase_currency' => 'GBP', 'purchase_country' => 'GB');
		}
		elseif ($currency_iso_code == 'EUR') {
			
			if (isset($this->context->cart) && $this->context->cart->id_address_delivery > 0) {
				$tmp_address = new Address((int) ($this->context->cart->id_address_delivery));
				$id_country_austria = (int) Country::getByIso('AT');
				$id_country_germany = (int) Country::getByIso('DE');
				
				if ($tmp_address->id_country == $id_country_germany) {
					if (Configuration::get('KCO_GERMANY') == 1) {
						//if ($this->ForceEnglish($language_iso_code)){
						//	return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE'); }
						return array('locale' => 'de-de', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE');
					}
					elseif (Configuration::get('KCO_AUSTRIA') == 1) {
						//if ($this->ForceEnglish($language_iso_code)){
						//	return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'AT'); }
						return array('locale' => 'de-at', 'purchase_currency' => 'EUR', 'purchase_country' => 'AT');
					}
					elseif (Configuration::get('KCO_FINLAND') == 1) {
						if ($this->ForceEnglish($language_iso_code)){
							return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI'); }
						return array('locale' => 'fi-fi', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI');
					}
				}
				elseif ($tmp_address->id_country == $id_country_austria) {
					
					if (Configuration::get('KCO_AUSTRIA') == 1) {
						//if ($this->ForceEnglish($language_iso_code)){
						//	return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'AT'); }
						return array('locale' => 'de-at', 'purchase_currency' => 'EUR', 'purchase_country' => 'AT');
					}
					elseif (Configuration::get('KCO_GERMANY') == 1) {
						//if ($this->ForceEnglish($language_iso_code)){
						//	return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE'); }
						return array('locale' => 'de-de', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE');
					}
					elseif (Configuration::get('KCO_FINLAND') == 1) {
						if ($this->ForceEnglish($language_iso_code)){
							return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI'); }
						return array('locale' => 'fi-fi', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI');
					}
				}
			}
			
			if (Configuration::get('KCO_FINLAND') == 1) {
				if ($this->ForceEnglish($language_iso_code)){
					return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI'); }
				return array('locale' => 'fi-fi', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI');
			}
			elseif (Configuration::get('KCO_GERMANY') == 1) {
				//if ($this->ForceEnglish($language_iso_code)){
				//	return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE'); }
				return array('locale' => 'de-de', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE');
			}
			elseif (Configuration::get('KCO_AUSTRIA') == 1) {
				//if ($this->ForceEnglish($language_iso_code)){
				//	return array('locale' => 'en-gb', 'purchase_currency' => 'EUR', 'purchase_country' => 'AT'); }
				return array('locale' => 'de-at', 'purchase_currency' => 'EUR', 'purchase_country' => 'AT');
			}
		}
		
		return false;
    }

    protected function assignSummaryInformations()
    {
        $summary = $this->context->cart->getSummaryDetails();
        $customizedDatas = Product::getAllCustomizedDatas($this->context->cart->id);

        // override customization tax rate with real tax (tax rules)
        if ($customizedDatas) {
            foreach ($summary['products'] as &$productUpdate) {
                if (isset($productUpdate['id_product'])) {
                    $productId = (int) $productUpdate['id_product'];
                } else {
                    $productId = (int) $productUpdate['product_id'];
                }
                
                if (isset($productUpdate['id_product_attribute'])) {
                    $productAttributeId = (int) $productUpdate['id_product_attribute'];
                } else {
                    $productAttributeId = (int) $productUpdate['product_attribute_id'];
                }
                
                if (isset($customizedDatas[$productId][$productAttributeId])) {
                    $productUpdate['tax_rate'] = Tax::getProductTaxRate(
                        $productId,
                        $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
                    );
                }
            }

            Product::addCustomizationPrice($summary['products'], $customizedDatas);
        }

        $cart_product_context = Context::getContext()->cloneContext();
        foreach ($summary['products'] as $key => &$product) {
            $product['quantity'] = $product['cart_quantity'];// for compatibility with 1.2 themes

            if ($cart_product_context->shop->id != $product['id_shop']) {
                $cart_product_context->shop = new Shop((int) $product['id_shop']);
            }
            $specific_price_output = null;
            $product['price_without_specific_price'] = Product::getPriceStatic(
                $product['id_product'],
                !Product::getTaxCalculationMethod(),
                $product['id_product_attribute'],
                2,
                null,
                false,
                false,
                1,
                false,
                null,
                null,
                null,
                $specific_price_output,
                true,
                true,
                $cart_product_context
            );

            if (Product::getTaxCalculationMethod()) {
                $product['is_discounted'] = $product['price_without_specific_price'] != $product['price'];
            } else {
                $product['is_discounted'] = $product['price_without_specific_price'] != $product['price_wt'];
            }
        }

        // Get available cart rules and unset the cart rules already in the cart
        $available_cart_rules = CartRule::getCustomerCartRules(
            $this->context->language->id,
            (isset($this->context->customer->id) ? $this->context->customer->id : 0),
            true,
            true,
            true,
            $this->context->cart
        );
        
        $cart_cart_rules = $this->context->cart->getCartRules();
        foreach ($available_cart_rules as $key => $available_cart_rule) {
            if (!$available_cart_rule['highlight'] || strpos($available_cart_rule['code'], 'BO_ORDER_') === 0) {
                unset($available_cart_rules[$key]);
                continue;
            }
            foreach ($cart_cart_rules as $cart_cart_rule) {
                if ($available_cart_rule['id_cart_rule'] == $cart_cart_rule['id_cart_rule']) {
                    unset($available_cart_rules[$key]);
                    continue 2;
                }
            }
        }

        $show_option_allow_separate_package = (!$this->context->cart->isAllProductsInStock(true) &&
        Configuration::get('PS_SHIP_WHEN_AVAILABLE'));

        $this->context->smarty->assign($summary);
        $this->context->smarty->assign(array(
            'token_cart' => Tools::getToken(false),
            'isVirtualCart' => $this->context->cart->isVirtualCart(),
            'productNumber' => $this->context->cart->nbProducts(),
            'voucherAllowed' => CartRule::isFeatureActive(),
            'shippingCost' => $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING),
            'shippingCostTaxExc' => $this->context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING),
            'customizedDatas' => $customizedDatas,
            'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
            'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
            'lastProductAdded' => $this->context->cart->getLastProduct(),
            'displayVouchers' => $available_cart_rules,
            'advanced_payment_api' => true,
            'currencySign' => $this->context->currency->sign,
            'currencyRate' => $this->context->currency->conversion_rate,
            'currencyFormat' => $this->context->currency->format,
            'currencyBlank' => $this->context->currency->blank,
            'show_option_allow_separate_package' => $show_option_allow_separate_package,
            'smallSize' => Image::getSize(ImageType::getFormatedName('small')),

        ));

        $this->context->smarty->assign(array(
            'HOOK_SHOPPING_CART' => Hook::exec('displayShoppingCartFooter', $summary),
            'HOOK_SHOPPING_CART_EXTRA' => Hook::exec('displayShoppingCart', $summary),
        ));
    }
    
    
    public function kcoGetAverageProductsTaxRate()
    {
        $cart_amount_ti = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $cart_amount_te = $this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);

        $cart_vat_amount = $cart_amount_ti - $cart_amount_te;

        if ($cart_vat_amount == 0 || $cart_amount_te == 0) {
            return 0;
        } else {
            return Tools::ps_round($cart_vat_amount / $cart_amount_te, 2);
        }
    }
    public function cutNum($num, $precision = 2)
    {
        return floor($num).Tools::substr($num-floor($num), 1, $precision+1);
    }
}

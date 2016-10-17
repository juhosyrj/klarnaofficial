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
 
class KlarnaOfficialCallbackValidationModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function init()
    {
        $klarnadata = Tools::file_get_contents('php://input');
        
        $klarnaorder = Tools::jsonDecode($klarnadata, true);
        //DO THE CHECKS ON THE CART
        if (isset($klarnaorder["merchant_reference"]["orderid2"])) {
            $id_cart = (int)$klarnaorder["merchant_reference"]["orderid2"];
            if ($id_cart > 0) {
                $cart = new Cart($id_cart);
                $this->context->currency = new Currency((int)$cart->id_currency);
                $language = new Language((int)$cart->id_lang);
                $this->context->language = $language;
                //Check cart exist and no order created
                if (Validate::isLoadedObject($cart) && $cart->OrderExists() == false) {
                    //Check stock
                    if (!$cart->checkQuantities()) {
                         $this->redirectKCO('index.php?controller=order&step=1');
                    }
                    //Check shipping
                    $shipping_cost_with_tax = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
                    $shipping_cost_with_tax = ($shipping_cost_with_tax * 100);
                    foreach ($klarnaorder["cart"]["items"] as $key => $cartitem) {
                        if ($cartitem["type"] == "shipping_fee") {
                            if ($shipping_cost_with_tax==$cartitem["unit_price"]) {
                                unset($klarnaorder["cart"]["items"][$key]);
                           }
                        }
                    }
                    //Check products
                    foreach ($cart->getProducts() as $product) {
                        $product_found = false;
                        $product_reference = $product['id_product'];
                        if (isset($product['reference']) &&
                        $product['reference'] != '') {
                            $product_reference = $product['reference'];
                        }

                        $price = Tools::ps_round($product['price_wt'], 2);
                        $price = "".($price * 100);

                        foreach ($klarnaorder["cart"]["items"] as $key => $cartitem) {
                            if ($cartitem["reference"] == $product_reference) {
                                if ((int)$cartitem["quantity"] == (int)$product['cart_quantity']) {
                                    if ((int)$cartitem["unit_price"] == (int)$price) {
                                        //All is matching, remove this.
                                        unset($klarnaorder["cart"]["items"][$key]);
                                        $product_found = true;
                                    }
                                }
                            }
                        }
                        if (!$product_found) {
                            //Prestashop cart has products kco has not
                            $this->redirectKCO();
                        }
                        
                        //CHECK DISCOUNTS
                        
                        foreach ($cart->getCartRules() as $cart_rule) {
                            $value_real = $cart_rule["value_real"];
                            $value_real = -(Tools::ps_round($value_real, 2) * 100);
                            
                            foreach ($klarnaorder["cart"]["items"] as $key => $cartitem) {
                                     if ($cartitem["type"] == "discount") {
                                        if ((int)$cartitem["unit_price"] == (int)$value_real) {
                                            unset($klarnaorder["cart"]["items"][$key]);
                                            //$cartdiscountsfound = true;
                                        }
                                     }
                                 }
                        }
                        
                        /*$totalDiscounts = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
                        $cartdiscountsfound = true;
                        if ($totalDiscounts > 0) {
                            $totalDiscounts = -number_format(($totalDiscounts * 100), 2, '.', '');
                            $cartdiscountsfound = false;
                            foreach ($klarnaorder["cart"]["items"] as $key => $cartitem) {
                                     if ($cartitem["type"] == "discount") {
                                         if ((int)$cartitem["unit_price"] == (int)$totalDiscounts) {
                                             unset($klarnaorder["cart"]["items"][$key]);
                                             $cartdiscountsfound = true;
                                         }
                                     }
                                 }
                        }*/
                        
                        //CHECK WRAPPING
                        $cartgiftfound = true;
                        if ($cart->gift == 1) {
                            $cart_wrapping = $cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
                            if ($cart_wrapping > 0) {
                                $wrappingreference = $this->module->wrappingreferences[$language->iso_code];
                                $cartgiftfound = false;
                                $cart_wrapping = Tools::ps_round($cart_wrapping, 2);
                                $cart_wrapping = ($cart_wrapping * 100);
                                 foreach ($klarnaorder["cart"]["items"] as $key => $cartitem) {
                                    if ($cartitem["reference"] == $wrappingreference) {
                                        if ($cartitem["unit_price"] == $cart_wrapping) {
                                            $cartgiftfound = true;
                                            unset($klarnaorder["cart"]["items"][$key]);
                                        }
                                    }
                                 }
                            }
                        }
                    }
                    
                    if (count($klarnaorder["cart"]["items"]) > 0) {
                            //Klarna has products that are not existing in Prestashop
                            $this->redirectKCO();
                    }
                    /*if ($cartdiscountsfound==false) {
                        $this->redirectKCO();
                    }*/
                    if ($cartgiftfound==false) {
                        $this->redirectKCO();
                    }
                    
                    //ALL IS OK
                    exit;
                } else {
                    PrestaShopLogger::addLog('KCO: cart not loaded', 3, null, '', 0, true);
                    $this->redirectKCO();
                }
            }
        } else {
            $this->redirectKCO();
        }
    }
    public function redirectKCO($url = false)
    {
        header('HTTP/1.1 303 See Other');
        header('Cache-Control: no-cache');

        $url = $this->context->link->getModuleLink(
            'klarnaofficial',
            'checkoutklarna',
            array("changed" => 1),
            true
        );
       // $url = "http://kcouk.prestaworks.se";
        Tools::redirect($url);
        exit;
    }
}

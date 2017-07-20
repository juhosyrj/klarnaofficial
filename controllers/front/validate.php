<?php
/**
 * custom validation logic added by juho.syrjanen@klarna.com
 */
 
class KlarnaOfficialValidateModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl = true;

    public function postProcess()
    {
		$base_url = Tools::getHttpHost(true).__PS_BASE_URI__;
		
        $json =  Tools::file_get_contents('php://input');
        $data = Tools::jsonDecode($json);

		$zip = $data->shipping_address->postal_code;
		
		// get the cart
		$id_cart = $_GET['cartid'];
		$cart = new Cart((int) ($id_cart));

		// hardcoded / check if we're using carrier that needs pickup point, check carrier id!
		if ($cart->id_carrier == 98)
		{
			// matkahuolto (ahco.fi)
			$sql = " SELECT * 	 FROM `" . _DB_PREFIX_ . "mhlahella`
					WHERE id_cart = '" . (int) $id_cart . "'
					LIMIT 1";
			$res = Db::getInstance()->ExecuteS($sql);
			
			if (Db::getInstance()->NumRows() == 0)
			{
				header("HTTP/1.0 303 See Other", true, 303);
				header('Location: ' . $base_url . '?fc=module&module=klarnaofficial&controller=checkoutklarna&pickupalert=1&zip=' . $zip . '#KCO_shipping_start');
			}
		}
		// no carrier selected
		elseif ($cart->id_carrier == 0)
		{
			header("HTTP/1.0 303 See Other", true, 303);
			header('Location: ' . $base_url . '?fc=module&module=klarnaofficial&controller=checkoutklarna&pickupalert=1&zip=' . $zip . '#KCO_shipping_start');
		}
			
		// ok
		header( "HTTP/1.1 200 OK" );
    }
}

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

class KlarnaOfficial extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'klarnaofficial';
        $this->tab = 'payments_gateways';
        $this->version = '1.8.36';
        $this->author = 'Prestaworks AB';
        $this->module_key = '0969b3c2f7f0d687c526fbcb0906e204';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('Klarna');
        $this->description = $this->l('Gateway for Klarna (KCO and KPM).');
    }

    public function uninstall()
    {
        if (parent::uninstall() == false ||
            Configuration::deleteByName('KCO_ROUNDOFF') == false ||
            Configuration::deleteByName('KCO_SHOW_IN_PAYMENTS') == false ||
            Configuration::deleteByName('KCO_NORWAY_ADDR') == false ||
            Configuration::deleteByName('KCO_SWEDEN_ADDR') == false ||
            Configuration::deleteByName('KCO_FINLAND_ADDR') == false ||
            Configuration::deleteByName('KCO_UK_ADDR') == false ||
            Configuration::deleteByName('KCO_GERMANY_ADDR') == false ||
            Configuration::deleteByName('KCO_COLORBUTTON') == false ||
            Configuration::deleteByName('KCO_COLORBUTTONTEXT') == false ||
            Configuration::deleteByName('KCO_COLORCHECKBOX') == false ||
            Configuration::deleteByName('KCO_COLORCHECKBOXMARK') == false ||
            Configuration::deleteByName('KCO_COLORHEADER') == false ||
            Configuration::deleteByName('KCO_COLORLINK') == false ||
            Configuration::deleteByName('KCO_UK_SECRET') == false ||
            Configuration::deleteByName('KCO_SWEDEN_SECRET') == false ||
            Configuration::deleteByName('KCO_FINLAND_SECRET') == false ||
            Configuration::deleteByName('KCO_NORWAY_SECRET') == false ||
            Configuration::deleteByName('KCO_GERMANY_SECRET') == false ||
            Configuration::deleteByName('KCO_UK_EID') == false ||
            Configuration::deleteByName('KCO_SWEDEN_EID') == false ||
            Configuration::deleteByName('KCO_NORWAY_EID') == false ||
            Configuration::deleteByName('KCO_FINLAND_EID') == false ||
            Configuration::deleteByName('KCO_GERMANY_EID') == false ||
            Configuration::deleteByName('KCO_AUTOFOCUS') == false ||
            Configuration::deleteByName('KCO_TESTMODE') == false ||
            Configuration::deleteByName('KCO_LAYOUT') == false ||
            Configuration::deleteByName('KCO_NORWAY') == false ||
            Configuration::deleteByName('KCO_FINLAND') == false ||
            Configuration::deleteByName('KCO_UK') == false ||
            Configuration::deleteByName('KCO_SWEDEN') == false ||
            Configuration::deleteByName('KCO_GERMANY') == false ||
            Configuration::deleteByName('KCO_ORDERID') == false ||
            Configuration::deleteByName('KCO_ACTIVATE_STATE') == false ||
            Configuration::deleteByName('KCO_CANCEL_STATE') == false ||
            Configuration::deleteByName('KCO_IS_ACTIVE') == false ||
            Configuration::deleteByName('KCO_SENDTYPE') == false ||
            Configuration::deleteByName('KCO_SHOWLINK') == false ||
            Configuration::deleteByName('KPM_SV_EID') == false ||
            Configuration::deleteByName('KPM_SV_SECRET') == false ||
            Configuration::deleteByName('KPM_NO_EID') == false ||
            Configuration::deleteByName('KPM_NO_SECRET') == false ||
            Configuration::deleteByName('KPM_FI_EID') == false ||
            Configuration::deleteByName('KPM_FI_SECRET') == false ||
            Configuration::deleteByName('KPM_DA_EID') == false ||
            Configuration::deleteByName('KPM_DA_SECRET') == false ||
            Configuration::deleteByName('KPM_DE_EID') == false ||
            Configuration::deleteByName('KPM_DE_SECRET') == false ||
            Configuration::deleteByName('KPM_NL_EID') == false ||
            Configuration::deleteByName('KPM_INVOICEFEE') == false ||
            Configuration::deleteByName('KPM_AT_EID') == false ||
            Configuration::deleteByName('KPM_AT_SECRET') == false ||
            Configuration::deleteByName('KPM_LOGO') == false ||
            Configuration::deleteByName('KPM_PENDING_INVOICE') == false ||
            Configuration::deleteByName('KPM_PENDING_PP') == false ||
            Configuration::deleteByName('KPM_ACCEPTED_INVOICE') == false ||
            Configuration::deleteByName('KPM_PENDING_INVOICE') == false ||
            Configuration::deleteByName('KCO_ADD_NEWSLETTERBOX') == false ||
            Configuration::deleteByName('KPM_ACCEPTED_PP') == false
        ) {
            return false;
        }
        $this->dropTables();

        return true;
    }
    public function install()
    {
        if (
            parent::install() == false
            || $this->registerHook('header') == false
            || $this->registerHook('footer') == false
            || $this->registerHook('updateOrderStatus') == false
            || $this->registerHook('displayProductButtons') == false
            || $this->registerHook('payment') == false
            || $this->registerHook('paymentReturn') == false
            || $this->registerHook('displayAdminOrder') == false
            || Configuration::updateValue('KCO_ROUNDOFF', 0) == false
            || $this->setKCOCountrySettings() == false
            ) {
            return false;
        }
        $this->createTables();

        $states = OrderState::getOrderStates(Configuration::get('PS_LANG_DEFAULT'));
        $name = $this->l('Klarna pending invoice');
        $config_name = 'KPM_PENDING_INVOICE';
        $this->createOrderStatus($name, $states, $config_name, false);

        $name = $this->l('Klarna pending partpayment');
        $config_name = 'KPM_PENDING_PP';
        $this->createOrderStatus($name, $states, $config_name, false);

        $name = $this->l('Klarna accepted invoice');
        $config_name = 'KPM_ACCEPTED_INVOICE';
        $this->createOrderStatus($name, $states, $config_name, true);

        $name = $this->l('Klarna accepted partpayment');
        $config_name = 'KPM_ACCEPTED_PP';
        $this->createOrderStatus($name, $states, $config_name, true);

        $metas = array();
        $metas[] = $this->setMeta('module-klarnaofficial-checkoutklarna');
        $metas[] = $this->setMeta('module-klarnaofficial-checkoutklarnauk');
        $metas[] = $this->setMeta('module-klarnaofficial-kpmpartpayment');
        $metas[] = $this->setMeta('module-klarnaofficial-thankyou');
        $metas[] = $this->setMeta('module-klarnaofficial-thankyouuk');
        foreach (Theme::getThemes() as $theme) {
            $theme->updateMetas($metas, false);
        }
            
        return true;
    }
    
    public function setMeta($name)
    {
        $metas = array();
        $name = pSQL($name);
        $sql = "SELECT id_meta FROM `"._DB_PREFIX_."meta` WHERE page='$name'";
        $id_meta = Db::getInstance()->getValue($sql);
        if ((int)$id_meta==0) {
            $meta = new Meta();
            $meta->page = $name;
            $meta->configurable = false;
            $meta->add();

            $metas['id_meta'] = (int)$meta->id;
            $metas['left'] = 0;
            $metas['right'] = 0;
        } else {
            $metas['id_meta'] = (int)$id_meta;
            $metas['left'] = 0;
            $metas['right'] = 0;
        }
        return $metas;
    }

    public function createOrderStatus($name, $states, $config_name, $paid)
    {
        $exists = false;
        foreach ($states as $state) {
            if ($state['name'] == $name) {
                $exists = true;
                Configuration::updateValue($config_name, $state['id_order_state']);

                return;
            }
        }

        $names = array();
        $templates = array();
        if ($exists == false) {
            $orderstate = new OrderState();
            foreach (Language::getLanguages(false) as $language) {
                $names[$language['id_lang']] = $name;
                $templates[$language['id_lang']] = '';
            }
            $orderstate->name = $names;
            $orderstate->send_email = false;
            $orderstate->invoice = true;
            $orderstate->color = '#008cd4';
            $orderstate->unremovable = true;
            $orderstate->hidden = true;
            $orderstate->logable = true;
            $orderstate->paid = $paid;
            $orderstate->save();
            Configuration::updateValue($config_name, $orderstate->id);

            if (!imageResize(
                dirname(__FILE__).'/views/img/klarna_os.gif',
                _PS_IMG_DIR_.'os/'.$orderstate->id.'.gif',
                null,
                null,
                'gif'
            )) {
                return false;
            }
        }
    }

    public function dropTables()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
    }
    public function createTables()
    {
        include(dirname(__FILE__).'/sql/install.php');
    }

    public function getContent()
    {
        $isSaved = false;
        $address_check_done = false;
        $errorMSG = '';

        if (Tools::isSubmit('runcheckup') && Tools::getValue('runcheckup') == '1') {
            $address_check_done = $this->setKCOCountrySettings();
        }
        if (Tools::isSubmit('btnKPMSubmit')) {
            Configuration::updateValue('KPM_SV_EID', Tools::getValue('KPM_SV_EID'));
            Configuration::updateValue('KPM_SV_SECRET', Tools::getValue('KPM_SV_SECRET'));
            Configuration::updateValue('KPM_NO_EID', Tools::getValue('KPM_NO_EID'));
            Configuration::updateValue('KPM_NO_SECRET', Tools::getValue('KPM_NO_SECRET'));
            Configuration::updateValue('KPM_FI_EID', Tools::getValue('KPM_FI_EID'));
            Configuration::updateValue('KPM_FI_SECRET', Tools::getValue('KPM_FI_SECRET'));
            Configuration::updateValue('KPM_DA_EID', Tools::getValue('KPM_DA_EID'));
            Configuration::updateValue('KPM_DA_SECRET', Tools::getValue('KPM_DA_SECRET'));
            Configuration::updateValue('KPM_DE_EID', Tools::getValue('KPM_DE_EID'));
            Configuration::updateValue('KPM_DE_SECRET', Tools::getValue('KPM_DE_SECRET'));
            Configuration::updateValue('KPM_NL_EID', Tools::getValue('KPM_NL_EID'));
            Configuration::updateValue('KPM_NL_SECRET', Tools::getValue('KPM_NL_SECRET'));
            Configuration::updateValue('KPM_AT_EID', Tools::getValue('KPM_AT_EID'));
            Configuration::updateValue('KPM_AT_SECRET', Tools::getValue('KPM_AT_SECRET'));
            Configuration::updateValue('KPM_INVOICEFEE', Tools::getValue('KPM_INVOICEFEE'));
            Configuration::updateValue('KPM_LOGO', Tools::getValue('KPM_LOGO'));
            $isSaved = true;
        }
        if (Tools::isSubmit('btnCommonSubmit')) {
            Configuration::updateValue('KCO_ACTIVATE_STATE', (int) Tools::getValue('KCO_ACTIVATE_STATE'));
            Configuration::updateValue('KCO_CANCEL_STATE', (int) Tools::getValue('KCO_CANCEL_STATE'));
            Configuration::updateValue('KCO_SENDTYPE', (int) Tools::getValue('KCO_SENDTYPE'));
            Configuration::updateValue('KCO_SHOWPRODUCTPAGE', (int) Tools::getValue('KCO_SHOWPRODUCTPAGE'));
            Configuration::updateValue('KCO_PRODUCTPAGELAYOUT', Tools::getValue('KCO_PRODUCTPAGELAYOUT'));
            Configuration::updateValue('KCO_FOOTERLAYOUT', Tools::getValue('KCO_FOOTERLAYOUT'));
            Configuration::updateValue('KCO_TESTMODE', (int) Tools::getValue('KCO_TESTMODE'));
            $isSaved = true;
        }
        if (Tools::isSubmit('btnKCOSubmit')) {
            Configuration::updateValue('KCO_COLORBUTTON', Tools::getValue('KCO_COLORBUTTON'));
            Configuration::updateValue('KCO_ADD_NEWSLETTERBOX', (int) Tools::getValue('KCO_ADD_NEWSLETTERBOX'));
            Configuration::updateValue('KCO_SHOW_IN_PAYMENTS', (int) Tools::getValue('KCO_SHOW_IN_PAYMENTS'));
            Configuration::updateValue('KCO_COLORBUTTONTEXT', Tools::getValue('KCO_COLORBUTTONTEXT'));
            Configuration::updateValue('KCO_COLORCHECKBOX', Tools::getValue('KCO_COLORCHECKBOX'));
            Configuration::updateValue('KCO_COLORCHECKBOXMARK', Tools::getValue('KCO_COLORCHECKBOXMARK'));
            Configuration::updateValue('KCO_COLORHEADER', Tools::getValue('KCO_COLORHEADER'));
            Configuration::updateValue('KCO_COLORLINK', Tools::getValue('KCO_COLORLINK'));
            Configuration::updateValue('KCO_UK_SECRET', Tools::getValue('KCO_UK_SECRET'));
            Configuration::updateValue('KCO_SWEDEN_SECRET', Tools::getValue('KCO_SWEDEN_SECRET'));
            Configuration::updateValue('KCO_NORWAY_SECRET', Tools::getValue('KCO_NORWAY_SECRET'));
            Configuration::updateValue('KCO_FINLAND_SECRET', Tools::getValue('KCO_FINLAND_SECRET'));
            Configuration::updateValue('KCO_GERMANY_SECRET', Tools::getValue('KCO_GERMANY_SECRET'));
            Configuration::updateValue('KCO_UK_EID', Tools::getValue('KCO_UK_EID'));
            Configuration::updateValue('KCO_SWEDEN_EID', (int) Tools::getValue('KCO_SWEDEN_EID'));
            Configuration::updateValue('KCO_NORWAY_EID', (int) Tools::getValue('KCO_NORWAY_EID'));
            Configuration::updateValue('KCO_FINLAND_EID', (int) Tools::getValue('KCO_FINLAND_EID'));
            Configuration::updateValue('KCO_GERMANY_EID', (int) Tools::getValue('KCO_GERMANY_EID'));
            Configuration::updateValue('KCO_ROUNDOFF', (int) Tools::getValue('KCO_ROUNDOFF'));
            Configuration::updateValue('KCO_LAYOUT', (int) Tools::getValue('KCO_LAYOUT'));
            Configuration::updateValue('KCO_NORWAY', (int) Tools::getValue('KCO_NORWAY'));
            Configuration::updateValue('KCO_FINLAND', (int) Tools::getValue('KCO_FINLAND'));
            Configuration::updateValue('KCO_UK', (int) Tools::getValue('KCO_UK'));
            Configuration::updateValue('KCO_SWEDEN', (int) Tools::getValue('KCO_SWEDEN'));
            Configuration::updateValue('KCO_GERMANY', (int) Tools::getValue('KCO_GERMANY'));
            Configuration::updateValue('KCO_ORDERID', (int) Tools::getValue('KCO_ORDERID'));
            Configuration::updateValue('KCO_IS_ACTIVE', (int) Tools::getValue('KCO_IS_ACTIVE'));
            Configuration::updateValue('KCO_SHOWLINK', (int) Tools::getValue('KCO_SHOWLINK'));
            Configuration::updateValue('KCO_AUTOFOCUS', Tools::getValue('KCO_AUTOFOCUS'));
            $isSaved = true;
        }
        $invoice_fee_not_found = false;
        if (Configuration::get('KPM_INVOICEFEE') != '') {
            $feeproduct = $this->getByReference(Configuration::get('KPM_INVOICEFEE'));
            if (!Validate::isLoadedObject($feeproduct)) {
                $invoice_fee_not_found = true;
            }
        }

        if (Tools::getIsset('deleteklarnaofficial')) {
            $segments = explode('-', Tools::getValue('key_id'));
            if (count($segments) === 2) {
                list($eid, $pclass) = $segments;
                $eid = pSQL($eid);
                $pclass = pSQL($pclass);
                $delete_sql = "DELETE FROM `"._DB_PREFIX_."kpmpclasses` WHERE id=$pclass AND eid=$eid";
                Db::getInstance()->execute($delete_sql);
            }
        }
        if (Tools::getIsset('updateplcassklarnaofficial')) {
            $eids = array();
            if (Configuration::get('KPM_SV_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_SV_EID'),
                    'secret' => Configuration::get('KPM_SV_SECRET'),
                    'lang' => 'sv',
                    'country' => 'se',
                    'currency' => 'sek'
                );
            }
            if (Configuration::get('KPM_NO_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_NO_EID'),
                    'secret' => Configuration::get('KPM_NO_SECRET'),
                    'lang' => 'no',
                    'country' => 'no',
                    'currency' => 'nok'
                );
            }
            if (Configuration::get('KPM_FI_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_FI_EID'),
                    'secret' => Configuration::get('KPM_FI_SECRET'),
                    'lang' => 'fi',
                    'country' => 'fi',
                    'currency' => 'eur'
                );
            }
            if (Configuration::get('KPM_DA_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_DA_EID'),
                    'secret' => Configuration::get('KPM_DA_SECRET'),
                    'lang' => 'da',
                    'country' => 'dk',
                    'currency' => 'dkk'
                );
            }
            if (Configuration::get('KPM_DE_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_DE_EID'),
                    'secret' => Configuration::get('KPM_DE_SECRET'),
                    'lang' => 'de',
                    'country' => 'de',
                    'currency' => 'eur'
                );
            }
            if (Configuration::get('KPM_NL_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_NL_EID'),
                    'secret' => Configuration::get('KPM_NL_SECRET'),
                    'lang' => 'nl',
                    'country' => 'nl',
                    'currency' => 'eur'
                );
            }
            if (Configuration::get('KPM_AT_EID') != '') {
                $eids[] = array(
                    'eid' => Configuration::get('KPM_AT_EID'),
                    'secret' => Configuration::get('KPM_AT_SECRET'),
                    'lang' => 'de',
                    'country' => 'at',
                    'currency' => 'eur'
                );
            }

            foreach ($eids as $eid) {
                $k = $this->initKlarnaAPI(
                    $eid['eid'],
                    $eid['secret'],
                    $eid['country'],
                    $eid['lang'],
                    $eid['currency']
                );
                
                try {
                    $k->fetchPClasses();
                    $k->getAllPClasses();
                } catch (Exception $e) {
                    $errorMSG = "{$e->getMessage()} (#{$e->getCode()})";
                }
            }
        }

        $this->context->smarty->assign(array(
            'errorMSG' => $errorMSG,
            'address_check_done' => $address_check_done,
            'isSaved' => $isSaved,
            'invoice_fee_not_found' => $invoice_fee_not_found,
            'commonform' => $this->createCommonForm(),
            'kpmform' => $this->createKPMForm(),
            'kcoform' => $this->createKCOForm(),
            'pclasslist' => $this->renderPclassList(),
            'REQUEST_URI' => Tools::safeOutput($_SERVER['REQUEST_URI']),
        ));

        return '<script type="text/javascript">var pwd_base_uri = "'.
        __PS_BASE_URI__.'";var pwd_refer = "'.
        (int) Tools::getValue('ref').'";</script>'.
        $this->display(__FILE__, 'views/templates/admin/klarna_admin.tpl');
    }

    public function renderPclassList()
    {
        $fields_list = array(
            'eid' => array(
                'title' => $this->l('EID'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'id' => array(
                'title' => $this->l('Pclass'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'description' => array(
                'title' => $this->l('Description'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'months' => array(
                'title' => $this->l('Months'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'interestrate' => array(
                'title' => $this->l('Interest rate'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'invoicefee' => array(
                'title' => $this->l('Invoice fee'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'startfee' => array(
                'title' => $this->l('Start fee'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'minamount' => array(
                'title' => $this->l('Min amount'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'country' => array(
                'title' => $this->l('Country'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'expire' => array(
                'title' => $this->l('Valid to'),
                'type' => 'date',
                'search' => false,
                'orderby' => false,
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'key_id';
        $helper->actions = array('delete');
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex.'&configure='.
            $this->name.'&updateplcass'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Update pclasses'),
        );

        $helper->title = $this->l('Pclasses');
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $sql = "SELECT *, CONCAT(eid, '-', id) as key_id FROM `"._DB_PREFIX_."kpmpclasses`";
        $content = Db::getInstance()->ExecuteS($sql);

        return $helper->generateList($content, $fields_list);
    }

    public function createCommonForm()
    {
        $states = OrderState::getOrderStates((int) $this->context->cookie->id_lang);
        $states[] = array('id_order_state' => '-1', 'name' => $this->l('Deactivated'));
        
        $fields_form = array();
        $fields_form[0]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Common Settings'),
                    'icon' => 'icon-cogs',
                  ),
                'input' => array(
                //common settings
                array(
                    'type' => 'switch',
                    'label' => $this->l('Testdrive'),
                    'name' => 'KCO_TESTMODE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'testmode_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'testmode_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Activate test drive.'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Send invoice method'),
                    'name' => 'KCO_SENDTYPE',
                    'desc' => $this->l('Send invoices by e-mail or regular mail.'),
                    'options' => array(
                        'query' => array(
                        array(
                            'value' => 1,
                            'label' => $this->l('E-mail'), ),
                        array(
                            'value' => 0,
                            'label' => $this->l('Mail'), ),
                    ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),

                array(
                    'type' => 'select',
                    'label' => $this->l('Activate order status'),
                    'name' => 'KCO_ACTIVATE_STATE',
                    'desc' => $this->l('Activate order will be sent to klarna when this order status is set.'),
                    'options' => array(
                        'query' => $states,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ),
                ),

                array(
                    'type' => 'select',
                    'label' => $this->l('Cancel reservation status'),
                    'name' => 'KCO_CANCEL_STATE',
                    'desc' => $this->l('Cancel order will be sent to klarna when this order status is set.'),
                    'options' => array(
                        'query' => $states,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ),
                ),

                array(
                    'type' => 'select',
                    'label' => $this->l('Show Payment Widget'),
                    'name' => 'KCO_SHOWPRODUCTPAGE',
                    'desc' => $this->l('Display payment options on the product page.'),
                    'options' => array(
                        'query' => array(
                        array(
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),

                array(
                    'type' => 'select',
                    'label' => $this->l('Payment Widget Layout'),
                    'name' => 'KCO_PRODUCTPAGELAYOUT',
                    'desc' => $this->l('Choose a layout for the Payment widget.'),
                    'options' => array(
                        'query' => array(
                        array(
                            'value' => 'pale-v2',
                            'label' => $this->l('pale-v2'), ),
                        array(
                            'value' => 'dark-v2',
                            'label' => $this->l('dark-v2'), ),
                        array(
                            'value' => 'deep-v2',
                            'label' => $this->l('deep-v2'), ),
                        array(
                            'value' => 'deep-extra-v2',
                            'label' => $this->l('deep-extra-v2'), ),
                    ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Footer Tooltip Layout'),
                    'name' => 'KCO_FOOTERLAYOUT',
                    'desc' => $this->l('Choose a layout for the Footer Toolip.'),
                    'options' => array(
                        'query' => array(
                        array(
                            'value' => 'long-blue',
                            'label' => $this->l('long-blue (KCO)'), ),
                        array(
                            'value' => 'long-white',
                            'label' => $this->l('long-white (KCO)'), ),
                        array(
                            'value' => 'short-blue',
                            'label' => $this->l('short-blue (KCO)'), ),
                        array(
                            'value' => 'short-white',
                            'label' => $this->l('short-white (KCO)'), ),
                        array(
                            'value' => 'blue-black',
                            'label' => $this->l('blue-black (only KPM)'), ),
                        array(
                            'value' => 'white',
                            'label' => $this->l('white (only KPM)'), ),
                        array(
                            'value' => 'blue-black+tuv',
                            'label' => $this->l('blue-black+tuv (only KPM)'), ),
                        array(
                            'value' => 'white+tuv',
                            'label' => $this->l('white+tuv (only KPM)'), ),
                    ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),
                //common settings
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang = 0;
        }
        
        $helper->submit_action = 'btnCommonSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
        '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }
    public function createKPMForm()
    {
        $fields_form = array();

        $fields_form[0] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                'input' => array(
                //KPM
                array(
                    'type' => 'select',
                    'label' => $this->l('Klarna logo'),
                    'name' => 'KPM_LOGO',
                    'desc' => $this->l('Select what logo is used in the checkout.'),
                    'options' => array(
                        'query' => array(
                        array(
                            'value' => 'blue-black',
                            'label' => $this->l('Light background'), ),
                        array(
                            'value' => 'white',
                            'label' => $this->l('Dark background'), ),
                    ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Invoice fee product'),
                    'name' => 'KPM_INVOICEFEE',
                    'class' => 'fixed-width-lg',
                    'required' => false,
                ),
                //KPM

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[1] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: SWEDEN
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sweden EID'),
                        'name' => 'KPM_SV_EID',
                        'class' => 'fixed-width-lg',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sweden shared secret'),
                        'name' => 'KPM_SV_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[2] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: NORWAY
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Norway EID'),
                        'name' => 'KPM_NO_EID',
                        'class' => 'fixed-width-lg',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Norway shared secret'),
                        'name' => 'KPM_NO_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[3] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: FINLAND
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Finland EID'),
                        'class' => 'fixed-width-lg',
                        'name' => 'KPM_FI_EID',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Finland shared secret'),
                        'name' => 'KPM_FI_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[4] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: DENMARK
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Denmark EID'),
                        'class' => 'fixed-width-lg',
                        'name' => 'KPM_DA_EID',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Denmark shared secret'),
                        'name' => 'KPM_DA_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[5] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: GERMANY
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Germany EID'),
                        'class' => 'fixed-width-lg',
                        'name' => 'KPM_DE_EID',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Germany shared secret'),
                        'name' => 'KPM_DE_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[6] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: NETHERLANDS
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Netherlands EID'),
                        'class' => 'fixed-width-lg',
                        'name' => 'KPM_NL_EID',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Netherlands shared secret'),
                        'name' => 'KPM_NL_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $fields_form[7] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                  //KPM: AUSTRIA
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Austria EID'),
                        'class' => 'fixed-width-lg',
                        'name' => 'KPM_AT_EID',
                        'required' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Austria shared secret'),
                        'name' => 'KPM_AT_SECRET',
                        'required' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang = 0;
        }
        $helper->submit_action = 'btnKPMSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ).'&configure='.$this->name.
        '&tab_module='.$this->tab.'&module_name='.$this->name;
        
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }
    public function createKCOForm()
    {
        $fields_form = array();
        $fields_form[0]['form'] = array(
                'legend' => array(
                    'title' => $this->l('KCO Settings'),
                    'icon' => 'icon-AdminAdmin',
                  ),
                'input' => array(
                //KCO: GENERAL
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active KCO in this shop'),
                    'name' => 'KCO_IS_ACTIVE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'kco_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'kco_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Activate KCO for this show, if set to no, KPM will be used.'),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Send id_order as order identifier'),
                    'name' => 'KCO_ORDERID',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'orderidentifier_on',
                            'value' => 1,
                            'label' => $this->l('id_order'), ),
                        array(
                            'id' => 'orderidentifier_off',
                            'value' => 0,
                            'label' => $this->l('Reference'), ),
                    ),
                    'desc' => $this->l('Order identifier sent to Klarna Online, Yes = id_order, No = order reference.'),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Round off total'),
                    'name' => 'KCO_ROUNDOFF',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'roundoff_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'roundoff_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Round off total value.'),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Use two column checkout'),
                    'name' => 'KCO_LAYOUT',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'layout_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'layout_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Use the two column layout.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate Autofocus'),
                    'name' => 'KCO_AUTOFOCUS',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'autofocus_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'autofocus_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Recommended setting is no.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show link to old checkout'),
                    'name' => 'KCO_SHOWLINK',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'showlink_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'showlink_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Show a link to the old checkout.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show link to KCO in checkout'),
                    'name' => 'KCO_SHOW_IN_PAYMENTS',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'showlink_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ),
                        array(
                            'id' => 'showlink_off',
                            'value' => 0,
                            'label' => $this->l('No'), ),
                    ),
                    'desc' => $this->l('Show a link to KCO in the old checkout.'),
                ),
                
                array(
                    'type' => 'select',
                    'label' => $this->l('Offer newsletter signup'),
                    'name' => 'KCO_ADD_NEWSLETTERBOX',
                    'desc' => $this->l('Show checkbox in kco window.'),
                    'options' => array(
                        'query' => array(
                        array(
                            'value' => 0,
                            'label' => $this->l('Yes, show sign up box'), ),
                        array(
                            'value' => 1,
                            'label' => $this->l('Yes, show sign up box (prechecked)'), ),
                        array(
                            'value' => 2,
                            'label' => $this->l('No, do not show (all customers set to subscribers)'), ),
                        array(
                            'value' => 3,
                            'label' => $this->l('No, do not show, customers are not set as subscribers'), ),
                    ),
                        'id' => 'value',
                        'name' => 'label',
                    ),
                ),

                //KCO
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $fields_form[1]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Color settings'),
                    'icon' => 'icon-AdminParentPreferences',
                  ),
                'input' => array(
                //KCO: COLOR SETTINGS
                array(
                        'type' => 'color',
                        'class' => 'color mColorPickerInput',
                        'label' => $this->l('Color buttons'),
                        'name' => 'KCO_COLORBUTTON',
                        'desc' => $this->l('Adjust the color of buttons in KCO window.'),
                        'required' => false,
                    ),
                array(
                        'type' => 'color',
                        'class' => 'color mColorPickerInput',
                        'label' => $this->l('Color button text'),
                        'name' => 'KCO_COLORBUTTONTEXT',
                        'desc' => $this->l('Adjust the color of texts on buttons in KCO window.'),
                        'required' => false,
                    ),
                array(
                        'type' => 'color',
                        'class' => 'color mColorPickerInput',
                        'label' => $this->l('Color checkbox'),
                        'name' => 'KCO_COLORCHECKBOX',
                        'desc' => $this->l('Adjust the color of checkbox in KCO window.'),
                        'required' => false,
                    ),
                array(
                        'type' => 'color',
                        'class' => 'color mColorPickerInput',
                        'label' => $this->l('Color checkbox marker'),
                        'name' => 'KCO_COLORCHECKBOXMARK',
                        'desc' => $this->l('Adjust the color of checkbox marker in KCO window.'),
                        'required' => false,
                    ),
                array(
                        'type' => 'color',
                        'class' => 'color mColorPickerInput',
                        'label' => $this->l('Color header'),
                        'name' => 'KCO_COLORHEADER',
                        'desc' => $this->l('Adjust the color of titles in KCO window.'),
                        'required' => false,
                    ),
                array(
                        'type' => 'color',
                        'class' => 'color mColorPickerInput',
                        'label' => $this->l('Color link'),
                        'desc' => $this->l('Adjust the color of all links in KCO window.'),
                        'name' => 'KCO_COLORLINK',
                        'required' => false,
                    ),
                //KCO
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $fields_form[2]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Sweden'),
                    'icon' => 'icon-AdminParentLocalization',
                  ),
                'input' => array(
                //KCO: SWEDEN
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active KCO Sweden'),
                        'name' => 'KCO_SWEDEN',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'sweden_on',
                                'value' => 1,
                                'label' => $this->l('Yes'), ),
                            array(
                                'id' => 'sweden_off',
                                'value' => 0,
                                'label' => $this->l('No'), ),
                        ),
                        'desc' => $this->l('Activate KCO for Sweden, SEK and SE language required.'),
                    ),

                    array(
                        'type' => 'text',
                        'label' => $this->l('EID'),
                        'name' => 'KCO_SWEDEN_EID',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shared secret'),
                        'name' => 'KCO_SWEDEN_SECRET',
                        'required' => true,
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $fields_form[3]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Norway'),
                    'icon' => 'icon-AdminParentLocalization',
                  ),
                'input' => array(
                //KCO: NORWAY
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active KCO Norway'),
                        'name' => 'KCO_NORWAY',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'norway_on',
                                'value' => 1,
                                'label' => $this->l('Yes'), ),
                            array(
                                'id' => 'norway_off',
                                'value' => 0,
                                'label' => $this->l('No'), ),
                        ),
                        'desc' => $this->l('Activate KCO for Norway, NOK and NO language required.'),
                    ),

                    array(
                        'type' => 'text',
                        'label' => $this->l('EID'),
                        'name' => 'KCO_NORWAY_EID',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shared secret'),
                        'name' => 'KCO_NORWAY_SECRET',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $fields_form[4]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Finland'),
                    'icon' => 'icon-AdminParentLocalization',
                  ),
                'input' => array(
                //KCO: FINLAND
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active KCO Finland'),
                        'name' => 'KCO_FINLAND',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'finland_on',
                                'value' => 1,
                                'label' => $this->l('Yes'), ),
                            array(
                                'id' => 'finland_off',
                                'value' => 0,
                                'label' => $this->l('No'), ),
                        ),
                        'desc' => $this->l('Activate KCO for Finland, EUR and FI and or SE languages required.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('EID'),
                        'name' => 'KCO_FINLAND_EID',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shared secret'),
                        'name' => 'KCO_FINLAND_SECRET',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $fields_form[5]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Germany'),
                    'icon' => 'icon-AdminParentLocalization',
                  ),
                'input' => array(
                //KCO: GERMANY
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active KCO Germany'),
                        'name' => 'KCO_GERMANY',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'germany_on',
                                'value' => 1,
                                'label' => $this->l('Yes'), ),
                            array(
                                'id' => 'germany_off',
                                'value' => 0,
                                'label' => $this->l('No'), ),
                        ),
                        'desc' => $this->l('Activate KCO for Germany, EUR and DE language required.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('EID'),
                        'name' => 'KCO_GERMANY_EID',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                    ),

                array(
                        'type' => 'text',
                        'label' => $this->l('Shared secret'),
                        'name' => 'KCO_GERMANY_SECRET',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $fields_form[6]['form'] = array(
                'legend' => array(
                    'title' => $this->l('United kingdom'),
                    'icon' => 'icon-AdminParentLocalization',
                  ),
                'input' => array(
                //KCO: UK
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active KCO UK'),
                        'name' => 'KCO_UK',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'uk_on',
                                'value' => 1,
                                'label' => $this->l('Yes'), ),
                            array(
                                'id' => 'uk_off',
                                'value' => 0,
                                'label' => $this->l('No'), ),
                        ),
                        'desc' => $this->l('Activate KCO for UK, GBP and EN language required.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('EID'),
                        'name' => 'KCO_UK_EID',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                    ),

                array(
                        'type' => 'text',
                        'label' => $this->l('Shared secret'),
                        'name' => 'KCO_UK_SECRET',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang = 0;
        }
        $helper->submit_action = 'btnKCOSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ).'&configure='.$this->name.
        '&tab_module='.$this->tab.'&module_name='.$this->name;
        
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }

    public function getConfigFieldsValues()
    {
        return array(
            'KCO_SHOW_IN_PAYMENTS' => Tools::getValue(
                'KCO_SHOW_IN_PAYMENTS',
                Configuration::get('KCO_SHOW_IN_PAYMENTS')
            ),
            'KCO_ADD_NEWSLETTERBOX' => Tools::getValue(
                'KCO_ADD_NEWSLETTERBOX',
                Configuration::get('KCO_ADD_NEWSLETTERBOX')
            ),
            'KCO_COLORBUTTONTEXT' => Tools::getValue(
                'KCO_COLORBUTTONTEXT',
                Configuration::get('KCO_COLORBUTTONTEXT')
            ),
            'KCO_COLORCHECKBOX' => Tools::getValue(
                'KCO_COLORCHECKBOX',
                Configuration::get('KCO_COLORCHECKBOX')
            ),
            'KCO_COLORCHECKBOXMARK' => Tools::getValue(
                'KCO_COLORCHECKBOXMARK',
                Configuration::get('KCO_COLORCHECKBOXMARK')
            ),
            'KCO_COLORHEADER' => Tools::getValue(
                'KCO_COLORHEADER',
                Configuration::get('KCO_COLORHEADER')
            ),
            'KCO_COLORLINK' => Tools::getValue(
                'KCO_COLORLINK',
                Configuration::get('KCO_COLORLINK')
            ),
            'KCO_TESTMODE' => Tools::getValue(
                'KCO_TESTMODE',
                Configuration::get('KCO_TESTMODE')
            ),
            'KCO_ORDERID' => Tools::getValue(
                'KCO_ORDERID',
                Configuration::get('KCO_ORDERID')
            ),
            'KCO_ROUNDOFF' => Tools::getValue(
                'KCO_ROUNDOFF',
                Configuration::get('KCO_ROUNDOFF')
            ),
            'KCO_UK_EID' => Tools::getValue(
                'KCO_UK_EID',
                Configuration::get('KCO_UK_EID')
            ),
            'KCO_SWEDEN_EID' => Tools::getValue(
                'KCO_SWEDEN_EID',
                Configuration::get('KCO_SWEDEN_EID')
            ),
            'KCO_FINLAND_EID' => Tools::getValue(
                'KCO_FINLAND_EID',
                Configuration::get('KCO_FINLAND_EID')
            ),
            'KCO_GERMANY_EID' => Tools::getValue(
                'KCO_GERMANY_EID',
                Configuration::get('KCO_GERMANY_EID')
            ),
            'KCO_NORWAY_EID' => Tools::getValue(
                'KCO_NORWAY_EID',
                Configuration::get('KCO_NORWAY_EID')
            ),
            'KCO_UK_SECRET' => Tools::getValue(
                'KCO_UK_SECRET',
                Configuration::get('KCO_UK_SECRET')
            ),
            'KCO_SWEDEN_SECRET' => Tools::getValue(
                'KCO_SWEDEN_SECRET',
                Configuration::get('KCO_SWEDEN_SECRET')
            ),
            'KCO_NORWAY_SECRET' => Tools::getValue(
                'KCO_NORWAY_SECRET',
                Configuration::get('KCO_NORWAY_SECRET')
            ),
            'KCO_GERMANY_SECRET' => Tools::getValue(
                'KCO_GERMANY_SECRET',
                Configuration::get('KCO_GERMANY_SECRET')
            ),
            'KCO_FINLAND_SECRET' => Tools::getValue(
                'KCO_FINLAND_SECRET',
                Configuration::get('KCO_FINLAND_SECRET')
            ),
            'KCO_LAYOUT' => Tools::getValue(
                'KCO_LAYOUT',
                Configuration::get('KCO_LAYOUT')
            ),
            'KCO_UK' => Tools::getValue(
                'KCO_UK',
                Configuration::get('KCO_UK')
            ),
            'KCO_SWEDEN' => Tools::getValue(
                'KCO_SWEDEN',
                Configuration::get('KCO_SWEDEN')
            ),
            'KCO_NORWAY' => Tools::getValue(
                'KCO_NORWAY',
                Configuration::get('KCO_NORWAY')
            ),
            'KCO_FINLAND' => Tools::getValue(
                'KCO_FINLAND',
                Configuration::get('KCO_FINLAND')
            ),
            'KCO_GERMANY' => Tools::getValue(
                'KCO_GERMANY',
                Configuration::get('KCO_GERMANY')
            ),
            'KCO_COLORBUTTON' => Tools::getValue(
                'KCO_COLORBUTTON',
                Configuration::get('KCO_COLORBUTTON')
            ),
            'KCO_SENDTYPE' => Tools::getValue(
                'KCO_SENDTYPE',
                Configuration::get('KCO_SENDTYPE')
            ),
            'KCO_ACTIVATE_STATE' => Tools::getValue(
                'KCO_ACTIVATE_STATE',
                Configuration::get('KCO_ACTIVATE_STATE')
            ),
            'KCO_CANCEL_STATE' => Tools::getValue(
                'KCO_CANCEL_STATE',
                Configuration::get('KCO_CANCEL_STATE')
            ),
            'KCO_IS_ACTIVE' => Tools::getValue(
                'KCO_IS_ACTIVE',
                Configuration::get('KCO_IS_ACTIVE')
            ),
            'KCO_SHOWLINK' => Tools::getValue(
                'KCO_SHOWLINK',
                Configuration::get('KCO_SHOWLINK')
            ),
            'KCO_AUTOFOCUS' => Tools::getValue(
                'KCO_AUTOFOCUS',
                Configuration::get('KCO_AUTOFOCUS')
            ),
            'KPM_NL_SECRET' => Tools::getValue(
                'KPM_NL_SECRET',
                Configuration::get('KPM_NL_SECRET')
            ),
            'KPM_NL_EID' => Tools::getValue(
                'KPM_NL_EID',
                Configuration::get('KPM_NL_EID')
            ),
            'KPM_DE_SECRET' => Tools::getValue(
                'KPM_DE_SECRET',
                Configuration::get('KPM_DE_SECRET')
            ),
            'KPM_DE_EID' => Tools::getValue(
                'KPM_DE_EID',
                Configuration::get('KPM_DE_EID')
            ),
            'KPM_DA_SECRET' => Tools::getValue(
                'KPM_DA_SECRET',
                Configuration::get('KPM_DA_SECRET')
            ),
            'KPM_DA_EID' => Tools::getValue(
                'KPM_DA_EID',
                Configuration::get('KPM_DA_EID')
            ),
            'KPM_FI_SECRET' => Tools::getValue(
                'KPM_FI_SECRET',
                Configuration::get('KPM_FI_SECRET')
            ),
            'KPM_FI_EID' => Tools::getValue(
                'KPM_FI_EID',
                Configuration::get('KPM_FI_EID')
            ),
            'KPM_NO_SECRET' => Tools::getValue(
                'KPM_NO_SECRET',
                Configuration::get('KPM_NO_SECRET')
            ),
            'KPM_NO_EID' => Tools::getValue(
                'KPM_NO_EID',
                Configuration::get('KPM_NO_EID')
            ),
            'KPM_SV_SECRET' => Tools::getValue(
                'KPM_SV_SECRET',
                Configuration::get('KPM_SV_SECRET')
            ),
            'KPM_SV_EID' => Tools::getValue(
                'KPM_SV_EID',
                Configuration::get('KPM_SV_EID')
            ),
            'KPM_INVOICEFEE' => Tools::getValue(
                'KPM_INVOICEFEE',
                Configuration::get('KPM_INVOICEFEE')
            ),
            'KPM_AT_EID' => Tools::getValue(
                'KPM_AT_EID',
                Configuration::get('KPM_AT_EID')
            ),
            'KPM_AT_SECRET' => Tools::getValue(
                'KPM_AT_SECRET',
                Configuration::get('KPM_AT_SECRET')
            ),
            'KPM_LOGO' => Tools::getValue(
                'KPM_LOGO',
                Configuration::get('KPM_LOGO')
            ),
            'KCO_SHOWPRODUCTPAGE' => Tools::getValue(
                'KCO_SHOWPRODUCTPAGE',
                Configuration::get('KCO_SHOWPRODUCTPAGE')
            ),
            'KCO_PRODUCTPAGELAYOUT' => Tools::getValue(
                'KCO_PRODUCTPAGELAYOUT',
                Configuration::get('KCO_PRODUCTPAGELAYOUT')
            ),
            'KCO_FOOTERLAYOUT' => Tools::getValue(
                'KCO_FOOTERLAYOUT',
                Configuration::get('KCO_FOOTERLAYOUT')
            ),
        );
    }

    public function hookDisplayProductButtons($params)
    {
        if ((int) Configuration::get('KCO_SHOWPRODUCTPAGE') == 0) {
            return;
        }
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }
        if (configuration::get('KPM_INVOICEFEE') != '') {
            $invoicefee = $this->getByReference(Configuration::get('KPM_INVOICEFEE'));
            if (Validate::isLoadedObject($invoicefee)) {
                $klarna_invoice_fee = $invoicefee->getPrice();
            } else {
                $klarna_invoice_fee = 0;
            }
        } else {
            $klarna_invoice_fee = 0;
        }

        $klarna_eid = '';
        $country_iso = '';
        if (isset($this->context->cart) &&
        isset($this->context->cart->id_address_delivery) &&
        (int) $this->context->cart->id_address_delivery > 0) {
            $address = new Address($this->context->cart->id_address_delivery);
            $country_iso = Country::getIsoById($address->id_country);
        } else {
            if (isset($this->context->language) &&
            isset($this->context->language->id) &&
            (int) $this->context->language->id > 0) {
                $language_iso = Language::getIsoById((int) $this->context->language->id);
            } else {
                $language_iso = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
            }
            $language_iso = Tools::strtolower($language_iso);
            if ($language_iso == 'sv') {
                $country_iso = 'se';
            }
            if ($language_iso == 'no' || $language_iso == 'nn' || $language_iso == 'nb') {
                $country_iso = 'no';
            }
            if ($language_iso == 'fi') {
                $country_iso = 'fi';
            }
            if ($language_iso == 'de') {
                $country_iso = 'de';
            }
            if ($language_iso == 'da') {
                $country_iso = 'da';
            }
            if ($language_iso == 'at') {
                $country_iso = 'at';
            }
            if ($language_iso == 'en') {
                $country_iso = 'gb';
            }
        }

        if ($country_iso == '') {
            $country_iso = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        }
        $country_iso = Tools::strtolower($country_iso);

        if ($country_iso == 'se') {
            if ((int) Configuration::get('KCO_SWEDEN', null, null, $this->context->shop->id) == 1) {
                $klarna_eid = Configuration::get('KCO_SWEDEN_EID', null, null, $this->context->shop->id);
            } else {
                $klarna_eid = Configuration::get('KPM_SV_EID', null, null, $this->context->shop->id);
            }
        }
        if ($country_iso == 'no') {
            if ((int) Configuration::get('KCO_NORWAY', null, null, $this->context->shop->id) == 1) {
                $klarna_eid = Configuration::get('KCO_NORWAY_EID', null, null, $this->context->shop->id);
            } else {
                $klarna_eid = Configuration::get('KPM_NO_EID', null, null, $this->context->shop->id);
            }
        }
        if ($country_iso == 'de') {
            if ((int) Configuration::get('KCO_GERMANY', null, null, $this->context->shop->id) == 1) {
                $klarna_eid = Configuration::get('KCO_GERMANY_EID', null, null, $this->context->shop->id);
            } else {
                $klarna_eid = Configuration::get('KPM_DE_EID', null, null, $this->context->shop->id);
            }
        }
        if ($country_iso == 'da') {
            $klarna_eid = Configuration::get('KPM_DA_EID', null, null, $this->context->shop->id);
        }
        if ($country_iso == 'fi') {
            if ((int) Configuration::get('KCO_FINLAND', null, null, $this->context->shop->id) == 1) {
                $klarna_eid = Configuration::get('KCO_FINLAND_EID', null, null, $this->context->shop->id);
            } else {
                $klarna_eid = Configuration::get('KPM_FI_EID', null, null, $this->context->shop->id);
            }
        }
        if ($country_iso == 'nl') {
            $klarna_eid = Configuration::get('KPM_NL_EID', null, null, $this->context->shop->id);
        }
        if ($country_iso == 'at') {
            $klarna_eid = Configuration::get('KPM_AT_EID', null, null, $this->context->shop->id);
        }
        if ($country_iso == 'gb') {
            return;
        }

        if ($klarna_eid == '') {
            return;
        }

        $this->context->smarty->assign('kcoeid', $klarna_eid);
        $productPrice = Product::getPriceStatic(
            (int) Tools::getValue('id_product'),
            true,
            null,
            6,
            null,
            false,
            true,
            1,
            false
        );
        $this->context->smarty->assign('kcoproductPrice', $productPrice);
        $klarna_locale = $this->getKlarnaLocale();

        $this->context->smarty->assign('klarna_invoice_fee', $klarna_invoice_fee);
        $this->context->smarty->assign('klarna_locale', $klarna_locale);
        $this->context->smarty->assign('klarna_widget_layout', Configuration::get('KCO_PRODUCTPAGELAYOUT'));

        return $this->display(__FILE__, 'klarnaproductpage.tpl');
    }
    public function hookFooter($params)
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }
        if (isset($this->context->language) &&
        isset($this->context->language->id) &&
        (int) $this->context->language->id > 0) {
            $language_iso = Language::getIsoById((int) $this->context->language->id);
        } else {
            $language_iso = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        }

        $kco_active = false;
        $eid = '';
        if ($language_iso == 'sv') {
            if (Configuration::get('KCO_IS_ACTIVE')) {
                $eid = Configuration::get('KCO_SWEDEN_EID');
                $kco_active = true;
            } else {
                $eid = Configuration::get('KPM_SV_EID');
            }
        }
        if ($language_iso == 'nb' || $language_iso == 'no' || $language_iso == 'nn') {
            if (Configuration::get('KCO_IS_ACTIVE')) {
                $eid = Configuration::get('KCO_NORWAY_EID');
                $kco_active = true;
            } else {
                $eid = Configuration::get('KPM_NO_EID');
            }
        }
        if ($language_iso == 'de') {
            if (Configuration::get('KCO_IS_ACTIVE')) {
                $eid = Configuration::get('KCO_GERMANY_EID');
                $kco_active = true;
            } else {
                $eid = Configuration::get('KPM_DE_EID');
            }
        }
        if ($language_iso == 'fi') {
            if (Configuration::get('KCO_IS_ACTIVE')) {
                $eid = Configuration::get('KCO_FINLAND_EID');
                $kco_active = true;
            } else {
                $eid = Configuration::get('KPM_FI_EID');
            }
        }
        if ($language_iso == 'en') {
            if (Configuration::get('KCO_IS_ACTIVE')) {
                $eid = Configuration::get('KCO_UK_EID');
                $kco_active = true;
            } else {
                $eid = Configuration::get('KPM_UK_EID');
            }
        }
        if ($language_iso == 'nl') {
            $eid = Configuration::get('KPM_NL_EID');
        }
        if ($eid == '') {
            return;
        }
        $this->smarty->assign('klarna_footer_layout', Configuration::get('KCO_FOOTERLAYOUT'));
        $this->smarty->assign('kco_footer_active', $kco_active);
        $this->smarty->assign('kco_footer_eid', $eid);
        $this->smarty->assign('kco_footer_locale', $this->getKlarnaLocale());

        return $this->display(__FILE__, 'klarnafooter.tpl');
    }

    public function hookHeader()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }
        $this->context->controller->addCSS(($this->_path).'views/css/kpm_common.css', 'all');
        if (Configuration::get('KCO_IS_ACTIVE')) {
            $this->context->controller->addJS(($this->_path).'views/js/kco_common.js');
            $this->smarty->assign(
                'kco_checkout_url',
                $this->context->link->getModuleLink('klarnaofficial', 'checkoutklarna')
            );

            return $this->display(__FILE__, 'header.tpl');
        }
    }

    /*public function hookTop($params)
    {
        return $this->hookRightColumn($params);
    }*/

    //Copied from block cart
    public function assignContentVars(&$params)
    {
        // Set currency
        if ((int) $params['cart']->id_currency && (int) $params['cart']->id_currency != $this->context->currency->id) {
            $currency = new Currency((int) $params['cart']->id_currency);
        } else {
            $currency = $this->context->currency;
        }

        $taxCalculationMethod = Group::getPriceDisplayMethod((int) Group::getCurrent()->id);

        $useTax = !($taxCalculationMethod == PS_TAX_EXC);

        $products = $params['cart']->getProducts(true);
        $nbTotalProducts = 0;
        foreach ($products as $product) {
            $nbTotalProducts += (int) $product['cart_quantity'];
        }
        $cart_rules = $params['cart']->getCartRules();

        $base_shipping = $params['cart']->getOrderTotal($useTax, Cart::ONLY_SHIPPING);
        $shipping_cost = Tools::displayPrice($base_shipping, $currency);
        $shipping_cost_float = Tools::convertPrice($base_shipping, $currency);
        $wrappingCost = (float) ($params['cart']->getOrderTotal($useTax, Cart::ONLY_WRAPPING));
        $totalToPay = $params['cart']->getOrderTotal($useTax);

        if ($useTax && Configuration::get('PS_TAX_DISPLAY') == 1) {
            $totalToPayWithoutTaxes = $params['cart']->getOrderTotal(false);
            $this->smarty->assign('tax_cost', Tools::displayPrice($totalToPay - $totalToPayWithoutTaxes, $currency));
        }

        // The cart content is altered for display
        foreach ($cart_rules as &$cart_rule) {
            if ($cart_rule['free_shipping']) {
                $shipping_cost = Tools::displayPrice(0, $currency);
                $shipping_cost_float = 0;
                $cart_rule['value_real'] -= Tools::convertPrice(
                    $params['cart']->getOrderTotal(
                        true,
                        Cart::ONLY_SHIPPING
                    ),
                    $currency
                );
                $cart_rule['value_tax_exc'] = Tools::convertPrice(
                    $params['cart']->getOrderTotal(
                        false,
                        Cart::ONLY_SHIPPING
                    ),
                    $currency
                );
            }
            if ($cart_rule['gift_product']) {
                foreach ($products as &$product) {
                    if ($product['id_product'] == $cart_rule['gift_product'] &&
                    $product['id_product_attribute'] == $cart_rule['gift_product_attribute']) {
                        $product['is_gift'] = 1;
                        $product['total_wt'] = Tools::ps_round(
                            $product['total_wt'] - $product['price_wt'],
                            (int) $currency->decimals * _PS_PRICE_DISPLAY_PRECISION_
                        );
                        
                        $product['total'] = Tools::ps_round(
                            $product['total'] - $product['price'],
                            (int) $currency->decimals * _PS_PRICE_DISPLAY_PRECISION_
                        );
                        $cart_rule['value_real'] = Tools::ps_round(
                            $cart_rule['value_real'] - $product['price_wt'],
                            (int) $currency->decimals * _PS_PRICE_DISPLAY_PRECISION_
                        );
                        $cart_rule['value_tax_exc'] = Tools::ps_round(
                            $cart_rule['value_tax_exc'] - $product['price'],
                            (int) $currency->decimals * _PS_PRICE_DISPLAY_PRECISION_
                        );
                    }
                }
            }
        }

        $total_free_shipping = 0;
        if ($free_shipping = Tools::convertPrice((float)Configuration::get('PS_SHIPPING_FREE_PRICE'), $currency)) {
            $calculation2 = $params['cart']->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
            $calculation1 = $params['cart']->getOrderTotal(true, Cart::ONLY_PRODUCTS) + $calculation2;
            $total_free_shipping = (float)($free_shipping - $calculation1);
            $discounts = $params['cart']->getCartRules(CartRule::FILTER_ACTION_SHIPPING);
            if ($total_free_shipping < 0) {
                $total_free_shipping = 0;
            }
            if (is_array($discounts) && count($discounts)) {
                $total_free_shipping = 0;
            }
        }

        $this->smarty->assign(array(
            'products' => $products,
            'customizedDatas' => Product::getAllCustomizedDatas((int) ($params['cart']->id)),
            'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
            'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
            'discounts' => $cart_rules,
            'nb_total_products' => (int) ($nbTotalProducts),
            'shipping_cost' => $shipping_cost,
            'shipping_cost_float' => $shipping_cost_float,
            'show_wrapping' => $wrappingCost > 0 ? true : false,
            'show_tax' => (int) (Configuration::get('PS_TAX_DISPLAY') == 1 && (int) Configuration::get('PS_TAX')),
            'wrapping_cost' => Tools::displayPrice($wrappingCost, $currency),
            'product_total' => Tools::displayPrice(
                $params['cart']->getOrderTotal($useTax, Cart::BOTH_WITHOUT_SHIPPING),
                $currency
            ),
            'total' => Tools::displayPrice($totalToPay, $currency),
            'order_process' => Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order',
            'ajax_allowed' => (int) (Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false,
            'static_token' => Tools::getToken(false),
            'free_shipping' => $total_free_shipping,
        ));
        if (isset($this->context->cookie->ajax_blockcart_display)) {
            $this->smarty->assign('colapseExpandStatus', $this->context->cookie->ajax_blockcart_display);
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int) Tools::getValue('id_order'));
        if ($order->module != $this->name) {
            return;
        }

        $sql = 'SELECT * FROM  `'._DB_PREFIX_.'klarna_orders` WHERE id_order='.(int) Tools::getValue('id_order');
        $klarna_orderinfo = Db::getInstance()->getRow($sql);
        $sql = 'SELECT error_message FROM `'._DB_PREFIX_.
        'klarna_errors` WHERE id_order='.(int) Tools::getValue('id_order');
        $klarna_errors = Db::getInstance()->executeS($sql);
        $this->context->smarty->assign('klarnacheckout_ssn', $klarna_orderinfo['ssn']);
        $this->context->smarty->assign('klarnacheckout_invoicenumber', $klarna_orderinfo['invoicenumber']);
        $this->context->smarty->assign('klarnacheckout_reservation', $klarna_orderinfo['reservation']);
        $this->context->smarty->assign('klarnacheckout_risk_status', $klarna_orderinfo['risk_status']);
        $this->context->smarty->assign('klarnacheckout_eid', $klarna_orderinfo['eid']);
        $this->context->smarty->assign('klarna_errors', $klarna_errors);

        return $this->display(__FILE__, 'klarnaofficial_adminorder.tpl');
    }

    public function hookUpdateOrderStatus($params)
    {
        $newOrderStatus = $params['newOrderStatus'];
        $order = new Order((int) $params['id_order']);
        
        if ($order->module == 'klarnaofficial') {
            if ($newOrderStatus->id == Configuration::get('KCO_CANCEL_STATE', null, null, $order->id_shop)) {
                $countryIso = '';
                $languageIso = '';
                $currencyIso = '';
                $sql = 'SELECT reservation, invoicenumber, eid, id_shop FROM '.
                _DB_PREFIX_.'klarna_orders WHERE id_order='.
                (int) $params['id_order'];
                $order_data = Db::getInstance()->getRow($sql);
                $reservation_number = $order_data['reservation'];
                $invoice_number = $order_data['invoicenumber'];
                $id_shop = $order_data['id_shop'];
                $eid = $order_data['eid'];

                $eid_ss_comb = $this->getAllEIDSScombinations($id_shop);
                $shared_secret = $eid_ss_comb[$eid];
                if ($reservation_number != '') {
                    try {
                        if ($eid == Configuration::get('KCO_UK_EID', null, null, $order->id_shop)) {
                            require_once dirname(__FILE__).'/libraries/KCOUK/autoload.php';
                            
                            if ((int) (Configuration::get('KCO_TESTMODE')) == 1) {
                                $url = \Klarna\Rest\Transport\ConnectorInterface::EU_TEST_BASE_URL;
                            } else {
                                $url = \Klarna\Rest\Transport\ConnectorInterface::EU_BASE_URL;
                            }
                            
                            $connector = \Klarna\Rest\Transport\Connector::create(
                                $eid,
                                $shared_secret,
                                $url
                            );

                            if ($invoice_number != '') {
                                $kcoorder = new \Klarna\Rest\OrderManagement\Order(
                                    $connector,
                                    $reservation_number
                                );
                                $kcoorder->fetch();

                                $data = array(
                                    'refunded_amount' => $kcoorder['order_amount'],
                                    'description' => 'Refund all of the order',
                                    'order_lines' => $kcoorder['order_lines'],
                                );

                                $kcoorder->refund($data);
                                $sql = 'UPDATE `'._DB_PREFIX_.
                                "klarna_orders` SET risk_status='credit' WHERE id_order=".
                                (int) $params['id_order'];
                                Db::getInstance()->execute($sql);
                            } else {
                                $kcoorder = new \Klarna\Rest\OrderManagement\Order(
                                    $connector,
                                    $reservation_number
                                );

                                $kcoorder->cancel();
                                $sql = 'UPDATE `'._DB_PREFIX_.
                                "klarna_orders` SET risk_status='cancel' WHERE id_order=".
                                (int) $params['id_order'];
                                Db::getInstance()->execute($sql);
                            }
                        } else {
                            $k = $this->initKlarnaAPI($eid, $shared_secret, $countryIso, $languageIso, $currencyIso);
                            if ($invoice_number != '') {
                                $result = $k->creditInvoice("$invoice_number");
                            } else {
                                $result = $k->cancelReservation("$reservation_number");
                            }
                            //$invoice_number = '';
                            $risk_status = '';

                            if ($invoice_number != '') {
                                $sql = 'UPDATE `'._DB_PREFIX_.
                                "klarna_orders` SET risk_status='credit' WHERE id_order=".
                                (int) $params['id_order'];
                                Db::getInstance()->execute($sql);
                            } else {
                                $sql = 'UPDATE `'._DB_PREFIX_.
                                "klarna_orders` SET risk_status='cancel' WHERE id_order=".
                                (int) $params['id_order'];
                                Db::getInstance()->execute($sql);
                            }
                        }
                    } catch (Exception $e) {
                        $this->storemessageonorder((int) $params['id_order'], $e->getMessage());
                    }
                }
            }
            if ($newOrderStatus->id == Configuration::get(
                'KCO_ACTIVATE_STATE',
                null,
                null,
                $order->id_shop
            )) {
                $countryIso = '';
                $languageIso = '';
                $currencyIso = '';
                $sql = 'SELECT reservation, invoicenumber, eid, id_shop FROM '._DB_PREFIX_.
                'klarna_orders WHERE id_order='.
                (int) $params['id_order'];
                $order_data = Db::getInstance()->getRow($sql);
                $reservation_number = $order_data['reservation'];
                $invoice_number = $order_data['invoicenumber'];
                $eid = $order_data['eid'];
                $id_shop = $order_data['id_shop'];
                
                $eid_ss_comb = $this->getAllEIDSScombinations($id_shop);
                $shared_secret = $eid_ss_comb[$eid];

                if ($reservation_number != '') {
                    try {
                        $invoice_number = '';
                        $risk_status = '';

                        if ($eid == Configuration::get('KCO_UK_EID', null, null, $order->id_shop)) {
                            require_once dirname(__FILE__).'/libraries/KCOUK/autoload.php';
                            
                            if ((int) (Configuration::get('KCO_TESTMODE')) == 1) {
                                $url = \Klarna\Rest\Transport\ConnectorInterface::EU_TEST_BASE_URL;
                            } else {
                                $url = \Klarna\Rest\Transport\ConnectorInterface::EU_BASE_URL;
                            }
                            
                            $connector = \Klarna\Rest\Transport\Connector::create(
                                $eid,
                                $shared_secret,
                                $url
                            );

                            $kcoorder = new \Klarna\Rest\OrderManagement\Order(
                                $connector,
                                $reservation_number
                            );
                            $kcoorder->fetch();

                            $data = array(
                                'captured_amount' => $kcoorder['order_amount'],
                                'description' => 'Shipped all of the order',
                                'order_lines' => $kcoorder['order_lines'],
                            );

                            $kcoorder->createCapture($data);
                            $invoice_number = $kcoorder['klarna_reference'];
                            $risk_status = $kcoorder['fraud_status'];
                        } else {
                            $k = $this->initKlarnaAPI($eid, $shared_secret, $countryIso, $languageIso, $currencyIso);
                            $method = Configuration::get('KCO_SENDTYPE', null, null, $order->id_shop);
                            if ($method == 1) {
                                $method = KlarnaFlags::RSRV_SEND_BY_EMAIL;
                            } else {
                                $method = KlarnaFlags::RSRV_SEND_BY_MAIL;
                            }

                            $result = $k->activate(
                                "$reservation_number",
                                null,
                                $method
                            );
                            if (isset($result[0])) {
                                $risk_status = $result[0];
                            }
                            if (isset($result[1])) {
                                $invoice_number = $result[1];
                            }
                        }
                        $risk_status = pSQL($risk_status);
                        $invoice_number = pSQL($invoice_number);
                        $sql = 'UPDATE `'._DB_PREFIX_.
                        "klarna_orders` SET risk_status='$risk_status' ,invoicenumber='$invoice_number' ".
                        "WHERE id_order=".(int) $params['id_order'];
                        Db::getInstance()->execute($sql);
                    } catch (Exception $e) {
                        $this->storemessageonorder((int) $params['id_order'], $e->getMessage());
                    }
                }
            }
        }
    }

    public function storemessageonorder($id_order, $message)
    {
        $id_order = (int) $id_order;
        $message = pSQL($message);
        $sql = 'INSERT INTO `'._DB_PREFIX_.
        "klarna_errors` (`id_order`, `error_message`) VALUES($id_order, '$message');";
        Db::getInstance()->execute($sql);
    }

    public function initKlarnaAPI($eid, $sharedSecret, $countryIso, $languageIso, $currencyIso, $id_shop = null)
    {
        require_once dirname(__FILE__).'/libraries/lib/Klarna.php';
        require_once dirname(__FILE__).'/libraries/lib/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
        require_once dirname(__FILE__).'/libraries/lib/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';

        if ($countryIso == 'se') {
            $klarna_country = KlarnaCountry::SE;
        } elseif ($countryIso == 'no') {
            $klarna_country = KlarnaCountry::NO;
        } elseif ($countryIso == 'de') {
            $klarna_country = KlarnaCountry::DE;
        } elseif ($countryIso == 'da' || $countryIso == 'dk') {
            $klarna_country = KlarnaCountry::DK;
        } elseif ($countryIso == 'fi') {
            $klarna_country = KlarnaCountry::FI;
        } elseif ($countryIso == 'nl') {
            $klarna_country = KlarnaCountry::NL;
        } elseif ($countryIso == 'at') {
            $klarna_country = KlarnaCountry::AT;
        } else {
            $klarna_country = "";
        }

        if ($currencyIso == 'sek') {
            $klarna_currency = KlarnaCurrency::SEK;
        } elseif ($currencyIso == 'nok') {
            $klarna_currency = KlarnaCurrency::NOK;
        } elseif ($currencyIso == 'eur') {
            $klarna_currency = KlarnaCurrency::EUR;
        } elseif ($currencyIso == 'dkk') {
            $klarna_currency = KlarnaCurrency::DKK;
        } else {
            $klarna_currency = "";
        }

        if ($languageIso == 'sv') {
            $klarna_lang = KlarnaLanguage::SV;
        } elseif ($languageIso == 'no' || $languageIso == 'nb' || $languageIso == 'nn') {
            $klarna_lang = KlarnaLanguage::NB;
        } elseif ($languageIso == 'de') {
            $klarna_lang = KlarnaLanguage::DE;
        } elseif ($languageIso == 'da') {
            $klarna_lang = KlarnaLanguage::DA;
        } elseif ($languageIso == 'fi') {
            $klarna_lang = KlarnaLanguage::FI;
        } elseif ($languageIso == 'nl') {
            $klarna_lang = KlarnaLanguage::NL;
        } elseif ($languageIso == 'en') {
            $klarna_lang = KlarnaLanguage::EN;
        } else {
            $klarna_lang = "";
        }

        if ($id_shop == null) {
            $id_shop = $this->context->shop->id;
        }

        if (Configuration::get('KCO_TESTMODE', null, null, $id_shop)) {
            $server = Klarna::BETA;
        } else {
            $server = Klarna::LIVE;
        }
        $k = new Klarna();

        $dbsettings = array(
            'user' => _DB_USER_,
            'passwd' => _DB_PASSWD_,
            'dsn' => _DB_SERVER_,
            'db' => _DB_NAME_,
            'table' => _DB_PREFIX_.'kpmpclasses',
          );

        $k->config(
            $eid,
            ''.$sharedSecret,
            $klarna_country,
            $klarna_lang,
            $klarna_currency,
            $server,
            'mysql',
            $dbsettings
        );

        return $k;
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($this->context->cart)) {
            return;
        }
        
        $iso = $this->getKlarnaLocale();
        if ($iso == '') {
            $iso = 'sv_se';
        }

        if (Configuration::get('KCO_IS_ACTIVE')) {
            $KCO_SHOW_IN_PAYMENTS = Configuration::get('KCO_SHOW_IN_PAYMENTS');
            if ($KCO_SHOW_IN_PAYMENTS) {
                $address = new Address($this->context->cart->id_address_delivery);
                $country = new Country($address->id_country);
                if ($country->iso_code=="DE") {
                    $active_in_country = Configuration::get('KCO_GERMANY');
                } elseif ($country->iso_code=="NO") {
                    $active_in_country = Configuration::get('KCO_NORWAY');
                } elseif ($country->iso_code=="FI") {
                    $active_in_country = Configuration::get('KCO_FINLAND');
                } elseif ($country->iso_code=="SE") {
                    $active_in_country = Configuration::get('KCO_SWEDEN');
                } elseif ($country->iso_code=="GB") {
                    $active_in_country = Configuration::get('KCO_UK');
                } else {
                    $active_in_country = false;
                }
                
                if (!$active_in_country) {
                    $KCO_SHOW_IN_PAYMENTS = false;
                }
            }
        } else {
            $KCO_SHOW_IN_PAYMENTS = false;
        }
        $this->smarty->assign('KCO_SHOW_IN_PAYMENTS', $KCO_SHOW_IN_PAYMENTS);
        $this->smarty->assign('KPM_LOGO', Configuration::get('KPM_LOGO'));
        $this->smarty->assign('KPM_LOGO_ISO_CODE', $iso);

        return $this->display(__FILE__, 'kpm_payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        return $this->display(__FILE__, 'kpm_payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getByReference($invoiceref)
    {
        $invoiceref = pSQL($invoiceref);
        $result = Db::getInstance()->getRow(
            'SELECT id_product FROM `'._DB_PREFIX_.
            "product` WHERE reference='$invoiceref'"
        );
        if (isset($result['id_product']) and (int) ($result['id_product']) > 0) {
            $feeproduct = new Product((int) ($result['id_product']), true);

            return $feeproduct;
        } else {
            return;
        }
    }

    public function getCustomerAddress($ssn)
    {
        require_once dirname(__FILE__).'/libraries/lib/Klarna.php';
        require_once dirname(__FILE__).'/libraries/lib/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
        require_once dirname(__FILE__).'/libraries/lib/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';

        $eid = Configuration::get('KPM_SV_EID', null, null, $this->context->shop->id);
        $sharedSecret = Configuration::get('KPM_SV_SECRET', null, null, $this->context->shop->id);

        $md5 = Tools::getValue('kpm_md5key');
        $secret = Tools::encrypt($sharedSecret);
        $ourmd5 = MD5($ssn.$secret);
        $result = array();
        
        if ($ourmd5 != $md5) {
            $result['hasError'] = true;
            $result['error'] = 'Bad token!';
            die(Tools::jsonEncode($result));
        }

        $k = $this->initKlarnaAPI($eid, $sharedSecret, 'se', 'sv', 'sek', $this->context->shop->id);
        $k->setCountry('se');

        setcookie('kpm_ssn', $ssn, time() + 86400, '/'); // 86400 = 1 day
        try {
            $results = array();
            $addrs = $k->getAddresses($ssn);
            foreach ($addrs as $addr) {
                if ($addr->isCompany) {
                    //company
                    $result['firstname'] = utf8_encode($addr->fname);
                    $result['lastname'] = utf8_encode($addr->lname);
                    $result['company'] = utf8_encode($addr->company);
                    $result['address'] = utf8_encode($addr->street);
                    $result['address2'] = utf8_encode($addr->careof);
                    $result['zip'] = utf8_encode($addr->zip);
                    $result['city'] = utf8_encode($addr->city);
                    $result['country'] = utf8_encode($addr->country);
                    $result['iscompany'] = $addr->isCompany;
                } else {
                    //consumer
                    $result['firstname'] = utf8_encode($addr->fname);
                    $result['lastname'] = utf8_encode($addr->lname);
                    $result['address'] = utf8_encode($addr->street);
                    $result['address2'] = utf8_encode($addr->careof);
                    $result['zip'] = utf8_encode($addr->zip);
                    $result['city'] = utf8_encode($addr->city);
                    $result['country'] = utf8_encode($addr->country);
                    $result['iscompany'] = utf8_encode($addr->isCompany);
                }
                $results[] = $result;
            }
            die(Tools::jsonEncode($results));
        } catch (Exception $e) {
            $result['hasError'] = true;
            $result['error'] = "{$e->getMessage()} (#{$e->getCode()})\n";
            die(Tools::jsonEncode($result));
        }
    }

    public function changeAddressOnCart(
        $firstname,
        $lastname,
        $address1,
        $address2,
        $company,
        $postcode,
        $city,
        $old_address,
        $klarna_phone
    ) {
        $customer = new Customer($old_address->id_customer);
        $delivery_address_id = 0;
        foreach ($customer->getAddresses($this->context->cart->id_lang) as $address) {
            if ($address['firstname'] == $firstname and $address['lastname'] == $lastname
                and $address['city'] == $city
                and $address['address2'] == $address2
                and $address['company'] == $company and $address['address1'] == $address1
                and $address['postcode'] == $postcode and $address['phone_mobile'] == $klarna_phone) {
                //LOAD SHIPPING ADDRESS
                    $delivery_address_id = $address['id_address'];
            }
        }
        if ($delivery_address_id == 0) {
            $new_address = new Address();
            $new_address->id_customer = $this->context->cart->id_customer;
            $new_address->firstname = (Tools::strlen($firstname) > 0 ? $firstname : $old_address->firstname);
            $new_address->lastname = (Tools::strlen($lastname) > 0 ? $lastname : $old_address->lastname);
            $new_address->address1 = $address1;
            $new_address->address2 = $address2;
            $new_address->company = $company;
            $new_address->postcode = $postcode;
            $new_address->city = $city;
            $new_address->phone = $old_address->phone;
            $new_address->phone_mobile = $klarna_phone;
            $new_address->id_country = $old_address->id_country;
            $new_address->alias = 'Klarna';
            $new_address->add();
            $this->context->cart->id_address_delivery = $new_address->id;
            $this->context->cart->id_address_invoice = $new_address->id;
        } else {
            $this->context->cart->id_address_delivery = $delivery_address_id;
            $this->context->cart->id_address_invoice = $delivery_address_id;
        }
        Db::getInstance()->Execute(
            'UPDATE '._DB_PREFIX_.
            'cart_product SET id_address_delivery='.
            (int) $this->context->cart->id_address_delivery.
            ' WHERE id_cart='.(int) $this->context->cart->id
        );
        $delivery_option_serialized = Db::getInstance()->getValue(
            'SELECT delivery_option FROM '
            ._DB_PREFIX_.'cart WHERE id_cart='.
            (int) $this->context->cart->id
        );
        if ($delivery_option_serialized and $delivery_option_serialized != '') {
            $delivery_option_values = unserialize($delivery_option_serialized);
            $new_delivery_options = array();
            foreach ($delivery_option_values as $value) {
                $new_delivery_options[(int) $this->context->cart->id_address_delivery] = $value;
            }
            $new_delivery_options_serialized = serialize($new_delivery_options);
            $update_sql = 'UPDATE '._DB_PREFIX_.'cart SET delivery_option=\''.
            pSQL($new_delivery_options_serialized).
            '\' WHERE id_cart='.(int) $this->context->cart->id;
            $this->context->cart->delivery_option = $new_delivery_options_serialized;
            Db::getInstance()->Execute($update_sql);
        }
        $this->context->cart->update(true);
        $this->context->cart->getPackageList(true);
        $this->context->cart->getDeliveryOptionList(null, true);
    }

    public function getL($key)
    {
        $translations = array(
            'Discount' => $this->l('Discount'),
            'extra_info' => $this->l('Flexible - Pay in your own tempo.'),
            'interestRate' => $this->l('interestRate: '),
            'monthlyFee' => $this->l('monthlyFee: '),
            'monthlyCost' => $this->l('monthlyCost: '),
            'Invoice' => $this->l('Invoice'),
            'Subscribe to our newsletter.' => $this->l('Subscribe to our newsletter.'),
        );

        return $translations[$key];
    }

    public function setKCOCountrySettings()
    {
        $norway_done = false;
        $finland_done = false;
        $sweden_done = false;
        $germany_done = false;
        $uk_done = false;

        $sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_SVERIGE_DEFAULT\'';
        $id_address_sweden = Db::getInstance()->getValue($sql);
        if ((int) ($id_address_sweden) > 0) {
            Configuration::updateValue('KCO_SWEDEN_ADDR', $id_address_sweden);
            $sweden_done = true;
        } else {
            $id_country = (int) Country::getByIso('SE');
            $insert_sql = 'INSERT INTO '._DB_PREFIX_.
            "address (id_country, id_state, id_customer, id_manufacturer, id_supplier, id_warehouse,".
            " alias, company, lastname, firstname, address1, address2, postcode,".
            " city, other,phone, phone_mobile, vat_number, dni, active, deleted, date_add, date_upd) ".
            "VALUES ($id_country, 0,0,0,0,0,'KCO_SVERIGE_DEFAULT','','Sverige', 'Person', ".
            "'Standardgatan 1', '', '12345', 'Stockholm', '', '1234567890','','','',1,0, NOW(), NOW());";
            Db::getInstance()->execute($insert_sql);
            $id_address_sweden = Db::getInstance()->getValue($sql);
            if ((int) ($id_address_sweden) > 0) {
                Configuration::updateValue('KCO_SWEDEN_ADDR', $id_address_sweden);
                $sweden_done = true;
            }
        }

        $sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_NORGE_DEFAULT\'';
        $id_address_norway = Db::getInstance()->getValue($sql);
        if ((int) ($id_address_norway) > 0) {
            Configuration::updateValue('KCO_NORWAY_ADDR', $id_address_norway);
            $norway_done = true;
        } else {
            $id_country = (int) Country::getByIso('NO');
            $insert_sql = 'INSERT INTO '._DB_PREFIX_."address (id_country, id_state, id_customer, ".
            "id_manufacturer, id_supplier, id_warehouse, alias, company, lastname, firstname, address1,".
            " address2, postcode, city, other,phone, phone_mobile, vat_number, dni, active, deleted,".
            " date_add, date_upd) VALUES ($id_country, 0,0,0,0,0,'KCO_NORGE_DEFAULT','','Norge', 'Person',".
            " 'Standardgatan 1', '', '12345', 'Oslo', '', '1234567890','','','',1,0, NOW(), NOW());";
            Db::getInstance()->execute($insert_sql);
            $id_address_norway = Db::getInstance()->getValue($sql);
            if ((int) ($id_address_norway) > 0) {
                Configuration::updateValue('KCO_NORWAY_ADDR', $id_address_norway);
                $norway_done = true;
            }
        }
        $sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_FINLAND_DEFAULT\'';
        $id_address_finland = Db::getInstance()->getValue($sql);
        if ((int) ($id_address_finland) > 0) {
            Configuration::updateValue('KCO_FINLAND_ADDR', $id_address_finland);
            $finland_done = true;
        } else {
            $id_country = (int) Country::getByIso('FI');
            $insert_sql = 'INSERT INTO '._DB_PREFIX_."address (id_country, id_state, id_customer, ".
            "id_manufacturer, id_supplier, id_warehouse, alias, company, lastname, firstname, address1, ".
            "address2, postcode, city, other,phone, phone_mobile, vat_number, ".
            "dni, active, deleted, date_add, date_upd) ".
            "VALUES ($id_country, 0,0,0,0,0,'KCO_FINLAND_DEFAULT','','Finland', 'Person', ".
            "'Standardgatan 1', '', '12345', 'Helsinkki', '', '1234567890','','','',1,0, NOW(), NOW());";
            Db::getInstance()->execute($insert_sql);
            $id_address_finland = Db::getInstance()->getValue($sql);
            if ((int) ($id_address_finland) > 0) {
                Configuration::updateValue('KCO_FINLAND_ADDR', $id_address_finland);
                $finland_done = true;
            }
        }

        $sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_GERMANY_DEFAULT\'';
        $id_address_germany = Db::getInstance()->getValue($sql);
        if ((int) ($id_address_germany) > 0) {
            Configuration::updateValue('KCO_GERMANY_ADDR', $id_address_germany);
            $germany_done = true;
        } else {
            $id_country = (int) Country::getByIso('DE');
            $insert_sql = 'INSERT INTO '._DB_PREFIX_."address (id_country, id_state, id_customer, id_manufacturer,".
            " id_supplier, id_warehouse, alias, company, lastname, firstname, address1, address2, postcode, city, ".
            "other,phone, phone_mobile, vat_number, dni, active, deleted, date_add, date_upd) VALUES ($id_country, ".
            "0,0,0,0,0,'KCO_GERMANY_DEFAULT','','Tyskland', 'Person', 'Standardgatan 1', '', '12345', 'Berlin', '',".
            " '1234567890','','','',1,0, NOW(), NOW());";
            Db::getInstance()->execute($insert_sql);
            $id_address_germany = Db::getInstance()->getValue($sql);
            if ((int) ($id_address_germany) > 0) {
                Configuration::updateValue('KCO_GERMANY_ADDR', $id_address_germany);
                $germany_done = true;
            }
        }

        $sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_UK_DEFAULT\'';
        $id_address_uk = Db::getInstance()->getValue($sql);
        if ((int) ($id_address_uk) > 0) {
            Configuration::updateValue('KCO_UK_ADDR', $id_address_uk);
            $uk_done = true;
        } else {
            $id_country = (int) Country::getByIso('GB');
            $insert_sql = 'INSERT INTO '._DB_PREFIX_."address (id_country, id_state, id_customer, id_manufacturer, ".
            "id_supplier, id_warehouse, alias, company, lastname, firstname, address1, address2, postcode, city, ".
            "other,phone, phone_mobile, vat_number, dni, active, deleted, date_add, date_upd) VALUES ($id_country,".
            " 0,0,0,0,0,'KCO_UK_DEFAULT','','United Kingdom', 'Person', 'Standardgatan 1', '', '12345', 'London', ".
            "'', '1234567890','','','',1,0, NOW(), NOW());";
            Db::getInstance()->execute($insert_sql);
            $id_address_uk = Db::getInstance()->getValue($sql);
            if ((int) ($id_address_uk) > 0) {
                Configuration::updateValue('KCO_UK_ADDR', $id_address_uk);
                $uk_done = true;
            }
        }

        if ($finland_done === true &&
        $norway_done === true &&
        $sweden_done === true &&
        $germany_done === true &&
        $uk_done === true) {
            return true;
        } else {
            return false;
        }
    }

    public function getRequiredKPMFields($iso_code)
    {
        if (Tools::strtolower($iso_code) == 'at') {
            return array(
                'ssn' => false,
                'birthdate' => true,
                'gender' => true,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => false,
                'housenumberext' => false,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        } elseif (Tools::strtolower($iso_code) == 'dk') {
            return array(
                'ssn' => true,
                'birthdate' => false,
                'gender' => false,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => false,
                'housenumberext' => false,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        } elseif (Tools::strtolower($iso_code) == 'fi') {
            return array(
                'ssn' => true,
                'birthdate' => false,
                'gender' => false,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => false,
                'housenumberext' => false,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        } elseif (Tools::strtolower($iso_code) == 'de') {
            return array(
                'ssn' => false,
                'birthdate' => true,
                'gender' => true,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => true,
                'housenumberext' => false,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        } elseif (Tools::strtolower($iso_code) == 'nl') {
            return array(
                'ssn' => false,
                'birthdate' => true,
                'gender' => true,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => true,
                'housenumberext' => true,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        } elseif (Tools::strtolower($iso_code) == 'no') {
            return array(
                'ssn' => true,
                'birthdate' => false,
                'gender' => false,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => false,
                'housenumberext' => false,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        } elseif (Tools::strtolower($iso_code) == 'se') {
            return array(
                'ssn' => true,
                'birthdate' => false,
                'gender' => false,
                'firstname' => true,
                'lastname' => true,
                'streetname' => true,
                'housenumber' => false,
                'housenumberext' => false,
                'zipcode' => true,
                'city' => true,
                'country' => false,
                'phone' => true,
                'mobilephone' => true,
                'email' => true
            );
        }
    }

    public function truncateValue($string, $length, $abconly = false)
    {
        //$string = utf8_decode($string);
        if ($abconly) {
            $string = preg_replace("/[^\p{L}\p{N} -]/u", '', $string);
        }
        //$string = utf8_encode($string);
        if (Tools::strlen($string) > $length) {
            return Tools::substr($string, 0, $length);
        } else {
            return $string;
        }
    }

    public function getKlarnaLocale()
    {
        if (isset($this->context->cart) &&
        isset($this->context->cart->id_address_delivery) &&
        (int) $this->context->cart->id_address_delivery > 0) {
            $address = new Address($this->context->cart->id_address_delivery);
            $country_iso = Country::getIsoById($address->id_country);
        } else {
            $country_iso = '';
        }
        if (isset($this->context->language) &&
        isset($this->context->language->id) &&
        (int) $this->context->language->id > 0) {
            $language_iso = Language::getIsoById((int) $this->context->language->id);
        } else {
            $language_iso = '';
        }

        $country_iso = Tools::strtolower($country_iso);
        $language_iso = Tools::strtolower($language_iso);
        if ($country_iso == '' && $language_iso == '') {
            $language_iso = Language::getIsoById((int) $this->context->language->id);
            $country_iso = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
            $country_iso = Tools::strtolower($country_iso);
            $language_iso = Tools::strtolower($language_iso);
        }
        if ($country_iso == '') {
            if ($language_iso == 'sv') {
                return 'sv_se';
            }
            if ($language_iso == 'no' || $language_iso == 'nb' || $language_iso == 'nn') {
                return 'nb_no';
            }
            if ($language_iso == 'fi') {
                return 'fi_fi';
            }
            if ($language_iso == 'de') {
                return 'de_de';
            }
            if ($language_iso == 'nl') {
                return 'nl_nl';
            }
            if ($language_iso == 'en') {
                return 'en_gb';
            }
        }
        if ($country_iso == 'fi') {
            if ($language_iso == 'sv') {
                return 'sv_fi';
            } else {
                return 'fi_fi';
            }
        }
        if ($country_iso == 'se') {
            if ($language_iso == 'sv' || $country_iso == 'se') {
                return 'sv_se';
            } else {
                return 'en_se';
            }
        }
        if ($country_iso == 'no') {
            if ($language_iso == 'nb' || $language_iso == 'nn' || $language_iso == 'no') {
                return 'nb_no';
            } else {
                return 'en_no';
            }
        }
        if ($country_iso == '') {
            $country_iso = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
            $country_iso = Tools::strtolower($country_iso);
        }

        return $language_iso.'_'.$country_iso;
    }

    private function getAllEIDSScombinations($id_shop)
    {
        $combosArray = array();
        $KCO_SWEDEN_EID = Configuration::get('KCO_SWEDEN_EID', null, null, $id_shop);
        $KCO_NORWAY_EID = Configuration::get('KCO_NORWAY_EID', null, null, $id_shop);
        $KCO_FINLAND_EID = Configuration::get('KCO_FINLAND_EID', null, null, $id_shop);
        $KCO_GERMANY_EID = Configuration::get('KCO_GERMANY_EID', null, null, $id_shop);
        $KPM_SV_EID = Configuration::get('KPM_SV_EID', null, null, $id_shop);
        $KPM_NO_EID = Configuration::get('KPM_NO_EID', null, null, $id_shop);
        $KPM_FI_EID = Configuration::get('KPM_FI_EID', null, null, $id_shop);
        $KPM_DA_EID = Configuration::get('KPM_DA_EID', null, null, $id_shop);
        $KPM_DE_EID = Configuration::get('KPM_DE_EID', null, null, $id_shop);
        $KPM_NL_EID = Configuration::get('KPM_NL_EID', null, null, $id_shop);
        $KPM_AT_EID = Configuration::get('KPM_AT_EID', null, null, $id_shop);
        $KCO_UK_EID = Configuration::get('KCO_UK_EID', null, null, $id_shop);
        $combosArray[$KCO_SWEDEN_EID] = Configuration::get('KCO_SWEDEN_SECRET', null, null, $id_shop);
        $combosArray[$KCO_NORWAY_EID] = Configuration::get('KCO_NORWAY_SECRET', null, null, $id_shop);
        $combosArray[$KCO_FINLAND_EID] = Configuration::get('KCO_FINLAND_SECRET', null, null, $id_shop);
        $combosArray[$KCO_GERMANY_EID] = Configuration::get('KCO_GERMANY_SECRET', null, null, $id_shop);
        $combosArray[$KPM_SV_EID] = Configuration::get('KPM_SV_SECRET', null, null, $id_shop);
        $combosArray[$KPM_NO_EID] = Configuration::get('KPM_NO_SECRET', null, null, $id_shop);
        $combosArray[$KPM_FI_EID] = Configuration::get('KPM_FI_SECRET', null, null, $id_shop);
        $combosArray[$KPM_DA_EID] = Configuration::get('KPM_DA_SECRET', null, null, $id_shop);
        $combosArray[$KPM_DE_EID] = Configuration::get('KPM_DE_SECRET', null, null, $id_shop);
        $combosArray[$KPM_NL_EID] = Configuration::get('KPM_DE_SECRET', null, null, $id_shop);
        $combosArray[$KPM_AT_EID] = Configuration::get('KPM_AT_SECRET', null, null, $id_shop);
        $combosArray[$KCO_UK_EID] = Configuration::get('KCO_UK_SECRET', null, null, $id_shop);

        return $combosArray;
    }

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $iso = $this->getKlarnaLocale();
        if ($iso == '') {
            $iso = 'sv_se';
        }

        $payment_options = array(
            'cta_text' => $this->l('Klarna'),
            'logo' => 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.$iso.'/basic/blue-black.png?width=200',
            'action' => $this->context->link->getModuleLink($this->name, 'kpmpartpayment', array(), true)
        );

        return $payment_options;
    }
    
    public function checkPendingStatus($id_order)
    {
        $sql = 'SELECT reservation, invoicenumber, eid, id_shop FROM '.
        _DB_PREFIX_.'klarna_orders WHERE id_order='.
        (int) $id_order;
        $order_data = Db::getInstance()->getRow($sql);
        $reservation_number = $order_data['reservation'];
        $id_shop = $order_data['id_shop'];
        $eid = $order_data['eid'];
        $eid_ss_comb = $this->getAllEIDSScombinations($id_shop);
        $shared_secret = $eid_ss_comb[$eid];
        $countryIso = '';
        $languageIso = '';
        $currencyIso = '';
        $k = $this->initKlarnaAPI($eid, $shared_secret, $countryIso, $languageIso, $currencyIso, $id_shop);
        $status = $k->checkOrderStatus($reservation_number);
        if ($status == KlarnaFlags::ACCEPTED) {
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                $new_status = Configuration::get('KPM_ACCEPTED_INVOICE', null, null, $order->id_shop);
                $history = new OrderHistory();
                $history->id_order = $id_order;
                $history->changeIdOrderState((int)$new_status, $id_order, true);
                $history->addWithemail(true, null);
            }
        } elseif ($status == KlarnaFlags::DENIED) {
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                if ((int)(Configuration::get('PS_OS_CANCELED'))>0) {
                    $cancel_status = Configuration::get('PS_OS_CANCELED');
                } else {
                    $cancel_status = _PS_OS_CANCELED_;
                }
                $history = new OrderHistory();
                $history->id_order = $id_order;
                $history->changeIdOrderState((int)$cancel_status, $id_order, true);
                $history->addWithemail(true, null);
            }
        }
    }
    public function createAddress($coutry_iso_code, $setting_name, $city, $country, $alias)
    {
        $coutry_iso_code = pSQL($coutry_iso_code);
        $setting_name = pSQL($setting_name);
        $city = pSQL($city);
        $alias = pSQL($alias);
        $country = pSQL($country);
        
        $addressidtoupd = (int)Configuration::get($setting_name);
        $id_country = (int)Country::getByIso($coutry_iso_code);
        //since opc is active with alot of countries, it is possible that it can reset the default address$sql_fix
        $sql_fix = "UPDATE "._DB_PREFIX_."address SET id_customer=0, ".
        "id_state=0, id_manufacturer=0, id_supplier=0,id_warehouse=0, ".
        "alias='$alias', company='', lastname='$country',firstname='Person', ".
        "address1='Standardgatan 1', address2='', postcode='12345',city='$city', ".
        "other='', phone='1234567890', phone_mobile='',vat_number='', ".
        "dni='', active='', deleted='',date_upd=NOW(), ".
        "id_country=$id_country WHERE id_address=$addressidtoupd";
        
        Db::getInstance()->execute($sql_fix);
    }
}

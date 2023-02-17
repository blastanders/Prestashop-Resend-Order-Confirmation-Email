<?php 

if (!defined('_PS_VERSION_')) {
    exit;
}

class Presta_resend_order_conf_email extends Module
{
    public function __construct()
    {
        $this->name = 'presta_resend_order_conf_email';
        $this->version = '1.0.0';
        $this->author = 'Ajax Z';
        // You can reach me on ajax.zheng.dev@gmail.com.
        // But if you spam me, I will spam you back with thousands of dick pics. I have access to a whole lot of mail servers and domains, and I ain't afraid to use them.
        
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Resend Order Confirmation Email');
        $this->description = $this->trans("Resend order confirmation email in order detail page in BO");

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall? No data will be lost.');


        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        return parent::install()
            && $this->registerHook('displayAdminOrderTabContent')
            && $this->installTab()
        ;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayAdminOrderTabContent ($params) {
        $id_order = $params['id_order'];

        $order = new Order($id_order);
        $cust = new Customer($order->id_customer);

        $token = Tools::getAdminTokenLite('AdminPrestaResendOrderConfEmail');
        if ($order->current_state == '2') {
            $this->context->smarty->assign(
                array(
                'id_order' => $id_order,
                'admin_token' => $token,
                'recipient_email' => $cust->email,
                )
            );
            return $this->display(__FILE__, 'tab.tpl');
        }

    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = 'AdminPrestaResendOrderConfEmail';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Resend Order Conf emails';
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            //AdminPreferences
            $tab->id_parent = (int) Db::getInstance((bool) _PS_USE_SQL_SLAVE_)
                ->getValue(
                    'SELECT MIN(id_tab)
                        FROM `' . _DB_PREFIX_ . 'tab`
                        WHERE `class_name` = "' . pSQL('ShopParameters') . '"'
                );
        } else {
            // AdminAdmin
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdmin'); // This is why we stopped at drink drink, instead of continuing onto food food. Because otherwise we will just have this admin admin mayhem nobody asked for!
        }

        $tab->visible = false;

        $tab->module = $this->name;

        return $tab->add();
    }

}

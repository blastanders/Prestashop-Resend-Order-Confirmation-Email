<?php
class AdminPrestaResendOrderConfEmailController extends ModuleAdminController
{
    public $module;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'Resend order_conf email';
        parent::__construct();
        $this->meta_title = $this->l('Resend order confirmation email');
    }


    public function postProcess()
    {
        parent::postProcess(); // IF YOU DON'T DO THIS FOR ANY REASON, SHIT WILL BREAK.
        return true;
    }

    public function renderView()
    {
        
    }

    public function initContent() {
        parent::initContent();
    }

    public function ajaxProcessresendOrderConfEmail(){
        $id_order = Tools::getValue('id_order');
        $recipient_email = Tools::getValue('recipient_email');
        $order = new Order($id_order);
        /* Resend conf email */
        if (isset($order)) {
            if ($this->access('edit')) {
                
                $carrier = new Carrier((int)$order->id_carrier);
                $product_list_txt = '';
                $product_list_html = '';
                $products = $order->getProducts();
                $customer = new Customer($order->id_customer);
                
                $customized_datas = Product::getAllCustomizedDatas((int)$order->id_cart);
                Product::addCustomizationPrice($products, $customized_datas);
                foreach ($products as $key => $product)
                {
                    $unit_price = Product::getTaxCalculationMethod($customer->id) == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'];

                    $customization_text = '';
                    if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']])) {
                        foreach ($customized_datas[$product['product_id']][$product['product_attribute_id']][$order->id_address_delivery] as $customization) {
                            if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                                foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                    $customization_text .= $text['name'].': '.$text['value'].'<br />';
                                }
                            }

                            if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                                $customization_text .= count($customization['datas'][Product::CUSTOMIZE_FILE]).' '.$this->trans('image(s)', array(), 'Modules.Mailalerts.Admin').'<br />';
                            }
        
                            $customization_text .= '---<br />';
                        }
                        if (method_exists('Tools', 'rtrimString')) {
                            $customization_text = Tools::rtrimString($customization_text, '---<br />');
                        } else {
                            $customization_text = preg_replace('/---<br \/>$/', '', $customization_text);
                        }
                    }


                    $url = $this->context->link->getProductLink($product['product_id']);
                    $product_list_html .=
                        '<tr>
                            <td style="padding: 10px 0;" width="18%">
                                <font size="2" face="Arial, sans-serif" color="#414a56">
                                    <p style="margin: 0; padding: 0 5px; font-size: 14px">'.$product['product_reference'].'</p>
                                </font>
                            </td>
                            <td style="padding: 10px 0;" width="47%">
                                <font size="2" face="Arial, sans-serif" color="#414a56">
                                    <p class="name-product" style="margin: 0; padding: 0 5px; font-size: 14px; color: #414a56; font-weight: bold;">'.$product['product_name']
                                    
                                    .(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '')
                                    .(!empty($customization_text) ? '<br />'.$customization_text : '')
                                    
                                 . '</p>
                                </font>
                            </td>                            
                            <td style="padding: 10px 0;" width="15%">
                                <font size="2" face="Arial, sans-serif" color="#414a56">
                                    <p style="margin: 0; padding: 0 5px; font-size: 14px">'.Tools::displayPrice($unit_price, $this->context->currency, false).'</p>
                                </font>
                            </td>
                            <td style="padding: 10px 0;" width="5%">
                                <font size="2" face="Arial, sans-serif" color="#414a56">
                                    <p style="margin: 0; padding: 0 5px; font-size: 14px">'.(int)$product['product_quantity'].'</p>
                                </font>
                            </td>
                            <td align="right" style="padding: 10px 0;" width="15%">
                                <font size="2" face="Arial, sans-serif" color="#414a56">
                                    <p style="margin: 0; padding: 0 5px; font-size: 14px">' 
                                        .Tools::displayPrice(($unit_price * $product['product_quantity']), $currency, false)
                                    .'</p>
                                </font>
                            </td>
                        </tr>';
                        
                    $product_list_txt .= $product['product_reference'].' - '.$product['product_name'].' - ' .(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '')
                                    .(!empty($customization_text) ? ' - '.$customization_text : '')
                                    
                                .' - '.Tools::displayPrice($unit_price, $currency, false).' - '.(int)$product['product_quantity'].' ks - '
                                .Tools::displayPrice(($unit_price * $product['product_quantity']), $currency, false);   
                }
                                
                $cart_rules_list_txt = '';
                $cart_rules_list_html = '';
                
                foreach ($order->getCartRules() as $discount)
                {

                    $cart_rules_list_html .='<tr class="conf_body">
                        <td colspan="3" align="left" style="padding: 10px 0; ">
                            <font size="2" face="Arial, sans-serif" color="#414a56">
                                <p class="name-product" style="margin: 0; padding: 0 5px; font-size: 14px; color: #414a56; font-weight: bold;">'.$discount['name'].'</p>
                            </font>
                        </td>
                        <td colspan="2" align="right" style="padding: 10px 0; ">
                            <font size="2" face="Arial, sans-serif" color="#414a56">
                                <p style="margin: 0; padding: 0 5px; font-size: 14px">'.Tools::displayPrice($discount['value'], $currency, false).'</p>
                            </font>
                        </td>
                    </tr>';

                    $cart_rules_list_txt .= $discount['name'].' - ' . Tools::displayPrice($discount['value'], $currency, false);
                }
                            

                $invoice = new Address((int)$order->id_address_invoice);
                $delivery = new Address((int)$order->id_address_delivery);
                $delivery_state = $delivery->id_state ? new State((int)$delivery->id_state) : false;
                $invoice_state = $invoice->id_state ? new State((int)$invoice->id_state) : false;

                $data = array(
                        '{message}' => $order->getFirstMessage(),
                        '{firstname}' => $customer->firstname,
                        '{lastname}' => $customer->lastname,
                        '{email}' => $customer->email,
                        '{delivery_block_txt}' => $this->_getFormatedAddress($delivery, "\n"),
                        '{invoice_block_txt}' => $this->_getFormatedAddress($invoice, "\n"),
                        '{delivery_block_html}' => $this->_getFormatedAddress($delivery, '<br />', array(
                            'firstname' => '<span style="font-weight:bold;">%s</span>',
                            'lastname' => '<span style="font-weight:bold;">%s</span>'
                        )),
                        '{invoice_block_html}' => $this->_getFormatedAddress($invoice, '<br />', array(
                            'firstname' => '<span style="font-weight:bold;">%s</span>',
                            'lastname' => '<span style="font-weight:bold;">%s</span>'
                        )),
                        '{delivery_company}' => $delivery->company,
                        '{delivery_firstname}' => $delivery->firstname,
                        '{delivery_lastname}' => $delivery->lastname,
                        '{delivery_address1}' => $delivery->address1,
                        '{delivery_address2}' => $delivery->address2,
                        '{delivery_city}' => $delivery->city,
                        '{delivery_postal_code}' => $delivery->postcode,
                        '{delivery_country}' => $delivery->country,
                        '{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
                        '{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
                        '{delivery_other}' => $delivery->other,
                        '{invoice_company}' => $invoice->company,
                        '{invoice_vat_number}' => $invoice->vat_number,
                        '{invoice_firstname}' => $invoice->firstname,
                        '{invoice_lastname}' => $invoice->lastname,
                        '{invoice_address2}' => $invoice->address2,
                        '{invoice_address1}' => $invoice->address1,
                        '{invoice_city}' => $invoice->city,
                        '{invoice_postal_code}' => $invoice->postcode,
                        '{invoice_country}' => $invoice->country,
                        '{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
                        '{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
                        '{invoice_other}' => $invoice->other,
                        '{order_name}' => $order->getUniqReference(),
                        '{date}' => Tools::displayDate($order->date_upd, null , 1),
                        '{carrier}' => $carrier->name,
                        '{payment}' => Tools::substr($order->payment, 0, 32),
                        '{products}' => $product_list_html,
                        '{products_txt}' => $product_list_txt,
                        '{discounts}' => $cart_rules_list_html,
                        '{discounts_txt}' => $cart_rules_list_txt,
                        '{total_paid}' => Tools::displayPrice($order->total_paid, $this->context->currency, false),
                        '{total_paid_tax_excl}' => Tools::displayPrice($order->total_paid_tax_excl, $this->context->currency, false),
                        '{total_products}' => Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? $order->total_products : $order->total_products_wt, $this->context->currency, false),
                        '{total_discounts}' => Tools::displayPrice($order->total_discounts, $this->context->currency, false),
                        '{total_shipping}' => Tools::displayPrice($order->total_shipping, $this->context->currency, false),
                        '{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $this->context->currency, false),
                        '{total_tax_paid}' => Tools::displayPrice($order->total_paid_tax_incl - $order->total_paid_tax_excl, $this->context->currency, false)
                );

               if (Validate::isEmail($customer->email)) {
                    if (empty($recipient_email)) {
                        $recipient_email = $customer->email;
                    }
                    $email_send_res = Mail::Send(
                        (int)$order->id_lang,
                        'order_conf',
                        Mail::l('Order confirmation', (int)$order->id_lang),
                        $data,
                        $recipient_email,
                        $customer->firstname . ' ' . $customer->lastname,
                        null,
                        null,
                        null,
                        null, _PS_MAIL_DIR_, false, (int)$order->id_shop
                    );
                } else {
                    $this->errors[] = "Invalid recipient [{$customer->email}]";
                }
            } else {
                $this->errors[] = $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
            }

            header("Content-type:application/json");
            if (empty($this->errors)) {
                $res = array();
                $res['status'] = 'OK';
                $res['messages'] = "Email sent to " . $recipient_email;
            } else {
                $res = array();
                $res['status'] = 'ERROR';
                $res['errors'] = $this->errors;
            }

            die(json_encode($res));
        }
    }

    protected function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = array())
    {
        return AddressFormat::generateAddress($the_address, array('avoid' => array()), $line_sep, ' ', $fields_style);
    }
}

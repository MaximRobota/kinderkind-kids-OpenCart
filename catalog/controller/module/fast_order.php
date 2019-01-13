<?php

class ControllerModuleFastOrder extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('module/fast_order');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('module/fast_order');

            $telephone = $this->request->post['telephone'];

            $data = array(
                'invoice_prefix' => $this->config->get('config_invoice_prefix'),
                'store_id' => $this->config->get('config_store_id'),
                'store_name' => $this->config->get('config_name'),
                'payment_firstname' => '',
                'payment_lastname' => '',
                'payment_company' => '',
                'payment_address_1' => '',
                'payment_address_2' => '',
                'payment_city' => '',
                'payment_postcode' => '',
                'payment_country' => '',
                'payment_country_id' => '',
                'payment_zone' => '',
                'payment_zone_id' => '',
                'payment_address_format' => '',
                'payment_method' => '',
                'payment_code' => '',
                'payment_custom_field' => 'a:0:{}',
                'shipping_firstname' => '',
                'shipping_lastname' => '',
                'shipping_company' => '',
                'shipping_address_1' => '',
                'shipping_address_2' => '',
                'shipping_city' => '',
                'shipping_postcode' => '',
                'shipping_country' => '',
                'shipping_country_id' => '',
                'shipping_zone' => '',
                'shipping_zone_id' => '',
                'shipping_address_format' => '',
                'shipping_method' => '',
                'shipping_code' => '',
                'shipping_custom_field' => 'a:0:{}',
                'comment' => '',
                'order_status_id' => $this->config->get('config_order_status_id')
            );

            if ($data['store_id']) {
                $data['store_url'] = $this->config->get('config_url');
            } else {
                $data['store_url'] = HTTP_SERVER;
            }

            //totals
            $data['totals'] = array();
            $total = 0;
            $taxes = $this->cart->getTaxes();

            $this->load->model('extension/extension');

            $sort_order = array();

            $results = $this->model_extension_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get($result['code'] . '_status')) {
                    $this->load->model('total/' . $result['code']);

                    $this->{'model_total_' . $result['code']}->getTotal($data['totals'], $total, $taxes);
                }
            }

            $sort_order = array();

            foreach ($data['totals'] as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $data['totals']);

            //customer info
            if ($this->customer->isLogged()) {
                $this->load->model('account/customer');

                $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

                $data['customer_id'] = $this->customer->getId();
                $data['customer_group_id'] = $customer_info['customer_group_id'];
                $data['firstname'] = $customer_info['firstname'];
                $data['lastname'] = $customer_info['lastname'];
                $data['email'] = $customer_info['email'];
                $data['telephone'] = $telephone;
                $data['fax'] = $customer_info['fax'];
                $data['custom_field'] = unserialize($customer_info['custom_field']);
            } else {
                $data['customer_id'] = 0;
                $data['customer_group_id'] = $this->config->get('config_customer_group_id');
                $data['firstname'] = 'firstname';
                $data['lastname'] = 'lastname';
                $data['email'] = 'email@example.com';
                $data['telephone'] = $telephone;
                $data['fax'] = '';
                $data['custom_field'] = 'a:0:{}';
            }

            //products
            foreach ($this->cart->getProducts() as $product) {
                $option_data = array();

                foreach ($product['option'] as $option) {
                    $option_data[] = array(
                        'product_option_id'       => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'option_id'               => $option['option_id'],
                        'option_value_id'         => $option['option_value_id'],
                        'name'                    => $option['name'],
                        'value'                   => $option['value'],
                        'type'                    => $option['type']
                    );
                }

                $data['products'][] = array(
                    'product_id' => $product['product_id'],
                    'name'       => $product['name'],
                    'model'      => $product['model'],
                    'option'     => $option_data,
                    'download'   => $product['download'],
                    'quantity'   => $product['quantity'],
                    'subtract'   => $product['subtract'],
                    'price'      => $product['price'],
                    'total'      => $product['total'],
                    'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                    'reward'     => $product['reward']
                );
            }

            // Gift Voucher
            $data['vouchers'] = array();

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $data['vouchers'][] = array(
                        'description'      => $voucher['description'],
                        'code'             => substr(md5(mt_rand()), 0, 10),
                        'to_name'          => $voucher['to_name'],
                        'to_email'         => $voucher['to_email'],
                        'from_name'        => $voucher['from_name'],
                        'from_email'       => $voucher['from_email'],
                        'voucher_theme_id' => $voucher['voucher_theme_id'],
                        'message'          => $voucher['message'],
                        'amount'           => $voucher['amount']
                    );
                }
            }

            $data['total'] = $total;

            if (isset($this->request->cookie['tracking'])) {
                $data['tracking'] = $this->request->cookie['tracking'];

                $subtotal = $this->cart->getSubTotal();

                // Affiliate
                $this->load->model('affiliate/affiliate');

                $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);

                if ($affiliate_info) {
                    $data['affiliate_id'] = $affiliate_info['affiliate_id'];
                    $data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
                } else {
                    $data['affiliate_id'] = 0;
                    $data['commission'] = 0;
                }

                // Marketing
                $this->load->model('checkout/marketing');

                $marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

                if ($marketing_info) {
                    $data['marketing_id'] = $marketing_info['marketing_id'];
                } else {
                    $data['marketing_id'] = 0;
                }
            } else {
                $data['affiliate_id'] = 0;
                $data['commission'] = 0;
                $data['marketing_id'] = 0;
                $data['tracking'] = '';
            }

            $data['language_id'] = $this->config->get('config_language_id');
            $data['currency_id'] = $this->currency->getId();
            $data['currency_code'] = $this->currency->getCode();
            $data['currency_value'] = $this->currency->getValue($this->currency->getCode());
            $data['ip'] = $this->request->server['REMOTE_ADDR'];

            if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                $data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                $data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
            } else {
                $data['forwarded_ip'] = '';
            }

            if (isset($this->request->server['HTTP_USER_AGENT'])) {
                $data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
            } else {
                $data['user_agent'] = '';
            }

            if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                $data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
            } else {
                $data['accept_language'] = '';
            }

            $order_id = $this->model_module_fast_order->addOrder($data);

            $this->cart->clear();

            // Add to activity log
            $this->load->model('account/activity');

            if ($this->customer->isLogged()) {
                $activity_data = array(
                    'customer_id' => $this->customer->getId(),
                    'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
                    'order_id'    => $order_id
                );

                $this->model_account_activity->addActivity('order_account', $activity_data);
            } else {
                $activity_data = array(
                    'name'     => 'firstname' . ' ' . 'lastname',
                    'order_id' => $order_id
                );

                $this->model_account_activity->addActivity('order_guest', $activity_data);
            }

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);

            echo json_encode(array('status' => true, 'msg' => $this->language->get('text_success'), 'total' => $this->currency->format(0)));
        } else {
            echo json_encode(array('status' => false, 'msg' => $this->error));
        }
    }

    protected function validate()
    {
        if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        return !$this->error;
    }
}
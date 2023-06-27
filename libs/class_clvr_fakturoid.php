<?php
if (!class_exists('Clvr_Fakturoid')){

    class Clvr_Fakturoid
    {
        const INVOICE_ID_META_KEY = "_fakturoid_invoice_id";
        const CUSTOMER_ID_META_KEY = '_fakturoid_client_id';
        const DEV_EMAIL = 'pavel@cleverstart.cz';
        private $context = 'eddfakturoid';
        private $client;

        function __construct(){
            require 'class_clvr_fakturoid_settings.php';
            require 'class_edd_customer_meta_wrapper.php';
            $settings = new Clvr_Fakturoid_Settings();
            add_action( 'edd_payment_receipt_after', array($this, 'addInvoiceToThankYou'), 10 );
            add_action( 'edd_complete_purchase', array($this, 'setInvoicePaid') );
            add_action( 'init', array($this,'listener'));
            remove_action('edd_purchase_form_after_cc_form','edd_checkout_tax_fields',999);
		    add_filter( 'edd_require_billing_address', '__return_false', 9999 );

        }

        public function isInitialized(){
            global $edd_options;
            return (isset($edd_options[$this->context.'_login'])) and (isset($edd_options[$this->context.'_token']) and isset($edd_options[$this->context.'_slug']) );
        }

        public function addInvoiceToThankYou( $payment ) {
            global $edd_options;
          if (!$this->isInitialized() ){
            return;
          }

            $invoice_id = $this->getInvoiceID($payment->ID)
            ?>
            <tr>
                <td><strong><?php _e( 'Faktura', $this->context ); ?>:</strong></td>
                <td><a class="edd_invoice_link" title="<?php _e( 'Stáhnout fakturu', $this->context ); ?>" href="<?php echo esc_url($this->getInvoiceLink($payment->ID) ); ?>"><?php _e( 'Stáhnout fakturu', $this->context ); ?></a></td>
            </tr>
            <?php
        }

        public function getClient(){
            if (!$this->isInitialized()){
                return 'null';
            }
            if (!empty($this->client)){
                return $this->client;
            }
            global $edd_options;

            $username = $edd_options[$this->context.'_login'];
            $password = $edd_options[$this->context.'_token'];
            $slug = $edd_options[$this->context.'_slug'];
            $this->client = new Fakturoid\Client($slug,$username,$password,self::DEV_EMAIL);
            return $this->client;
        }

        public function getCustomerID($payment_id){
            $payment      = new EDD_Payment( $payment_id );
			$edd_customer_id = $payment->customer_id;
			$wrapper = new Clvr_EDD_Customer_Meta_wrapper();
			$invoicing_customer_id = $wrapper->get_meta($edd_customer_id,self::CUSTOMER_ID_META_KEY);
			if (!empty($invoicing_customer_id)){
				return $invoicing_customer_id;
            }
            $payment_meta   = $payment->get_meta();
			$user_info = edd_get_payment_meta_user_info( $payment_id );
			$customer_data = [
                'name' => $this->getCustomerName($payment_meta['edd_firma'],$payment_meta['user_info']['first_name'],$payment_meta['user_info']['last_name']),
                'street' => $payment_meta['edd_ulice'],
                'city' => $payment_meta['edd_mesto'],
                'zip' => $payment_meta['edd_psc'],
                'country' => $payment_meta['edd_stat'],
                'registration_no' => $payment_meta['edd_ic'],
                'vat_no' => $payment_meta['edd_dic']
            ];
            try{
                $response = $this->getClient()->createSubject($customer_data);
            $subject = $response->getBody();
            $invoicing_customer_id = $subject->id;
            $wrapper->add_meta($edd_customer_id,self::CUSTOMER_ID_META_KEY,$invoicing_customer_id);
            return $invoicing_customer_id;
            }catch (Exception $e){
                return $e;
            }

        }

        private function getCustomerName($company, $firstName, $lastName){
            if (empty($company)){
              if (!empty($lastName)){
                return $firstName .' '. $lastName;
              }else{
                return $firstName;
              }
            }
            return $company;
        }

        public function getInvoiceID($payment_id){
            if(!$this->isInitialized()){
                return;
            }
            $invoice_id = get_post_meta(  $payment_id, self::INVOICE_ID_META_KEY, true );
            if (!empty($invoice_id)){
                return $invoice_id;
            }
            $payment_meta = edd_get_payment_meta( $payment_id );
            $items = $this->prepareInvoiceItems($payment_meta['cart_details']);
            $customer_id = $this->getCustomerID($payment_id);
            $edd_payment = new  EDD_Payment($payment_id);
            $data =[
                'subject_id' => $customer_id,
                'lines' => $items
            ];
            $response = $this->getClient()->createInvoice($data);
            $invoice = $response->getBody();
            add_post_meta( $payment_id, self::INVOICE_ID_META_KEY, $invoice->id );
            return $invoice->id;
        }

        public function prepareInvoiceItems($cart){

            $items = array();
            foreach($cart as $cart_item){
              $total = $cart_item['subtotal'] - $cart_item['discount'];
              // if ($total<=0){
              //   $total = $cart_item['item_price'] - $cart_item['tax'];
              // }
              $item = array(
                'name' => $cart_item['name'],
                'quantity' => $cart_item['quantity'],
                'unit_price' => $total,
                'unit_price_without_vat' => $total,
                'vat_rate' => 0
              );
              array_push($items,$item);
            }
            return $items;

        }

        public function getInvoiceLink($payment_id){
            $invoice_id = $this->getInvoiceID($payment_id);
            return get_home_url() . "?edd-listener=".$this->context."&id=" . $invoice_id;


        }

        public function listener(){
            if (isset($_GET) && ($_GET['edd-listener'] == $this->context)){
                if (!$this->isInitialized()){
                    return;
                }
                if (isset($_GET['id'])){
                    $invoice_id = $_GET['id'];
                    try{
                        $response = $this->getClient()->getInvoicePdf($invoice_id);
                        $pdf = $response->getBody();
                        $filename = 'download.pdf';
                    }catch(Exception $e){
                        return;
                    }
                    header("Content-type:application/pdf");

                    // It will be called {variable_symbol}.pdf
                    header("Content-Disposition:attachment;filename={$filename}");



                    // The PDF source is in original.pdf
                    echo($pdf);
                    exit;
                }
                return;
            }
        }

        public function setInvoicePaid($payment_id){
            if(!$this->isInitialized()){
                return;
            }
            $invoice_id = $this->getInvoiceID($payment_id);
            return $this->getClient()->fireInvoice($invoice_id,'pay');
        }



    } //end class

}// class exists

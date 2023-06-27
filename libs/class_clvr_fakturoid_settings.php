<?php

if (!class_exists('Clvr_Fakturoid_Settings')){

    class Clvr_Fakturoid_Settings extends Clvr_Fakturoid
    {
        private $context = "eddfakturoid";
        function __construct(){
            add_filter( 'edd_settings_sections_extensions', array($this,'settings_section') );
            add_filter( 'edd_settings_extensions', array($this,'add_settings') );
            
            add_filter( 'edd_payment_meta', array($this,'store_payment_meta') );
            add_action( 'edd_payment_personal_details_list', array($this,'view_order_details'), 10, 2 );
            add_filter( 'edd_purchase_form_required_fields', array($this,'purchase_form_required_fields') );
            add_action( 'edd_purchase_form_user_info_fields', array($this,'checkout_fields') );
            $this->add_email_tags();
           
        }  

        public function settings_section($sections){
            
			$sections[$this->context.'-settings'] = __( 'Nastavení Fakturoid', $this->context );
			return $sections;
        }

        public function add_settings($settings){
            
            $my_settings = array (
				array(
					'id' => $this->context.'_settings',
					'name' => '<strong>Nastavení propojení EDD s Fakturoid</strong>',
					'desc' => 'Níže uvedené údaje se budou zobrazovat na každé vystavené faktuře.',
					'type' => 'header'
				),
		    array(
		      'id' => $this->context.'_login',
		      'name' => 'Přihlašovací email',
					'desc' => 'E-Mail který jste uvedli ve Fakturoid',
					'type' => 'text',
					'size' => 'regular'
		    ),
		    array(
		      'id' => $this->context.'_token',
		      'name' => 'Token Fakturoid',
					'desc' => 'Vygenerovaný token',
					'type' => 'text',
					'size' => 'regular'
            ),
            array(
                'id' => $this->context.'_slug',
                'name' => 'Název vašeho účtu',
                      'desc' => 'Zkopírujte položku Účet z nastavení účtu',
                      'type' => 'text',
                      'size' => 'regular'
              ),
				
			array(
		      'id' => $this->context.'_povinne_prijmeni',
		      'name' => 'Povinné příjmení',
					'desc' => 'Má být příjmení povinné?',
					'type' => 'checkbox'
		    ),
			array(
		      'id' => $this->context.'_povinny_stat',
		      'name' => 'Povinný stát',
					'desc' => 'Má být stát povinný?',
					'type' => 'checkbox'
		    ),
				array(
		      'id' => $this->context.'_povinna_ulice',
		      'name' => 'Povinná ulice',
					'desc' => 'Má být ulice povinná?',
					'type' => 'checkbox'
		    ),
				array(
		      'id' => $this->context.'_povinne_mesto',
		      'name' => 'Povinné město',
					'desc' => 'Má být město povinné?',
					'type' => 'checkbox'
		    ),
				array(
		      'id' =>  $this->context.'_povinne_psc',
		      'name' => 'Povinné PSČ',
					'desc' => 'Má být PSČ povinné?',
					'type' => 'checkbox'
		    ),
				array(
					'id' => $this->context.'_povinne_firemni_udaje',
		      'name' => 'Skrýt formulář nákupu na firmu',
					'desc' => 'pokud bude zaškrtnuto, tak se formulář s nákupem na firmu nezobrazí',
					'type' => 'checkbox'
				 )
			);
            if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
				$my_settings = array( $this->context.'-settings'  => $my_settings );
			}
			return array_merge( $settings, $my_settings );
    }

    public function checkout_fields() {
    	global $edd_options;
    	if(isset($edd_options[$this->context.'_povinne_firemni_udaje']) && !empty($edd_options[$this->context.'_povinne_firemni_udaje'])){
    		return;
    	}
    ?>
        <p id="edd-faktura-general">
    	  <strong>Fakturační údaje:</strong>
    	</p>
        <p id="edd-faktura-firma-wrap">
            <label class="edd-label" for="edd-firma">Název společnosti</label>
            <span class="edd-description">
            	Vyplňte název vaší společnosti. Pokud toto pole vyplníte, vaše křestní jméno se na faktuře neobjeví
            </span>
            <input class="edd-input" type="text" name="edd_firma" id="edd-firma" placeholder="" />
        </p>
    		<?php if(isset($edd_options[$this->context.'_povinny_stat']) && !empty($edd_options[$this->context.'_povinny_stat'])): ?>
    	<p id="edd-faktura-stat-wrap">
            <label class="edd-label" for="edd-stat">Stát <span class="edd-required-indicator">*</span></label>
            <span class="edd-description">
            	Vyplňte stát vaší společnosti
            </span>
            <input class="edd-input" type="text" name="edd_stat" id="edd-stat" value="Česká Republika" />
        </p>
    	<?php endif;?>
        <p id="edd-faktura-ic-wrap">
            <label class="edd-label" for="edd-ic">IČ</label>
            <span class="edd-description">
            	Vyplňte IČ vaší společnosti
            </span>
            <input class="edd-input" type="text" name="edd_ic" id="edd-ic" placeholder="" />
        </p>
        <p id="edd-faktura-dic-wrap">
            <label class="edd-label" for="edd-dic">DIČ</label>
            <span class="edd-description">
            	Vyplňte DIČ vaší společnosti
            </span>
            <input class="edd-input" type="text" name="edd_dic" id="edd-dic" placeholder="" />
        </p>
    		<?php if(isset($edd_options[$this->context.'_povinna_ulice']) && !empty($edd_options[$this->context.'_povinna_ulice'])): ?>
    	<p id="edd-faktura-ulice-wrap">
            <label class="edd-label" for="edd-ulice">Ulice a číslo popisné <span class="edd-required-indicator">*</span></label>
            <span class="edd-description">
            	Vyplňte ulici sídla vaší společnosti
            </span>
            <input class="edd-input" type="text" name="edd_ulice" id="edd-ulice" placeholder="" />
        </p>
    	<?php endif;?>
    	<?php if(isset($edd_options[$this->context.'_povinne_mesto']) && !empty($edd_options[$this->context.'_povinne_mesto'])): ?>
    	<p id="edd-faktura-mesto-wrap">
            <label class="edd-label" for="edd-mesto">Město <span class="edd-required-indicator">*</span></label>
            <span class="edd-description">
            	Vyplňte město sídla vaší společnosti
            </span>
            <input class="edd-input" type="text" name="edd_mesto" id="edd-mesto" placeholder="" />
        </p>
    		<?php endif;?>
    		<?php if(isset($edd_options[$this->context.'_povinne_psc']) && !empty($edd_options[$this->context.'_povinne_psc'])): ?>
    	<p id="edd-faktura-psc-wrap">
            <label class="edd-label" for="edd-psc">PSČ <span class="edd-required-indicator">*</span></label>
            <span class="edd-description">
            	Vyplňte PSČ
            </span>
            <input class="edd-input" type="text" name="edd_psc" id="edd-psc" placeholder="" />
        </p>
        <?php endif;
    }

    function all_extra_fields() {
        $fields[] = "edd_firma";
        $fields[] = "edd_stat";
        $fields[] = "edd_ic";
        $fields[] = "edd_dic";
        $fields[] = "edd_ulice";
        $fields[] = "edd_mesto";
        $fields[] = "edd_psc";
        return $fields;
    }

    public function store_payment_meta($payment_meta){
        $extra_fields = $this->all_extra_fields();
        foreach ($extra_fields as $key => $extra_field){
              if(empty($payment_meta[$extra_field])){
                  $payment_meta[$extra_field] = isset( $_POST[$extra_field] ) ? sanitize_text_field( $_POST[$extra_field] ) : '';
              }
        }
        return $payment_meta;
    }

    public function view_order_details( $payment_meta, $user_info ) {
        $firma = isset( $payment_meta['edd_firma'] ) ? $payment_meta['edd_firma'] : 'položka neuvedena';
        $stat = isset( $payment_meta['edd_stat'] ) ? $payment_meta['edd_stat'] : 'položka neuvedena';
        $ic = isset( $payment_meta['edd_ic'] ) ? $payment_meta['edd_ic'] : 'položka neuvedena';
        $dic = isset( $payment_meta['edd_dic'] ) ? $payment_meta['edd_dic'] : 'položka neuvedena';
        $ulice = isset( $payment_meta['edd_ulice'] ) ? $payment_meta['edd_ulice'] : 'položka neuvedena';
        $mesto = isset( $payment_meta['edd_mesto'] ) ? $payment_meta['edd_mesto'] : 'položka neuvedena';
        $psc = isset( $payment_meta['edd_psc'] ) ? $payment_meta['edd_psc'] : 'položka neuvedena';
        
    ?>
        <div class="column-container">
            <div class="column">
                <strong>Firma: </strong>
                 <?php echo $firma; ?>
            </div>
            <div class="column">
                <strong>Stát: </strong>
                 <?php echo $stat; ?>
            </div>
            <div class="column">
                <strong>IČ: </strong>
                 <?php echo $ic; ?>
            </div>
            <div class="column">
                <strong>DIČ: </strong>
                 <?php echo $dic; ?>
            </div>
            <div class="column">
                <strong>Ulice a číslo popisné: </strong>
                 <?php echo $ulice; ?>
            </div>
            <div class="column">
                <strong>Město: </strong>
                 <?php echo $mesto; ?>
            </div>
            <div class="column">
                <strong>PSČ: </strong>
                 <?php echo $psc; ?>
            </div>
            
        </div>
    <?php
    }

    public function purchase_form_required_fields( $required_fields ) {
        global $edd_options;
        if(isset($edd_options[$this->context.'_povinne_prijmeni']) && !empty($edd_options[$this->context.'_povinne_prijmeni'])){
            $required_fields['edd_last'] = array(
            'error_id' => 'invalid_last_name',
            'error_message' => 'Prosím vyplňte příjmení.'
        );
        }
        if(isset($edd_options[$this->context.'_povinny_stat']) && !empty($edd_options[$this->context.'_povinny_stat'])){
                $required_fields['edd_stat'] = array(
                'error_id' => 'invalid_edd_stat',
                'error_message' => 'Prosím vyplňte stát.'
            );
        }
        if(isset($edd_options[$this->context.'_povinna_ulice']) && !empty($edd_options[$this->context.'_povinna_ulice'])){
                $required_fields['edd_ulice'] = array(
                'error_id' => 'invalid_edd_ulice',
                'error_message' => 'Prosím vyplňte ulici a číslo popisné.'
            );
        }
        if(isset($edd_options[$this->context.'_povinne_mesto']) && !empty($edd_options[$this->context.'_povinne_mesto'])){
                $required_fields['edd_mesto'] = array(
                'error_id' => 'invalid_edd_mesto',
                'error_message' => 'Prosím vyplňte město.'
            );
        }
        if(isset($edd_options[$this->context.'_povinne_psc']) && !empty($edd_options[$this->context.'_povinne_psc'])){
            $required_fields['edd_psc'] = array(
            'error_id' => 'invalid_edd_psc',
            'error_message' => 'Prosím vyplňte PSČ.'
            );
        }
        return $required_fields;
    }

    public function add_email_tags(){
        edd_add_email_tag( $this->context.'_firma', 'Firma zákazníka', array($this,'email_tag_firma') );
        edd_add_email_tag( $this->context.'_stat', 'Stát zákazníka', array($this, 'email_tag_stat') );
        edd_add_email_tag( $this->context.'_ic', 'IČ zákazníka', array($this, 'email_tag_ic') );
        edd_add_email_tag( $this->context.'_dic', 'DIČ zákazníka', array($this, 'email_tag_dic') );
        edd_add_email_tag( $this->context.'_ulice', 'Ulice a číslo popisné zákazníka', array($this,'email_tag_ulice') );
        edd_add_email_tag( $this->context.'_mesto', 'Město zákazníka', array($this,'email_tag_mesto') );
        edd_add_email_tag( $this->context.'_psc', 'PSČ zákazníka', array($this,'email_tag_psc') );
        edd_add_email_tag( $this->context.'_polozky', 'Položky v košíku', array($this,'email_tag_polozky') );
        edd_add_email_tag( $this->context.'_datum', 'Datum nákupu', array($this,'email_tag_datum') );
        edd_add_email_tag( $this->context.'_faktura_link', 'Neformátovaný odkaz na fakturu', array($this,'getInvoiceLink') );
        edd_add_email_tag( $this->context.'_faktura_invoice', 'Vloží odkaz s textem Fakturu si stáhněte zde', array($this,'getInvoiceDownload') );
        add_filter( 'edd_email_preview_template_tags', array($this,'email_preview'));
    }
    
    public function email_preview($message){
		$download_list = '<ul>';
		$download_list .= '<li>' . __( 'Sample Product Title', 'easy-digital-downloads' ) . '<br />';
		$download_list .= '<div>';
		$download_list .=  __( 'Sample Download File Name', 'easy-digital-downloads' ) . ' - <small>' . __( 'Optional notes about this download.', 'easy-digital-downloads' ) . '</small>';
		$download_list .= '</div>';
		$download_list .= '</li>';
        $download_list .= '</ul>';
        $payment_id = rand(1, 100);
		$link = $this->getInvoiceLink($payment_id);
		$html = "<a href=\"" .$link. "\">Fakturu si stáhněte zde</a>";

		$message = str_replace( '{'.$this->context.'_firma}', 'Ukázková firma s.r.o.', $message );
		$message = str_replace( '{'.$this->context.'_stat}', 'Česko', $message );
		$message = str_replace( '{'.$this->context.'_ic}', '25596641', $message );
		$message = str_replace( '{'.$this->context.'_dic}', 'CZ25596641', $message );
		$message = str_replace( '{'.$this->context.'_ulice}', 'Ukázková ulice 1', $message );
		$message = str_replace( '{'.$this->context.'_mesto}', 'Ukázkové město', $message );
		$message = str_replace( '{'.$this->context.'_psc}', '11150', $message );
		$message = str_replace( '{'.$this->context.'_polozky}', $download_list, $message );
		$message = str_replace( '{'.$this->context.'_datum}', '21.12.2012', $message );
		$message = str_replace( '{'.$this->context.'_faktura_link}', $link, $message );
		$message = str_replace( '{'.$this->context.'_faktura_invoice}', $html, $message );
		//$message = apply_filters( 'edd_email_preview_template_tags', $message );

		return apply_filters( 'edd_email_template_wpautop', true ) ? wpautop( $message ) : $message;
    }
    
    /**
    * The {Firma} email tag
    */
    public function email_tag_firma( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_firma'];
    }

    /**
     * The {Stat} email tag
     */
    public function email_tag_stat( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_stat'];
    }

    /**
     * The {IC} email tag
     */
    public function email_tag_ic( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_ic'];
    }

    /**
     * The {DIC} email tag
     */
    public function email_tag_dic( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_dic'];
    }

    /**
     * The {Ulice} email tag
     */
    public function email_tag_ulice( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_ulice'];
    }

    /**
     * The {Mesto} email tag
     */
    public function email_tag_mesto( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_mesto'];
    }

    /**
     * The {PSC} email tag
     */
    public function email_tag_psc( $payment_id ) {
    	$payment_data = edd_get_payment_meta( $payment_id );
    	return $payment_data['edd_psc'];
    }

    public function email_tag_polozky($payment_id){
       $decissions = $this->format_cart_items($payment_id);
       return $decissions[1];
    }

    public function email_tag_datum($payment_id){
      $payment_meta = edd_get_payment_meta( $payment_id );
      $date = DateTime::createFromFormat('Y-m-d G:i:s', $payment_meta['date']);
      return $date->format('d.m. Y');
    }

    private function format_cart_items($payment_id){
        if (function_exists('get_home_path')){
        	$path = get_home_path();
        	$path .= "wp-content/plugins/easy-digital-downloads/includes/payments/class-edd-payment.php";
        }else{
        	$path = dirname(__FILE__) . "/../easy-digital-downloads/includes/payments/class-edd-payment.php";
        }
        //require_once($path);

    	$cart_items = edd_get_payment_meta_cart_details( $payment_id );
      $payment = new EDD_Payment( $payment_id );
    	$payment_data  = $payment->get_meta();
    	$download_list = '<ul>';
    	$cart_items    = $payment->cart_details;
    	$email         = $payment->email;
        	if ( $cart_items ) {
    		$show_names = apply_filters( 'edd_email_show_names', true );
    		$show_links = apply_filters( 'edd_email_show_links', true );
            $i = 0;
    		foreach ( $cart_items as $item ) {
    			if ( edd_use_skus() ) {
    				$sku = edd_get_download_sku( $item['id'] );
    			}

    				$quantity = $item['quantity'];
                    $pavelCart[$i]['quantity'] = $item['quantity'];

            $price_id = edd_get_cart_item_price_id( $item );
    			//if ( $show_names ) {
    			if ( false ) {
    				$title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

    				if ( ! empty( $quantity ) && $quantity > 1 ) {
    					$title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
    				}
    				if ( ! empty( $sku ) ) {
    					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
    				}
    				if ( $price_id !== null ) {
    					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );

    				}
    				$download_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . '<br/>';
    			}
          			$price_id = edd_get_cart_item_price_id( $item );
                    $pavelCart[$i]['price_id']  = edd_get_cart_item_price_id( $item );
    			if ( $show_names ) {
    				$title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';
                    $pavelCart[$i]['title'] = get_the_title( $item['id'] );
    				if ( ! empty( $quantity )  ) {
    					$title .= "&nbsp;&ndash;&nbsp;" . __( 'Množství', 'easy-digital-downloads' ) . ': ' . $quantity ." ks";
    				}
    				if ( ! empty( $sku ) ) {
    					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
    				}
    				if ( $price_id !== null ) {
    					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
                        $pavelCart[$i]['price_option_name'] = edd_get_price_option_name( $item['id'], $price_id, $payment_id );
    				}
                    $title .="&nbsp;&ndash;&nbsp;" . "Jednotková cena: " .$item["price"]. " CZK</span>";
                    $pavelCart[$i]['jednotkova_cena'] = $item["price"];
                    //$title .="<span>; " .$item["quantity"]. " ks";
                    $i++;
    				$download_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . '<br/>';
    			}
    			$files = edd_get_download_files( $item['id'], $price_id );
    			if ( ! empty( $files ) ) {
    				foreach ( $files as $filekey => $file ) {
    					if ( $show_links ) {
    						$download_list .= '<div>';
    							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
    							$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
    							$download_list .= '</div>';
    					} else {
    						$download_list .= '<div>';
    							$download_list .= edd_get_file_name( $file );
    						$download_list .= '</div>';
    					}
    				}
    			} elseif ( edd_is_bundled_product( $item['id'] ) ) {
    				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );
    				foreach ( $bundled_products as $bundle_item ) {
    					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';
    					$files = edd_get_download_files( $bundle_item );
    					foreach ( $files as $filekey => $file ) {
    						if ( $show_links ) {
    							$download_list .= '<div>';
    							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
    							$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
    							$download_list .= '</div>';
    						} else {
    							$download_list .= '<div>';
    							$download_list .= edd_get_file_name( $file );
    							$download_list .= '</div>';
    						}
    					}
    				}
    			}
    			if ( '' != edd_get_product_notes( $item['id'] ) ) {
    				$download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
    			}
    			if ( $show_names ) {
    				$download_list .= '</li>';
    			}
    		}
    	}
    	$download_list .= '</ul>';

        $decissions = array(
           '1' =>$download_list,
           '2' =>$pavelCart
        );
    	return $decissions;

    }

    public function getInvoiceDownload($payment_id){
        $link = $this->getInvoiceLink($payment_id);
        $html = "<a href=\"" .$link. "\">Fakturu si stáhněte zde</a>";
        return $html;
      }

        
    } //end class
    

} // class exists
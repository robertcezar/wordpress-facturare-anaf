<?php
	/*
		* Plugin Name: Romanian Billing Fields
		* Description: Add Romanian billing fields to WooCommerce checkout.
		* Version: 1.8.0
		* Author: Gheorghiu Robert
		* Author URI: https://www.linkedin.com/in/cezar-robert-gheorghiu/
		* Tested up to: 6.4
		* License: GPL v3 or later
		* License URI: http://www.gnu.org/licenses/gpl-3.0.html
	*/	
	
	
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
	
	define("ANAF_API_URL", "https://webservicesp.anaf.ro/PlatitorTvaRest/api/v8/ws/tva");
	define("CIF_ERROR_MESSAGE", "<b>CIF/CUI</b> nu exista sau nu este corect");
	define("ANAF_API_URL_PLUG",  WP_PLUGIN_URL . "/". str_replace( basename( __FILE__ ), "", plugin_basename(__FILE__) ));
	
	if (in_array("woocommerce/woocommerce.php", apply_filters("active_plugins", get_option("active_plugins"))) && !function_exists("initialize_romanian_billing_fields")) {
		$grc_address_fields = array(
		"first_name",
		"last_name",
		"company",
		"b_nrregcom",
		"b_cif",
		"b_cnp",
		"address_1",
		"city",
		"state",
		"postcode",
		"country"
		);
		
		$grc_ext_fields = array("b_cif","b_nrregcom","b_cnp");
		
		add_filter( "woocommerce_default_address_fields" , "grc_override_default_address_fields" );
		
		function grc_override_default_address_fields( $address_fields ){
			$temp_fields = array();
			$address_fields["b_cif"] = array(
			"label"     => __("CIF:", "woocommerce"),
			"required"  => true,
			"placeholder" => "Introdu CIF/CUI firma si apasa butonul",
			"class"     => array("form-row-last"),
			"type"  => "text",
			"priority" => 35,
			);
			$address_fields["b_nrregcom"] = array(
			"label"     => __("Nr.Reg.Com.:", "woocommerce"),
			"required"  => true,
			"placeholder" => "Nr.Reg.Com.",
			"class"     => array("form-row-first"),
			"type"  => "text",
			"priority" => 35,
			);
			$address_fields["b_cnp"] = array(
			"label"     => __("CNP:", "woocommerce"),
			"required"  => false,
			"placeholder" => "CNP (optional)",
			"class"     => array("form-row-wide"),
			"type"  => "text",
			"priority" => 35,
			);
			$address_fields["company"]["required"] = true;
			
			global $grc_address_fields;
			
			foreach($grc_address_fields as $fky){       
				$temp_fields[$fky] = $address_fields[$fky];
			}
			
			$address_fields = $temp_fields;
			
			if( isset($_POST["persoana"]) && $_POST["persoana"]=="pf"){ 
				$address_fields["company"]["required"]= false; 
				$address_fields["b_cif"]["required"]= false;
				$address_fields["b_nrregcom"]["required"]= false;
			} 
			
			return $address_fields;
		}
		
		add_filter("woocommerce_formatted_address_replacements", "custom_formatted_address_replacements", 99, 2);
		
		function custom_formatted_address_replacements($address, $args) {
			$custom_field_map = array(
			"company"     => "company",
			"b_cif"       => "b_cif",
			"b_nrregcom"  => "b_nrregcom",
			"b_cnp"       => "b_cnp",
			);
			$custom_fields_string = "";
			
			foreach ($custom_field_map as $field_key => $arg_key) {
				if (isset($args[$arg_key])) {
					$custom_fields_string .= $args[$arg_key] . "\n";
				}
			}
			$address["{company}"] = $custom_fields_string;
			
			return $address;
		}
		
		add_filter( "woocommerce_order_formatted_billing_address", "grc_update_formatted_billing_address", 99, 2);
		function grc_update_formatted_billing_address( $address, $obj ){
			global $grc_address_fields;
			if(is_array($grc_address_fields)){
				foreach($grc_address_fields as $waf){
					$address[$waf] = get_post_meta($obj->get_id(), "_billing_".$waf, true);
				}
			}
			return $address;    
		}
		
		add_filter("woocommerce_my_account_my_address_formatted_address", "grc_my_account_address_formatted_address", 99, 3);
		function grc_my_account_address_formatted_address( $address, $customer_id, $name ){
			global $grc_address_fields;
			if(is_array($grc_address_fields)){
				foreach($grc_address_fields as $waf){
					$address[$waf] = get_user_meta( $customer_id, $name."_".$waf, true );
				}
			}
			return $address;
		}   
		
		add_filter("woocommerce_admin_billing_fields", "grc_add_extra_customer_field");
		function grc_add_extra_customer_field( $fields ){
			$email = $fields["email"]; 
			$phone = $fields["phone"];
			$fields = grc_override_default_address_fields( $fields );
			$fields["email"] = $email;
			$fields["phone"] = $phone;
			
			global $grc_ext_fields;
			
			if(is_array($grc_ext_fields)){
				foreach($grc_ext_fields as $wef){
					$fields[$wef]["show"] = false; 
				}
			}
			return $fields;
		}
		
		add_filter("woocommerce_shipping_fields","grc_custom_billing_fields");
		function grc_custom_billing_fields( $fields = array() ) {
			unset($fields["shipping_b_cif"]);
			unset($fields["shipping_b_nrregcom"]);
			unset($fields["shipping_b_cnp"]);
			return $fields;
		}
		
		add_action( "woocommerce_checkout_before_customer_details", "grc_add_checkout_content", 12 );
		function grc_add_checkout_content() {
			if(is_checkout()) {
				woocommerce_form_field( "persoana", array(
				"type"          => "select",
				"class"         => array( "tip-facturare" ),
				"required"     => "yes",
				"label"         => __( "Alege tipul de facturare" ),
				"options"       => array(
				"pf"		=> __( "Persoana fizica", "grc" ),
				"pj"	=> __( "Persoana juridica", "grc" )
				)
				));
			}
		}
		
		add_action("woocommerce_checkout_process", "is_cif", 10,2);
		add_action( "woocommerce_after_save_address_validation", "is_cif", 10,2);
		function is_cif() {
			if (!empty($_POST["billing_b_cif"])){
				$url = ANAF_API_URL;
				$ch = curl_init($url);
				$jsonData = array(
				"cui" => preg_replace("/[^0-9]/", "", $_POST["billing_b_cif"]),
				"data" => date("Y-m-d")
				);
				$jsonDataEncoded = json_encode([$jsonData]);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				$result = curl_exec($ch);
				curl_close($ch);
				$json = json_decode($result, true);
				if( $json["cod"]==200){
					die('ok200');
					if( empty( $json["found"][0]["denumire"] ) ) {
						wc_add_notice(CIF_ERROR_MESSAGE." - ANAF CODE: ".$json["cod"], "error");
					}
				}
				else
				{
					wc_add_notice(CIF_ERROR_MESSAGE." - ANAF CODE: ".$json["cod"], "error");
				}
			}
		}
		
		add_action("wp_footer", "autofill_billing_fields");
		function autofill_billing_fields() {
			if (is_checkout()) {
			?>
			<script>
				jQuery(document).ready(function($) {
					function fillBillingFields(response) {
						if (response.found && response.found.length > 0) {
							var data = response.found[0].date_generale;
							console.log(response.found[0]);
							$("#billing_company").val(data.denumire);
							$("#billing_address_1").val(data.adresa);
							if( !data.nrRegCom ){$("#billing_b_nrregcom").val("J00/000/0000");}else{$("#billing_b_nrregcom").val(data.nrRegCom);}
							$("#billing_city").val(data.sdenumire_Localitate);
							$("#billing_phone").val(data.telefon);
							$("#billing_postcode").val(data.codPostal);
							$("#billing_company_field, #billing_b_nrregcom_field").show();
							$(".anafcheck").hide();
							} else {
							alert("CIF/CUI este incorect, verifica si introdu din nou.");
							console.log("No matching data found.");
						}
					}
					
					function doApiCall(cifValue) {
						$.ajax({
							type: "POST",
							url: "<?php echo ANAF_API_URL_PLUG; ?>anaf-call.php",
							data: {
								billing_b_cif: cifValue
							},
							dataType: "json",
							success: fillBillingFields,
							error: function(xhr, status, error) {
								console.log("Error: " + status + " - " + error);
							}
						});
					}
					
					$("#persoana").change(function () {
						var selectedValue = this.value;
						if (selectedValue === "pf") {
							showPFFields();
							} else if (selectedValue === "pj") {
							showPJFields();
							if ($("#autofillButton").length === 0) {
								$("#billing_b_cif_field").append("<small><button id=\"autofillButton\" class=\"anafcheck button expand form-row-wide\" type=\"button\">Autocompleteaza datele firmei</button></small>");
							}
							$("#autofillButton").click(function() {
								var cifValue = $("#billing_b_cif").val();
								doApiCall(cifValue);
							});
						}
					});
					
					function toggleFields() {
						$("#billing_company_field, #autofillButton, #billing_b_nrregcom_field, #billing_b_cif_field, #billing_b_cnp_field").hide();
					}
					
					function showPFFields() {
						toggleFields();
						$("#billing_b_cnp_field").show();
					}
					
					function showPJFields() {
						toggleFields();
						$("#billing_b_cif_field").show();
					}
					
					toggleFields();
				});
			</script>
			<?php
			}
		}
	}
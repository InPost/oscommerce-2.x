<?php
/*

Paczkomaty InPost osCommerce Module
Revision 2.0.0

Copyright (c) 2012 InPost Sp. z o.o.

*/
class easypack24 {

	var $code, $title, $description, $enabled;

	function easypack24() {
		global $order, $total_weight, $address;

		$this->code = 'easypack24';
		$this->title = MODULE_SHIPPING_EASYPACK24_TEXT_TITLE;
        $this->subtitle = MODULE_SHIPPING_EASYPACK24_TEXT_SUBTITLE;
		$this->description = MODULE_SHIPPING_EASYPACK24_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_SHIPPING_EASYPACK24_SORT_ORDER;
		$this->icon = '';//'http://media.paczkomaty.pl/pieczatka.gif';
		$this->tax_class = MODULE_SHIPPING_EASYPACK24_TAX_CLASS;
		$this->enabled = ((MODULE_SHIPPING_EASYPACK24_STATUS == 'True') ? true : false);
        $this->easypack24 = array();
    }

	function get_customer() {
		global $customer_id, $sendto;

		$account_query = tep_db_query("select customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$account = tep_db_fetch_array($account_query);
		$customer['email'] = $account['customers_email_address'];
		$customer['phone'] = $account['customers_telephone'];
        if(!preg_match('/^[1-9]{1}\d{8}$/', $account['customers_telephone'])){
            $customer['phone'] = null;
        }

        $account_query = tep_db_query("select entry_postcode, entry_city from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$sendto . "'");
		$account = tep_db_fetch_array($account_query);
		$customer['postcode'] = $account['entry_postcode'];
        $customer['city'] = $account['entry_city'];

		return $customer;
	}

	function quote($method = '') {
		global $order, $total_weight, $shipping_weight, $shipping_num_boxes, $customer_id, $sendto, $easypack24;

		$customer = $this->get_customer();

		$dest_country = $order->delivery['country']['iso_code_2'];
		if($dest_country == 'GB'){$dest_country = 'UK';}

        $errors = false;

        $countries_table = explode(',', constant('MODULE_SHIPPING_EASYPACK24_ALLOWED_COUNTRY'));

		if (in_array($dest_country, $countries_table)) {
			$prices_table = explode(',', trim(constant('MODULE_SHIPPING_EASYPACK24_PRICE')));
			$key = array_search($dest_country, $countries_table);
			if (array_key_exists($key, $prices_table)) {
				$shipping_cost = $prices_table[$key] * $shipping_num_boxes;
			} else {
				$shipping_cost = $prices_table[0] * $shipping_num_boxes;
			}
		} else {
            $errors[] = MODULE_SHIPPING_EASYPACK24_TEXT_INVALID_ZONE;
		}

        if($this->validate() == false || isset($this->quotes['error'] )){
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message='.$this->quotes['error']));
        }

		if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

        // check weight
		if ($total_weight > constant('MODULE_SHIPPING_EASYPACK24_MAX_WEIGHT')) {
            $errors[] = MODULE_SHIPPING_EASYPACK24_UNDEFINED_RATE.' '.MODULE_SHIPPING_EASYPACK24_TEXT_UNITS.' ('.$total_weight.' '.MODULE_SHIPPING_EASYPACK24_TEXT_UNITS.') ';
            //tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message='.MODULE_SHIPPING_EASYPACK24_UNDEFINED_RATE));
		}

        // check dimensions
        // oscommerce doesn't support products dimmension
        $parcelSize = 'A';
        $this->easypack24['parcel_size'] = $parcelSize;

        // get machines
        require_once DIR_WS_FUNCTIONS.'easypack24_functions.php';
        $allMachines = easypack24_connect(
            array(
                'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'machines',
                'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
                'methodType' => 'GET',
                'params' => array(
                )
            )
        );

        $parcelTargetAllMachinesId = array();
        $parcelTargetAllMachinesDetail = array();
        $machines = array();
        if(is_array(@$allMachines['result']) && !empty($allMachines['result'])){
            foreach($allMachines['result'] as $key => $machine){
                $parcelTargetAllMachinesId[$machine->id] = addslashes($machine->id.', '.@$machine->address->city.', '.@$machine->address->street);
                $parcelTargetAllMachinesDetail[$machine->id] = array(
                    'id' => $machine->id,
                    'address' => array(
                        'building_number' => @$machine->address->building_number,
                        'flat_number' => @$machine->address->flat_number,
                        'post_code' => @$machine->address->post_code,
                        'province' => @$machine->address->province,
                        'street' => @$machine->address->street,
                        'city' => @$machine->address->city
                    )
                );
                if($machine->address->post_code == $customer['postcode']){
                    $machines[$key] = $machine;
                    continue;
                }elseif($machine->address->city == $customer['city']){
                    $machines[$key] = $machine;
                }

                $this->easypack24['parcelTargetAllMachinesId'] = $parcelTargetAllMachinesId;
                $this->easypack24['parcelTargetAllMachinesDetail'] = $parcelTargetAllMachinesDetail;
            }
        }

        $parcelTargetMachinesId = array();
        $parcelTargetMachinesDetail = array();
        $this->easypack24['defaultSelect'] = 'Select Machine..';
        if(is_array(@$machines) && !empty($machines)){
            foreach($machines as $key => $machine){
                $parcelTargetMachinesId[$machine->id] = $machine->id.', '.@$machine->address->city.', '.@$machine->address->street;
                $parcelTargetMachinesDetail[$machine->id] = array(
                    'id' => $machine->id,
                    'address' => array(
                        'building_number' => @$machine->address->building_number,
                        'flat_number' => @$machine->address->flat_number,
                        'post_code' => @$machine->address->post_code,
                        'province' => @$machine->address->province,
                        'street' => @$machine->address->street,
                        'city' => @$machine->address->city
                    )
                );
            }
            $this->easypack24['parcelTargetMachinesId'] = $parcelTargetMachinesId;
        }else{
            $this->easypack24['defaultSelect'] = 'no terminals in your city';
        }

        $this->quotes = array(
            'id' => $this->code,
            'module' => $this->title,
            'methods' => array(
                array(
                    'id' => $this->code,
                    'title' => $this->subtitle,
                    'cost' => $shipping_cost,
                    'easypack24' => $this->easypack24,
                    'customer' => $customer
                )
            )
        );

		if ($this->tax_class > 0) {
			$this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
		}

		if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

        if (!empty($errors)) $this->quotes['error'] = implode("<br>", $errors);

        return $this->quotes;
	}

	function check() {
		
		if (!isset($this->_check)) {
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_EASYPACK24_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		$default_countries = 'UK';
		
		tep_db_query("create table if not exists order_shipping_easypack24 (
          id int(11) unsigned NOT NULL auto_increment,
	      order_id int(11) NOT NULL,
	      parcel_id varchar(200) NOT NULL default '',
	      parcel_status varchar(200) NOT NULL default '',
	      parcel_detail text NOT NULL default '',
	      parcel_target_machine_id varchar(200) NOT NULL default '',
	      parcel_target_machine_detail text NOT NULL default '',
          sticker_creation_date TIMESTAMP NULL DEFAULT NULL,
          creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      PRIMARY KEY (id));"
        );
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable module InPost Parcel Lockers 24/7', 'MODULE_SHIPPING_EASYPACK24_STATUS', 'True', 'Do you want to offer InPost Parcel Lockers 24/7 shipping?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Api url', 'MODULE_SHIPPING_EASYPACK24_API_URL', 'http://api-uk.easypack24.net/', 'Api url from easypack24', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Api key', 'MODULE_SHIPPING_EASYPACK24_API_KEY', '', 'Api key from easypack24', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Price', 'MODULE_SHIPPING_EASYPACK24_PRICE', '14', 'Sending price', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_EASYPACK24_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max weight', 'MODULE_SHIPPING_EASYPACK24_MAX_WEIGHT', '25', 'Total weight of items in checkout', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max dimension a', 'MODULE_SHIPPING_EASYPACK24_MAX_DIMENSION_A', '8x38x64', 'Max dimension of items in checkout', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max dimension b', 'MODULE_SHIPPING_EASYPACK24_MAX_DIMENSION_B', '19x38x64', 'Max dimension of items in checkout', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max dimension c', 'MODULE_SHIPPING_EASYPACK24_MAX_DIMENSION_C', '19x38x64', 'Max dimension of items in checkout', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Allowed country', 'MODULE_SHIPPING_EASYPACK24_ALLOWED_COUNTRY', '" . $default_countries . "', 'Allowed country', '6', '0', now())");
    }

	function remove() {
		
		tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		$keys = array(
			'MODULE_SHIPPING_EASYPACK24_STATUS',
			'MODULE_SHIPPING_EASYPACK24_API_URL',
            'MODULE_SHIPPING_EASYPACK24_API_KEY',
            'MODULE_SHIPPING_EASYPACK24_PRICE',
            'MODULE_SHIPPING_EASYPACK24_TAX_CLASS',
            'MODULE_SHIPPING_EASYPACK24_MAX_WEIGHT',
            'MODULE_SHIPPING_EASYPACK24_MAX_DIMENSION_A',
            'MODULE_SHIPPING_EASYPACK24_MAX_DIMENSION_B',
            'MODULE_SHIPPING_EASYPACK24_MAX_DIMENSION_C',
            'MODULE_SHIPPING_EASYPACK24_ALLOWED_COUNTRY'
		);
		return $keys;
	}

    function generate_form($quotes) {
        global $shipping, $currencies, $radio_buttons, $n, $n2;

        require_once DIR_WS_FUNCTIONS.'easypack24_functions.php';

        $checked = preg_match('/easypack24/', $shipping['id']);
        ?>
        <script type="text/javascript" src="https://geowidget.inpost.co.uk/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function"></script>
        <?php

        if ( ($checked) || ($n == 1 && $n2 == 1) ) {
            echo '<tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, '.$radio_buttons.')">';
        } else {
            echo '<tr id="easypack24Row" class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, '.$radio_buttons.')">';
        }

        ?>
            <td width="75%" style="padding-left: 15px;"><?php echo $quotes['methods'][0]['title'] ?></td>
            <td><?php echo $currencies->format(tep_add_tax($quotes['methods'][0]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0))) ?></td>
            <td align="right"><?php echo tep_draw_radio_field('shipping', $quotes['id'] . '_' . $quotes['methods'][0]['id'], $checked, 'id="easypack24"') ?></td>
        </tr>
        <tr id="easypack24_detail">
            <td>
                <br>&nbsp; &nbsp; &nbsp; &nbsp;<select id="shipping_easypack24" onChange="choose_from_dropdown()" name="shipping_easypack24[parcel_target_machine_id]">
                    <option value='' <?php if(@$_POST['shipping_easypack24']['parcel_target_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo @$quotes['methods'][0]['easypack24']['defaultSelect'];?></option>
                    <?php foreach(@$quotes['methods'][0]['easypack24']['parcelTargetMachinesId'] as $key => $parcelTargetMachineId): ?>
                    <option value='<?php echo $key ?>' <?php if(@$_POST['shipping_easypack24']['parcel_target_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo @$parcelTargetMachineId;?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="name" name="name" disabled="disabled" />
                <input type="hidden" id="box_machine_town" name="box_machine_town" disabled="disabled" />
                <input type="hidden" id="address" name="address" disabled="disabled" />
                <br>&nbsp; &nbsp; &nbsp; &nbsp;
                <a href="#" onclick="openMap(); return false;">Map</a>&nbsp|&nbsp<input type="checkbox" name="show_all_machines"> Show terminals in other cities
                <br>
                <br>&nbsp; &nbsp; &nbsp; &nbsp;<b>Mobile e.g. 523045856 *: </b>
                <br>&nbsp; &nbsp; &nbsp; &nbsp;(07) <input type='text' onChange="choose_from_dropdown()" name='shipping_easypack24[receiver_phone]' title="mobile /^[1-9]{1}\d{8}$/" id="easypack24_phone" title="mobile /^[1-9]{1}\d{8}$/" value='<?php echo @$_POST['shipping_easypack24']['receiver_phone']?@$_POST['shipping_easypack24']['receiver_phone']:@$quotes['methods'][0]['customer']['phone']; ?>' />
                <br><br>
            </td>
        </tr>
        <script type="text/javascript">
            function user_function(value) {
                var address = value.split(';');
                //document.getElementById('town').value=address[1];
                //document.getElementById('street').value=address[2]+address[3];
                var box_machine_name = document.getElementById('name').value;
                var box_machine_town = document.value=address[1];
                var box_machine_street = document.value=address[2];


                var is_value = 0;
                document.getElementById('shipping_easypack24').value = box_machine_name;
                var shipping_easypack24 = document.getElementById('shipping_easypack24');

                for(i=0;i<shipping_easypack24.length;i++){
                    if(shipping_easypack24.options[i].value == document.getElementById('name').value){
                        shipping_easypack24.selectedIndex = i;
                        is_value = 1;
                    }
                }

                if (is_value == 0){
                    shipping_easypack24.options[shipping_easypack24.options.length] = new Option(box_machine_name+','+box_machine_town+','+box_machine_street, box_machine_name);
                    shipping_easypack24.selectedIndex = shipping_easypack24.length-1;
                }

                //document.getElementById('easypack24').value = 'easypack24%7Ceasypack24%7C'+address[0]+'/mob:'+document.getElementById('easypack24_phone').value+'%7C<?php echo number_format($Total_Shipping_Handling,2)."%7C8";?>';
            }

            function choose_from_dropdown() {
                //document.getElementById('easypack24').value = 'easypack24%7Ceasypack24%7C'+document.getElementById('shipping_easypack24').value+'/mob:'+document.getElementById('easypack24_phone').value+'%7C<?php echo number_format($Total_Shipping_Handling,2)."%7C8";?>';
            }

            jQuery(document).ready(function(){
                jQuery('input[type="checkbox"][name="show_all_machines"]').click(function(){
                    var machines_list_type = jQuery(this).is(':checked');

                    if(machines_list_type == true){
                        //alert('all machines');
                        var machines = {
                            '' : 'Select Machine..',
                            <?php foreach($quotes['methods'][0]['easypack24']['parcelTargetAllMachinesId'] as $key => $parcelTargetAllMachineId): ?>
                                '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetAllMachineId) ?>',
                                <?php endforeach; ?>
                        };
                    }else{
                        //alert('criteria machines');
                        var machines = {
                            '' : 'Select Machine..',
                            <?php foreach($quotes['methods'][0]['easypack24']['parcelTargetMachinesId'] as $key => $parcelTargetMachineId): ?>
                                '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetMachineId) ?>',
                                <?php endforeach; ?>
                        };
                    }

                    jQuery('#shipping_easypack24 option').remove();
                    jQuery.each(machines, function(val, text) {
                        jQuery('#shipping_easypack24').append(
                                jQuery('<option></option>').val(val).html(text)
                        );
                    });
                });

                jQuery("#easypack24_detail").hide();
                if(jQuery('#easypack24').is(':checked')) {
                    jQuery("#easypack24_detail").show();
                }

                jQuery('tr[class="moduleRow"],tr[class="moduleRowSelected"]').click(function(){
                    if(jQuery('#easypack24').is(':checked')) {
                        jQuery("#easypack24_detail").show();
                    }else{
                        jQuery("#easypack24_detail").hide();
                    }
                });

            });

        </script>
        <?php
        $radio_buttons++;
    }

    function validate(){
        if(isset($_POST['shipping_easypack24']['parcel_target_machine_id']) && $_POST['shipping_easypack24']['parcel_target_machine_id'] == ''){
            $this->quotes['error'] = MODULE_SHIPPING_EASYPACK24_TARGET_MACHINE_ERROR;
            return false;
        }

        if(isset($_POST['shipping_easypack24']['receiver_phone']) && !preg_match('/^[1-9]{1}\d{8}$/', $_POST['shipping_easypack24']['receiver_phone'])){
            $this->quotes['error'] = MODULE_SHIPPING_EASYPACK24_MOBILE_ERROR;
            return false;
        }

        return true;
    }

    function create_parcel($shipping, $payment, $order_total, $order_id) {
        require_once DIR_WS_FUNCTIONS.'easypack24_functions.php';

        $order_id = @$order_id;
        $parcel_id = null;
        $parcel_status = 'Created';
        $parcel_detail = array(
            //'cod_amount' => Mage::getStoreConfig('carriers/easypack24/cod_amount'),
            'description' => 'Order number:'.$order_id,
            //'insurance_amount' => Mage::getStoreConfig('carriers/easypack24/insurance_amount'),
            'receiver' => array(
                'email' => $shipping['easypack24']['user_email'],
                'phone' => $shipping['easypack24']['receiver_phone'],
            ),
            'size' => $shipping['easypack24']['parcel_size'],
            //'source_machine' => $data['parcel_source_machine'],
            'tmp_id' => easypack24_generate(4, 15),
        );
        $parcel_target_machine_id = $shipping['easypack24']['parcelTargetMachineId'];
        $parcel_target_machine_detail = $shipping['easypack24']['parcelTargetMachineDetail'];

        // create Inpost parcel
        $params = array(
            'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'parcels',
            'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
            'methodType' => 'POST',
            'params' => array(
                //'cod_amount' => '',
                'description' => @$parcel_detail['description'],
                //'insurance_amount' => '',
                'receiver' => array(
                    'phone' => str_replace('mob:', '', @$parcel_detail['receiver']['phone']),
                    'email' => @$parcel_detail['receiver']['email']
                ),
                'size' => @$parcel_detail['size'],
                //'source_machine' => '',
                'tmp_id' => @$parcel_detail['tmp_id'],
                'target_machine' => $parcel_target_machine_id
            )
        );

        $parcelApi = easypack24_connect($params);

        if(@$parcelApi['info']['redirect_url'] != ''){

            // get machines
            $parcelApi = easypack24_connect(
                array(
                    'url' => $parcelApi['info']['redirect_url'],
                    'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
                    'ds' => '&',
                    'methodType' => 'GET',
                    'params' => array(
                    )
                )
            );

            if(!isset($parcelApi['result']->id)){
                return false;
            }

            $parcel_id = $parcelApi['result']->id;

            $fields = array(
                'order_id' => $order_id,
                'parcel_id' => $parcel_id,
                'parcel_status' => $parcel_status,
                'parcel_detail' => json_encode($parcel_detail),
                'parcel_target_machine_id' => $parcel_target_machine_id,
                'parcel_target_machine_detail' => json_encode($parcel_target_machine_detail),
            );

            tep_db_query("insert into " . TABLE_ORDER_SHIPPING_EASYPACK24 . " (
                  order_id,
                  parcel_id,
                  parcel_status,
                  parcel_detail,
                  parcel_target_machine_id,
                  parcel_target_machine_detail
                ) values (
                  '".$fields['order_id']."',
                  '".$fields['parcel_id']."',
                  '".$fields['parcel_status']."',
                  '".$fields['parcel_detail']."',
                  '".$fields['parcel_target_machine_id']."',
                  '".$fields['parcel_target_machine_detail']."'
                )"
            );

            $street_address = $shipping['easypack24']['parcelTargetMachineDetail']['address']['street'].' '.$shipping['easypack24']['parcelTargetMachineDetail']['address']['building_number'];
            if(@$shipping['easypack24']['parcelTargetMachineDetail']['address']['flat_number'] != ''){
                $street_address .= '/'.$shipping['easypack24']['parcelTargetMachineDetail']['address']['flat_number'];
            }


            tep_db_query("update " . TABLE_ORDERS . " set
                delivery_street_address = '" . $street_address . "',
                delivery_city = '" . $parcel_target_machine_detail['address']['city'] . "',
                delivery_postcode = '" . $parcel_target_machine_detail['address']['post_code'] . "',
                delivery_state = '" . $parcel_target_machine_detail['address']['province'] . "'
                where orders_id = '" . (int)$order_id . "'"
            );

            //unset($_SESSION['easypack24']);
        }else{
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message='.MODULE_SHIPPING_EASYPACK24_CANNOT_CREATE_PARCEL));;
        }
    }

    function prepare_shipping($quote, $shipping, $free_shipping) {
        $easypack24 = explode('_', $shipping['id']);

        $shipping = array(
            'id' => $shipping['id'],
            'title' => $quote[0]['module'],
            'cost' => $quote[0]['methods'][0]['cost'],
            'easypack24' => array(
                'defaultSelect' => $quote[0]['methods'][0]['easypack24']['defaultSelect'],
                'user_email' => $quote[0]['methods'][0]['customer']['email'],
                'parcel_size' => $quote[0]['methods'][0]['easypack24']['parcel_size']
            )
        );

        if(isset($_POST['shipping_easypack24'])){
            $shipping['easypack24']['parcelTargetMachineId'] = $_POST['shipping_easypack24']['parcel_target_machine_id'];
            $shipping['easypack24']['parcelTargetMachineDetail'] = $quote[0]['methods'][0]['easypack24']['parcelTargetAllMachinesDetail'][$_POST['shipping_easypack24']['parcel_target_machine_id']];
            $shipping['easypack24']['receiver_phone'] = $_POST['shipping_easypack24']['receiver_phone'];
            $shipping['title'] = $shipping['title'].' / '.$_POST['shipping_easypack24']['parcel_target_machine_id'];
        }

        return $shipping;
    }

}
?>

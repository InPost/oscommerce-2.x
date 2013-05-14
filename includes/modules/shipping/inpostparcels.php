<?php
/*

Paczkomaty InPost osCommerce Module
Revision 2.0.0

Copyright (c) 2012 InPost Sp. z o.o.

*/
class inpostparcels {

	var $code, $title, $description, $enabled;

	function inpostparcels() {
		global $order, $total_weight, $address;

		$this->code = 'inpostparcels';
		$this->title = INPOSTPARCELS_NAME;
        $this->subtitle = INPOSTPARCELS_SUBTITLE;
		$this->description = INPOSTPARCELS_DESCRIPTION;
		$this->sort_order = MODULE_SHIPPING_INPOSTPARCELS_SORT_ORDER;
		$this->icon = '';//'http://media.paczkomaty.pl/pieczatka.gif';
		$this->tax_class = MODULE_SHIPPING_INPOSTPARCELS_TAX_CLASS;
		$this->enabled = ((MODULE_SHIPPING_INPOSTPARCELS_STATUS == 'True') ? true : false);
        $this->inpostparcels = array();
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
		global $order, $total_weight, $shipping_weight, $shipping_num_boxes, $customer_id, $sendto, $inpostparcels;
        require_once DIR_WS_FUNCTIONS.'inpostparcels_functions.php';


		$customer = $this->get_customer();

		$dest_country = $order->delivery['country']['iso_code_2'];
		if($dest_country == 'GB'){$dest_country = 'UK';}

        $errors = false;

        $countries_table = explode(',', constant('MODULE_SHIPPING_INPOSTPARCELS_ALLOWED_COUNTRY'));

		if (in_array($dest_country, $countries_table)) {
			$prices_table = explode(',', trim(constant('MODULE_SHIPPING_INPOSTPARCELS_PRICE')));
			$key = array_search($dest_country, $countries_table);
			if (array_key_exists($key, $prices_table)) {
				$shipping_cost = $prices_table[$key] * $shipping_num_boxes;
			} else {
				$shipping_cost = $prices_table[0] * $shipping_num_boxes;
			}
		} else {
            $errors[] = INPOSTPARCELS_MSG_ERROR_ALLOWED_COUNTRY;
		}

        if($this->validate() == false || isset($this->quotes['error'] )){
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message='.$this->quotes['error']));
        }

		if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

        // check weight
		if ($total_weight > constant('MODULE_SHIPPING_INPOSTPARCELS_MAX_WEIGHT')) {
            $errors[] = INPOSTPARCELS_MAX_WEIGHT_IS.' '.constant('MODULE_SHIPPING_INPOSTPARCELS_MAX_WEIGHT').'. ('.INPOSTPARCELS_ACTUAL.' '.$total_weight.') ';
            //tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message='.MODULE_SHIPPING_INPOSTPARCELS_UNDEFINED_RATE));
		}

        // check dimensions
        // oscommerce doesn't support products dimmension
        // check dimensions ( multiple product )
        $calculateDimension = inpostparcels_calculateDimensions(array(),
            array(
                'MAX_DIMENSION_A' => constant('MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_A'),
                'MAX_DIMENSION_B' => constant('MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_B'),
                'MAX_DIMENSION_C' => constant('MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_C')
            )
        );

        if(!$calculateDimension['isDimension']){
            return false;
        }

        $this->inpostparcels['parcel_size'] = $calculateDimension['parcelSize'];

        // get machines
        $machine_params = array();
        switch(inpostparcels_getCurrentApi()){
            case 'PL':
                $machine_params['payment_available'] = true;
                break;
            case 'UK':
                break;
        }

        $allMachines = inpostparcels_connect(
            array(
                'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'machines',
                'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                'methodType' => 'GET',
                'params' => $machine_params
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

                $this->inpostparcels['parcelTargetAllMachinesId'] = $parcelTargetAllMachinesId;
                $this->inpostparcels['parcelTargetAllMachinesDetail'] = $parcelTargetAllMachinesDetail;
            }
        }

        $parcelTargetMachinesId = array();
        $parcelTargetMachinesDetail = array();
        $this->inpostparcels['defaultSelect'] = INPOSTPARCELS_SELECT_MACHINE;
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
            $this->inpostparcels['parcelTargetMachinesId'] = $parcelTargetMachinesId;
        }else{
            $this->inpostparcels['defaultSelect'] = INPOSTPARCELS_DEFAULT_SELECT;
        }

        $this->quotes = array(
            'id' => $this->code,
            'module' => $this->title,
            'methods' => array(
                array(
                    'id' => $this->code,
                    'title' => $this->subtitle,
                    'cost' => $shipping_cost,
                    'inpostparcels' => $this->inpostparcels,
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
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_INPOSTPARCELS_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {

		tep_db_query("create table if not exists order_shipping_inpostparcels (
          id int(11) unsigned NOT NULL auto_increment,
	      order_id int(11) NOT NULL,
	      parcel_id varchar(200) NOT NULL default '',
	      parcel_status varchar(200) NOT NULL default '',
	      parcel_detail text NOT NULL default '',
	      parcel_target_machine_id varchar(200) NOT NULL default '',
	      parcel_target_machine_detail text NOT NULL default '',
          sticker_creation_date TIMESTAMP NULL DEFAULT NULL,
          creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      api_source varchar(3) NOT NULL default '',
	      variables text NOT NULL default '',
	      PRIMARY KEY (id));"
        );

        $default_countries = 'UK';
        $default_lang = 'en';
        $lang_name = 'english';

        $languages_query = tep_db_query("select configuration_value from configuration where configuration_key='DEFAULT_LANGUAGE'");
        $def_lan = tep_db_fetch_array($languages_query);

        if(isset($def_lan['configuration_value'])){
            $default_lang = $def_lan['configuration_value'];
        }

        switch($default_lang){
            case 'en':
                $default_countries = 'UK';
                $lang_name = 'english';
                break;
            case 'pl':
                $default_countries = 'PL';
                $lang_name = 'polish';
                break;
        }

        if(file_exists(DIR_FS_ADMIN.DIR_WS_LANGUAGES.$lang_name.DIRECTORY_SEPARATOR.FILENAME_INPOSTPARCELS)){
            require(DIR_FS_ADMIN.DIR_WS_LANGUAGES.$lang_name.DIRECTORY_SEPARATOR.FILENAME_INPOSTPARCELS);
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('".INPOSTPARCELS_CONFIG_INFO_ENABLE_MODULE."', 'MODULE_SHIPPING_INPOSTPARCELS_STATUS', 'True', '".INPOSTPARCELS_CONFIG_INFO_ENABLE_MODULE_CONFIRM."', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_API_URL."', 'MODULE_SHIPPING_INPOSTPARCELS_API_URL', '".INPOSTPARCELS_CONFIG_DEFAULT_API_URL."', '".INPOSTPARCELS_CONFIG_INFO_API_URL."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_API_KEY."', 'MODULE_SHIPPING_INPOSTPARCELS_API_KEY', '', '".INPOSTPARCELS_CONFIG_INFO_API_KEY."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_PRICE."', 'MODULE_SHIPPING_INPOSTPARCELS_PRICE', '".INPOSTPARCELS_CONFIG_DEFAULT_PRICE."', '".INPOSTPARCELS_CONFIG_INFO_PRICE."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('".INPOSTPARCELS_CONFIG_TAX_CLASS."', 'MODULE_SHIPPING_INPOSTPARCELS_TAX_CLASS', '0', '".INPOSTPARCELS_CONFIG_INFO_TAX_CLASS."', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_MAX_WEIGHT."', 'MODULE_SHIPPING_INPOSTPARCELS_MAX_WEIGHT', '".INPOSTPARCELS_CONFIG_DEFAULT_MAX_WEIGHT."', '".INPOSTPARCELS_CONFIG_INFO_MAX_WEIGHT."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_MAX_DIMENSION_A."', 'MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_A', '".INPOSTPARCELS_CONFIG_DEFAULT_MAX_DIMENSION_A."', '".INPOSTPARCELS_CONFIG_INFO_MAX_DIMENSION_A."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_MAX_DIMENSION_B."', 'MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_B', '".INPOSTPARCELS_CONFIG_DEFAULT_MAX_DIMENSION_B."', '".INPOSTPARCELS_CONFIG_INFO_MAX_DIMENSION_B."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_MAX_DIMENSION_C."', 'MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_C', '".INPOSTPARCELS_CONFIG_DEFAULT_MAX_DIMENSION_C."', '".INPOSTPARCELS_CONFIG_INFO_MAX_DIMENSION_C."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_ALLOWED_COUNTRY."', 'MODULE_SHIPPING_INPOSTPARCELS_ALLOWED_COUNTRY', '" . $default_countries . "', '".INPOSTPARCELS_CONFIG_INFO_ALLOWED_COUNTRY."', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".INPOSTPARCELS_CONFIG_SHOP_CITIES."', 'MODULE_SHIPPING_INPOSTPARCELS_SHOP_CITIES', '', '".INPOSTPARCELS_CONFIG_INFO_SHOP_CITIES."', '6', '0', now())");
    }

	function remove() {
		
		tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		$keys = array(
			'MODULE_SHIPPING_INPOSTPARCELS_STATUS',
			'MODULE_SHIPPING_INPOSTPARCELS_API_URL',
            'MODULE_SHIPPING_INPOSTPARCELS_API_KEY',
            'MODULE_SHIPPING_INPOSTPARCELS_PRICE',
            'MODULE_SHIPPING_INPOSTPARCELS_TAX_CLASS',
            'MODULE_SHIPPING_INPOSTPARCELS_MAX_WEIGHT',
            'MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_A',
            'MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_B',
            'MODULE_SHIPPING_INPOSTPARCELS_MAX_DIMENSION_C',
            'MODULE_SHIPPING_INPOSTPARCELS_ALLOWED_COUNTRY',
            'MODULE_SHIPPING_INPOSTPARCELS_SHOP_CITIES'
		);
		return $keys;
	}

    function generate_form($quotes) {
        global $shipping, $currencies, $radio_buttons, $n, $n2;

        require_once DIR_WS_FUNCTIONS.'inpostparcels_functions.php';

        $checked = preg_match('/inpostparcels/', $shipping['id']);
        ?>
        <script type="text/javascript" src="<?php echo inpostparcels_getGeowidgetUrl(); ?>"></script>
        <?php

        if ( ($checked) || ($n == 1 && $n2 == 1) ) {
            echo '<tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, '.$radio_buttons.')">';
        } else {
            echo '<tr id="inpostparcelsRow" class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, '.$radio_buttons.')">';
        }

        ?>
            <td width="75%" style="padding-left: 15px;"><?php echo $quotes['methods'][0]['title'] ?></td>
            <td><?php echo $currencies->format(tep_add_tax($quotes['methods'][0]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0))) ?></td>
            <td align="right"><?php echo tep_draw_radio_field('shipping', $quotes['id'] . '_' . $quotes['methods'][0]['id'], $checked, 'id="inpostparcels"') ?></td>
        </tr>
        <tr id="inpostparcels_detail">
            <td>
                <br>&nbsp; &nbsp; &nbsp; &nbsp;<select id="shipping_inpostparcels" onChange="choose_from_dropdown()" name="shipping_inpostparcels[parcel_target_machine_id]">
                    <option value='' <?php if(@$_POST['shipping_inpostparcels']['parcel_target_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo @$quotes['methods'][0]['inpostparcels']['defaultSelect'];?></option>
                    <?php foreach(@$quotes['methods'][0]['inpostparcels']['parcelTargetMachinesId'] as $key => $parcelTargetMachineId): ?>
                    <option value='<?php echo $key ?>' <?php if(@$_POST['shipping_inpostparcels']['parcel_target_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo @$parcelTargetMachineId;?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="name" name="name" disabled="disabled" />
                <input type="hidden" id="box_machine_town" name="box_machine_town" disabled="disabled" />
                <input type="hidden" id="address" name="address" disabled="disabled" />
                <br>&nbsp; &nbsp; &nbsp; &nbsp;
                <a href="#" onclick="openMap(); return false;"><?php echo INPOSTPARCELS_MAP ?></a>&nbsp|&nbsp<input type="checkbox" name="show_all_machines"> <?php echo INPOSTPARCELS_SHOW_TERMINALS ?>
                <br>
                <br>&nbsp; &nbsp; &nbsp; &nbsp;<b><?php echo INPOSTPARCELS_MOB_EXAMPLE ?>: </b>
                <br>&nbsp; &nbsp; &nbsp; &nbsp;(07) <input type='text' onChange="choose_from_dropdown()" name='shipping_inpostparcels[receiver_phone]' title="mobile /^[1-9]{1}\d{8}$/" id="inpostparcels_phone" title="<?php echo INPOSTPARCELS_MAP ?>" value='<?php echo @$_POST['shipping_inpostparcels']['receiver_phone']?@$_POST['shipping_inpostparcels']['receiver_phone']:@$quotes['methods'][0]['customer']['phone']; ?>' />
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
                document.getElementById('shipping_inpostparcels').value = box_machine_name;
                var shipping_inpostparcels = document.getElementById('shipping_inpostparcels');

                for(i=0;i<shipping_inpostparcels.length;i++){
                    if(shipping_inpostparcels.options[i].value == document.getElementById('name').value){
                        shipping_inpostparcels.selectedIndex = i;
                        is_value = 1;
                    }
                }

                if (is_value == 0){
                    shipping_inpostparcels.options[shipping_inpostparcels.options.length] = new Option(box_machine_name+','+box_machine_town+','+box_machine_street, box_machine_name);
                    shipping_inpostparcels.selectedIndex = shipping_inpostparcels.length-1;
                }

                //document.getElementById('inpostparcels').value = 'inpostparcels%7Cinpostparcels%7C'+address[0]+'/mob:'+document.getElementById('inpostparcels_phone').value+'%7C<?php echo number_format($Total_Shipping_Handling,2)."%7C8";?>';
            }

            function choose_from_dropdown() {
                //document.getElementById('inpostparcels').value = 'inpostparcels%7Cinpostparcels%7C'+document.getElementById('shipping_inpostparcels').value+'/mob:'+document.getElementById('inpostparcels_phone').value+'%7C<?php echo number_format($Total_Shipping_Handling,2)."%7C8";?>';
            }

            jQuery(document).ready(function(){
                jQuery('input[type="checkbox"][name="show_all_machines"]').click(function(){
                    var machines_list_type = jQuery(this).is(':checked');

                    if(machines_list_type == true){
                        //alert('all machines');
                        var machines = {
                            '' : '<?php echo INPOSTPARCELS_SELECT_MACHINE ?>',
                            <?php foreach($quotes['methods'][0]['inpostparcels']['parcelTargetAllMachinesId'] as $key => $parcelTargetAllMachineId): ?>
                                '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetAllMachineId) ?>',
                                <?php endforeach; ?>
                        };
                    }else{
                        //alert('criteria machines');
                        var machines = {
                            '' : '<?php echo INPOSTPARCELS_SHOW_TERMINAL ?>',
                            <?php foreach($quotes['methods'][0]['inpostparcels']['parcelTargetMachinesId'] as $key => $parcelTargetMachineId): ?>
                                '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetMachineId) ?>',
                                <?php endforeach; ?>
                        };
                    }

                    jQuery('#shipping_inpostparcels option').remove();
                    jQuery.each(machines, function(val, text) {
                        jQuery('#shipping_inpostparcels').append(
                                jQuery('<option></option>').val(val).html(text)
                        );
                    });
                });

                jQuery("#inpostparcels_detail").hide();
                if(jQuery('#inpostparcels').is(':checked')) {
                    jQuery("#inpostparcels_detail").show();
                }

                jQuery('tr[class="moduleRow"],tr[class="moduleRowSelected"]').click(function(){
                    if(jQuery('#inpostparcels').is(':checked')) {
                        jQuery("#inpostparcels_detail").show();
                    }else{
                        jQuery("#inpostparcels_detail").hide();
                    }
                });

            });

        </script>
        <?php
        $radio_buttons++;
    }

    function validate(){
        if(isset($_POST['shipping_inpostparcels']['parcel_target_machine_id']) && $_POST['shipping_inpostparcels']['parcel_target_machine_id'] == ''){
            $this->quotes['error'] = INPOSTPARCELS_VALID_SELECT;
            return false;
        }

        if(isset($_POST['shipping_inpostparcels']['receiver_phone']) && !preg_match('/^[1-9]{1}\d{8}$/', $_POST['shipping_inpostparcels']['receiver_phone'])){
            $this->quotes['error'] = INPOSTPARCELS_VALID_MOBILE;
            return false;
        }

        return true;
    }

    function create_parcel($shipping, $payment, $order_total, $order_id) {
        require_once DIR_WS_FUNCTIONS.'inpostparcels_functions.php';

        $parcel_detail = array(
            'description' => 'Order number:'.$order_id,
            'receiver' => array(
                'email' => $shipping['inpostparcels']['user_email'],
                'phone' => $shipping['inpostparcels']['receiver_phone'],
            ),
            'size' => $shipping['inpostparcels']['parcel_size'],
            'tmp_id' => inpostparcels_generate(4, 15),
            'target_machine' => $shipping['inpostparcels']['parcelTargetMachineId']
        );

        switch (inpostparcels_getCurrentApi()){
            case 'PL':
                $parcel_detail['cod_amount'] = ($payment == 'cod')? sprintf("%.2f" ,$order_total) : '';
                break;
        }
        $parcel_target_machine_id = $shipping['inpostparcels']['parcelTargetMachineId'];
        $parcel_target_machine_detail = $shipping['inpostparcels']['parcelTargetMachineDetail'];

        $fields = array(
            'order_id' => $order_id,
            'parcel_detail' => json_encode($parcel_detail),
            'parcel_target_machine_id' => $parcel_target_machine_id,
            'parcel_target_machine_detail' => json_encode($parcel_target_machine_detail),
        );

        tep_db_query("insert into " . TABLE_ORDER_SHIPPING_INPOSTPARCELS . " (
             order_id,
             parcel_detail,
             parcel_target_machine_id,
             parcel_target_machine_detail,
             api_source
            ) values (
             '".$fields['order_id']."',
             '".$fields['parcel_detail']."',
             '".$fields['parcel_target_machine_id']."',
             '".$fields['parcel_target_machine_detail']."',
             '".inpostparcels_getCurrentApi()."'
            )"
        );

        $street_address = $shipping['inpostparcels']['parcelTargetMachineDetail']['address']['street'].' '.$shipping['inpostparcels']['parcelTargetMachineDetail']['address']['building_number'];
        if(@$shipping['inpostparcels']['parcelTargetMachineDetail']['address']['flat_number'] != ''){
            $street_address .= '/'.$shipping['inpostparcels']['parcelTargetMachineDetail']['address']['flat_number'];
        }

        tep_db_query("update " . TABLE_ORDERS . " set
            delivery_street_address = '" . $street_address . "',
            delivery_city = '" . $parcel_target_machine_detail['address']['city'] . "',
            delivery_postcode = '" . $parcel_target_machine_detail['address']['post_code'] . "',
            delivery_state = '" . $parcel_target_machine_detail['address']['province'] . "'
            where orders_id = '" . (int)$order_id . "'"
        );
    }

    function prepare_shipping($quote, $shipping, $free_shipping) {
        $inpostparcels = explode('_', $shipping['id']);

        $shipping = array(
            'id' => $shipping['id'],
            'title' => $quote[0]['module'],
            'cost' => $quote[0]['methods'][0]['cost'],
            'inpostparcels' => array(
                'defaultSelect' => $quote[0]['methods'][0]['inpostparcels']['defaultSelect'],
                'user_email' => $quote[0]['methods'][0]['customer']['email'],
                'parcel_size' => $quote[0]['methods'][0]['inpostparcels']['parcel_size']
            )
        );

        if(isset($_POST['shipping_inpostparcels'])){
            $shipping['inpostparcels']['parcelTargetMachineId'] = $_POST['shipping_inpostparcels']['parcel_target_machine_id'];
            $shipping['inpostparcels']['parcelTargetMachineDetail'] = $quote[0]['methods'][0]['inpostparcels']['parcelTargetAllMachinesDetail'][$_POST['shipping_inpostparcels']['parcel_target_machine_id']];
            $shipping['inpostparcels']['receiver_phone'] = $_POST['shipping_inpostparcels']['receiver_phone'];
            $shipping['title'] = $shipping['title'].' / '.$_POST['shipping_inpostparcels']['parcel_target_machine_id'];
        }

        return $shipping;
    }

}
?>

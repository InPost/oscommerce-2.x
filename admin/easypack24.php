<?php
/*

Paczkomaty InPost osCommerce Module
Revision 2.0.0

Copyright (c) 2012 InPost Sp. z o.o.

*/

require('includes/application_top.php');
require('../'.DIR_WS_FUNCTIONS.'easypack24_functions.php');
require(DIR_WS_CLASSES.'easypack24Model.php');

$action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
$error = (isset($HTTP_GET_VARS['error']) ? $HTTP_GET_VARS['error'] : '');
$message = (isset($HTTP_GET_VARS['message']) ? $HTTP_GET_VARS['message'] : '');
$pID = (isset($HTTP_GET_VARS['pID']) ? $HTTP_GET_VARS['pID'] : '');
$keyword = (isset($HTTP_GET_VARS['keyword']) ? $HTTP_GET_VARS['keyword'] : '');
$status = (isset($HTTP_GET_VARS['status']) ? $HTTP_GET_VARS['status'] : '');

if (tep_not_null($error)) {
	$messageStack->add( $error, 'error' );
}
if (tep_not_null($message)) {
	$messageStack->add( $message, 'success' );
}

$easypack24Model = new Easypack24Model($messageStack);

if (tep_not_null($action)) {
	switch ($action) {
    	case 'sticker':
    		$response = $easypack24Model->sticker($pID);

			break;
		case 'refresh_status':
	        $pID = tep_db_prepare_input($HTTP_GET_VARS['pID']);
	        $easypack24Model->refresh_status($pID);
	        tep_redirect(tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('id', 'action'))));
	        break;
		case 'cancel':
            $pId = tep_db_prepare_input($HTTP_GET_VARS['pID']);
            if($HTTP_POST_VARS['cancel_parcel'] == "on"){
                $easypack24Model->cancel($pID);
            }
            tep_redirect(tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('id', 'action'))));
			break;
        case 'update':
            $pId = tep_db_prepare_input($HTTP_GET_VARS['pID']);
            if($HTTP_POST_VARS['update_parcel'] == "on"){
                $easypack24Model->update($pID);
            }
            break;
	}
}
		
require(DIR_WS_INCLUDES . 'template_top.php');

if($action != 'update'){
  // list form
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td width="100%">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
					<td class="pageHeading" align="right"><img src="images/pixel_trans.gif" border="0" alt="" width="1" height="40"></td>
                    <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tr><?php echo tep_draw_form('orders', FILENAME_EASYPACK24, '', 'get'); ?>
                            <td class="smallText" align="right">Search <?php echo tep_draw_input_field('keyword', '', 'size="12"'); ?></td>
                            <?php echo tep_hide_session_id(); ?></form></tr>
                        <tr><?php echo tep_draw_form('status', FILENAME_EASYPACK24, '', 'get'); ?>
                            <td class="smallText" align="right">Status<?php echo tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => 'All parcels')), easypack24_getParcelStatus()), '', 'onchange="this.form.submit();"'); ?></td>
                            <?php echo tep_hide_session_id(); ?></form></tr>
                    </table></td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
            		<td valign="top">
            			<table border="0" width="100%" cellspacing="0" cellpadding="2">
              				<tr class="dataTableHeadingRow">
                				<td class="dataTableHeadingContent">ID</td>
                				<td class="dataTableHeadingContent">Order ID</td>
                				<td class="dataTableHeadingContent">Parcel ID</td>
                				<td class="dataTableHeadingContent">Status</td>
                				<td class="dataTableHeadingContent" align="right">Machine ID</td>
                                <td class="dataTableHeadingContent" align="right">Sticker creation date</td>
                                <td class="dataTableHeadingContent" align="right">Creation date</td>
                				<td class="dataTableHeadingContent" align="right">Action&nbsp;</td>
              				</tr>
<?php


$parcels_query_raw = "select * from ".TABLE_ORDER_SHIPPING_EASYPACK24." WHERE (id > 0) AND ";

if (!empty($keyword)) {
    $parcels_query_raw .= "(parcel_id LIKE '%$keyword%' ";
    $parcels_query_raw .= "OR parcel_target_machine_id LIKE '%$keyword%' ";
    $parcels_query_raw .= "OR parcel_detail LIKE '%$keyword%' ";
    $parcels_query_raw .= "OR parcel_target_machine_detail LIKE '%$keyword%' ";
    $parcels_query_raw .= ") AND ";
}
if (!empty($status)) {
    $parcels_query_raw .= "parcel_status = '$status' AND ";
}
$parcels_query_raw .= "order_id > 0 ";
$parcels_query_raw .= "ORDER BY order_id DESC ";

$parcels_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $parcels_query_raw, $parcels_query_numrows);
$parcels_query = tep_db_query($parcels_query_raw);


while ($parcels = tep_db_fetch_array($parcels_query)) {

    if ((!isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $parcels['id']))) && !isset($pInfo)) {
        $pInfo = new objectInfo($parcels);
    }

    if (isset($pInfo) && is_object($pInfo) && ($parcels['id'] == $pInfo->id)) {
        echo '			<tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
    } else {
        echo '			<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $parcels['id']) . '\'">' . "\n";
    }
    ?>
                                <td class="dataTableContent"><?php echo $parcels['id'] ?></td>
                                <td class="dataTableContent"><?php echo $parcels['order_id'] ?></td>
                                <td class="dataTableContent"><?php echo $parcels['parcel_id'] ?></td>
                                <td class="dataTableContent"><?php echo $parcels['parcel_status']; ?></td>
                                <td class="dataTableContent" align="right"><?php echo $parcels['parcel_target_machine_id'] ?></td>
                                <td class="dataTableContent" align="right"><?php echo $parcels['sticker_creation_date'] ?></td>
                                <td class="dataTableContent" align="right"><?php echo $parcels['creation_date'] ?></td>
                                <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('id')) . 'id=' . $parcels['id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; ?>&nbsp;</td>
                            </tr>
<?php  } ?>
							<tr>
								<td colspan="4">
									<table border="0" width="100%" cellspacing="0" cellpadding="2">
                  						<tr>
											<td class="smallText" valign="top"><?php echo $parcels_split->display_count($parcels_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_PACKS); ?></td>
											<td class="smallText" align="right"><?php echo $parcels_split->display_links($parcels_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'id', 'action'))); ?></td>
										</tr>
									</table>
								</td>
              				</tr>
            			</table>
            		</td>
            		
<?php
	$heading = array();
	$contents = array();

	switch ($action) {

        case 'cancel_confirm':
            $heading[] = array('text' => '<strong>['.TEXT_ORDER_NUMBER.$pInfo->order_id.' - '.$pInfo->parcel_id.']&nbsp;&nbsp;</strong>');
		    $contents = array('form' => tep_draw_form('packs', FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=cancel'));
		    $contents[] = array('text' => TEXT_INFO_DELETE_PACK . '<br />');
		    if ($pInfo->parcel_status == 'Created')
		    	$contents[] = array('text' => '<br />' . tep_draw_checkbox_field('cancel_parcel') . ' ' . TEXT_INFO_CANCEL_PACK);
		    else
		      	$contents[] = array('text' => '<br />' . TEXT_INFO_CANCEL_PACK_UNAVAILABLE);
		    $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id)));
            break;

        default:
            if (isset($pInfo) && is_object($pInfo)) {
                $heading[] = array('text' => '<strong>['.TEXT_ORDER_NUMBER.$pInfo->order_id.' - '.$pInfo->parcel_id.']&nbsp;&nbsp;</strong>');

                $button_update = tep_draw_button('Edit', 'document', tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=update'));
                $button_sticker = tep_draw_button(($pInfo->sticker_creation_date)? IMAGE_DOWNLOAD_STICKER : IMAGE_GENERATE_STICKER, 'document', tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=sticker'));
                $button_refresh_status = tep_draw_button('Parcel refresh status', 'document', tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=refresh_status'));
                $button_cancel = tep_draw_button('Parcel cancel 	', 'document', tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=cancel_confirm'));

                $contents[] = array('align' => 'center', 'text' => $button_update . $button_sticker);
                $contents[] = array('align' => 'center', 'text' => $button_refresh_status . $button_cancel);
                $contents[] = array('text' => '<br />'.TEXT_DATE_PACK_CREATED.': '.tep_date_short($pInfo->creation_date));
                $contents[] = array('text' => TEXT_PACK_STATUS.': '.$pInfo->parcel_status);
            }
            break;
	}
	
	if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    	echo '            <td width="25%" valign="top">' . "\n";
	
    	$box = new box;
		echo $box->infoBox($heading, $contents);
		echo '            </td>' . "\n";
	}
?>
          		</tr>
        	</table>
		</td>
    </tr>
</table>
    
<?php
}else{
    // edit form

    if (!empty($pID)) {
        $parcels_query_raw = "select * from ".TABLE_ORDER_SHIPPING_EASYPACK24." WHERE id = '$pID' ";
        $parcels_query = tep_db_query($parcels_query_raw);
        $parcel = tep_db_fetch_array($parcels_query);
    }

    if (isset($parcel['id']) || $pID == 0) {

        $parcelTargetMachineDetailDb = json_decode($parcel['parcel_target_machine_detail']);
        $parcelDetailDb = json_decode($parcel['parcel_detail']);

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
                $parcelTargetAllMachinesId[$machine->id] = $machine->id.', '.@$machine->address->city.', '.@$machine->address->street;
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
                if($machine->address->post_code == @$parcelTargetMachineDetailDb->address->post_code){
                    $machines[$key] = $machine;
                    continue;
                }elseif($machine->address->city == @$parcelTargetMachineDetailDb->address->city){
                    $machines[$key] = $machine;
                }
            }
        }

        $parcelTargetMachinesId = array();
        $parcelTargetMachinesDetail = array();
        $defaultSelect = 'Select Machine..';
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
        }else{
            $defaultMachine = 'no terminals in your city';
        }

        $easypack24Data = array(
            'id' => $parcel['id'],
            'parcel_target_machine_id' => $parcel['parcel_target_machine_id'],
            'parcel_description' => @$parcelDetailDb->description,
            'parcel_size' => @$parcelDetailDb->size,
            'parcel_status' => $parcel['parcel_status'],
            'parcel_id' => $parcel['parcel_id']
        );

        $defaultParcelSize = @$parcelDetailDb->size;

        $disabledMachines = 'disabled';
        if($parcel['parcel_status'] != 'Created' || $parcel['parcel_status'] == ''){
            $disabledParcelSize = 'disabled';
        }
    } else {
        //$vmLogger->err('Item does not exist');
    }

    ?>
    <input type="hidden" name="parcel_id" value="<?php echo $easypack24Data['parcel_id']; ?>" />
    <input type="hidden" name="id" value="<?php echo $easypack24Data['id']; ?>" />


    <script type="text/javascript" src="https://geowidget.inpost.co.uk/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function"></script>
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
        }
    </script>

    <?php echo tep_draw_form('parcels', FILENAME_EASYPACK24, tep_get_all_get_params(array('action')) . 'action=update', 'post', '') ?>
    <input type="hidden" name="update_parcel" value="on" />
    <input type="hidden" name="parcel_id" value="<?php echo $easypack24Data['parcel_id']; ?>" />
    <input type="hidden" name="id" value="<?php echo $easypack24Data['id']; ?>" />

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                        <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>

        <tr>
            <td class="formArea">
                <table border="0" cellspacing="2" cellpadding="2">
                    <tr>
                        <td>
                            <select id="shipping_easypack24" name="parcel_target_machine_id" <?php echo $disabledMachines; ?>>
                                <option value='' <?php if(@$easypack24Data['parcel_target_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo $defaultMachine;?></option>
                                <?php foreach($parcelTargetMachinesId as $key => $parcelTargetMachine): ?>
                                <option value='<?php echo $key ?>' <?php if($easypack24Data['parcel_target_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo $parcelTargetMachine;?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($disabledMachines != 'disabled'): ?>
                            <input type="hidden" id="name" name="name" disabled="disabled" />
                            <input type="hidden" id="box_machine_town" name="box_machine_town" disabled="disabled" />
                            <input type="hidden" id="address" name="address" disabled="disabled" />
                            <a href="#" onclick="openMap(); return false;">Map</a>
                            &nbsp|&nbsp<input type="checkbox" name="show_all_machines"> Show terminals in other cities
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><textarea name="parcel_description" rows="10" cols="35"><?php echo $easypack24Data['parcel_description']; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>
                            <select id="parcel_size" name="parcel_size" <?php echo $disabledParcelSize; ?>>
                                <option value='' <?php if($easypack24Data['parcel_size'] == ''){ echo "selected=selected";} ?>><?php echo $defaultParcelSize;?></option>
                                <option value='A' <?php if($easypack24Data['parcel_size'] == 'A'){ echo "selected=selected";} ?>>A</option>
                                <option value='B' <?php if($easypack24Data['parcel_size'] == 'B'){ echo "selected=selected";} ?>>B</option>
                                <option value='C' <?php if($easypack24Data['parcel_size'] == 'C'){ echo "selected=selected";} ?>>C</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><input class="input-text required-entry" name="parcel_status" value="<?php echo $easypack24Data['parcel_status']; ?>" <?php ?>/></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
            <td align="right" class="smallText"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_EASYPACK24, tep_get_all_get_params(array('action')))); ?></td>
        </tr>
    </table>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('input[type="checkbox"][name="show_all_machines"]').click(function(){
                var machines_list_type = jQuery(this).is(':checked');

                if(machines_list_type == true){
                    //alert('all machines');
                    var machines = {
                        '' : 'Select Machine..',
                        <?php foreach($parcelTargetAllMachinesId as $key => $parcelTargetAllMachineId): ?>
                            '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetAllMachineId) ?>',
                            <?php endforeach; ?>
                    };
                }else{
                    //alert('criteria machines');
                    var machines = {
                        '' : 'Select Machine..',
                        <?php foreach($parcelTargetMachinesId as $key => $parcelTargetMachineId): ?>
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
        });
    </script>
   <?php
}

require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>
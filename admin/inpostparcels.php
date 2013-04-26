<?php
/*

Paczkomaty InPost osCommerce Module
Revision 2.0.0

Copyright (c) 2012 InPost Sp. z o.o.

*/

require('includes/application_top.php');
require('../'.DIR_WS_FUNCTIONS.'inpostparcels_functions.php');
require(DIR_WS_CLASSES.'inpostparcelsModel.php');

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

$inpostparcelsModel = new InpostparcelsModel($messageStack);

if (tep_not_null($action)) {
	switch ($action) {
    	case 'sticker':
    		$response = $inpostparcelsModel->sticker($pID);
			break;
		case 'refresh_status':
	        $pID = tep_db_prepare_input($HTTP_GET_VARS['pID']);
	        $inpostparcelsModel->refresh_status($pID);
	        tep_redirect(tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('id', 'action'))));
	        break;
		case 'cancel':
            $pId = tep_db_prepare_input($HTTP_GET_VARS['pID']);
            if($HTTP_POST_VARS['cancel_parcel'] == "on"){
                $inpostparcelsModel->cancel($pID);
            }
            tep_redirect(tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('id', 'action'))));
			break;
        case 'update':
            $pId = tep_db_prepare_input($HTTP_GET_VARS['pID']);
            if($HTTP_POST_VARS['update_parcel'] == "on"){
                if($inpostparcelsModel->update($pID)){
                    tep_redirect(tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('id', 'action'))));
                }else{
                    tep_redirect(tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pID . '&action=update&parcel=create'));
                }
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
					<td class="pageHeading"><?php echo INPOSTPARCELS_VIEW_PARCEL_LIST; ?></td>
					<td class="pageHeading" align="right"><img src="images/pixel_trans.gif" border="0" alt="" width="1" height="40"></td>
                    <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tr><?php echo tep_draw_form('orders', FILENAME_INPOSTPARCELS, '', 'get'); ?>
                            <td class="smallText" align="right">Search <?php echo tep_draw_input_field('keyword', '', 'size="12"'); ?></td>
                            <?php echo tep_hide_session_id(); ?></form></tr>
                        <tr><?php echo tep_draw_form('status', FILENAME_INPOSTPARCELS, '', 'get'); ?>
                            <td class="smallText" align="right"><?php echo INPOSTPARCELS_VIEW_STATUS ?><?php echo tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => INPOSTPARCELS_VIEW_ALL_PARCELS)), inpostparcels_getParcelStatus()), '', 'onchange="this.form.submit();"'); ?></td>
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
                				<td class="dataTableHeadingContent"><?php echo INPOSTPARCELS_VIEW_ORDER_ID ?></td>
                				<td class="dataTableHeadingContent"><?php echo INPOSTPARCELS_VIEW_PARCEL_ID ?></td>
                				<td class="dataTableHeadingContent"><?php echo INPOSTPARCELS_VIEW_STATUS ?></td>
                				<td class="dataTableHeadingContent" align="right"><?php echo INPOSTPARCELS_VIEW_MACHINE_ID ?></td>
                                <td class="dataTableHeadingContent" align="right"><?php echo INPOSTPARCELS_VIEW_STICKER_CREATION_DATE ?></td>
                                <td class="dataTableHeadingContent" align="right"><?php echo INPOSTPARCELS_VIEW_CREATION_DATE ?></td>
                				<td class="dataTableHeadingContent" align="right"><?php echo INPOSTPARCELS_VIEW_CREATION_ACTION ?>&nbsp;</td>
              				</tr>
<?php


$parcels_query_raw = "select * from ".TABLE_ORDER_SHIPPING_INPOSTPARCELS." WHERE (id > 0) AND ";

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
        echo '			<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $parcels['id']) . '\'">' . "\n";
    }
    ?>
                                <td class="dataTableContent"><?php echo $parcels['id'] ?></td>
                                <td class="dataTableContent"><?php echo $parcels['order_id'] ?></td>
                                <td class="dataTableContent"><?php echo $parcels['parcel_id'] ?></td>
                                <td class="dataTableContent"><?php echo $parcels['parcel_status']; ?></td>
                                <td class="dataTableContent" align="right"><?php echo $parcels['parcel_target_machine_id'] ?></td>
                                <td class="dataTableContent" align="right"><?php echo $parcels['sticker_creation_date'] ?></td>
                                <td class="dataTableContent" align="right"><?php echo $parcels['creation_date'] ?></td>
                                <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('id')) . 'id=' . $parcels['id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; ?>&nbsp;</td>
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
            $heading[] = array('text' => '<strong>['.INPOSTPARCELS_VIEW_TEXT_ORDER_NUMBER.$pInfo->order_id.' - '.$pInfo->parcel_id.']&nbsp;&nbsp;</strong>');
		    $contents = array('form' => tep_draw_form('packs', FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=cancel'));
		    $contents[] = array('text' => INPOSTPARCELS_VIEW_TEXT_INFO_DELETE_PACK . '<br />');
		    if ($pInfo->parcel_status == 'Created')
		    	$contents[] = array('text' => '<br />' . tep_draw_checkbox_field('cancel_parcel') . ' ' . INPOSTPARCELS_TEXT_INFO_CANCEL_PACK);
		    else
		      	$contents[] = array('text' => '<br />' . INPOSTPARCELS_VIEW_TEXT_INFO_CANCEL_PACK_UNAVAILABLE);
		    $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id)));
            break;

        default:
            if (isset($pInfo) && is_object($pInfo) && $pInfo->parcel_id != '') {
                $heading[] = array('text' => '<strong>['.INPOSTPARCELS_VIEW_TEXT_ORDER_NUMBER.$pInfo->order_id.' - '.$pInfo->parcel_id.']&nbsp;&nbsp;</strong>');

                $button_update = tep_draw_button(INPOSTPARCELS_VIEW_BUTTON_5, 'document', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=update'));
                $button_sticker = tep_draw_button(($pInfo->sticker_creation_date)? INPOSTPARCELS_VIEW_BUTTON_1 : INPOSTPARCELS_VIEW_BUTTON_1, 'document', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=sticker'));
                $button_refresh_status = tep_draw_button(INPOSTPARCELS_VIEW_BUTTON_2, 'document', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=refresh_status'));
                $button_cancel = tep_draw_button(INPOSTPARCELS_VIEW_BUTTON_3, 'document', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=cancel_confirm'));

                $contents[] = array('align' => 'center', 'text' => $button_update . $button_sticker);
                $contents[] = array('align' => 'center', 'text' => $button_refresh_status . $button_cancel);
            }else{
                $heading[] = array('text' => '<strong>['.INPOSTPARCELS_VIEW_TEXT_ORDER_NUMBER.$pInfo->order_id.']&nbsp;&nbsp;</strong>');

                $button_create = tep_draw_button(INPOSTPARCELS_VIEW_BUTTON_6, 'document', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('pID', 'action')) . 'pID=' . $pInfo->id . '&action=update&parcel=create'));

                $contents[] = array('align' => 'center', 'text' => $button_create);
            }
            $contents[] = array('text' => '<br />'.INPOSTPARCELS_VIEW_TEXT_DATE_PACK_CREATED.': '.tep_date_short($pInfo->creation_date));
            $contents[] = array('text' => INPOSTPARCELS_VIEW_TEXT_PACK_STATUS.': '.$pInfo->parcel_status);

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
        $parcels_query_raw = "select * from ".TABLE_ORDER_SHIPPING_INPOSTPARCELS." WHERE id = '$pID' ";
        $parcels_query = tep_db_query($parcels_query_raw);
        $parcel = tep_db_fetch_array($parcels_query);
    }

    if (isset($parcel['id']) || $pID == 0) {

        $parcelTargetMachineDetailDb = json_decode($parcel['parcel_target_machine_detail']);
        $parcelDetailDb = json_decode($parcel['parcel_detail']);

        // set disabled
        $disabledCodAmount = '';
        $disabledDescription = '';
        $disabledInsuranceAmount = '';
        $disabledReceiverPhone = '';
        $disabledReceiverEmail = '';
        $disabledParcelSize = '';
        $disabledParcelStatus = '';
        $disabledSourceMachine = '';
        $disabledTmpId = '';
        $disabledTargetMachine = '';

        if($parcel['parcel_status'] != 'Created' && $parcel['parcel_status'] != ''){
            $disabledCodAmount = 'disabled';
            $disabledDescription = 'disabled';
            $disabledInsuranceAmount = 'disabled';
            $disabledReceiverPhone = 'disabled';
            $disabledReceiverEmail = 'disabled';
            $disabledParcelSize = 'disabled';
            $disabledParcelStatus = 'disabled';
            $disabledSourceMachine = 'disabled';
            $disabledTmpId = 'disabled';
            $disabledTargetMachine = 'disabled';
        }
        if($parcel['parcel_status'] == 'Created'){
            $disabledCodAmount = 'disabled';
            //$disabledDescription = 'disabled';
            $disabledInsuranceAmount = 'disabled';
            $disabledReceiverPhone = 'disabled';
            $disabledReceiverEmail = 'disabled';
            //$disabledParcelSize = 'disabled';
            //$disabledParcelStatus = 'disabled';
            $disabledSourceMachine = 'disabled';
            $disabledTmpId = 'disabled';
            $disabledTargetMachine = 'disabled';
        }

        $allMachines = inpostparcels_connect(
            array(
                'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'machines',
                'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                'methodType' => 'GET',
                'params' => array(
                )
            )
        );

        // target machines
        $parcelTargetAllMachinesId = array();
        $parcelTargetAllMachinesDetail = array();
        $machines = array();
        if(is_array(@$allMachines['result']) && !empty($allMachines['result'])){
            foreach($allMachines['result'] as $key => $machine){
                if(in_array($parcel['api_source'], array('PL'))){
                    if($machine->payment_available == false){
                        continue;
                    }
                }

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
        $defaultTargetMachine = INPOSTPARCELS_VIEW_SELECT_MACHINE;
        if(is_array(@$machines) && !empty($machines)){
            foreach($machines as $key => $machine){
                $parcelTargetMachinesId[$machine->id] = $machine->id.', '.@$machine->address->city.', '.@$machine->address->street;
                $parcelTargetMachinesDetail[$machine->id] = $parcelTargetAllMachinesDetail[$machine->id];
            }
        }else{
            $defaultTargetMachine = INPOSTPARCELS_VIEW_DEFAULT_SELECT;
        }

        //$parcel['api_source'] = 'PL';
        $parcelInsurancesAmount = array();
        $defaultInsuranceAmount = INPOSTPARCELS_VIEW_SELECT_INSURANCE;
        switch($parcel['api_source']){
            case 'PL':
                $api = inpostparcels_connect(
                    array(
                        'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'customer/pricelist',
                        'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                        'methodType' => 'GET',
                        'params' => array(
                        )
                    )
                );

                if(isset($api['result']) && !empty($api['result'])){
                    $parcelInsurancesAmount = array(
                        ''.$api['result']->insurance_price1.'' => $api['result']->insurance_price1,
                        ''.$api['result']->insurance_price2.'' => $api['result']->insurance_price2,
                        ''.$api['result']->insurance_price3.'' => $api['result']->insurance_price3
                    );
                }

                $_SESSION['inpostparcels']['parcelInsurancesAmount'] = $parcelInsurancesAmount;
                $parcelSourceAllMachinesId = array();
                $parcelSourceAllMachinesDetail = array();
                $machines = array();
                $shopCities = explode(',',constant('MODULE_SHIPPING_INPOSTPARCELS_SHOP_CITIES'));

                if(is_array(@$allMachines['result']) && !empty($allMachines['result'])){
                    foreach($allMachines['result'] as $key => $machine){
                        $parcelSourceAllMachinesId[$machine->id] = $machine->id.', '.@$machine->address->city.', '.@$machine->address->street;
                        $parcelSourceAllMachinesDetail[$machine->id] = array(
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
                        if(in_array($machine->address->city, $shopCities)){
                            $machines[$key] = $machine;
                        }
                    }
                }

                $parcelSourceMachinesId = array();
                $parcelSourceMachinesDetail = array();
                $defaultSourceMachine = INPOSTPARCELS_VIEW_SELECT_MACHINE;
                if(is_array(@$machines) && !empty($machines)){
                    foreach($machines as $key => $machine){
                        $parcelSourceMachinesId[$machine->id] = $machine->id.', '.@$machine->address->city.', '.@$machine->address->street;
                        $parcelSourceMachinesDetail[$machine->id] = $parcelSourceAllMachinesDetail[$machine->id];
                    }
                }else{
                    $defaultSourceMachine = INPOSTPARCELS_VIEW_DEFAULT_SELECT;
                    if(@$parcelDetailDb->source_machine != ''){
                        $parcelSourceMachinesId[$parcelDetailDb->source_machine] = @$parcelSourceAllMachinesId[$parcelDetailDb->source_machine];
                        $parcelSourceMachinesDetail[$parcelDetailDb->source_machine] = @$parcelSourceMachinesDetail[$parcelDetailDb->source_machine];
                    }
                }

                break;
        }

        $inpostparcelsData = array(
            'id' => $parcel['id'],
            'parcel_id' => $parcel['parcel_id'],

            'parcel_cod_amount' => @$parcelDetailDb->cod_amount,
            'parcel_description' => @$parcelDetailDb->description,
            'parcel_insurance_amount' => @$parcelDetailDb->insurance_amount,
            'parcel_receiver_phone' => @$parcelDetailDb->receiver->phone,
            'parcel_receiver_email' => @$parcelDetailDb->receiver->email,
            'parcel_size' => @$parcelDetailDb->size,
            'parcel_status' => $parcel['parcel_status'],
            'parcel_source_machine_id' => @$parcelDetailDb->source_machine,
            'parcel_tmp_id' => @$parcelDetailDb->tmp_id,
            'parcel_target_machine_id' => @$parcelDetailDb->target_machine,
        );

        $defaultParcelSize = @$parcelDetailDb->size;

        } else {
        //$vmLogger->err('Item does not exist');
    }

    ?>
    <input type="hidden" name="parcel_id" value="<?php echo $inpostparcelsData['parcel_id']; ?>" />
    <input type="hidden" name="id" value="<?php echo $inpostparcelsData['id']; ?>" />


    <script type="text/javascript" src="<?php echo inpostparcels_getGeowidgetUrl(); ?>"></script>
    <script type="text/javascript">
        function user_function(value) {

            var address = value.split(';');
            var openIndex = address[4];
            var sufix = '';

            if(openIndex == 'source_machine') {
                sufix = '_source';
            }

            //document.getElementById('town').value=address[1];
            //document.getElementById('street').value=address[2]+address[3];
            var box_machine_name = document.getElementById('name').value;
            var box_machine_town = document.value=address[1];
            var box_machine_street = document.value=address[2];


            var is_value = 0;
            document.getElementById('shipping_inpostparcels'+sufix).value = box_machine_name;
            var shipping_inpostparcels = document.getElementById('shipping_inpostparcels'+sufix);

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
        }
    </script>

    <?php echo tep_draw_form('parcels', FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('action')) . 'action=update', 'post', '') ?>
    <input type="hidden" name="update_parcel" value="on" />
    <input type="hidden" name="parcel_id" value="<?php echo $inpostparcelsData['parcel_id']; ?>" />
    <input type="hidden" name="id" value="<?php echo $inpostparcelsData['id']; ?>" />

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
                    <?php if(in_array($parcel['api_source'], array('PL'))): ?>
                        <tr>
                            <td align="right"><?php echo INPOSTPARCELS_VIEW_COD_AMOUNT ?>:</td>
                            <td><input class="input-text required-entry" name="parcel_cod_amount" value="<?php echo $inpostparcelsData['parcel_cod_amount']; ?>" <?php echo $disabledCodAmount; ?> <?php ?>/></td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td align="right" valign="top"><?php echo INPOSTPARCELS_VIEW_DESCRIPTION ?>:</td>
                        <td><textarea name="parcel_description" rows="10" cols="35" <?php echo $disabledDescription; ?>><?php echo $inpostparcelsData['parcel_description']; ?></textarea></td>
                    </tr>

                    <?php if(in_array($parcel['api_source'], array('PL'))): ?>
                        <tr>
                            <td align="right"><?php echo INPOSTPARCELS_VIEW_INSURANCE_AMOUNT ?>:</td>
                            <td>
                                <select id="parcel_size" name="parcel_insurance_amount" <?php echo $disabledInsuranceAmount; ?>>
                                    <option value='' <?php if(@$inpostparcelsData['parcel_insurance_amount'] == ''){ echo "selected=selected";} ?>><?php echo $defaultInsuranceAmount; ?></option>
                                    <?php foreach($parcelInsurancesAmount as $key => $parcelInsuranceAmount): ?>
                                    <option value='<?php echo $key ?>' <?php if($inpostparcelsData['parcel_insurance_amount'] == $key){ echo "selected=selected";} ?>><?php echo $parcelInsuranceAmount;?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td align="right"><?php echo INPOSTPARCELS_VIEW_RECEIVER_PHONE ?>:</td>
                        <td><input class="input-text required-entry" name="parcel_receiver_phone" value="<?php echo $inpostparcelsData['parcel_receiver_phone']; ?>" <?php echo $disabledReceiverPhone; ?>/></td>
                    </tr>

                    <tr>
                        <td align="right"><?php echo INPOSTPARCELS_VIEW_RECEIVER_EMAIL ?>:</td>
                        <td><input class="input-text required-entry" name="parcel_receiver_email" value="<?php echo $inpostparcelsData['parcel_receiver_email']; ?>" <?php echo $disabledReceiverEmail; ?>/></td>
                    </tr>

                    <tr>
                        <td align="right"><?php echo INPOSTPARCELS_VIEW_SIZE ?>:</td>
                        <td>
                            <select id="parcel_size" name="parcel_size" <?php echo $disabledParcelSize; ?>>
                                <option value='' <?php if($inpostparcelsData['parcel_size'] == ''){ echo "selected=selected";} ?>><?php echo $defaultParcelSize;?></option>
                                <option value='<?php echo INPOSTPARCELS_VIEW_SIZE_A ?>' <?php if($inpostparcelsData['parcel_size'] == INPOSTPARCELS_VIEW_SIZE_A){ echo "selected=selected";} ?>><?php echo INPOSTPARCELS_VIEW_SIZE_A ?></option>
                                <option value='<?php echo INPOSTPARCELS_VIEW_SIZE_B ?>' <?php if($inpostparcelsData['parcel_size'] == INPOSTPARCELS_VIEW_SIZE_B){ echo "selected=selected";} ?>><?php echo INPOSTPARCELS_VIEW_SIZE_B ?></option>
                                <option value='<?php echo INPOSTPARCELS_VIEW_SIZE_C ?>' <?php if($inpostparcelsData['parcel_size'] == INPOSTPARCELS_VIEW_SIZE_C){ echo "selected=selected";} ?>><?php echo INPOSTPARCELS_VIEW_SIZE_C ?></option>
                            </select>
                        </td>
                    </tr>

                    <?php if($parcel['parcel_status'] != ''): ?>
                    <tr>
                        <td align="right"><?php echo INPOSTPARCELS_VIEW_STATUS ?>:</td>
                        <td><input class="input-text required-entry" name="parcel_status" value="<?php echo $inpostparcelsData['parcel_status']; ?>" <?php echo $disabledParcelStatus; ?>/></td>
                    </tr>
                    <?php endif; ?>

                    <?php if(in_array($parcel['api_source'], array('PL'))): ?>
                        <tr>
                            <td align="right"><?php echo INPOSTPARCELS_VIEW_SOURCE_MACHINE ?>:</td>
                            <td>
                                <select id="shipping_inpostparcels_source" name="parcel_source_machine_id" <?php echo $disabledSourceMachine; ?>>
                                    <option value='' <?php if(@$inpostparcelsData['parcel_source_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo $defaultSourceMachine;?></option>
                                    <?php foreach($parcelSourceMachinesId as $key => $parcelSourceMachine): ?>
                                    <option value='<?php echo $key ?>' <?php if($inpostparcelsData['parcel_source_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo $parcelSourceMachine;?></option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="#" onclick="openMap('source_machine'); return false;"><?php echo INPOSTPARCELS_VIEW_MAP ?></a>
                                &nbsp|&nbsp<input type="checkbox" name="show_all_machines_source" <?php echo $disabledSourceMachine; ?>> <?php echo INPOSTPARCELS_VIEW_SHOW_TERMINAL ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td align="right"><?php echo INPOSTPARCELS_VIEW_TMP_ID ?>:</td>
                        <td><input class="input-text required-entry" name="parcel_tmp_id" value="<?php echo $inpostparcelsData['parcel_tmp_id']; ?>" <?php echo $disabledTmpId; ?>/></td>
                    </tr>

                    <tr>
                        <td align="right"><?php echo INPOSTPARCELS_VIEW_TARGET_MACHINE ?>:</td>
                        <td>
                            <select id="shipping_inpostparcels" name="parcel_target_machine_id" <?php echo $disabledTargetMachine; ?>>
                                <option value='' <?php if(@$inpostparcelsData['parcel_target_machine_id'] == ''){ echo "selected=selected";} ?>><?php echo $defaultTargetMachine;?></option>
                                <?php foreach($parcelTargetMachinesId as $key => $parcelTargetMachine): ?>
                                <option value='<?php echo $key ?>' <?php if($inpostparcelsData['parcel_target_machine_id'] == $key){ echo "selected=selected";} ?>><?php echo $parcelTargetMachine;?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="name" name="name" disabled="disabled" />
                            <input type="hidden" id="box_machine_town" name="box_machine_town" disabled="disabled" />
                            <input type="hidden" id="address" name="address" disabled="disabled" />
                            <a href="#" onclick="openMap('target_machine'); return false;"><?php echo INPOSTPARCELS_VIEW_MAP ?></a>
                            &nbsp|&nbsp<input type="checkbox" name="show_all_machines" <?php echo $disabledTargetMachine; ?>> <?php echo INPOSTPARCELS_VIEW_SHOW_TERMINAL ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
            <td align="right" class="smallText"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_INPOSTPARCELS, tep_get_all_get_params(array('action')))); ?></td>
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
                        '' : '<?php echo INPOSTPARCELS_VIEW_SELECT_MACHINE ?>',
                        <?php foreach($parcelTargetAllMachinesId as $key => $parcelTargetAllMachineId): ?>
                            '<?php echo $key ?>' : '<?php echo addslashes($parcelTargetAllMachineId) ?>',
                            <?php endforeach; ?>
                    };
                }else{
                    //alert('criteria machines');
                    var machines = {
                        '' : '<?php echo INPOSTPARCELS_VIEW_SELECT_MACHINE ?>',
                        <?php foreach($parcelTargetMachinesId as $key => $parcelTargetMachineId): ?>
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

            jQuery('input[type="checkbox"][name="show_all_machines_source"]').click(function(){
                var machines_list_type = jQuery(this).is(':checked');

                if(machines_list_type == true){
                    //alert('all machines');
                    var machines = {
                        '' : '<?php echo INPOSTPARCELS_VIEW_SELECT_MACHINE ?>',
                        <?php foreach($parcelSourceAllMachinesId as $key => $parcelSourceAllMachineId): ?>
                            '<?php echo $key ?>' : '<?php echo addslashes($parcelSourceAllMachineId) ?>',
                            <?php endforeach; ?>
                    };
                }else{
                    //alert('criteria machines');
                    var machines = {
                        '' : '<?php echo INPOSTPARCELS_VIEW_SELECT_MACHINE ?>',
                        <?php foreach($parcelSourceMachinesId as $key => $parcelSourceMachineId): ?>
                            '<?php echo $key ?>' : '<?php echo addslashes($parcelSourceMachineId) ?>',
                            <?php endforeach; ?>
                    };
                }

                jQuery('#shipping_inpostparcels_source option').remove();
                jQuery.each(machines, function(val, text) {
                    jQuery('#shipping_inpostparcels_source').append(
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
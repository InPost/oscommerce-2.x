<?php


class InpostparcelsModel {

    protected $messageStack;

	public function __construct($messageStack = null) {
	    $this->messageStack = $messageStack;
        //$this->messageStack->add_session('test error', 'error');
        //$this->messageStack->add_session('test success', 'success');
    }

    public function sticker($id) {
        $parcelsIds = array($id);
        $countSticker = 0;
        $countNonSticker = 0;
        $pdf = null;
        $parcelsCode = array();
        $parcelsToPay = array();

        foreach ($parcelsIds as $key => $id) {
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_INPOSTPARCELS." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            if($parcel['parcel_id'] != ''){
                $parcelsCode[$id] = $parcel['parcel_id'];
                if($parcel['sticker_creation_date'] == ''){
                    $parcelsToPay[$id] = $parcel['parcel_id'];
                }
            }else{
                continue;
            }
        }

        if(empty($parcelsCode)){
            $this->messageStack->add(INPOSTPARCELS_MSG_PARCEL_7, 'error');
        }else{
            if(!empty($parcelsToPay)){
                $parcelApiPay = inpostparcels_connect(array(
                    'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'parcels/'.implode(';', $parcelsToPay).'/pay',
                    'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                    'methodType' => 'POST',
                    'params' => array(
                    )
                ));

                if(@$parcelApiPay['info']['http_code'] != '204'){
                    $countNonSticker = count($parcelsIds);
                    if(!empty($parcelApiPay['result'])){
                        foreach(@$parcelApiPay['result'] as $key => $error){
                            $this->messageStack->add('Parcel '.$key.' '.$error, 'error');
                        }
                    }
                    return;
                }
            }

            $parcelApi = inpostparcels_connect(array(
                'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'stickers/'.implode(';', $parcelsCode),
                'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                'methodType' => 'GET',
                'params' => array(
                    'format' => 'Pdf',
                    'type' => 'normal'
                )
            ));
        }

        if(@$parcelApi['info']['http_code'] != '200'){
            $countNonSticker = count($parcelsIds);
            if(!empty($parcelApi['result'])){
                foreach(@$parcelApi['result'] as $key => $error){
                    $this->messageStack->add('Parcel '.$key.' '.$error, 'error');
                }
            }
        }else{
            foreach ($parcelsIds as $parcelId) {
                $fields = array(
                    'parcel_status' => 'Prepared',
                    'sticker_creation_date' => date('Y-m-d H:i:s')
                );

                if(isset($parcelsToPay[$parcelId])){
                    tep_db_query("update " . TABLE_ORDER_SHIPPING_INPOSTPARCELS . " set
                        parcel_status = '" . $fields['parcel_status'] . "',
                        sticker_creation_date = '" . $fields['sticker_creation_date'] . "'
                        where id = '" . $parcelId . "'"
                    );
                }
                $countSticker++;
            }
            $pdf = base64_decode(@$parcelApi['result']);
        }

        if ($countNonSticker) {
            if ($countNonSticker) {
                $this->messageStack->add($countNonSticker.INPOSTPARCELS_MSG_STICKER_1, 'error');
            } else {
                $this->messageStack->add(INPOSTPARCELS_MSG_STICKER_2, 'error');
            }
        }
        if ($countSticker) {
            $this->messageStack->add($countSticker.INPOSTPARCELS_MSG_STICKER_3, 'success');
        }

        if(!is_null($pdf)){
            header('Content-type', 'application/pdf');
            header('Content-Disposition: attachment; filename=stickers_'.date('Y-m-d_H-i-s').'.pdf');
            print_r($pdf);
        }
    }

    public function refresh_status($id) {
        $parcelsIds = array($id);
        $countRefreshStatus = 0;
        $countNonRefreshStatus = 0;

        $parcelsCode = array();
        foreach ($parcelsIds as $key => $id) {
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_INPOSTPARCELS." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            if($parcel['parcel_id'] != ''){
                $parcelsCode[$id] = $parcel['parcel_id'];
            }else{
                continue;
            }
        }

        if(empty($parcelsCode)){
            $this->messageStack->add_session(INPOSTPARCELS_MSG_PARCEL_7, 'error');
        }else{
            $parcelApi = inpostparcels_connect(array(
                'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'parcels/'.implode(';', $parcelsCode),
                'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                'methodType' => 'GET',
                'params' => array()
            ));
        }

        if(@$parcelApi['info']['http_code'] != '200'){
            $countNonRefreshStatus = count($parcelsIds);
            if(!empty($parcelApi['result'])){
                foreach(@$parcelApi['result'] as $key => $error){
                    $this->messageStack->add_session('Parcel '.$key.' '.$error, 'error');
                }
            }
        }else{
            if(!is_array(@$parcelApi['result'])){
                @$parcelApi['result'] = array(@$parcelApi['result']);
            }
            foreach (@$parcelApi['result'] as $parcel) {
                $fields = array(
                    'parcel_status' => @$parcel->status
                );

                tep_db_query("update " . TABLE_ORDER_SHIPPING_INPOSTPARCELS . " set
                    parcel_status = '" . $fields['parcel_status'] . "'
                    where parcel_id = '" . @$parcel->id . "'"
                );
                $countRefreshStatus++;
            }
        }

        if ($countNonRefreshStatus) {
            if ($countNonRefreshStatus) {
                $this->messageStack->add_session($countNonRefreshStatus.INPOSTPARCELS_MSG_PARCEL_1, 'error');
            } else {
                $this->messageStack->add_session($countNonRefreshStatus.INPOSTPARCELS_MSG_PARCEL_2, 'error');
            }
        }
        if ($countRefreshStatus) {
            $this->messageStack->add_session($countRefreshStatus.INPOSTPARCELS_MSG_PARCEL_3, 'success');
        }
    }

    public function cancel($id) {
        $parcelsIds = array($id);
        $countCancel = 0;
        $countNonCancel = 0;

        $parcelsCode = array();
        foreach ($parcelsIds as $key => $id) {
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_INPOSTPARCELS." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            if($parcel['parcel_id'] != ''){
                $parcelsCode[$id] = $parcel['parcel_id'];
            }else{
                continue;
            }
        }

        if(empty($parcelsCode)){
            $this->messageStack->add_session(INPOSTPARCELS_MSG_PARCEL_7, 'error');
        }else{
            foreach($parcelsCode as $id => $parcelId){
                $parcelApi = inpostparcels_connect(array(
                    'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'parcels',
                    'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                    'methodType' => 'PUT',
                    'params' => array(
                        'id' => $parcelId,
                        'status' => 'cancelled'
                    )
                ));

                if(@$parcelApi['info']['http_code'] != '204'){
                    $countNonCancel = count($parcelsIds);
                    if(!empty($parcelApi['result'])){
                        foreach(@$parcelApi['result'] as $key => $error){
                            if(is_array($error)){
                                foreach($error as $subKey => $subError){
                                    $this->messageStack->add_session('Parcel '.$parcelId.' '.$subError, 'error');
                                }
                            }else{
                                $this->messageStack->add_session('Parcel '.$parcelId.' '.$error, 'error');
                            }
                        }
                    }
                }else{
                    foreach (@$parcelApi['result'] as $parcel) {
                        $fields = array(
                            'parcel_status' => @$parcel->status
                        );
                        tep_db_query("update " . TABLE_ORDER_SHIPPING_INPOSTPARCELS . " set
                            parcel_status = '" . $fields['parcel_status'] . "'
                            where parcel_id = '" . @$parcel->id . "'"
                        );
                        $countCancel++;
                    }
                }
            }
        }

        if ($countNonCancel) {
            if ($countNonCancel) {
                $this->messageStack->add_session($countNonCancel.INPOSTPARCELS_MSG_PARCEL_4, 'error');
            } else {
                $this->messageStack->add_session(INPOSTPARCELS_MSG_PARCEL_5, 'error');
            }
        }
        if ($countCancel) {
            $this->messageStack->add_session($countNonCancel.INPOSTPARCELS_MSG_PARCEL_6, 'success');
        }
    }

    public function update($id) {
        $parcelsIds = array($id);
        try {
            $postData = $_POST;
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_INPOSTPARCELS." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            $parcelTargetMachineDetailDb = json_decode($parcel['parcel_target_machine_detail']);
            $parcelDetailDb = json_decode($parcel['parcel_detail']);

            if($parcel['parcel_id'] != ''){
                // update Inpost parcel
                $params = array(
                    'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'parcels',
                    'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                    'methodType' => 'PUT',
                    'params' => array(
                        'description' => !isset($postData['parcel_description']) || $postData['parcel_description'] == @$parcelDetailDb->description?null:$postData['parcel_description'],
                        'id' => $postData['parcel_id'],
                        'size' => !isset($postData['parcel_size']) || $postData['parcel_size'] == @$parcelDetailDb->size?null:$postData['parcel_size'],
                        'status' => !isset($postData['parcel_status']) || $postData['parcel_status'] == $parcel['parcel_status']?null:$postData['parcel_status'],
                        //'target_machine' => !isset($postData['parcel_target_machine_id']) || $postData['parcel_target_machine_id'] == $parcel['parcel_target_machine_id']?null:$postData['parcel_target_machine_id']
                    )
                );
            }else{
                // create Inpost parcel e.g.
                $params = array(
                    'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'parcels',
                    'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                    'methodType' => 'POST',
                    'params' => array(
                        'description' => @$postData['parcel_description'],
                        'description2' => 'oscommerce-2.x',
                        'receiver' => array(
                            'phone' => @$postData['parcel_receiver_phone'],
                            'email' => @$postData['parcel_receiver_email']
                        ),
                        'size' => @$postData['parcel_size'],
                        'tmp_id' => @$postData['parcel_tmp_id'],
                        'target_machine' => @$postData['parcel_target_machine_id']
                    )
                );

                switch($parcel['api_source']){
                    case 'PL':
                        $insurance_amount = $_SESSION['inpostparcels']['parcelInsurancesAmount'];
                        $params['params']['cod_amount'] = @$postData['parcel_cod_amount'];
                        if(@$postData['parcel_insurance_amount'] != ''){
                            $params['params']['insurance_amount'] = $insurance_amount[@$postData['parcel_insurance_amount']];
                        }
                        $params['params']['source_machine'] = @$postData['parcel_source_machine_id'];
                        break;
                }
            }

            $parcelApi = inpostparcels_connect($params);

            if(@$parcelApi['info']['http_code'] != '204' && @$parcelApi['info']['http_code'] != '201'){
                if(!empty($parcelApi['result'])){
                    foreach(@$parcelApi['result'] as $key => $error){
                        if(is_array($error)){
                            foreach($error as $subKey => $subError){
                                $this->messageStack->add_session('Parcel '.$key.' '.$postData['parcel_id'].' '.$subError, 'error');
                            }
                        }else{
                            $this->messageStack->add_session('Parcel '.$key.' '.$error, 'error');
                        }
                    }
                }
                return false;
            }else{
                if($parcel['parcel_id'] != ''){
                    $parcelDetail = $parcelDetailDb;
                    $parcelDetail->description = $postData['parcel_description'];
                    $parcelDetail->size = $postData['parcel_size'];
                    $parcelDetail->status = $postData['parcel_status'];

                    $fields = array(
                        'parcel_status' => isset($postData['parcel_status'])?$postData['parcel_status']:$parcel['parcel_status'],
                        'parcel_detail' => json_encode($parcelDetail),
                        'variables' => json_encode(array())
                    );

                    tep_db_query("update " . TABLE_ORDER_SHIPPING_INPOSTPARCELS . " set
                        parcel_status = '" . $fields['parcel_status'] . "',
                        parcel_detail = '" . $fields['parcel_detail'] . "',
                        variables = '" . $fields['variables'] . "'
                        where id = '" . $id . "'"
                    );
                }else{
//                    $parcelApi = inpostparcels_connect(
//                        array(
//                            'url' => $parcelApi['info']['redirect_url'],
//                            'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
//                            'ds' => '&',
//                            'methodType' => 'GET',
//                            'params' => array(
//                            )
//                        )
//                    );

                    $fields = array(
                        'parcel_id' => $parcelApi['result']->id,
                        'parcel_status' => 'Created',
                        'parcel_detail' => json_encode($params['params']),
                        'parcel_target_machine_id' => isset($postData['parcel_target_machine_id'])?$postData['parcel_target_machine_id']:$parcel['parcel_target_machine_id'],
                        'parcel_target_machine_detail' => $parcel['parcel_target_machine_detail'],
                        'variables' => json_encode(array())
                    );

                    if($parcel['parcel_target_machine_id'] != $postData['parcel_target_machine_id']){
                        $parcelApi = inpostparcels_connect(
                            array(
                                'url' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_URL').'machines/'.$postData['parcel_target_machine_id'],
                                'token' => constant('MODULE_SHIPPING_INPOSTPARCELS_API_KEY'),
                                'methodType' => 'GET',
                                'params' => array(
                                )
                            )
                        );

                        $fields['parcel_target_machine_detail'] = json_encode($parcelApi['result']);
                    }

                    tep_db_query("update " . TABLE_ORDER_SHIPPING_INPOSTPARCELS . " set
                        parcel_id = '" . $fields['parcel_id'] . "',
                        parcel_status = '" . $fields['parcel_status'] . "',
                        parcel_detail = '" . $fields['parcel_detail'] . "',
                        parcel_target_machine_id = '" . $fields['parcel_target_machine_id'] . "',
                        parcel_target_machine_detail = '" . $fields['parcel_target_machine_detail'] . "',
                        variables = '" . $fields['variables'] . "'
                        where id = '" . $id . "'"
                    );
                }
            }
            $this->messageStack->add_session(INPOSTPARCELS_MSG_PARCEL_MODIFIED, 'success');
            return true;
        } catch (Exception $e) {
            //$vmLogger->err($e->getMessage());
            return;
        }
    }


    }
<?php


class Easypack24Model {

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

        foreach ($parcelsIds as $key => $id) {
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_EASYPACK24." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            if($parcel['parcel_id'] != ''){
                $parcelsCode[$id] = $parcel['parcel_id'];
            }else{
                continue;
            }
        }

        if(empty($parcelsCode)){
            $this->messageStack->add_session('Parcel ID is empty', 'error');
        }else{

            if($parcel['sticker_creation_date'] == ''){
                $parcelApiPay = easypack24_connect(array(
                    'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'parcels/'.implode(';', $parcelsCode).'/pay',
                    'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
                    'methodType' => 'POST',
                    'params' => array(
                    )
                ));

                if(@$parcelApiPay['info']['http_code'] != '204'){
                    $countNonSticker = count($parcelsIds);
                    if(!empty($parcelApiPay['result'])){
                        foreach(@$parcelApiPay['result'] as $key => $error){
                            $this->messageStack->add_session('Parcel '.$key.' '.$error, 'error');
                        }
                    }
                    return;
                }
            }

            $parcelApi = easypack24_connect(array(
                'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'stickers/'.implode(';', $parcelsCode),
                'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
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
                    $this->messageStack->add_session('Parcel '.$key.' '.$error, 'error');
                }
            }
        }else{
            foreach ($parcelsIds as $parcelId) {
                $fields = array(
                    'parcel_status' => 'Prepared',
                    'sticker_creation_date' => date('Y-m-d H:i:s')
                );

                tep_db_query("update " . TABLE_ORDER_SHIPPING_EASYPACK24 . " set
                    parcel_status = '" . $fields['parcel_status'] . "',
                    sticker_creation_date = '" . $fields['sticker_creation_date'] . "'
                    where id = '" . $parcelId . "'"
                );
                $countSticker++;
            }
            $pdf = base64_decode(@$parcelApi['result']);
        }

        if ($countNonSticker) {
            if ($countNonSticker) {
                $this->messageStack->add_session($countNonSticker.' sticker(s) cannot be generated', 'error');
            } else {
                $this->messageStack->add_session('The sticker(s) cannot be generated', 'error');
            }
        }
        if ($countSticker) {
            $this->messageStack->add_session($countSticker.' sticker(s) have been generated.', 'success');
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
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_EASYPACK24." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            if($parcel['parcel_id'] != ''){
                $parcelsCode[$id] = $parcel['parcel_id'];
            }else{
                continue;
            }
        }

        if(empty($parcelsCode)){
            $this->messageStack->add_session('Parcel ID is empty', 'error');
        }else{
            $parcelApi = easypack24_connect(array(
                'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'parcels/'.implode(';', $parcelsCode),
                'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
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

                tep_db_query("update " . TABLE_ORDER_SHIPPING_EASYPACK24 . " set
                    parcel_status = '" . $fields['parcel_status'] . "'
                    where parcel_id = '" . @$parcel->id . "'"
                );
                $countRefreshStatus++;
            }
        }

        if ($countNonRefreshStatus) {
            if ($countNonRefreshStatus) {
                $this->messageStack->add_session($countNonRefreshStatus.' parcel status cannot be refresh', 'error');
            } else {
                $this->messageStack->add_session($countNonRefreshStatus.' The parcel status cannot be refresh', 'error');
            }
        }
        if ($countRefreshStatus) {
            $this->messageStack->add_session($countRefreshStatus.' parcel status have been refresh.', 'success');
        }
    }

    public function cancel($id) {
        $parcelsIds = array($id);
        $countCancel = 0;
        $countNonCancel = 0;

        $parcelsCode = array();
        foreach ($parcelsIds as $key => $id) {
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_EASYPACK24." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            if($parcel['parcel_id'] != ''){
                $parcelsCode[$id] = $parcel['parcel_id'];
            }else{
                continue;
            }
        }

        if(empty($parcelsCode)){
            $this->messageStack->add_session('Parcel ID is empty', 'error');
        }else{
            foreach($parcelsCode as $id => $parcelId){
                $parcelApi = easypack24_connect(array(
                    'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'parcels',
                    'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
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
                        tep_db_query("update " . TABLE_ORDER_SHIPPING_EASYPACK24 . " set
                            parcel_status = '" . $fields['parcel_status'] . "'
                            where id = '" . @$parcel->id . "'"
                        );
                        $countCancel++;
                    }
                }
            }
        }

        if ($countNonCancel) {
            if ($countNonCancel) {
                $this->messageStack->add_session($countNonCancel.' parcel status cannot be cancel', 'error');
            } else {
                $this->messageStack->add_session('The parcel status cannot be cancel', 'error');
            }
        }
        if ($countCancel) {
            $this->messageStack->add_session($countNonCancel.' parcel status have been cancel.', 'success');
        }
    }

    public function update($id) {
        $parcelsIds = array($id);
        try {
            $postData = $_POST;
            $parcel_query  = tep_db_query("SELECT * FROM ".TABLE_ORDER_SHIPPING_EASYPACK24." WHERE id = '". $id . "'");
            $parcel = tep_db_fetch_array($parcel_query);

            $parcelTargetMachineDetailDb = json_decode($parcel['parcel_target_machine_detail']);
            $parcelDetailDb = json_decode($parcel['parcel_detail']);

            // update Inpost parcel
            $params = array(
                'url' => constant('MODULE_SHIPPING_EASYPACK24_API_URL').'parcels',
                'token' => constant('MODULE_SHIPPING_EASYPACK24_API_KEY'),
                'methodType' => 'PUT',
                'params' => array(
                    'description' => !isset($postData['parcel_description']) || $postData['parcel_description'] == @$parcelDetailDb->description?null:$postData['parcel_description'],
                    'id' => $postData['parcel_id'],
                    'size' => !isset($postData['parcel_size']) || $postData['parcel_size'] == @$parcelDetailDb->size?null:$postData['parcel_size'],
                    'status' => !isset($postData['parcel_status']) || $postData['parcel_status'] == $parcel['parcel_status']?null:$postData['parcel_status'],
                    //'target_machine' => !isset($postData['parcel_target_machine_id']) || $postData['parcel_target_machine_id'] == $parcel['parcel_target_machine_id']?null:$postData['parcel_target_machine_id']
                )
            );
            $parcelApi = easypack24_connect($params);

            if(@$parcelApi['info']['http_code'] != '204'){
                if(!empty($parcelApi['result'])){
                    foreach(@$parcelApi['result'] as $key => $error){
                        if(is_array($error)){
                            foreach($error as $subKey => $subError){
                                $this->messageStack->add_session('Parcel '.$key.' '.$postData['parcel_id'].' '.$subError, 'error');
                            }
                        }else{
                            $this->messageStack->add('Parcel '.$key.' '.$error, 'error');
                        }
                    }
                }
                return;
            }else{
                $fields = array(
                    'parcel_status' => isset($postData['parcel_status'])?$postData['parcel_status']:$parcel['parcel_status'],
                    'parcel_detail' => json_encode(array(
                        'description' => $postData['parcel_description'],
                        'receiver' => array(
                            'email' => $parcelDetailDb->receiver->email,
                            'phone' => $parcelDetailDb->receiver->phone
                        ),
                        'size' => isset($postData['parcel_size'])?$postData['parcel_size']:@$parcelDetailDb->size,
                        'tmp_id' => $parcelDetailDb->tmp_id,
                    ))
                );

                tep_db_query("update " . TABLE_ORDER_SHIPPING_EASYPACK24 . " set
                            parcel_status = '" . $fields['parcel_status'] . "',
                            parcel_detail = '" . $fields['parcel_detail'] . "'
                            where id = '" . $id . "'"
                );
            }
            $this->messageStack->add('Parcel modified', 'success');
            return;
        } catch (Exception $e) {
            //$vmLogger->err($e->getMessage());
            return;
        }
    }

}
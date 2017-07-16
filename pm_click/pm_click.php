<?php
defined('_JEXEC') or die();
class pm_click extends PaymentRoot
{
  public function showPaymentForm($params, $pmconfigs)
  {
    include(dirname(__FILE__) . "/paymentform.php");
  }
  public function loadLanguageFile()
  {
    $lang    = JFactory::getLanguage();
    $langtag = $lang->getTag();
    if (file_exists(JPATH_ROOT . '/components/com_jshopping/payments/pm_click/lang/' . $langtag . '.php')) {
      require_once(JPATH_ROOT . '/components/com_jshopping/payments/pm_click/lang/' . $langtag . '.php');
    } else {
      require_once(JPATH_ROOT . '/components/com_jshopping/payments/pm_click/lang/ru-RU.php'); //если языковый файл не найден, то подключаем en-GB.php
    }
  }
  public function showAdminFormParams($params)
  {
    $array_params = array(
      'click_merchant_id',
	  'click_merchant_user_id',
	  'click_merchant_service_id',
      'click_secret_key',
      'transaction_end_status',
      'transaction_pending_status',
      'transaction_failed_status'
    );
    foreach ($array_params as $key) {
      if (!isset($params[$key])) {
        $params[$key] = '';
      }
    }
    $orders = JModelLegacy::getInstance('orders', 'JshoppingModel');
    $this->loadLanguageFile(); //подключаем нужный язык
    include(dirname(__FILE__) . '/adminparamsform.php');
  }
  public function getUrlParams($pmconfigs)
  {
    $db = JFactory::getDbo();
	$merchant_trans_id=$_POST['merchant_trans_id'];
	$payment_id = $db->setQuery("SELECT payment_method_id FROM #__jshopping_orders WHERE order_id='$merchant_trans_id'")->loadResult();
	  if ($payment_id=="12"){
    $params                      = array();
    $params['order_id']          = $_POST['merchant_trans_id'];
    $params['hash']              = '';
    $params['checkHash']         = 0;
    $params['checkReturnParams'] = 0;
    return $params;
	 }else{
		 $result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order->order_id,
										'merchant_prepare_id'=>$order->order_id,
										'error'=>-5,
										'error_note'=>"User does not exist");
		header('Content-Type: application/json');
		echo json_encode($result);
		die();
	  }
  }
  function showEndForm($pmconfigs, $order)
  {
     $url = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http').'://' . $_SERVER['HTTP_HOST'];
     $trans_amount = number_format((float)$order->order_total, 2, '.', '');
     $date=date("Y-m-d h:i:s");
	 $secret=$pmconfigs['click_secret_key'];
	 $service_id=$pmconfigs['click_merchant_service_id'];
	 $trans_id=$order->order_id;
	 $sign=md5($date.$secret.$service_id.$trans_id.$amount);
     $fields = array(
	  'MERCHANT_ID' 	  		=> "",
      'MERCHANT_USER_ID'  		=> "",
	  'MERCHANT_SERVICE_ID'  	=> "",
      'MERCHANT_TRANS_ID'       => $trans_id,
	  'MERCHANT_TRANS_AMOUNT'   => $trans_amount,
	  'MERCHANT_TRANS_NOTE' 	=> "Оплата заказа ".$trans_id,
	  'SIGN_TIME'			 	=> $date,
      'SIGN_STRING' 	       	=> $sign
    );
   $form = '<form name="click"  action="https://my.click.uz/pay/" method="POST">';
   foreach ($fields as $key=>$value) {
      $form .=  '<input type="hidden" name="'.$key.'" value="'.$value.'">';
    }
    $form .= '</form>
    <script type="text/javascript">
      document.click.submit();
    </script>
    ';
    echo $form;
    die;
  }
 function checkTransaction($pmconfigs, $order, $act)
  {  
	//$responsedata = (array)json_decode(file_get_contents('php://input'));
	$amount_chk = number_format((float)$order->order_total, 2, '.', '');
	$success=0;
	$order=$order->order_id;
	$order_status=$order->order_status;
		if ($_SERVER["REQUEST_METHOD"] == "POST" && $act == 'notify') 
		{	
			if ($_POST['merchant_prepare_id'] != $order && $_POST['action']==1) {
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-6,
										'error_note'=>"Transaction does not exist");
				header('Content-Type: application/json');
				echo json_encode($result);
				die();
			}
			switch ($_POST['action']){
				case '0':	//для prepare
					$sign_string=md5($_POST['click_trans_id'].$_POST['service_id'].$pmconfigs['click_secret_key'].$_POST['merchant_trans_id'].$_POST['amount'].$_POST['action'].$_POST['sign_time']);
					if ($_POST['sign_string'] != $sign_string){
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_prepare_id'=>$order,
										'error'=>-1,
										'error_note'=>"SIGN CHECK FILED!");
					}
					elseif ($_POST['amount'] != $amount_chk) {
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_prepare_id'=>$order,
										'error'=>-2,
										'error_note'=>"Incorrect parametr ammount");
					}
					elseif ($_POST['merchant_trans_id'] != $order){
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_prepare_id'=>$order,
										'error'=>-5,
										'error_note'=>"User does not exist");
					}	
					else{ 
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_prepare_id'=>$order,
										'error'=>0,
										'error_note'=>"Success");
					}
				break;
				case '1':	//для complete
				$sign_string=md5($_POST['click_trans_id'].$_POST['service_id'].$pmconfigs['click_secret_key'].$_POST['merchant_trans_id'].$_POST['merchant_prepare_id'].$_POST['amount'].$_POST['action'].$_POST['sign_time']);
					if ($_POST['sign_string'] != $sign_string){
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-1,
										'error_note'=>"SIGN CHECK FAILED");
					}
					elseif ($_POST['amount'] != $amount_chk) {
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-2,
										'error_note'=>"Incorrect ammount");
					}
					elseif ($order_status == "6" && $_POST['error']!="-5017"){ 
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-4,
										'error_note'=>"Already paid");
					}
					elseif ($order_status == "6" && $_POST['error']=="-5017"){ 
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-9,
										'error_note'=>"Transaction cancelled");
						header('Content-Type: application/json');
						echo json_encode($result);
						return array(3, $order);
						die();
					}
					elseif ($order_status == "3" && $_POST['error']=="-5017"){ 
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-9,
										'error_note'=>"Transaction cancelled");
					}
					elseif ($order_status == "3"){
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-9,
										'error_note'=>"Transaction already cancelled");
					}
					elseif ($order_status == "6" && $_POST['error']=="-1"){
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>-4,
										'error_note'=>"Transaction can not be cancelled");
					}
					else{
						$result = array('click_trans_id' => $_POST['click_trans_id'],
										'merchant_trans_id'=>$order,
										'merchant_confirm_id'=>$order,
										'error'=>0,
										'error_note'=>"Success");
						header('Content-Type: application/json');
						echo json_encode($result);
						return array(1,  $order);
						die();
					}
				break;	
			}
			header('Content-Type: application/json');
			echo json_encode($result);
			die();
		}
	} 
} 
	

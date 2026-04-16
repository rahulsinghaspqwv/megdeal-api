<?
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $date=json_decode(file_get_contents('php://input'), true);
    if (!isset($data['user_id'])||!isset($data['amount'])){
        echo json_encode(['success'=>false, 'message'=>'User ID and amount required']);
        exit;
    }
    $user_id=intval($data['user_id']);
    $amount=floatval($data['amount']);
    $paytm_number=isset($data['paytm_number'])?$data['paytm_number']:'';
    try {
        //Check if user has sufficient balance 
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? AND is_active = TRUE");
        $stmt->execute([$user_id]);
        $user=$stmt->fetch();
        if (!$user){
            echo json_encode(['success'=>false, 'message'=>'User not found or inactive']);
            exit;
        } 
        if ($user['balance']<$amount){
            echo json_encode(['success'=>false,'message'=>'Insufficient balance']);
            exit;
        }
        if ($amount < 10){
            echo json_encode(['success'=>false,'message'=>'Minimum withdrawal amount is ₹10']);
            exit;
        }
        // Generate order ID
        $order_id = 'ORDER_'.time().'_'.$user_id.'_'.rand(1000, 9999);
        //paytm Credentials (Test credentials)
        $mid='YOUR_MERCHANT_ID';// Replace with actual Paytm MID
        $mkey='YOUR_MERCHANT_KEY';// Replace with actual Paytm Merchant Key
        // Create transaction record
        $stmt=$pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, reference_id) VALUES (?, 'withdrawal', ?, ?, 'pending'. ?)");
        //For testing, generate dummy checksum
        $checksum_data=$mid.'|'.$order_id.'|'.$user_id.'|'.$amount.'|'.$mkey;
        $checksum=hash('sha256',$checksum_data);
        echo json_encode(['success'=>true, 'txn_token'=>$checksum, 'order_id'=> $order_id, 'mid'=>$mid, 'amount'=>$amount]);
    } catch(Exception $e){
        Error_log("Paytm checksum error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to generate payment token']);
    }
}
?>

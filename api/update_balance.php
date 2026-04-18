<?
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $data=json_decode(file_get_contents('php://input'), true);
    if (!isset($data['user_id'])||!isset($data['amount'])){
        echo json_encode(['success'=>false,'message'=>'User ID and amount required']);
        exit;    
    }
    $user_id=intval($data['user_id']);
    $amount=floatval($data['amount']);
    $offer_id=isset($data['offer_id'])?$data['offer_id']:null;
    $type=isset($data['type'])?$data['type']:'earning';
    $description=isset($data['description'])?$data['description']:'Offer completion reward';
    try {
        $pdo->beginTransaction();
        //Check if user exists and is active
        $stmt=$pdo->prepare("SELECT id, is_active FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user=$stmt->fetch();
        if (!$user){
            echo json_encode(['success'=>false,'message'=>'User not found']);
            exit;
        }
        if (!$user['is_active']){
            echo json_encode(['success'=>false,'message'=>'Account is deactivated']);
            exit;
        }
        // Update user balance
        $stmt=$pdo->prepare("UPDATE users SET balance=balance+?, total_earned=total_earned+? WHERE id=?");
        $stmt->execute([$amount, $amount, $user_id]);
        if ($offer_id){
            // Find pending transaction and UPDATE it
            $stmt=$pdo->prepare("SELECT id FROM transactions WHERE user_id=? AND offer_id=? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$user_id, $offer_id]);
            $pending=$stmt->fetch();
            if ($pending){
                // Update existing pending transaction
                $stmt=$pdo->prepare("UPDATE transactions SET amount=?, status='completed', description=? WHERE id =?");
                $stmt->execute([$amount, $description, $pending['id']]);
            } else {
                // No pending record found, create new completed transaction 
                $stmt=$pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, offer_id) VALUES (?,?,?,?,'completed',?");
                $stmt->execute([$user_id, $type, $description, $amount, $offer_id]);
            }
        } else {
            // No offer_id, just insert
            $stmt=$pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status) VALUES (?,?,?,?,'completed')");
            $stmt->execute([$user_id, $type, $description, $amount]);
        }
        $pdo->commit();
        // Get update balance
        $new_balance = getCurrentBalance($pdo, $user_id);
        echo json_encode(['success'=>true,'message'=>'Balance updated successfully','amount_added'=>$amount,'new_balance=>$new_balance']);
    } catch (Exception $e){
        $pdo->rollBack();
        error_log("Update balance error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to update balance']);
    }
}
?>

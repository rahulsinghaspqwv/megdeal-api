<?php
require_once 'config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $data=json_decode(file_get_contents('php://input'), true);
    // Validate required fields
    if (!isset($data['user_id'])||!isset($data['offer_id'])){
        echo json_encode(['success'=>false,'message'=>'User ID and Offer ID required']);
        exit;
    }
    $user_id=intval($data['user_id']);
    $offer_id=$data['offer_id'];
    $action = isset($data['action'])?$data['action']:'start';
    try {
        //Check if user exists and is active
        $stmt=$pdo->prepare("SELECT id, is_active FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user=$stmt->fetch();
        if (!$user) {
            echo json_encode(['success'=>false,'message'=>'User not found']);
            exit;
        }
        if (!$user['is_active']){
            echo json_encode(['success'=>false,'message'=> 'Account is deactivated']);
            exit();
        }
        //Check if offer already started/completed by this user today 
        $stmt=$pdo->prepare("SELECT id FROM transactions WHERE user_id=? AND offer_id=? AND DATE(created_at)=CURDATE() AND status='completed'");
        $stmt->execute([$user_id, $offer_id]);
        if ($stmt->fetch()){
            echo json_encode(['success'=>false,'message'=>'Offer already completed today']);
            exit;
        }
        // Log the offer start (optional - can create a separate table)
        // For now, create a pending transaction record 
        $description="Offer started: ".$offer_id;
        $amount = 0.00; // No amount yet, will be updated on completion 
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status, offer_id) VALUES (?, 'earning', ?, ?, 'pending', ?)"); 
        $stmt->execute([$user_id, $description, $amount, $offer_id]);
        $transaction_id=$pdo->lastInsertId();
        echo json_encode(['success'=>true, 'message'=> 'Offer started successfully', 'transaction_id'=>$transaction_id, 'offer_id'=>$offer_id, 'user_id'=>$user_id]);
    } catch(PDOException $e) {
        error_log("Start offer error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to start offer']);
    }
} else {
    echo json_encode(['success'=>false, 'message'=>'Invalid request method']);
}
?>
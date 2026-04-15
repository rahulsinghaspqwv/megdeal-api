<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);
    // Validate input 
    if (!isset($data['name']) || !isset($data['mobile']) || !isset($data['paytm_number']) || !isset($data['password'])){
        echo json_encode(['success'=>false, 'message' => 'Missing required fields']);
        exit;
    }
    $name = trim($data['name']);
    $mobile = trim($data['mobile']);
    $email = isset($data['email']) ? trim($data['email']) : '';
    $paytm_number = trim($data['paytm_number']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $referral_code = generateReferralCode();
    $referred_by = isset($data['referral_code']) ? getUserIdByReferral($pdo, $data['referral_code']) : null;
    
    try{
        // Check if mobile already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->execute([$mobile]);
        if($stmt->fetch()){
            echo json_encode(['success'=>false, 'message'=>'Mobile number already registered']);
            exit;
        }
        //Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name, mobile, email, paytm_number, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $mobile, $email, $paytm_number, $password, $referral_code, $referred_by]);
        $user_id=$pdo->lastInsertId();
        //if referred, give referral bonus
        if ($referred_by){
            $bonus = 50.00;// Referral bonus amount
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE ID = ?");
            $stmt->execute([$bonus, $referred_by]);
            // Record referral transaction
            $stmt->$pdo->prepare("INSERT INTO transactions (user_id, type, description, amount, status) VALUES (?, 'referral', ?, ?, 'completed')");
            $stmt->execute([$referred_by, "Referral bonus for user $mobile", $bonus]);
        }
        echo json_encode(['success' => true,'message'=>'Registration successful', 'user_id'=>$user_id, 'referral_code'=>$referral_code]);
    } catch(PDOException $e) {
        error_log("Registration error: ".$e->getMessage());
        echo json_encode(['success'=>false, 'message'=>'Registration failed']);
    }
}
function getUserIdByReferral($pdo, $referral_code){
    $stmt=$pdo->prepare("SELECT id FROM users WHERE referral_code=?");
    $stmt->execute([$referral_code]);
    $result=$stmt->fetch();
    return $result ? $result['id'] : null;
}
?>
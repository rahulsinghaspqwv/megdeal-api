<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $data=json_decode(file_get_contents('php://input'), true);
    if (!isset($data['mobile']) || !isset($data['password'])){
        echo json_encode(['success'=>false, 'message'=>'Mobile and password required']);
        exit;
    }
    $mobile=trim($data['mobile']);
    $password=$data['password'];
    $device_token=isset($data['device_token']) ? $data['device_token'] : null;
    try {
        $stmt=$pdo->prepare("SELECT id, name, mobile, email, paytm_number, password, balance, total_earned, total_withdrawn, referral_code, is_active FROM users WHERE mobile=?");
        $stmt->execute([$mobile]);
        $user=$stmt->fetch();
        if ($user && password_verify($password, $user['password'])){
            if (!$user['is_active']){
                echo json_encode(['success'=>false, 'message'=>'Account is deactivated']);
                exit;
            }
            // update device token if provided
            if($device_token){
                $stmt=$pdo->prepare("UPDATE users SET device_token =? WHERE id=?");
                $stmt->execute([$device_token, $user['id']]);
            }
            // Remove sensitive data
            unset($user['password']);
            echo json_encode(['success'=>true,'message'=>'Login successful','user'=>$user]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Invalid mobile or password']);
        }
    } catch (PDOException $e){
        error_log("Login error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Login Failed']);
    }
}
?>
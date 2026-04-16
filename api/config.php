<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database Configuration 
$host='localhost';
$dbname='megdeal_earning';
$username='megdeal_earning_user';
$password='Megdeal@1234';

try{
    $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e){
    error_log("Connection failed: ".$e->getMessage());
    echo json_encode(['success'=>false, 'message'=>'Database connection failed','error'=>$e->getMessage()]);
    exit;
}

function generateReferralCode($length=6){
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
}

function getCurrentBalance($pdo, $user_id){
    $stmt=$pdo->prepare("SELECT balance FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $result=$stmt->fetch();
    return $result ? floatval($result['balance']) : 0.0;
}

?>
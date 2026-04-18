<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD']==='GET'){
    if (!isset($_GET['user_id'])){
        echo json_encode(['success'=>false,'message'=>'User ID required']);
        exit;
    }
    $user_id=intval($_GET['user_id']);
    try {
        //Get user balance and stats
        $stmt=$pdo->prepare("SELECT 
        u.balance, 
        u.total_earned, 
        u.total_withdrawn, 
        COALESCE(today.today_earning, 0) as today_earning, 
        COALESCE(week.week_earning, 0) as week_earning, 
        COALESCE(month.month_earning, 0) as month_earning 
        FROM users u 
        LEFT JOIN(
        SELECT user_id, 
        COALESCE(SUM(amount), 0) as today_earning 
        FROM transactions WHERE user_id = ? AND type = 'earning' AND DATE(created_at)=CURDATE() 
        GROUP BY user_id) 
        today ON u.id = today.user_id 
        LEFT JOIN(
        SELECT user_id, COALESCE(SUM(amount), 0) as week_earning
        FROM transactions
        WHERE user_id=? AND type='earning' AND YEARWEEK(created_at)=YEARWEEK(NOW())
        GROUP BY user_id) 
        week ON u.id=week.user_id
        LEFT JOIN(
        SELECT user_id, COALESCE(SUM(amount), 0) as month_earning
        FROM transactions
        WHERE user_id=? AND type='earning' AND MONTH(created_at)=MONTH(NOW())
        GROUP BY user_id)
        month ON u.id=month.user_id
        WHERE u.id=?");
        $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
        $result=$stmt->fetch();
        if ($result){
            echo json_encode([
                'success'=>true,
                'balance'=>floatval($result['balance']),
                'today_earning'=> floatval($result['today_earning']),
                'week_earning'=>floatval($result['week_earning']),
                'month_earning'=>floatval($result['month_earning']),
                'total_earned'=>floatval($result['total_earned']),
                'total_withdrawn'=>floatval($result['total_withdrawn'])
            ]);
        } else {
            echo json_encode(['success'=>false,'message'=>'User not found']);
        }
 
    } catch (PDOException $e) {
        error_log("Get balance error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to fetch balance']);
    }
}
?>
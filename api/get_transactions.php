<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD']==='GET'){
    if (!isset($_GET['user_id'])){
        echo json_encode(['success'=>false,'message'=>'User ID required']);
        exit;
    }
    $user_id=intval($_GET['user_id']);
    $filter=isset($_GET['filter'])?$_GET['filter']:'all';
    $limit=isset($_GET['limit'])?intval($_GET['limit']):50;
    $offset=isset($_GET['offset'])?intval($_GET['offset']):0;
    try {
        $query="SELECT id, type, description, amount, status, reference_id, offer_id, created_at FROM transactions WHERE user_id=?";
        $param=[$user_id];
        if($filter=='all'){
            $query="AND type=?";
            $params[] = $filter;
        }
        $query=" ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[]=$limit;
        $params[]=$offset;
        $stmt=$pdo->prepare($query);
        $stmt->execute($params);
        $transactions=$stmt->fetchAll();
        //Get total count for pagination
        $countQuery="SELECT COUNT(*) as total FROM transactions WHERE user_id=?";
        $countParams=[$user_id];
        if($filter!=='all'){
            $countQuery.="And type=?";
            $countParams[]=$filter;
        }
        $stmt=$pdo->prepare($countQuery);
        $stmt->execute($countParams);
        $total=$stmt->fetch()['total'];
        echo json_encode(['success'=>true,'transactions'=>$transactions,'total'=>$total,'limit'=>$limit,'offset'=>$offset]);
    } catch(PDOException $e){
        error_log("Get transactions error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to fetch transactions']);
    }
}
?>
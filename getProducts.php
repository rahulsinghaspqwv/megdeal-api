
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
    
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = mysqli_connect(
	"sql102.infinityfree.com",	 	// DB Host (from MySQL page)
    "if0_41510971", 				// DB Username
    "M11Mmonsteracc",				// DB Password
    "if0_41510971_shopdb"			// DB name
);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$result = mysqli_query($conn, "SELECT * FROM products");
$data = array();
while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}
echo json_encode($data);
exit();
?>
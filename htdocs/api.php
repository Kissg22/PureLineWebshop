<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$database = "shop";

// Kapcsolódás létrehozása
$conn = new mysqli($servername, $username, $password, $database);

// Ha nem sikerült a kapcsolat
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Rendelések lekérdezése
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['REQUEST_URI'] == '/api.php/orders') {
    $sql = "SELECT id, order_date, order_status, total_price FROM orders";
    $result = $conn->query($sql);

    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode(["success" => true, "data" => $orders], JSON_PRETTY_PRINT);
} 

// Bejelentkezés validálása
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['REQUEST_URI'] == '/api.php/login' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL lekérdezés a user_type és password ellenőrzésére
    $sql = $conn->prepare("SELECT id, first_name, email, user_type, password FROM users WHERE email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {  // Ha a jelszó helyes
            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "Hibás jelszó"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Felhasználó nem található"]);
    }
}

$conn->close();
?>

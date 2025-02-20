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

// Rendelés frissítés vagy törlés
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['REQUEST_URI'] == '/api.php/orders') {
    $order_id = intval($_POST['order_id']);

    if (isset($_POST['order_status'])) {
        $order_status = intval($_POST['order_status']);
        $sql = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $sql->bind_param("ii", $order_status, $order_id);
    } elseif (isset($_POST['delete_order'])) {
        $sql = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $sql->bind_param("i", $order_id);
    }

    if ($sql->execute()) {
        echo json_encode(["success" => true, "message" => "Művelet sikeresen végrehajtva"]);
    } else {
        echo json_encode(["success" => false, "message" => "Hiba: " . $conn->error]);
    }
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
            if ($user['user_type'] == 'admin') {  // Ha admin
                echo json_encode(["success" => true, "user" => $user]);
            } else {
                echo json_encode(["success" => false, "message" => "Nem admin típusú felhasználó"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Hibás jelszó"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Felhasználó nem található"]);
    }
}

$conn->close();
?>

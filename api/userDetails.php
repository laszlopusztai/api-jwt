<?php
include_once '../services/databaseService.php';
require "../vendor/autoload.php";
require '_partials/header.php';

$jwtConfig = require_once('../config/jwt.php');

use Firebase\JWT\Key;
use \Firebase\JWT\JWT;

$secret_key = $jwtConfig["secret_key"];;
$jwt = null;
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];

$data = json_decode(file_get_contents("php://input"));

$userId = $data->user_id;

$arr = explode(" ", $authHeader);

$jwt = $arr[1];

if ($jwt) {

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        $query = "SELECT id, first_name, last_name, email FROM users where id = :userId";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row > 0) {
            echo json_encode([
                "message" => "Access granted",
                "user" => [$row],
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "message" => "User not found",
            ]);
        }

    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode([
            "message" => "Access denied.",
            "error" => $e->getMessage(),
        ]);
    }
}
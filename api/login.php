<?php
include_once '../services/databaseService.php';
require "../vendor/autoload.php";
require '_partials/header.php';

$jwtConfig = require_once('../config/jwt.php');

use \Firebase\JWT\JWT;

$email = '';
$password = '';

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$query = "SELECT id, first_name, last_name, password FROM users WHERE email = ? LIMIT 0,1";

$stmt = $conn->prepare($query);

$stmt->bindParam(1, $email);
$stmt->execute();
$num = $stmt->rowCount();

if ($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $firstname = $row['first_name'];
    $lastname = $row['last_name'];
    $password2 = $row['password'];

    if (password_verify($password, $password2)) {
        $secret_key = $jwtConfig["secret_key"];
        $issuer_claim = "THE_ISSUER"; // this can be the servername
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 10; //not before in seconds
        $expire_claim = $issuedat_claim + 1800; // expire time in seconds
        $token = [
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => [
                "id" => $id,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "email" => $email,
            ]];

        http_response_code(200);

        $jwt = JWT::encode($token, $secret_key, "HS256");
        echo json_encode(
            [
                "message" => "Successful login.",
                "jwt" => $jwt,
                "email" => $email,
                "expireAt" => $expire_claim,
            ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Login failed.", "password" => $password]);
    }
}

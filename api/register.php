<?php
include_once '../services/databaseService.php';
require "../vendor/autoload.php";
require '_partials/header.php';

$firstName = '';
$lastName = '';
$email = '';
$password = '';
$conn = null;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$firstName = $data->first_name;
$lastName = $data->last_name;
$email = $data->email;
$password = $data->password;

$query = "INSERT INTO users
                SET first_name = :firstname,
                    last_name = :lastname,
                    email = :email,
                    password = :password";

$stmt = $conn->prepare($query);

$stmt->bindParam(':firstname', $firstName);
$stmt->bindParam(':lastname', $lastName);
$stmt->bindParam(':email', $email);

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt->bindParam(':password', $password_hash);

if ($stmt->execute()) {

    http_response_code(200);
    echo json_encode(["message" => "User was successfully registered."]);
} else {
    http_response_code(400);

    echo json_encode(["message" => "Unable to register the user."]);
}

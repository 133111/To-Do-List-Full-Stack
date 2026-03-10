<?php
header('Content-Type: application/json; charset=utf-8');

$output = [
    'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT FOUND',
    'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NOT FOUND',
    'HTTP_X_CUSTOM_AUTH' => $_SERVER['HTTP_X_CUSTOM_AUTH'] ?? 'NOT FOUND',
    'all_headers' => getallheaders(),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'php_input' => file_get_contents('php://input'),
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>

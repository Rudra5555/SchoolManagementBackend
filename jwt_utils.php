<?php
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generate_jwt($headers, $payload, $secret = 'my_secret_key') {
    $headers_encoded = base64url_encode(json_encode($headers));
    $payload_encoded = base64url_encode(json_encode($payload));
    $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);
    return "$headers_encoded.$payload_encoded.$signature_encoded";
}

function is_jwt_valid($jwt, $secret = 'my_secret_key') {
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) return false;

    [$headers_encoded, $payload_encoded, $signature_provided] = $tokenParts;

    $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);

    if ($signature_encoded !== $signature_provided) return false;

    $payload = json_decode(base64url_decode($payload_encoded), true);
    return isset($payload['exp']) && $payload['exp'] >= time();
}
?>

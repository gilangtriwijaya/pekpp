<?php
function read_env($path){
    $out = [];
    $lines = @file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    if(!$lines) return $out;
    foreach($lines as $l){
        $l = trim($l);
        if($l === '' || $l[0] === '#') continue;
        if(strpos($l, '=') === false) continue;
        list($k,$v) = explode('=', $l, 2);
        $out[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
    return $out;
}
$env = read_env(__DIR__.'/.env');
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $env['DB_HOST'] ?? '127.0.0.1', $env['DB_DATABASE'] ?? '');
$pdo = new PDO($dsn, $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
function q($pdo, $sql, $params=[]){
    echo "-- $sql\n";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $r) echo json_encode($r)."\n";
}
q($pdo, "SELECT id, name, email, sso_user_id FROM users WHERE email LIKE :v OR name LIKE :v", [':v'=>'%setda%']);
q($pdo, "SELECT id, name, email, sso_user_id FROM users WHERE email LIKE :v OR name LIKE :v", [':v'=>'%superadmin%']);
q($pdo, "SELECT id, code, name FROM apps WHERE code = :c OR code LIKE :c2 OR name LIKE :c2", [':c'=>'pekpp', ':c2'=>'%pekpp%']);

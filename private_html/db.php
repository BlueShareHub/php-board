<?php
// .env 파일 경로 설정 (상위 디렉토리의 private_html 폴더 내)
$env_path = dirname(__DIR__) . '/private_html/.env';

if (!file_exists($env_path)) {
    die("환경 설정 파일(.env)을 찾을 수 없습니다.");  // .env 파일이 존재하지 않으면 종료
}

// .env 파일을 읽어 환경 변수로 로드
$env = parse_ini_file($env_path);

if (!$env) {
    die("환경 설정 파일(.env)을 읽을 수 없습니다.");  // .env 파일을 읽을 수 없으면 종료
}

// DB 연결 설정
$host = $env['DB_HOST'];
$db   = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$charset = $env['DB_CHARSET'];
$port = isset($env['DB_PORT']) ? $env['DB_PORT'] : 3306;  // 포트 설정 (기본값: 3306)

// 관리자 비밀번호 설정 (관리자 비밀번호는 .env 파일에서 로드)
$admin_password = $env['ADMIN_PASSWORD'];

// PDO 연결 설정
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // 예외 처리 모드
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // 기본 페치 모드 설정 (연관 배열)
    PDO::ATTR_EMULATE_PREPARES => false,  // 에뮬레이션 프리페어드 스테이트먼트 비활성화
];

try {
    // PDO 객체 생성 및 DB 연결 시도
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // DB 연결 실패 시 에러 메시지 출력 후 종료
    die("DB 연결 실패: " . $e->getMessage());
}

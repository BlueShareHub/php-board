<?php
// DB 설정 파일 포함 (private_html/db.php에서 설정 불러오기)
require_once dirname(__DIR__, 2) . '/private_html/db.php';

// 게시글 ID와 사용자 입력 비밀번호 가져오기
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$password = isset($_POST['password']) ? $_POST['password'] : '';

// 게시글 정보 가져오기 (비밀번호 해시 포함)
$sql = "SELECT password_hash FROM php_board WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch();

// 게시글이 존재하는지 확인
if (!$post) {
    echo "<script>alert('해당 게시글을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

// 비밀번호 검증 (관리자 비밀번호 또는 게시글 비밀번호 확인)
if ($password === $admin_password || password_verify($password, $post['password_hash'])) {
    // 게시글 삭제 쿼리 실행
    $delete_sql = "DELETE FROM php_board WHERE id = :id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $delete_stmt->execute();

    echo "<script>alert('게시글이 성공적으로 삭제되었습니다.'); location.href='list.php?access=blueshare_board';</script>";
    exit;
} else {
    // 비밀번호 불일치 시 처리
    echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
    exit;
}
?>

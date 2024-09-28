<?php
// DB 설정 파일 포함 (private_html/db.php에서 설정 불러오기)
require_once dirname(__DIR__, 2) . '/private_html/db.php';

// 댓글 ID와 게시글 ID, 비밀번호 가져오기
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$password = isset($_POST['password']) ? $_POST['password'] : '';

// 댓글 ID 또는 게시글 ID가 없는 경우 오류 처리
if ($comment_id === 0 || $post_id === 0) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

// 댓글 정보 가져오기
$sql = "SELECT password_hash FROM php_comments WHERE id = :comment_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->execute();
$comment = $stmt->fetch();

// 댓글이 존재하는지 확인
if (!$comment) {
    echo "<script>alert('해당 댓글을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

// 비밀번호 검증: 관리자 비밀번호 또는 댓글 작성자의 비밀번호 확인
if ($password === $admin_password || password_verify($password, $comment['password_hash'])) {
    // 댓글 삭제 처리
    $sql_delete = "DELETE FROM php_comments WHERE id = :comment_id";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt_delete->execute();

    // 삭제 후 게시글로 리다이렉트
    echo "<script>alert('정상적으로 삭제되었습니다.'); window.location.href = 'view.php?id=$post_id';</script>";
    exit;
} else {
    // 비밀번호 불일치 시 처리
    echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
    exit;
}
?>

<?php
// DB 설정 파일 포함 (private_html/db.php에서 설정 불러오기)
require_once dirname(__DIR__, 2) . '/private_html/db.php';

// 게시글 ID 가져오기
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 게시글 정보 가져오기
$sql = "SELECT id, title, content, author, views, created_at FROM php_board WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch();

// 게시글 조회수 증가 처리
if ($post) {
    $sql_update_views = "UPDATE php_board SET views = views + 1 WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update_views);
    $stmt_update->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt_update->execute();
} else {
    echo "해당 게시글을 찾을 수 없습니다.";
    exit;
}

// 댓글 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $comment_content = trim($_POST['content']);
    $comment_author = trim($_POST['author']);
    $comment_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if ($comment_content && $comment_author && $comment_password) {
        $sql_insert_comment = "INSERT INTO php_comments (post_id, content, author, password_hash, created_at, updated_at) 
                               VALUES (:post_id, :content, :author, :password_hash, NOW(), NOW())";
        $stmt_insert = $pdo->prepare($sql_insert_comment);
        $stmt_insert->bindValue(':post_id', $id, PDO::PARAM_INT);
        $stmt_insert->bindValue(':content', $comment_content, PDO::PARAM_STR);
        $stmt_insert->bindValue(':author', $comment_author, PDO::PARAM_STR);
        $stmt_insert->bindValue(':password_hash', $comment_password, PDO::PARAM_STR);
        $stmt_insert->execute();

        // 댓글 저장 후 페이지 새로고침
        header('Location: view.php?id=' . $id);
        exit;
    } else {
        $comment_error = "모든 필드를 입력해주세요.";
    }
}

// 게시글에 달린 댓글 가져오기
$sql_comments = "SELECT id, content, author, created_at FROM php_comments WHERE post_id = :post_id ORDER BY created_at DESC";
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->bindValue(':post_id', $id, PDO::PARAM_INT);
$stmt_comments->execute();
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>게시글 상세 보기</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 15px;
            background-color: #f0f8ff;
        }

        .container {
            max-width: 1000px;
            margin-top: 30px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            text-align: center;
            font-weight: bold;
            padding-bottom: 10px;
        }

        .post-meta {
            color: #6c757d;
            margin-bottom: 20px;
            text-align: right;
        }

        .post-title {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .post-content {
            margin-bottom: 20px;
        }

        .content-area {
            margin-bottom: 20px;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
        }

        .btn-left,
        .btn-right {
            display: flex;
            gap: 10px;
        }

        .btn-right {
            justify-content: flex-end;
        }

        .small-input {
            max-width: 300px;
        }

        /* 댓글 영역 스타일 */
        .comment-section {
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 8px;
            margin: 30px 10px;
        }

        .comment-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            margin-bottom: 10px;
        }

        .comment-author {
            font-weight: bold;
        }

        .comment-timestamp {
            color: #999;
            font-size: 0.9em;
            margin-left: 10px;
        }

        .comment-content {
            padding: 5px;
        }

        .comment-form {
            background-color: #ffffff;
            border: 1px solid;
            margin-top: 30px;
            padding: 20px;
        }

        .comment-form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-form-group label {
            margin-right: 10px;
            min-width: 100px;
        }

        .comment-form-group input {
            flex: 1;
        }

        .char-count {
            margin-left: 5px;
            font-size: 13px;
        }

        .comment-form-group-margin {
            margin-top: 10px;
        }

        .submit-button-margin {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="page-title">게시글</h1>

        <!-- 게시글 정보 -->
        <div class="post-meta">
            번호: <?php echo $post['id']; ?> |
            작성자: <?php echo htmlspecialchars($post['author']); ?> |
            작성일: <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?> |
            조회수: <?php echo $post['views']; ?> |
            댓글수: <?php echo count($comments); ?>
        </div>

        <!-- 제목 -->
        <div class="post-title">
            제목: <?php echo htmlspecialchars($post['title']); ?>
        </div>

        <!-- 내용 -->
        <div class="post-content content-area">
            <textarea class="form-control" rows="10" readonly><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>

        <!-- 버튼 그룹 -->
        <div class="btn-group">
            <div class="btn-left">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
                    <i class="fas fa-edit"></i> 수정하기
                </button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash-alt"></i> 삭제하기
                </button>
                <button class="btn btn-secondary" onclick="location.href='list.php?access=blueshare_board'">
                    <i class="fas fa-list"></i> 목록보기
                </button>
            </div>
        </div>

        <!-- 댓글 리스트 -->
        <div class="comment-section">
            <?php foreach ($comments as $comment): ?>
            <div class="comment-item">
                <span style="font-weight: bold; font-size: 16px; margin-right: 10px;">
                    <?php echo htmlspecialchars($comment['author']); ?>
                </span>
                <span>
                    <?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?>
                </span>
                <button class="btn btn-danger btn-sm float-end" data-bs-toggle="modal"
                    data-bs-target="#deleteCommentModal-<?php echo $comment['id']; ?>"><i class="fas fa-trash-alt"></i>
                    삭제</button>
                <div class="comment-content" style="margin-top: 15px;">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </div>
            </div>

            <!-- 댓글 삭제 확인 모달 -->
            <div class="modal fade" id="deleteCommentModal-<?php echo $comment['id']; ?>" tabindex="-1"
                aria-labelledby="deleteCommentModalLabel-<?php echo $comment['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCommentModalLabel-<?php echo $comment['id']; ?>">댓글 삭제</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>댓글을 삭제하려면 비밀번호를 입력해주세요.</p>
                            <input type="password" id="deleteCommentPassword-<?php echo $comment['id']; ?>" class="form-control"
                                placeholder="비밀번호" maxlength="20">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                    class="fas fa-times"></i>취소</button>
                            <button type="button" class="btn btn-danger"
                                onclick="submitDeleteCommentForm(<?php echo $comment['id']; ?>)"><i
                                    class="fas fa-trash-alt"></i>삭제</button>
                        </div>
                    </div>
                </div>
            </div>

            <form id="deleteCommentForm-<?php echo $comment['id']; ?>" method="POST" action="delete_comment.php"
                style="display: none;">
                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                <input type="hidden" name="password" id="deleteCommentPasswordInput-<?php echo $comment['id']; ?>">
                <input type="hidden" name="post_id" value="<?php echo $id; ?>"> <!-- post_id 필드 추가 -->
            </form>

            <?php endforeach; ?>

            <!-- 댓글 작성 폼 -->
            <div class="comment-form">
                <form action="view.php?id=<?php echo $id; ?>" method="POST" onsubmit="return validatePassword()">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                    <!-- 댓글 입력 -->
                    <div class="form-group-textarea">
                        <textarea class="form-control" id="content" name="content" rows="7" maxlength="1000" required
                            placeholder="댓글을 입력하세요" oninput="updateCommentCharCount()"></textarea>
                        <span class="char-count"><span id="commentCharCount">0</span>/1000</span>
                    </div>

                    <!-- 작성자 입력 -->
                    <div class="comment-form-group comment-form-group-margin">
                        <label for="author">작성자</label>
                        <input type="text" class="form-control small-input" id="author" name="author" maxlength="20" required
                            placeholder="작성자를 입력하세요">
                    </div>

                    <!-- 비밀번호 입력 -->
                    <div class="comment-form-group">
                        <label for="password">비밀번호</label>
                        <input type="password" class="form-control small-input" id="password" name="password" maxlength="20"
                            required placeholder="비밀번호를 입력하세요">
                        <div style="color: red; margin-left: 5px;">※ 비밀번호를 잊어버리면 삭제가 불가능하니 주의하세요.</div>
                    </div>

                    <!-- 비밀번호 확인 입력 -->
                    <div class="comment-form-group">
                        <label for="confirm_password">비밀번호 확인</label>
                        <input type="password" class="form-control small-input" id="confirm_password" name="confirm_password"
                            maxlength="20" required placeholder="비밀번호를 다시 입력하세요">
                        <div id="passwordMismatch" style="color: red; display: none; margin-left: 10px">비밀번호가 일치하지 않습니다.</div>
                    </div>

                    <button type="submit" class="btn btn-primary submit-button-margin">
                        <i class="fas fa-comment"></i> 댓글 달기
                    </button>
                </form>
            </div>
        </div>

        <!-- 풋터 문구 -->
        <div class="text-center mt-4" style="font-weight: bold; font-size: 16px;">
            Copyright © 2024. BlueShare All rights reserved.
        </div>

        <!-- 수정 확인 모달 -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">게시글 수정</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>게시글을 수정하려면 비밀번호를 입력해주세요.</p>
                        <input type="password" id="editPassword" class="form-control" placeholder="비밀번호" maxlength="20">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i>
                            취소</button>
                        <button type="button" class="btn btn-primary" onclick="submitEditForm()"><i class="fas fa-check"></i>
                            확인</button>
                    </div>
                </div>
            </div>
        </div>

        <form id="editForm" method="POST" action="edit.php" style="display: none;">
            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
            <input type="hidden" name="password" id="editPasswordInput">
            <input type="hidden" name="path" value="view">
        </form>

        <!-- 삭제 확인 모달 -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">게시글 삭제</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>게시글을 삭제하려면 비밀번호를 입력해주세요.</p>
                        <p style="color: red;">※ 게시글이 삭제되면 연결된 모든 댓글도 자동으로 삭제됩니다.</p> <!-- 경고 문구 추가 -->
                        <input type="password" id="deletePassword" class="form-control" placeholder="비밀번호" maxlength="20">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i>
                            취소</button>
                        <button type="button" class="btn btn-danger" onclick="submitDeleteForm()"><i class="fas fa-trash-alt"></i>
                            삭제</button>
                    </div>
                </div>
            </div>
        </div>

        <form id="deleteForm" method="POST" action="delete.php" style="display: none;">
            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
            <input type="hidden" name="password" id="passwordInput">
        </form>
    </div>

    <script>
        function submitEditForm() {
            const password = document.getElementById('editPassword').value;
            if (!password) {
                alert('비밀번호를 입력해주세요.');
                return;
            }
            document.getElementById('editPasswordInput').value = password;
            document.getElementById('editForm').submit();
        }

        function submitDeleteForm() {
            const password = document.getElementById('deletePassword').value;
            if (!password) {
                alert('비밀번호를 입력해주세요.');
                return;
            }
            document.getElementById('passwordInput').value = password;
            document.getElementById('deleteForm').submit();
        }

        // 댓글 글자 수 체크
        function updateCommentCharCount() {
            const commentInput = document.getElementById('content');
            const commentCharCount = document.getElementById('commentCharCount');
            commentCharCount.textContent = commentInput.value.length;
        }

        // 비밀번호 확인 함수
        function validatePassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                document.getElementById('passwordMismatch').style.display = 'block';
                return false;
            }

            return true;
        }

        function submitDeleteCommentForm(commentId) {
            const password = document.getElementById(`deleteCommentPassword-${commentId}`).value;
            if (!password) {
                alert('비밀번호를 입력해주세요.');
                return;
            }
            document.getElementById(`deleteCommentPasswordInput-${commentId}`).value = password;
            document.getElementById(`deleteCommentForm-${commentId}`).submit();
        }
    </script>

</body>

</html>

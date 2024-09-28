<?php
// DB 설정 파일 포함 (private_html/db.php에서 설정 불러오기)
require_once dirname(__DIR__, 2) . '/private_html/db.php';

// ID, 비밀번호, 경로(path) 가져오기
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$password = isset($_POST['password']) ? $_POST['password'] : '';
$path = isset($_POST['path']) ? $_POST['path'] : '';

// ID가 없는 경우 오류 메시지 출력
if ($id === 0) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// 게시글 정보 가져오기
$sql = "SELECT id, title, content, author, password_hash, created_at, views FROM php_board WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch();

// 게시글 존재 여부 확인
if (!$post) {
    echo "<script>alert('해당 게시글을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

// 비밀번호 검증: 글 상세보기(view.php)에서 넘어온 경우에만 비밀번호 체크
if ($path == 'view') {
    if (!($password === $admin_password || password_verify($password, $post['password_hash']))) {
        // 비밀번호 불일치 시 처리
        echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
        exit;
    }
}

// 게시글 수정 처리: 수정하기 버튼 클릭 시
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path == 'edit') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if ($title && $content) {
        // 게시글 수정 쿼리 실행
        $sql = "UPDATE php_board SET title = :title, content = :content, updated_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // 수정 완료 후 상세 페이지로 리다이렉트
        header('Location: view.php?id=' . $id);
        exit;
    } else {
        $error = "모든 필드를 입력해주세요.";
    }
}

// 댓글 수 가져오기
$sql_comment_count = "SELECT COUNT(*) AS comment_count FROM php_comments WHERE post_id = :post_id";
$stmt_comment_count = $pdo->prepare($sql_comment_count);
$stmt_comment_count->bindValue(':post_id', $id, PDO::PARAM_INT);
$stmt_comment_count->execute();
$comment_count = $stmt_comment_count->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>게시글 수정</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            background-color: #f0f8ff;
        }

        .container {
            max-width: 1000px;
            margin-top: 30px;
            background-color: #ffffff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1.page-title {
            text-align: center;
            font-weight: bold;
        }

        .form-label {
            font-weight: bold;
            width: 120px;
            text-align: left;
            padding-right: 20px;
            font-size: 15px;
        }

        .form-control {
            max-width: 700px;
        }

        .small-input {
            max-width: 300px;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-group-textarea {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .btn-submit {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            margin-bottom: 10px;
            gap: 10px;
        }

        .post-meta {
            margin-left: 120px;
            margin-bottom: 30px;
        }

        .char-count {
            font-size: 0.9em;
            color: #888;
            margin-left: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="my-4 page-title">게시글 수정</h1>

        <!-- 게시글 수정 Form -->
        <form action="edit.php?id=<?php echo $id; ?>" method="POST">

            <!-- 게시글 정보 -->
            <div class="post-meta">
                번호: <?php echo $post['id']; ?> |
                작성자: <?php echo htmlspecialchars($post['author']); ?> |
                작성일: <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?> |
                조회수: <?php echo $post['views']; ?> |
                댓글수: <?php echo $comment_count; ?>
            </div>

            <!-- 제목 입력 -->
            <div class="form-group">
                <label for="title" class="form-label">제목</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="50" required
                    value="<?php echo htmlspecialchars($post['title']); ?>" placeholder="제목을 입력하세요"
                    oninput="updateTitleCharCount()">
                <span class="char-count"><span id="titleCharCount">
                    <?php echo strlen($post['title']); ?>
                </span>/50</span>
            </div>

            <!-- 내용 입력 -->
            <div class="form-group-textarea">
                <label for="content" class="form-label">내용</label>
                <textarea class="form-control" id="content" name="content" rows="10" maxlength="2000" required
                    placeholder="내용을 입력하세요"
                    oninput="updateContentCharCount()"><?php echo htmlspecialchars($post['content']); ?></textarea>
                <span class="char-count"><span id="contentCharCount">
                    <?php echo strlen($post['content']); ?>
                </span>/2000</span>
            </div>

            <!-- 등록 및 취소 버튼 -->
            <div class="btn-submit">
                <button type="button" class="btn btn-secondary btn-cancel"
                    onclick="location.href='view.php?id=<?php echo $post['id']; ?>'"><i class="fas fa-times"></i> 취소</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-pencil-alt"></i> 수정</button>
            </div>

            <!-- 숨겨진 필드로 ID 및 path 전송 -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="path" value="edit">
        </form>

    </div>

    <!-- 풋터 문구 -->
    <div class="text-center mt-4" style="font-weight: bold; font-size: 16px;">
        Copyright © 2024. BlueShare All rights reserved.
    </div>

    <!-- JS for character count update -->
    <script>
        function updateTitleCharCount() {
            const titleInput = document.getElementById('title');
            const titleCharCount = document.getElementById('titleCharCount');
            titleCharCount.textContent = titleInput.value.length;
        }

        function updateContentCharCount() {
            const contentInput = document.getElementById('content');
            const contentCharCount = document.getElementById('contentCharCount');
            contentCharCount.textContent = contentInput.value.length;
        }
    </script>

</body>

</html>

<?php
// DB 설정 파일 포함 (private_html/db.php에서 설정 불러오기)
require_once dirname(__DIR__, 2) . '/private_html/db.php';

// 글이 제출되었는지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 입력 데이터 가져오기
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // 비밀번호 해시화
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 유효성 검사 (필수 항목이 모두 입력되었는지 확인)
    if ($title && $author && $content && $password) {
        // 게시글 저장 쿼리 실행
        $sql = "INSERT INTO php_board (title, content, author, password_hash, views, created_at, updated_at) 
                VALUES (:title, :content, :author, :password_hash, 0, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->bindValue(':author', $author, PDO::PARAM_STR);
        $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
        $stmt->execute();

        // 글 작성 완료 후 목록 페이지로 리다이렉트
        header('Location: list.php?access=blueshare_board');
        exit;
    } else {
        $error = "모든 필드를 입력해주세요.";
    }
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>글쓰기</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
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

        .char-count {
            font-size: 0.9em;
            color: #888;
            margin-left: 10px;
        }

        .btn-submit {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            margin-bottom: 10px;
            gap: 10px;
        }

        #passwordMismatch {
            color: red;
            display: none;
            margin-left: 5px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="my-4 page-title">글쓰기</h1>

        <!-- 글쓰기 Form -->
        <form action="" method="POST" onsubmit="return validatePassword()">

            <!-- 작성자 입력 -->
            <div class="form-group">
                <label for="author" class="form-label">작성자</label>
                <input type="text" class="form-control small-input" id="author" name="author" maxlength="20" required
                    placeholder="작성자를 입력하세요">
            </div>

            <!-- 제목 입력 -->
            <div class="form-group">
                <label for="title" class="form-label">제목</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="50" required
                    placeholder="제목을 입력하세요" oninput="updateTitleCharCount()">
                <span class="char-count"><span id="titleCharCount">0</span>/50</span>
            </div>

            <!-- 내용 입력 -->
            <div class="form-group-textarea">
                <label for="content" class="form-label">내용</label>
                <textarea class="form-control" id="content" name="content" rows="8" maxlength="2000" required
                    placeholder="내용을 입력하세요" oninput="updateContentCharCount()"></textarea>
                <span class="char-count"><span id="contentCharCount">0</span>/2000</span>
            </div>

            <!-- 비밀번호 입력 -->
            <div class="form-group">
                <label for="password" class="form-label">비밀번호</label>
                <input type="password" class="form-control small-input" id="password" name="password" maxlength="20" required
                    placeholder="비밀번호를 입력하세요">
                <div style="color: red; margin-left: 5px;">※ 비밀번호를 잊어버리면 수정 및 삭제가 불가능하니 주의하세요.</div>
            </div>

            <!-- 비밀번호 확인 입력 -->
            <div class="form-group">
                <label for="confirm_password" class="form-label">비밀번호 확인</label>
                <input type="password" class="form-control small-input" id="confirm_password" name="confirm_password"
                    maxlength="20" required placeholder="비밀번호를 다시 입력하세요">
                <div id="passwordMismatch">비밀번호가 일치하지 않습니다.</div>
            </div>

            <!-- 등록 및 취소 버튼 -->
            <div class="btn-submit">
                <button type="button" class="btn btn-secondary btn-cancel" onclick="cancelWrite()"><i class="fas fa-times"></i>
                    취소</button> <!-- 취소 시 목록으로 이동 -->
                <button type="submit" class="btn btn-primary"><i class="fas fa-pencil-alt"></i> 등록</button>
            </div>
        </form>
    </div>

    <!-- 풋터 문구 -->
    <div class="text-center mt-4" style="font-weight: bold; font-size: 16px;">
        Copyright © 2024. BlueShare All rights reserved.
    </div>

    <!-- JS to handle character count and password validation -->
    <script>
        // 제목 글자 수 카운트
        function updateTitleCharCount() {
            const titleInput = document.getElementById('title');
            const titleCharCount = document.getElementById('titleCharCount');
            titleCharCount.textContent = titleInput.value.length;
        }

        // 내용 글자 수 카운트
        function updateContentCharCount() {
            const contentInput = document.getElementById('content');
            const contentCharCount = document.getElementById('contentCharCount');
            contentCharCount.textContent = contentInput.value.length;
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

        // 취소 버튼 클릭 시 목록 페이지로 이동
        function cancelWrite() {
            window.location.href = 'list.php?access=blueshare_board';  // 목록 페이지로 이동
        }
    </script>

</body>

</html>

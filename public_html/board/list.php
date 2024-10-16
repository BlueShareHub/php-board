<?php
// 접근 파라미터가 설정되어 있는지 확인
if (!isset($_GET['access']) || $_GET['access'] !== 'blueshare_board') {
    echo "<script>alert('잘못된 접근입니다.'); window.close();</script>";
    exit;
}

// DB 설정 파일 포함
require_once dirname(__DIR__, 2) . '/private_html/db.php';

// 검색어 처리
$search = $_GET['search'] ?? '';
$search_type = $_GET['search_type'] ?? 'title';

// 페이지 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 검색 타입에 따른 컬럼 결정
$search_column = $search_type === 'author' ? 'author' : 'title';

// 게시글 목록 가져오기 (댓글 수 포함)
$sql = "SELECT b.id, b.title, b.author, b.views, b.created_at, 
        (SELECT COUNT(*) FROM php_comments c WHERE c.post_id = b.id) AS comment_count 
        FROM php_board b 
        WHERE $search_column LIKE :search 
        ORDER BY b.created_at DESC 
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// 전체 게시글 수 가져오기
$total_sql = "SELECT COUNT(*) FROM php_board WHERE $search_column LIKE :search";
$total_stmt = $pdo->prepare($total_sql);
$total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$total_stmt->execute();
$total_posts = $total_stmt->fetchColumn();
$total_pages = ceil($total_posts / $limit);
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="게시판 목록 페이지: 게시글 목록을 확인하고 원하는 게시글을 선택하여 조회할 수 있습니다. 다양한 게시글의 제목, 작성자, 작성일, 조회수를 확인해보세요.">
    <title>게시판 목록</title>
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .container {
            margin-top: 20px;
        }

        table {
            margin-top: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }

        .table td:nth-child(2) {
            text-align: left;
        }

        /* 각 열의 넓이 설정 */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            /* 번호 */
            width: 5%;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            /* 제목 */
            width: 54%;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            /* 작성자 */
            width: 10%;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            /* 작성일 */
            width: 10%;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            /* 조회수 */
            width: 8%;
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            /* 댓글수 */
            width: 8%;
        }

        .search-container {
            text-align: right;
            margin-bottom: 20px;
        }

        .search-title {
            font-size: 24px;
            font-weight: bold;
        }

        .input-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .search-button {
            width: 80px;
        }

        .pagination {
            justify-content: center;
        }

        .btn-write {
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="container">

    <!-- 검색 폼 -->
    <div class="search-container">
        <form method="GET" action="" class="form-inline">
            <div class="input-group mb-3">
                <h1 class="search-title">게시글 목록</h1>
                <div class="d-flex justify-content-end" style="margin-left: auto;">
                    <input type="hidden" name="access" value="blueshare_board">
                    <select name="search_type" style="width: 100px; margin-right: 5px;">
                        <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>제목</option>
                        <option value="author" <?= $search_type === 'author' ? 'selected' : '' ?>>작성자</option>
                    </select>
                    <input type="text" style="width: 300px; margin-right: 5px;" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="검색어를 입력하세요">
                    <button type="submit" class="btn btn-primary search-button"><i class="fas fa-search"></i> 검색</button>
                </div>
            </div>
        </form>
    </div>

    <!-- 공지 문구 -->
    <div class="text-muted mb-3" style="font-weight: bold; font-size: 16px;">
        <span style="color: blue;">☞ 이 게시판은 방명록, Q&A 등 자유롭게 글을 작성하고 의견을 나누는 공간입니다.</span>
    </div>

    <!-- 게시글 목록 테이블 -->
    <table class="table table-hover table-bordered">
        <thead class="table-dark">
            <tr>
                <th>번호</th>
                <th>제목</th>
                <th>작성자</th>
                <th>작성일</th>
                <th>조회수</th>
                <th>댓글수</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $index => $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($total_posts - ($page - 1) * $limit - $index) ?></td>
                        <td><a href="view.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></td>
                        <td><?= htmlspecialchars($post['author']) ?></td>
                        <td><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                        <td><?= htmlspecialchars($post['views']) ?></td>
                        <td><?= htmlspecialchars($post['comment_count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">게시글이 없습니다.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 글쓰기 버튼 -->
    <a href="write.php" class="btn btn-primary btn-write"><i class="fas fa-pencil-alt"></i> 글쓰기</a>

    <!-- 페이지네이션 -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?access=blueshare_board&page=<?= $i ?>&search=<?= urlencode($search) ?>&search_type=<?= urlencode($search_type) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <!-- 푸터 문구 -->
    <div class="text-center mt-4" style="font-weight: bold; font-size: 16px;">
        Copyright © 2024. BlueShare All rights reserved.
    </div>

</body>

</html>
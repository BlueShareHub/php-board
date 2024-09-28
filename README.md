# php 게시판

이 프로젝트는 PHP 7.4와 MariaDB 11.3을 기반으로 구현된 간단한 게시판 시스템입니다. 사용자는 글을 작성하고, 수정 및 삭제할 수 있으며 댓글 기능도 포함되어 있습니다.

## 설치 방법

1. 프로젝트를 클론합니다.

``` bash
git clone https://github.com/your-username/blueshare-board.git
```

2. 클론한 프로젝트의 폴더 구조는 다음과 같습니다.

```
/ (프로젝트 루트)
├── LICENSE               # 프로젝트 라이센스 파일
├── private_html/
│   ├── .env.example      # 환경 설정 파일
│   └── db.php            # 데이터베이스 연결 설정 파일
├── public_html/
│   └── board/
│       ├── list.php      # 게시글 목록 페이지
│       ├── view.php      # 게시글 상세보기 페이지
│       ├── write.php     # 게시글 작성 페이지
│       ├── edit.php      # 게시글 수정 페이지
│       ├── delete.php    # 게시글 삭제 처리 페이지
│       ├── delete_comment.php # 댓글 삭제 처리 페이지
│       └── open.html     # 게시판 팝업 열기 페이지
└── README.md             # 프로젝트 설명 파일
```

3. `private_html/.env` 파일을 설정합니다. `.env.example` 파일을 참고하여 `.env` 파일을 생성하고, 데이터베이스 정보와 관리자 비밀번호를 설정합니다.

4. 데이터베이스 테이블을 생성합니다. 아래의 SQL 쿼리를 사용하여 MariaDB 11.3에 테이블을 생성합니다.

``` sql
-- 게시판 테이블
CREATE TABLE php_board (
  id INT AUTO_INCREMENT PRIMARY KEY,    -- 게시글 ID (자동 증가, 기본 키)
  title VARCHAR(255) NOT NULL,          -- 게시글 제목
  content TEXT NOT NULL,                -- 게시글 내용
  author VARCHAR(100) NOT NULL,         -- 작성자 이름
  password_hash VARCHAR(255) NOT NULL,  -- 비밀번호 해시값
  views INT DEFAULT 0,                  -- 조회수 (기본값: 0)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- 게시글 작성 시간 (자동 현재 시간)
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- 게시글 수정 시간 (자동 업데이트)
);

-- 댓글 테이블
CREATE TABLE php_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,    -- 댓글 ID (자동 증가, 기본 키)
  post_id INT NOT NULL,                 -- 게시글 ID (php_board 테이블과 외래 키 관계)
  content TEXT NOT NULL,                -- 댓글 내용
  author VARCHAR(100) NOT NULL,         -- 댓글 작성자 이름
  password_hash VARCHAR(255) NOT NULL,  -- 댓글 비밀번호 해시값
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- 댓글 작성 시간 (자동 현재 시간)
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- 댓글 수정 시간 (자동 업데이트)
  
  -- php_board 테이블의 id 필드와 외래 키 관계 설정, 게시글이 삭제되면 연결된 댓글도 삭제됨
  FOREIGN KEY (post_id) REFERENCES php_board(id) ON DELETE CASCADE
);
```

5. 웹 서버의 `public_html/board` 디렉토리에 프로젝트 파일을 배치하고 PHP 7.4 환경을 설정합니다.

6. 이제 브라우저에서 적용된 게시판 페이지를 확인할 수 있습니다.

※ 적용된 게시판 페이지 예시: [https://bluesharehub.com/board/list.php?access=blueshare_board](https://bluesharehub.com/board/list.php?access=blueshare_board)

## 사용 방법

- **글 작성**: 게시판에서 '글쓰기' 버튼을 클릭하여 글을 작성할 수 있습니다.
- **댓글 작성**: 각 게시글 하단에서 댓글을 작성할 수 있습니다.
- **글 수정 및 삭제**: 작성한 글을 수정하거나 삭제하려면 작성 시 입력한 비밀번호를 입력해야 합니다.

## 라이센스

이 프로젝트는 [MIT 라이센스](LICENSE)를 따릅니다.

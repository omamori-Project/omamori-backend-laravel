# Windows 사용자 가이드

## Windows + Docker Desktop 환경 설정

### 1. Docker Desktop 설치 확인

Docker Desktop이 설치되어 있어야 합니다:

- [Docker Desktop 다운로드](https://www.docker.com/products/docker-desktop/)
- 설치 후 재부팅 필요
- WSL 2 백엔드 사용 (권장)

**설치 확인:**

```cmd
docker --version
docker-compose --version
```

### 2. WSL 2 설정 (권장)

Docker Desktop은 WSL 2를 사용하면 성능이 훨씬 좋습니다.

**확인 방법:**

1. Docker Desktop 아이콘 우클릭 → Settings
2. General → Use the WSL 2 based engine 체크
3. Resources → WSL Integration 활성화

---

## 빠른 시작 (3가지 방법)

### 방법 1: PowerShell 스크립트 (추천)

1. 프로젝트 폴더에서 마우스 우클릭
2. "PowerShell 여기에서 열기" 선택
3. 다음 명령어 실행:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\start.ps1
```

### 방법 2: 배치 파일

1. `start.bat` 파일을 더블 클릭
2. 자동으로 설정됩니다

### 방법 3: 수동 실행

```cmd
# .env 파일 생성
copy .env.example .env

# Docker 컨테이너 실행
docker-compose up -d --build

# Laravel 설정
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan storage:link
docker-compose exec php php artisan migrate
```

---

## Windows 경로 설정

### 파일 권한 문제 해결

Windows에서 Docker 볼륨 마운트 시 권한 문제가 발생할 수 있습니다.

**해결 방법:**

```powershell
# PowerShell에서 실행
docker-compose exec php chmod -R 775 storage bootstrap/cache
docker-compose exec php chown -R www:www storage bootstrap/cache
```

### 줄바꿈 문제 (CRLF vs LF)

Git에서 체크아웃 시 줄바꿈이 자동 변환될 수 있습니다.

**.gitattributes 설정:**

```
* text=auto
*.sh text eol=lf
*.conf text eol=lf
```

---

## 🛠 Windows 전용 명령어

### 컨테이너 관리

```cmd
# 시작
docker-compose up -d

# 중지
docker-compose down

# 재시작
docker-compose restart

# 로그 보기 (Ctrl+C로 종료)
docker-compose logs -f

# 특정 서비스 로그
docker-compose logs -f nginx
docker-compose logs -f php
```

### PHP 컨테이너 접속

```cmd
# Bash 쉘 접속
docker-compose exec php bash

# 단일 명령어 실행
docker-compose exec php php artisan route:list
docker-compose exec php composer require intervention/image
```

### 데이터베이스 접속

**TablePlus, DBeaver, pgAdmin 등 사용:**

- Host: `localhost`
- Port: `5432`
- Database: `omamori_db`
- Username: `omamori_user`
- Password: `omamori_pass`

**명령줄에서 접속:**

```cmd
docker-compose exec postgres psql -U omamori_user -d omamori_db
```

---

## Windows 특화 문제 해결

### 문제 1: "Drive has not been shared"

**증상:** Docker가 드라이브에 접근할 수 없다는 오류

**해결:**

1. Docker Desktop → Settings
2. Resources → File Sharing
3. 프로젝트가 있는 드라이브 추가 (예: C:)
4. Apply & Restart

### 문제 2: 포트가 이미 사용 중

**증상:** `port is already allocated` 오류

**해결:**

```cmd
# 포트 사용 중인 프로세스 확인
netstat -ano | findstr :8080

# 프로세스 종료 (PID는 위에서 확인)
taskkill /PID <PID> /F
```

또는 `docker-compose.yml`에서 포트 변경:

```yaml
ports:
    - "8081:80" # 8080 → 8081로 변경
```

### 문제 3: Docker Desktop이 느림

**해결:**

1. Docker Desktop → Settings → Resources
2. CPU와 메모리 할당 늘리기
    - CPU: 최소 2 cores
    - Memory: 최소 4GB
3. WSL 2 백엔드 사용 확인

### 문제 4: 파일 변경이 반영되지 않음

**증상:** 코드를 수정해도 웹사이트에 반영 안 됨

**해결:**

```cmd
# PHP-FPM 재시작
docker-compose restart php

# 또는 전체 재시작
docker-compose restart
```

### 문제 5: Composer가 느림

**해결:**

```cmd
# Composer 캐시 클리어
docker-compose exec php composer clear-cache

# 또는 Packagist 미러 사용
docker-compose exec php composer config repo.packagist composer https://packagist.kr
```

---

## Windows 파일 탐색기에서 접근

### 프로젝트 폴더 구조

```
C:\Users\YourName\omamori-project\
├── app/
├── docker/
├── storage/
│   └── app/
│       └── public/
│           └── omamori/        여기에 이미지 저장됨
└── public/
```

### Storage 폴더 접근

```
파일 탐색기 → 주소창에 입력:
%USERPROFILE%\omamori-project\storage\app\public\omamori
```

---

## VS Code 설정 (권장)

### 확장 프로그램 설치

- **Docker** by Microsoft
- **Laravel Extension Pack**
- **PHP Intelephense**
- **Remote - Containers** (선택사항)

### workspace 설정 (.vscode/settings.json)

```json
{
    "files.associations": {
        "*.php": "php",
        ".env*": "dotenv"
    },
    "php.validate.executablePath": "php",
    "[php]": {
        "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
    }
}
```

---

## 일상적인 개발 워크플로우

### 아침에 개발 시작

```cmd
# Docker Desktop 실행 확인
docker ps

# 컨테이너 시작 (이미 실행 중이면 생략)
docker-compose up -d

# 브라우저에서 확인
start http://localhost:8080
```

### 저녁에 개발 종료

```cmd
# 컨테이너 중지 (선택사항 - 계속 실행해도 됨)
docker-compose down

# 또는 Docker Desktop을 그냥 종료
```

### 코드 변경 후

```cmd
# 자동으로 반영됨 (재시작 불필요)
# 단, Config 변경 시에만 캐시 클리어 필요
docker-compose exec php php artisan config:clear
```

---

## 성능 최적화 팁

### 1. Docker Desktop 메모리 설정

- 최소 4GB 이상 할당
- SSD 드라이브에 프로젝트 저장

### 2. WSL 2 메모리 제한 설정

`C:\Users\YourName\.wslconfig` 파일 생성:

```ini
[wsl2]
memory=4GB
processors=2
swap=2GB
```

### 3. Composer 속도 개선

```cmd
docker-compose exec php composer global require hirak/prestissimo
```

---

## 유용한 링크

- [Laravel 11 한글 문서](https://laravel.kr/docs/11.x)
- [Docker Desktop 문서](https://docs.docker.com/desktop/windows/)
- [WSL 2 설치 가이드](https://docs.microsoft.com/ko-kr/windows/wsl/install)
- [PostgreSQL GUI 도구](https://www.pgadmin.org/)

---

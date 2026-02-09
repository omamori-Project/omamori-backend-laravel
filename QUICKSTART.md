# 오마모리 플랫폼 - 5분 빠른 시작 가이드

## 최소 요구사항

✅ Windows 10/11  
✅ Docker Desktop 설치  
✅ 8GB 이상 RAM  
✅ 10GB 이상 여유 공간

---

## 🚀 3단계로 시작하기

### Docker Desktop 실행

- Docker Desktop 프로그램 실행
- 고래 아이콘이 초록색이 될 때까지 대기

### 2️3 프로젝트 폴더로 이동

```cmd
cd C:\path\to\omamori-project
```

### 3️3 자동 설치 스크립트 실행

**PowerShell 사용 (추천):**

```powershell
# 마우스 우클릭 → "PowerShell 여기에서 열기"
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\start.ps1
```

**또는 배치 파일 더블 클릭:**

- `start.bat` 파일 더블 클릭

---

## 완료!

브라우저에서 접속:
**http://localhost:9090**

데이터베이스 접속:

- Host: localhost:5432
- Database: omamori_db
- User: omamori_user
- Password: omamori_pass

---

## 프로젝트 구조

```
omamori-project/
├── docker/                    # Docker 설정 파일
│   ├── nginx/                # Nginx 웹 서버 설정
│   └── php/                  # PHP-FPM 설정
├── docker-compose.yml        # Docker 컨테이너 정의
├── .env.example              # 환경 변수 템플릿
├── start.ps1                 # PowerShell 자동 설치 스크립트
├── start.bat                 # 배치 자동 설치 스크립트
├── README.md                 # 상세 가이드
├── WINDOWS_GUIDE.md          # Windows 전용 가이드
└── DEV_COMMANDS.md           # 개발 명령어 모음
```

---

## 다음 단계

### 1. Laravel 프로젝트 생성

```cmd
# 프로젝트 폴더 안에서
docker-compose exec php composer create-project laravel/laravel . "11.*"
```

### 2. 기본 설정 완료

```cmd
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan storage:link
docker-compose exec php php artisan migrate
```

### 3. 개발 시작!

- 모델 생성: `docker-compose exec php php artisan make:model Omamori -m`
- 컨트롤러 생성: `docker-compose exec php php artisan make:controller OmamoriController --api`

---

## 🛠 자주 사용하는 명령어

```cmd
# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 로그 확인
docker-compose logs -f

# PHP 컨테이너 접속
docker-compose exec php bash

# Artisan 명령어 실행
docker-compose exec php php artisan migrate
```

---

## 더 자세한 정보

- **상세 가이드**: README.md 파일 참고
- **Windows 특화**: WINDOWS_GUIDE.md 파일 참고
- **개발 명령어**: DEV_COMMANDS.md 파일 참고

---

## 🆘 문제 발생 시

### 포트 충돌

- `docker-compose.yml` 파일에서 포트 변경
- 8080 → 8081로 수정

### 권한 오류

```cmd
docker-compose exec php chmod -R 775 storage bootstrap/cache
```

### 전체 재시작

```cmd
docker-compose down -v
docker-compose up -d --build
```

---

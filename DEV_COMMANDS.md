# Omamori Platform - 개발 유틸리티 스크립트

## 빠른 명령어

### Laravel Artisan 명령어

```cmd
# 마이그레이션 실행
docker-compose exec php php artisan migrate

# 마이그레이션 롤백
docker-compose exec php php artisan migrate:rollback

# 새 모델 생성 (마이그레이션 포함)
docker-compose exec php php artisan make:model Omamori -m

# 새 컨트롤러 생성
docker-compose exec php php artisan make:controller OmamoriController --api

# 라우트 목록 확인
docker-compose exec php php artisan route:list

# 캐시 클리어
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan view:clear

# Tinker (Laravel REPL)
docker-compose exec php php artisan tinker
```

### Composer 명령어

```cmd
# 패키지 설치
docker-compose exec php composer require intervention/image

# 패키지 제거
docker-compose exec php composer remove intervention/image

# 오토로드 업데이트
docker-compose exec php composer dump-autoload

# 전체 업데이트
docker-compose exec php composer update
```

### 데이터베이스 명령어

```cmd
# PostgreSQL 접속
docker-compose exec postgres psql -U omamori_user -d omamori_db

# 데이터베이스 백업
docker-compose exec postgres pg_dump -U omamori_user omamori_db > backup.sql

# 데이터베이스 복원
docker-compose exec -T postgres psql -U omamori_user omamori_db < backup.sql

# 테이블 목록 확인 (psql 접속 후)
# \dt
# \d+ table_name

# 데이터베이스 초기화 (주의!)
docker-compose exec php php artisan migrate:fresh
```

### Docker 관리

```cmd
# 컨테이너 상태 확인
docker-compose ps

# 리소스 사용량 확인
docker stats

# 특정 컨테이너 로그
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f postgres

# 컨테이너 재시작
docker-compose restart php

# 전체 재빌드
docker-compose down
docker-compose up -d --build

# 볼륨 포함 완전 삭제 (주의!)
docker-compose down -v
```

### 파일 및 권한 관리

```cmd
# Storage 권한 수정
docker-compose exec php chmod -R 775 storage bootstrap/cache

# Storage 링크 재생성
docker-compose exec php php artisan storage:link

# 이미지 디렉토리 생성
docker-compose exec php mkdir -p storage/app/public/omamori/layers
docker-compose exec php mkdir -p storage/app/public/omamori/generated
docker-compose exec php mkdir -p storage/app/public/omamori/temp

# 로그 파일 확인
docker-compose exec php tail -f storage/logs/laravel.log
```

### 테스트 실행

```cmd
# 전체 테스트
docker-compose exec php php artisan test

# 특정 테스트
docker-compose exec php php artisan test --filter OmamoriTest

# 커버리지 리포트
docker-compose exec php php artisan test --coverage
```

---

## 프로젝트 초기 설정 체크리스트

### 1. 환경 설정 확인

```cmd
# .env 파일 확인
type .env

# 환경 변수 테스트
docker-compose exec php php artisan config:show database
```

### 2. 데이터베이스 연결 테스트

```cmd
# PostgreSQL 연결 확인
docker-compose exec postgres pg_isready -U omamori_user

# Laravel에서 DB 연결 확인
docker-compose exec php php artisan tinker
# >>> DB::connection()->getPdo();
```

### 3. 이미지 저장소 확인

```cmd
# Storage 디렉토리 확인
docker-compose exec php ls -la storage/app/public/omamori/

# 심볼릭 링크 확인
docker-compose exec php ls -la public/storage
```

### 4. Composer 패키지 확인

```cmd
# 설치된 패키지 목록
docker-compose exec php composer show

# 필수 패키지 설치 확인
docker-compose exec php composer show | findstr "laravel/framework"
```

---

## 추천 패키지 설치

### 이미지 처리

```cmd
# Intervention Image (이미지 레이어 병합용)
docker-compose exec php composer require intervention/image

# 또는 Imagick 확장 사용 (이미 Dockerfile에 포함됨)
```

### API 개발

```cmd
# Laravel Sanctum (API 인증)
docker-compose exec php composer require laravel/sanctum
docker-compose exec php php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker-compose exec php php artisan migrate

# API Resource 생성
docker-compose exec php php artisan make:resource OmamoriResource
```

### 디버깅

```cmd
# Laravel Debugbar
docker-compose exec php composer require barryvdh/laravel-debugbar --dev

# Laravel Telescope (개발 환경 모니터링)
docker-compose exec php composer require laravel/telescope --dev
docker-compose exec php php artisan telescope:install
docker-compose exec php php artisan migrate
```

### AWS S3 연동 (배포 준비)

```cmd
# AWS SDK
docker-compose exec php composer require league/flysystem-aws-s3-v3 "^3.0"
```

---

## 이미지 처리 테스트

### Intervention Image 테스트

```php
// docker-compose exec php php artisan tinker

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->read('path/to/image.png');
$image->resize(300, 200);
$image->save('path/to/output.png');
```

### ImageMagick 테스트

```cmd
# ImageMagick 설치 확인
docker-compose exec php php -m | findstr imagick

# 이미지 레이어 병합 테스트 (PHP)
docker-compose exec php php -r "
\$base = new Imagick('layer1.png');
\$overlay = new Imagick('layer2.png');
\$base->compositeImage(\$overlay, Imagick::COMPOSITE_OVER, 0, 0);
\$base->writeImage('merged.png');
echo 'Image merged successfully';
"
```

---

## Git 워크플로우

### 기본 브랜치 전략

```cmd
# 개발 브랜치에서 작업
git checkout -b feature/omamori-creation
git add .
git commit -m "feat: 오마모리 생성 기능 구현"
git push origin feature/omamori-creation

# 메인 브랜치에 머지
git checkout main
git merge feature/omamori-creation
git push origin main
```

### .gitignore 확인

```cmd
# 무시되는 파일 확인
git status --ignored

# 실수로 커밋된 파일 제거
git rm --cached .env
git commit -m "Remove .env from repository"
```

---

## 모니터링 및 로그

### 실시간 로그 확인

```cmd
# Laravel 로그
docker-compose exec php tail -f storage/logs/laravel.log

# Nginx 액세스 로그
docker-compose logs -f nginx | findstr GET

# Nginx 에러 로그
docker-compose logs -f nginx | findstr error

# PostgreSQL 로그
docker-compose logs -f postgres
```

### 성능 모니터링

```cmd
# 컨테이너 리소스 사용량
docker stats omamori_nginx omamori_php omamori_postgres

# 디스크 사용량
docker-compose exec php df -h

# 메모리 사용량
docker-compose exec php free -h
```

---

## 🆘 긴급 복구 명령어

### 전체 초기화 (주의!)

```cmd
# 1. 컨테이너 및 볼륨 삭제
docker-compose down -v

# 2. .env 파일 재생성
copy .env.example .env

# 3. 재시작
docker-compose up -d --build

# 4. Laravel 재설정
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate:fresh
```

### 데이터베이스만 초기화

```cmd
docker-compose exec php php artisan migrate:fresh --seed
```

### 캐시 완전 클리어

```cmd
docker-compose exec php php artisan optimize:clear
```

---

이 가이드를 참고하여 개발하시면 됩니다!

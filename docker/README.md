# Football Simulation - Docker Setup

Thiết lập Docker cho project Laravel với MySQL 8 và PHP 8.1-FPM.

## Yêu cầu

- Docker & Docker Compose đã cài đặt
- Ports 80 (HTTP), 443 (HTTPS), 3306 (MySQL) không bị sử dụng

## Hướng dẫn sử dụng

### 1. Cấu hình `.env`

`.env` đã có ở **root của project** (bên ngoài folder `docker/`).

Nếu cần tùy chỉnh, chỉnh sửa file `.env` ở root:

```env
DB_PORT=3306
MYSQL_PORT_HOST=3306
NGINX_HTTP_PORT=80
```

**Lưu ý**: File `.env` tự động được Docker load, không cần copy gì thêm.

### 2. Build và chạy containers

```bash
docker compose up -d --build
```

### 3. Chạy Laravel migrations

```bash
docker compose exec php php artisan migrate
```

### 3.1. Nếu thay đổi cấu hình hoặc `.env`

Khi bạn thay đổi `DB_*`, `.env`, hoặc các config Laravel, nên làm sạch cache/config:

```bash
docker compose exec php php artisan config:clear

docker compose exec php php artisan cache:clear

docker compose exec php php artisan route:clear
```

Sau đó nếu muốn, có thể tạo lại cache config cho nhanh:

```bash
docker compose exec php php artisan config:cache
```

### 4. Chạy Laravel seeding (nếu cần)

```bash
docker compose exec php php artisan db:seed
```

### 5. Truy cập ứng dụng

- **Frontend**: http://localhost
- **MySQL**: localhost:3306
  - Username: `football_user` (hoặc `root`)
  - Password: `password` (hoặc `root`)

## Các lệnh hữu ích

### Xem logs

```bash
# Xem tất cả logs
docker compose logs -f

# Xem logs của PHP
docker compose logs -f php

# Xem logs của MySQL
docker compose logs -f mysql

# Xem logs của Nginx
docker compose logs -f nginx
```

### Truy cập container

```bash
# Truy cập PHP container
docker compose exec php sh

# Truy cập MySQL container
docker compose exec mysql mysql -u root -p

# Chạy Artisan commands
docker compose exec php php artisan tinker
docker compose exec php php artisan cache:clear
```

### Dừng containers

```bash
docker compose down

# Xóa cả volumes (MySQL data)
docker compose down -v
```

### Rebuild images

```bash
docker compose up -d --build
```

## Cấu trúc thư mục

```
docker/
├── docker-compose.yml      # Docker Compose configuration
├── Dockerfile              # PHP 8.1-FPM image
├── .env.example            # Example environment variables
├── nginx/
│   └── conf.d/
│       └── app.conf        # Nginx configuration
└── mysql/
    └── init/               # Optional: SQL init scripts
```

## Ports

| Service | Port (Container) | Port (Host) | Environment |
|---------|------------------|-------------|-------------|
| Nginx   | 80               | 80          | NGINX_HTTP_PORT |
| Nginx   | 443              | 443         | NGINX_HTTPS_PORT |
| PHP-FPM | 9000             | (internal)  | - |
| MySQL   | 3306             | 3306        | MYSQL_PORT_HOST |

## Troubleshooting

### Lỗi: "Cannot start service php: no matching manifest"

Chắc chắn Docker Desktop/Engine của bạn đã cập nhật.

### Lỗi: "Port already in use"

Thay đổi ports trong `.env`:

```env
NGINX_HTTP_PORT=8080
MYSQL_PORT_HOST=3307
```

### Composer install thất bại

Chạy lại build:

```bash
docker compose down
docker compose up -d --build
```

### Database connection refused

Chờ MySQL khởi động xong (có thể mất vài giây):

```bash
docker compose logs -f mysql
```

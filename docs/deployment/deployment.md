# 🚀 Hướng dẫn Triển khai

## 📋 Yêu cầu Hệ thống

### Development
- **OS:** Linux/macOS hoặc Windows với WSL2
- **Podman:** 4.0+
- **Node.js:** 18.0+ (nếu không dùng container)
- **PHP:** 8.2+ (nếu không dùng container)
- **Composer:** 2.0+

### Production
- **VPS/Server:** Tối thiểu 2 CPU, 4GB RAM
- **Podman:** 4.0+
- **Domain:** Với SSL certificate

---

## 🛠️ Cài đặt Development

### 1. Clone Repository

```bash
git clone <repository-url>
cd exam-management-system
```

### 2. Cấu hình Environment

```bash
# Copy file env
cp docs/deployment/.env.example docs/deployment/.env

# Chỉnh sửa các biến môi trường
nano docs/deployment/.env
```

### 3. Khởi động Containers

```bash
cd docs/deployment

# Khởi động với profile dev (bao gồm phpMyAdmin)
podman-compose --profile dev up -d

# Xem logs
podman-compose logs -f
```

### 4. Cài đặt Dependencies

```bash
# Backend Laravel
podman exec exam_backend composer install
podman exec exam_backend php artisan key:generate
podman exec exam_backend php artisan migrate --seed

# Frontend React (nếu cần)
podman exec exam_frontend npm install
```

### 5. Truy cập

| Service | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| Backend API | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |

---

## 🏭 Triển khai Production

### 1. Chuẩn bị Server

```bash
# Cài đặt Podman
sudo dnf install podman podman-compose -y  # Fedora/RHEL
sudo apt install podman podman-compose -y  # Ubuntu/Debian

# Tạo thư mục
mkdir -p /opt/exam-system
cd /opt/exam-system
```

### 2. Clone và Cấu hình

```bash
# Clone
git clone <repository-url> .

# Cấu hình production
cp docs/deployment/.env.example docs/deployment/.env
nano docs/deployment/.env
```

### Cấu hình `.env` Production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://exam.yourdomain.com

DB_ROOT_PASSWORD=<strong_password>
DB_DATABASE=exam_production
DB_USERNAME=exam_prod_user
DB_PASSWORD=<strong_password>

APP_KEY=base64:...
```

### 3. Khởi động với SSL

```bash
cd docs/deployment

# Copy SSL certificates
mkdir -p nginx/ssl
cp /path/to/fullchain.pem nginx/ssl/
cp /path/to/privkey.pem nginx/ssl/

# Khởi động production
podman-compose --profile prod up -d
```

### 4. Chạy Migrations

```bash
podman exec exam_backend php artisan migrate --force
podman exec exam_backend php artisan config:cache
podman exec exam_backend php artisan route:cache
```

---

## 🔧 Lệnh Podman Thường dùng

```bash
# Xem containers
podman ps

# Xem logs
podman logs exam_backend
podman-compose logs -f

# Truy cập shell
podman exec -it exam_backend bash
podman exec -it exam_mysql bash

# Restart service
podman-compose restart backend

# Stop all
podman-compose down

# Stop và xóa volumes (cẩn thận!)
podman-compose down -v
```

---

## 💾 Backup Database

```bash
# Backup
podman exec exam_mysql mysqldump -u root -p exam_management > backup.sql

# Restore
podman exec -i exam_mysql mysql -u root -p exam_management < backup.sql
```

---

## 🔍 Troubleshooting

### Container không khởi động
```bash
podman-compose logs mysql
podman-compose logs backend
```

### Lỗi kết nối database
```bash
# Kiểm tra MySQL
podman exec exam_mysql mysqladmin -u root -p ping

# Kiểm tra từ backend
podman exec exam_backend php artisan db:show
```

### Clear cache
```bash
podman exec exam_backend php artisan cache:clear
podman exec exam_backend php artisan config:clear
```

---

*Cập nhật: 01/2026*

# Khởi động CloudMusic trên Windows (Laragon)

## 1. Yêu cầu

- PHP 8.2–8.5, khuyến nghị PHP 8.4.
- Composer 2.
- MySQL 8 hoặc MariaDB 10.6+.
- Các extension PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `xml`, `ctype`, `tokenizer`.

Kiểm tra:

```powershell
php -v
composer --version
php -m | findstr /I "pdo_mysql mbstring fileinfo openssl"
```

## 2. Tạo database

Mở HeidiSQL/phpMyAdmin và chạy:

```sql
CREATE DATABASE cloudmusic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Không cần import file SQL để chạy bình thường. Migration là nguồn cấu trúc chính.

## 3. Cài đặt

```powershell
cd cloudmusic_laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Mở `.env`, kiểm tra:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloudmusic
DB_USERNAME=root
DB_PASSWORD=
```

Sau đó chạy:

```powershell
php artisan migrate:fresh --seed
php artisan storage:link
php artisan optimize:clear
php artisan cloudmusic:doctor
php artisan serve
```

Truy cập: `http://127.0.0.1:8000`

## 4. Tài khoản mẫu

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | admin@cloudmusic.test | CloudMusic@123 |
| Nghệ sĩ | artist@cloudmusic.test | CloudMusic@123 |
| Người nghe | listener@cloudmusic.test | CloudMusic@123 |

## 5. Kiểm tra tự động

```powershell
php artisan test
```

## 6. Lỗi thường gặp

### could not find driver

Bật extension trong `php.ini`:

```ini
extension=pdo_mysql
```

Sau đó khởi động lại terminal/Laragon và kiểm tra `php -m`.

### Không hiện ảnh upload

```powershell
php artisan storage:link
```

Nếu link cũ bị lỗi:

```powershell
Remove-Item public\storage -Force
php artisan storage:link
```

### File nhạc không phát hoặc không tua được

- Không chuyển file nhạc sang `public`.
- Kiểm tra file tồn tại trong `storage/app/private/music`.
- Kiểm tra route `/songs/{id}/stream` trả mã `200` hoặc `206`.
- Apache/Nginx không được chặn header `Range`.

### Đổi cấu trúc migration

Trong môi trường phát triển:

```powershell
php artisan migrate:fresh --seed
```

Lệnh này xóa toàn bộ dữ liệu cũ.

# Báo cáo kiểm tra bản bàn giao

Ngày kiểm tra: 26/07/2026.

## Kiểm tra đã thực hiện trong môi trường tạo dự án

- PHP CLI: 8.4.16.
- `php -l`: đạt trên toàn bộ 60 file PHP trong `app`, `bootstrap`, `config`, `database`, `routes` và `tests`.
- `node --check public/js/app.js`: đạt, không có lỗi cú pháp JavaScript.
- Kiểm tra tham chiếu route trong 27 Blade view: không có route name bị thiếu.
- Kiểm tra view được controller gọi: không có file view bị thiếu.
- Kiểm tra asset nội bộ được gọi bằng `asset()`: không có file bị thiếu.
- Kiểm tra 4 file WAV demo: tồn tại và có dữ liệu hợp lệ.
- Kiểm tra tên index migration: đã dùng tên ngắn cho index ghép để tránh lỗi giới hạn identifier MySQL.

## Giới hạn kiểm tra

Môi trường tạo file không có Composer và không truy cập được kho package qua DNS, vì vậy chưa thể chạy `composer install`, `php artisan migrate` và PHPUnit ngay trong môi trường này. Bộ mã nguồn có sẵn test và lệnh chẩn đoán để chạy trên máy đích sau khi cài dependency.

## Kiểm tra bắt buộc trên máy chạy dự án

```powershell
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan cloudmusic:doctor
php artisan test
```

Chỉ khởi động server khi `cloudmusic:doctor` báo `All checks passed` và test không thất bại.

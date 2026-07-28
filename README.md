# CloudMusic Laravel

CloudMusic là website nghe và chia sẻ âm nhạc lấy cảm hứng từ mô hình SoundCloud. Dự án tập trung vào một luồng nghe nhạc hoàn chỉnh: upload private, stream có HTTP Range, player toàn cục, hàng chờ, lịch sử, chống tăng lượt nghe, playlist, tương tác, studio nghệ sĩ và quản trị.

## Chức năng

- Đăng ký listener/artist, đăng nhập có remember và rate limit.
- Trang chủ, tìm kiếm, lọc thể loại, nghệ sĩ và playlist cộng đồng.
- Player cố định: play/pause, seek, volume, next, previous, shuffle, repeat, queue, Media Session.
- Upload audio/cover, album, chỉnh sửa, xóa và kiểm duyệt.
- Like, comment/reply, follow, playlist và lịch sử nghe.
- Profile nghệ sĩ và studio quản lý track.
- Admin dashboard, người dùng, thể loại, bài hát và báo cáo.
- Dữ liệu seed kèm 4 file WAV tổng hợp nguyên bản.

## Khởi động nhanh

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan cloudmusic:doctor
php artisan serve
```

Trên Windows xem chi tiết tại [RUN_WINDOWS.md](RUN_WINDOWS.md).

## Tài liệu

- [Use case](USE_CASES.md)
- [Ghi chú kỹ thuật](NOTES.md)
- [Hướng dẫn Windows](RUN_WINDOWS.md)
- [Schema SQL tham khảo](database/schema/cloudmusic.sql)

## Lưu ý về file SQL

Dự án chạy bằng migration. File SQL chỉ giúp đọc cấu trúc hoặc nhập nhanh khi cần kiểm tra; không nên vừa import SQL vừa chạy migration trên cùng database.

## Tài khoản seed

- Admin: `admin@cloudmusic.test` / `CloudMusic@123`
- Artist: `artist@cloudmusic.test` / `CloudMusic@123`
- Listener: `listener@cloudmusic.test` / `CloudMusic@123`

## Kiểm thử

```bash
php artisan test
```

Không cần `npm install`: giao diện dùng Bootstrap CDN và JavaScript thuần.

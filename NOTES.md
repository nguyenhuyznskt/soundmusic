# Ghi chú kỹ thuật CloudMusic

## Quyết định kiến trúc

- Laravel monolith + Blade giúp dự án dễ chạy trên Laragon và không phụ thuộc npm.
- Bootstrap và Bootstrap Icons được tải qua CDN.
- File ảnh dùng public disk; file âm thanh dùng private local disk.
- Âm thanh chỉ được phục vụ qua `StreamController` để kiểm tra quyền truy cập và hỗ trợ HTTP Range.
- Migration là nguồn dữ liệu chuẩn. `database/schema/cloudmusic.sql` chỉ dùng tham khảo hoặc xem ERD thủ công.

## Quy tắc tính lượt nghe

Frontend chỉ gửi sự kiện sau khi người nghe phát tối thiểu khoảng 10 giây hoặc 30% thời lượng. Backend tiếp tục chống trùng: cùng người dùng/phiên và cùng bài hát chỉ được tính một lượt trong 30 phút.

## Trạng thái bài hát

- `pending`: chờ duyệt.
- `published`: đã công khai.
- `rejected`: bị từ chối, nghệ sĩ có thể chỉnh sửa và gửi lại.
- `blocked`: bị quản trị viên chặn.

Nghệ sĩ tải bài mới sẽ nhận trạng thái `pending`; admin tải bài sẽ được `published`.

## Bảo mật đã áp dụng

- Hash mật khẩu bằng hệ thống hashing của Laravel.
- CSRF cho toàn bộ form và request JavaScript.
- Kiểm tra role bằng middleware.
- Kiểm tra chủ sở hữu bằng policy.
- Validate loại và dung lượng file.
- Không lộ đường dẫn thật của file âm thanh.
- Lưu hash IP thay vì lưu IP thô trong bảng lượt nghe.
- Giới hạn đăng nhập sai và endpoint ghi nhận lượt nghe.

## Điểm nên bổ sung khi triển khai thật

- S3/Cloudflare R2 và CDN cho âm thanh.
- Queue để đọc metadata, tạo waveform và chuyển mã HLS.
- Xác minh email, quên mật khẩu, 2FA.
- Quét virus file upload.
- Hệ thống copyright fingerprint.
- Redis cho cache, rate limit và queue.
- HTTPS, secure cookie, backup database và object storage.
- Chính sách bản quyền/DMCA và quy trình khiếu nại.

## Cấu trúc dữ liệu chính

- `users`: listener, artist, admin.
- `genres`, `albums`, `songs`: catalog âm nhạc.
- `playlists`, `playlist_song`: danh sách phát.
- `song_likes`, `comments`, `follows`: tương tác.
- `listening_histories`, `play_events`: lịch sử và thống kê.
- `reports`: báo cáo vi phạm dạng polymorphic.

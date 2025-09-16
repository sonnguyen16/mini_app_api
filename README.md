# Hệ thống Tích điểm - Quản trị Đa App

Hệ thống tích điểm và quản trị đa app được xây dựng với Laravel 10, Vue 3, Inertia.js và AdminLTE.

## Tính năng chính

- **Quản trị đa tenant** theo app_id với phân quyền chi tiết
- **API mobile** hoàn chỉnh với Laravel Sanctum authentication
- **Trang quản trị web** với AdminLTE + Vue 3 + Inertia.js
- **QR Scanner** để cộng điểm và quét voucher
- **CKEditor** cho rich text editing (vouchers, policies)
- **Tích điểm** và hệ thống đổi voucher
- **Quản lý chính sách** thành viên và bảo mật
- **Upload hình ảnh** cho vouchers và app logos
- **Thống kê** và báo cáo chi tiết

## Công nghệ sử dụng

- **Backend:** PHP 8.1, Laravel 10
- **Auth:** Laravel Sanctum
- **Frontend:** AdminLTE, Bootstrap, Vue 3, Inertia.js
- **Database:** MySQL/MariaDB
- **Permissions:** Spatie Laravel Permission

## Cài đặt

### 1. Clone project và cài đặt dependencies

```bash
git clone <repository-url>
cd miniappapi
composer install
npm install
```

### 2. Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

Cập nhật file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=miniapp_db
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### 3. Chạy migrations và seeders

```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Build frontend

```bash
npm run build
# hoặc cho development
npm run dev
```

## Tài khoản demo

Sau khi chạy seeder, bạn có thể sử dụng các tài khoản sau:

### Admin (Super Admin)
- **Email:** admin@miniapp.com
- **Password:** admin123
- **Quyền:** Quản lý tất cả apps

### App Owner 1 (Coffee Shop)
- **Email:** coffee@owner.com
- **Password:** coffee123
- **App:** Coffee Shop Rewards

### App Owner 2 (Beauty Salon)
- **Email:** beauty@owner.com
- **Password:** beauty123
- **App:** Beauty Salon Points

### End Users
- **Email:** user1@example.com đến user20@example.com
- **Password:** user123

## API Endpoints

### Authentication
```
POST /api/v1/auth/login
POST /api/v1/auth/register
POST /api/v1/auth/logout
```

### Profile
```
GET /api/v1/me
PUT /api/v1/me
```

### Categories
```
GET /api/v1/categories
```

### Vouchers
```
GET /api/v1/vouchers
GET /api/v1/vouchers/latest
POST /api/v1/vouchers/{id}/redeem
POST /api/v1/wallet/{code}/use
GET /api/v1/wallet
GET /api/v1/history
```

### Policies
```
GET /api/v1/policies/membership
GET /api/v1/policies/privacy
```

## Sử dụng API

Tất cả API cần header `X-App-Id` để xác định app:

```bash
curl -H "Authorization: Bearer {token}" \
     -H "X-App-Id: 1" \
     -H "Content-Type: application/json" \
     https://your-domain.com/api/v1/me
```

## Cấu trúc Database

### Bảng chính
- `apps` - Thông tin các app
- `users` - Người dùng hệ thống
- `app_user_profiles` - Profile người dùng theo từng app
- `categories` - Danh mục voucher
- `vouchers` - Voucher/phần thưởng
- `voucher_user_wallet` - Ví voucher của user
- `points_ledger` - Sổ cái điểm
- `transactions` - Lịch sử giao dịch
- `app_policies` - Chính sách của app

## Phân quyền

### Admin
- Quản lý tất cả apps
- Có toàn bộ quyền của App Owner
- Xem dữ liệu cross-app

### App Owner
- Chỉ quản lý app của mình
- CRUD users, categories, vouchers
- Cộng/trừ điểm
- Quản lý chính sách

### End User
- Chỉ sử dụng API mobile
- Xem profile, vouchers
- Đổi và sử dụng voucher

## Development

### Chạy server development
```bash
php artisan serve
npm run dev
```

### Chạy tests
```bash
php artisan test
```

### Tạo migration mới
```bash
php artisan make:migration create_table_name
```

### Tạo model với migration
```bash
php artisan make:model ModelName -m
```

## Production

### Build assets
```bash
npm run build
```

### Optimize Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Lỗi permission denied
```bash
chmod -R 755 storage bootstrap/cache
```

### Lỗi storage link
```bash
php artisan storage:link
```

### Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## License

MIT License

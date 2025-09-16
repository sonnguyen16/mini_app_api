## System

Bạn là một kiến trúc sư & lập trình viên full-stack senior. Hãy tạo một ứng dụng **tích điểm – quản trị đa app** với công nghệ:

- **Backend:** PHP 8.1, **Laravel 10**
- **Auth API:** token (ưu tiên **Laravel Sanctum**; nếu dùng Passport, giải thích & cấu hình tương đương)
- **Frontend admin:** **AdminLTE** + **Bootstrap** + **Vue 3** + **Inertia.js** ( adminlte và bootstrap dùng cdn thay vì npm)
- **WYSIWYG:** CKEditor (hoặc tương đương, nhưng mặc định CKEditor, dùng cdn)
- **DB:** MySQL/MariaDB
- **QR:** trang quản trị có thể **nhập sđt** hoặc **quét QR** (camera web, hoặc máy quét) để cộng điểm/đổi voucher

Tư duy đa tenant theo **app_id**. Tách rõ **roles** và **policies**; mọi truy vấn dữ liệu của “người quản trị app” đều **scope theo app_id**.

## Vai trò & phân quyền

- **Admin (super admin):** Xem & thao tác trên **mọi app**, có toàn bộ tính năng của “người quản trị app”. Trên mọi màn hình cho phép **filter theo app**.
- **Người quản trị app (app_owner):** Chỉ thấy và thao tác dữ liệu thuộc **app_id** họ sở hữu.
- **Người dùng thường (end_user):** Chỉ tương tác qua **API mobile** (đăng nhập token), không có giao diện admin.

Dùng **spatie/laravel-permission** hoặc Policy + Gate bản địa, miễn là:

- Seed 3 roles: `admin`, `app_owner`, `end_user`.
- Mapping quyền chi tiết theo từng module.

## Bảng dữ liệu (đề xuất)

Thiết kế migration, model, factory, seeder, index & ràng buộc:

1. **apps**

- id (PK), name, description, owner_email, owner_password_hash, mini_app_id (nullable), active (bool), timestamps
- Ghi chú: không lưu plain password; seed tạo **tài khoản đăng nhập** cho người sở hữu app (role `app_owner`).

2. **users**

- id (PK), phone (nullable), timestamps
- Quan hệ: nhiều user có thể dùng cùng 1 phone ở **các app khác nhau** (phân biệt ở bảng liên kết dưới).

3. **app_user_profiles** (profile người dùng theo từng app)

- id, **user_id**, **app_id**, name, birthday, gender, address, points_total (int, default 0), active (bool)
- Unique composite (**user_id**, **app_id**)
- Index phone + app_id để tìm nhanh bằng sđt trong app
- Đây là nơi lưu **điểm** của user theo từng app

4. **categories**

- id, **app_id**, name, icon (path hoặc class), active (bool), timestamps

5. **vouchers**

- id, **app_id**, category_id (nullable), name, description (text), image (path), detail (longtext – CKEditor), required_points (int), expire_at (datetime, nullable), usage_condition (longtext – CKEditor), quantity (int, nullable = không giới hạn), active (bool), timestamps
- Index: app_id, category_id, active, expire_at

6. **voucher_user_wallet** (ví voucher của user)

- id, **user_id**, **app_id**, voucher_id, code (unique), status (enum: `redeemed`,`used`,`expired`), redeemed_at, used_at, expire_at (snapshot từ voucher), timestamps
- Index: (user_id, app_id), status, code

7. **points_ledger** (sổ cái điểm)

- id, **user_id**, **app_id**, phone_snapshot, amount (int, + cộng, - trừ), reason (string), ref_type/ref_id (nullable), created_by (admin/app_owner id), timestamps

8. **transactions** (đổi/sử dụng voucher)

- id, **user_id**, **app_id**, voucher_id, type (`redeem`,`use`), status (`success`,`failed`), metadata (json), created_by (nullable – ai thao tác), timestamps

## Chức năng trang quản trị (UI + logic)

### Bộ lọc chung

Mọi grid/list đều có **filter theo keyword**, **active**, **date range**; riêng **Voucher** có thêm filter **category**. Với **Admin**, mọi màn hình có filter theo **app**.

### Admin

- **Quản lý danh sách app:**

  - Trường: tên app, mô tả, tài khoản & mật khẩu **của người sở hữu app** để đăng nhập trang quản trị, active, mini_app_id (nullable).
  - Khi tạo app mới: tự động tạo user `app_owner` (email + password set ở form), gán role `app_owner`, liên kết với app.
  - Có thể **reset password** cho chủ app.

- Có **tất cả** chức năng của Người quản trị app, nhưng scope **mọi app**.

### Người quản trị app

- **Quản trị người dùng thường** (trong phạm vi app_id):

  - Trường hiển thị: họ tên, ngày sinh, giới tính, địa chỉ, **số điện thoại** (có thể trùng ở app khác), **số điểm** đã tích lũy (từ `app_user_profiles.points_total`), **active** (true/false).
  - **App id** chỉ có trong DB, **không hiển thị**.
  - Xem **ví voucher** của user (grid voucher_user_wallet).
  - CRUD profile (chỉ trong app scope).

- **Cộng điểm cho người dùng**:

  - Nhập **số điện thoại** _hoặc_ quét **QR** do người dùng cung cấp → tìm user trong `app_user_profiles` theo (phone, app_id).
  - Nhập **số điểm** (+/-) và **lý do** → ghi vào `points_ledger`, cập nhật `points_total`.
  - Validate: không cho **điểm âm** nếu policy không cho phép; ghi lịch sử.

- **Danh mục (Category)**:

  - Trường: tên, icon, active, app_id (ẩn trong form, lấy theo scope).
  - CRUD, soft delete (tùy chọn).

- **Voucher**:

  - Trường: tên, mô tả ngắn, hình ảnh, chi tiết (CKEditor), **số điểm đổi**, hạn sử dụng, **điều kiện sử dụng** (CKEditor), **danh mục**, active, app_id (ẩn), **số lượng**.
  - Khi **đổi voucher** sẽ trừ quantity nếu có giới hạn.
  - Trang list có filter category, active, keyword, sort theo mới nhất/hạn dùng gần.
  - Preview nội dung chi tiết.

- **Chính sách CT thành viên** (CKEditor, theo app)

  - Lưu ở bảng **app_policies** (id, app_id, type: `membership_policy`, content longtext).

- **Chính sách bảo mật** (CKEditor, theo app)

  - type: `privacy_policy`.

## API (cho mobile – người dùng thường)

Chuẩn REST, trả JSON, xác thực bằng token (Sanctum). Route prefix: `/api/v1`.

- **Auth** (nếu cần): `/auth/login`, `/auth/logout`, `/auth/register` (tùy chọn).
- **Profile**

  - `GET /me` → lấy profile (từ users + app_user_profiles).
  - `PUT /me` → cập nhật: name, birthday, gender, address.

- **Danh mục**

  - `GET /categories?active=1`

- **Voucher**

  - `GET /vouchers?category_id=&keyword=&points_lt=&points_gte=&sort=latest`
  - `GET /vouchers/latest` (mặc định 20 mới nhất trong app)
  - `GET /vouchers/search?name=&points_range=lt100|100_500|gt500` (hoặc query bằng points_lt/points_gte)
  - `POST /vouchers/{id}/redeem` → trừ điểm, phát hành vào ví (code unique, sinh QR code nếu cần)
  - `POST /wallet/{code}/use` → sử dụng voucher (check expire, active, quantity, status)
  - `GET /wallet` → xem ví voucher (owned/redeemed/used/expired)
  - `GET /history` → lịch sử đổi/sử dụng (từ `transactions`)

- **Chính sách**

  - `GET /policies/membership`
  - `GET /policies/privacy`

### Quy tắc API

- Mọi API phải **scope theo app_id** (lấy từ **header `X-App-Id`** hoặc từ token claim).
- Chuẩn hóa response: `{ success: bool, data, error }`.
- Xử lý lỗi, mã lỗi rõ ràng (400/401/403/404/422/429/500).
- Throttling hợp lý.

## Bảo mật & chuẩn code

- Dùng **Sanctum** cho token cá nhân.
- Policies/Gates cho mọi hành động.
- Validate kỹ input (FormRequest).
- Ẩn trường nhạy cảm (password hash, internal IDs không cần thiết).
- CSRF cho phần web/admin; CORS cho API.
- Upload ảnh voucher: lưu storage/app/public, tạo symlink; validate loại file & kích thước.
- Nhật ký audit cơ bản (created_by/updated_by).

## Frontend Admin (AdminLTE + Vue 3 + Inertia)

- Layout tổng: sidebar menu các module (Apps – chỉ admin, Users, Categories, Vouchers, Policies, Points, Transactions).
- Mỗi list có: ô **keyword**, **active**, **date range**, **category** (vouchers), **app** (chỉ admin).
- Form CKEditor cho `detail`, `usage_condition`, `membership_policy`, `privacy_policy`.
- Thành phần **QR scanner** (ví dụ `vue-qrcode-reader` hoặc native getUserMedia) tại màn “Cộng điểm” và “Sử dụng voucher”.
- UX:

  - Bảng có phân trang, sort, bulk actions (active/inactive).
  - Toast thông báo thành công/thất bại.
  - Confirm trước hành động quan trọng (trừ điểm, đổi/sử dụng voucher).

## Seed & Demo

- Tạo seeder:

  - 1 `admin` (email/password)
  - 2 apps mẫu (active), mỗi app có vài `app_owner`.
  - 20 end_user + app_user_profiles ngẫu nhiên (điểm bất kỳ).
  - 5 categories/app.
  - 20 vouchers/app (đủ trường).
  - 30 points_ledger entries.
  - Một số wallet/voucher transactions.

- Tạo **tài khoản đăng nhập trang quản trị** cho chủ app như mô tả.

## Kiểm thử & tài liệu

- Feature tests cho:

  - Scope theo app_id (app_owner không “leak” dữ liệu).
  - Redeem/use voucher (điểm đủ/không đủ, hết hạn, hết số lượng).
  - Cộng điểm bằng sđt và bằng QR.

- Postman collection/OpenAPI YAML cho tất cả endpoints.
- README: hướng dẫn cài đặt, .env mẫu, lệnh artisan, build frontend, chạy queue nếu cần.

## Triển khai

- Cấu hình `.env` đầy đủ: DB, SANCTUM, APP_URL, SESSION, CORS, STORAGE.
- `php artisan storage:link`
- `php artisan migrate --seed`
- Build Vite (Vue + Inertia).
- Gợi ý Dockerfile/Compose (tùy chọn).

## Deliverables mong muốn

1. **Mã nguồn hoàn chỉnh** theo cấu trúc Laravel 10 + Inertia + Vue 3 + AdminLTE.
2. Tập **migrations**, **models**, **policies**, **requests**, **controllers**, **resources**, **routes** (`web.php`, `api.php`).
3. **Seeder** tạo dữ liệu mẫu & tài khoản admin/app_owner.
4. **Thành phần Vue** cho tất cả màn hình quản trị (list + form + filter + QR).
5. **OpenAPI/Swagger** hoặc Postman collection cho API mobile.
6. **README** chi tiết + **script** khởi tạo (migrate/seed/build).

## Gợi ý routes (tham khảo)

- `web.php`: auth admin, dashboard, apps (admin), users, categories, vouchers, policies, points (top-up UI), transactions.
- `api.php` (prefix `/api/v1`): như phần API ở trên. Middleware: `auth:sanctum`, `app.scope` (tự viết) đọc `X-App-Id`.

## Ràng buộc chất lượng

- Code theo PSR-12, đặt tên nhất quán.
- Tách repository/service nơi cần thiết (voucher redemption, points service).
- Tối ưu N+1 (eager loading), index phù hợp.
- Log & xử lý ngoại lệ có ý nghĩa.
- Viết comment ở điểm phức tạp (redeem/use logic, quantity & expire).

---

**Yêu cầu:** Tạo project hoàn chỉnh, chạy được ngay với `php artisan migrate --seed` và build frontend; cung cấp tài khoản demo `admin` và 1-2 `app_owner`.
Khi hoàn thành, xuất kèm hướng dẫn chạy, thông tin đăng nhập demo và file OpenAPI/Postman.

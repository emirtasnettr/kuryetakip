# Kurye Takip Sistemi

Kuryelerin vardiya başlangıç ve bitiş işlemlerinin konum ve fotoğraf ile kayıt altına alındığı operasyon takip sistemi.

## 🚀 Proje Özeti

Bu proje, kuryelerin mobil cihazlarından vardiya başlatıp bitirebildiği, operasyon ekibinin ise web panel üzerinden tüm aktiviteleri takip edebildiği bir sistemdir.

### Temel Özellikler

- ✅ Mobil-first kurye arayüzü (GPS + Fotoğraf)
- ✅ Rol bazlı yetkilendirme (5 farklı rol)
- ✅ İlçe bazlı erişim kontrolü
- ✅ REST API (Laravel Sanctum)
- ✅ Operasyon yönetim paneli
- ✅ Detaylı raporlama

---

## 📦 Teknoloji Stack

- **Backend:** Laravel 10+
- **Authentication:** Laravel Sanctum
- **Database:** MySQL
- **Frontend:** Blade + Tailwind CSS
- **Storage:** Local / S3 uyumlu

---

## 🎭 Roller ve Yetkiler

| Rol | Açıklama | Panel | Mobil |
|-----|----------|-------|-------|
| **Kurye** | Vardiya başlatır/bitirir | ❌ | ✅ |
| **Operasyon Uzmanı** | Yetkili ilçelerdeki kuryeleri görür | ✅ | ❌ |
| **Operasyon Yöneticisi** | Kurye ve vardiya yönetimi | ✅ | ❌ |
| **İş Ortağı** | Kendi kuryelerini görür | ✅ | ❌ |
| **Sistem Yöneticisi** | Tüm yetkiler | ✅ | ❌ |

---

## 🗃️ Veritabanı Şeması

### Tablolar

```
roles                  - Sistem rolleri
├── id, name, display_name, permissions

users                  - Tüm kullanıcılar
├── id, name, email, password
├── role_id → roles
├── partner_id → users (İş ortağı ilişkisi)
├── employee_code, vehicle_type, vehicle_plate

districts              - İlçeler
├── id, name, city, code

courier_districts      - Kurye-İlçe ilişkisi (pivot)
├── user_id → users
├── district_id → districts
├── is_primary, assigned_by

user_districts         - Operasyon yetkili ilçeleri (pivot)
├── user_id → users
├── district_id → districts
├── access_level (view/manage/full)

shifts                 - Vardiyalar
├── id, user_id, district_id, status
├── started_at, start_latitude, start_longitude
├── ended_at, end_latitude, end_longitude
├── package_count, total_minutes

shift_logs             - Vardiya logları
├── id, shift_id, type (start/end)
├── latitude, longitude
├── ip_address, user_agent, device_info

shift_photos           - Vardiya fotoğrafları
├── id, shift_id, type (start/end)
├── filename, path, disk
├── exif_latitude, exif_longitude
```

---

## 🔌 API Endpoints

### Authentication

```
POST   /api/v1/login                  - Giriş
GET    /api/v1/auth/me                - Kullanıcı bilgileri
POST   /api/v1/auth/logout            - Çıkış
PUT    /api/v1/auth/profile           - Profil güncelle
PUT    /api/v1/auth/password          - Şifre değiştir
```

### Shifts (Vardiyalar)

```
GET    /api/v1/shifts                 - Vardiya listesi
GET    /api/v1/shifts/active          - Aktif vardiya
GET    /api/v1/shifts/statistics      - İstatistikler
POST   /api/v1/shifts/start           - Vardiya başlat
POST   /api/v1/shifts/{id}/end        - Vardiya bitir
GET    /api/v1/shifts/{id}            - Vardiya detayı
POST   /api/v1/shifts/{id}/photos     - Fotoğraf yükle
```

### Districts (İlçeler)

```
GET    /api/v1/districts              - İlçe listesi
GET    /api/v1/districts/{id}         - İlçe detayı
```

---

## 📱 Kurye Akışı

### Vardiya Başlatma

```http
POST /api/v1/shifts/start
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "latitude": 41.0082,
  "longitude": 28.9784,
  "district_id": 1,          // Opsiyonel
  "photo": [file],           // Opsiyonel
  "device_id": "xxx",        // Opsiyonel (loglama için)
  "device_model": "iPhone 14"
}
```

### Vardiya Bitirme

```http
POST /api/v1/shifts/{id}/end
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "latitude": 41.0082,
  "longitude": 28.9784,
  "package_count": 45,       // Zorunlu
  "photo": [file],           // Opsiyonel
  "notes": "Sorunsuz tamamlandı"
}
```

---

## 🛠️ Kurulum

### 1. Projeyi Klonla

```bash
git clone <repo-url>
cd papyon
```

### 2. Bağımlılıkları Yükle

```bash
composer install
```

### 3. Ortam Dosyasını Yapılandır

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Veritabanını Ayarla

`.env` dosyasında veritabanı bilgilerini düzenle:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kurye_takip
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migration ve Seed

```bash
php artisan migrate
php artisan db:seed
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Sunucuyu Başlat

```bash
php artisan serve
```

---

## 👥 Test Kullanıcıları

Seeder çalıştırıldıktan sonra aşağıdaki hesaplarla giriş yapabilirsiniz:

| E-posta | Şifre | Rol |
|---------|-------|-----|
| admin@kuryetakip.com | password | Sistem Yöneticisi |
| yonetici@kuryetakip.com | password | Operasyon Yöneticisi |
| uzman@kuryetakip.com | password | Operasyon Uzmanı |
| partner@kuryetakip.com | password | İş Ortağı |
| kurye1@kuryetakip.com | password | Kurye |
| kurye2@kuryetakip.com | password | Kurye |

---

## 🔒 Yetkilendirme Sistemi

### Policy Yapısı

```php
// ShiftPolicy.php
public function start(User $user): bool
{
    return $user->isCourier() && 
           !$user->hasActiveShift() && 
           $user->is_active;
}

public function end(User $user, Shift $shift): bool
{
    return $user->isCourier() && 
           $shift->user_id === $user->id && 
           $shift->isActive();
}
```

### Gate Tanımları

```php
// AuthServiceProvider.php
Gate::define('access-panel', fn(User $user) => $user->canAccessPanel());
Gate::define('access-mobile', fn(User $user) => $user->isCourier() && $user->is_active);
Gate::define('manage-couriers', fn(User $user) => $user->isOperationManager() || $user->isBusinessPartner());
```

---

## 📂 Proje Yapısı

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # API Controllers
│   │   │   ├── AuthController.php
│   │   │   ├── ShiftController.php
│   │   │   └── DistrictController.php
│   │   ├── Courier/       # Kurye Web Controllers
│   │   │   └── MobileController.php
│   │   └── Panel/         # Panel Controllers
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── ShiftController.php
│   │       └── CourierController.php
│   ├── Middleware/
│   └── Requests/
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── District.php
│   ├── Shift.php
│   ├── ShiftLog.php
│   └── ShiftPhoto.php
├── Policies/
│   ├── ShiftPolicy.php
│   ├── UserPolicy.php
│   └── DistrictPolicy.php
└── Providers/

resources/views/
├── layouts/
│   ├── courier.blade.php  # Mobil layout
│   └── panel.blade.php    # Panel layout
├── courier/               # Kurye mobil sayfaları
│   ├── login.blade.php
│   ├── home.blade.php
│   ├── shift-start.blade.php
│   ├── shift-end.blade.php
│   ├── shifts.blade.php
│   └── profile.blade.php
└── panel/                 # Operasyon panel sayfaları
    ├── auth/
    ├── dashboard.blade.php
    ├── shifts/
    └── couriers/
```

---

## 📊 Ekran Görüntüleri

### Kurye Mobil Arayüzü

- **Ana Sayfa:** Aktif vardiya durumu, günlük özet
- **Vardiya Başlat:** GPS + Fotoğraf çekimi
- **Vardiya Bitir:** Konum + Paket sayısı
- **Geçmiş:** Vardiya listesi

### Operasyon Paneli

- **Dashboard:** Anlık istatistikler
- **Aktif Vardiyalar:** Canlı takip
- **Vardiya Listesi:** Filtreleme ve arama
- **Kurye Yönetimi:** CRUD işlemleri
- **Raporlar:** Kurye bazlı performans

---

## 🚀 Production İçin

### 1. Optimizasyon

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Storage Disk (S3)

`.env` dosyasında S3 ayarları:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=your-bucket
```

### 3. Queue

Fotoğraf işleme için queue önerilir:

```env
QUEUE_CONNECTION=redis
```

---

## 📝 Lisans

MIT License

---

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing`)
5. Pull Request açın

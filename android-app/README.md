# Papyon Kurye Android Uygulaması

WebView tabanlı kurye mobil uygulaması.

## Özellikler

- ✅ Tam ekran WebView (tarayıcı gibi değil, uygulama gibi)
- ✅ GPS konum desteği
- ✅ Kamera ile fotoğraf çekme
- ✅ Galeriden fotoğraf seçme
- ✅ Aşağı çekerek yenileme (Pull to refresh)
- ✅ Geri tuşu desteği
- ✅ Siyah tema (status bar, navigation bar)
- ✅ Offline durumda hata gösterimi

## Kurulum

### Android Studio ile

1. Android Studio'yu aç
2. "Open an Existing Project" seç
3. `android-app` klasörünü seç
4. Gradle sync tamamlanana kadar bekle
5. Run > Run 'app' ile çalıştır

### APK Build Etme

```bash
cd android-app
./gradlew assembleRelease
```

APK dosyası: `app/build/outputs/apk/release/app-release-unsigned.apk`

### İmzalı APK (Play Store için)

1. Android Studio > Build > Generate Signed Bundle / APK
2. APK seç
3. Keystore oluştur veya mevcut olanı kullan
4. Release seç
5. Build et

## Yapılandırma

URL'i değiştirmek için `MainActivity.kt` dosyasında:

```kotlin
private const val APP_URL = "https://papyon.iksoft.com.tr/courier/login"
```

## Minimum Gereksinimler

- Android 7.0 (API 24) ve üzeri
- İnternet bağlantısı

## İzinler

- `INTERNET` - Web sayfası yükleme
- `ACCESS_FINE_LOCATION` - GPS konum
- `ACCESS_COARSE_LOCATION` - Yaklaşık konum
- `CAMERA` - Fotoğraf çekme
- `READ_EXTERNAL_STORAGE` - Galeriden fotoğraf seçme

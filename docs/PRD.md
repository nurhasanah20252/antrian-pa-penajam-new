# Product Requirements Document (PRD)

## Sistem Antrian PTSP Digital - Pengadilan Agama Penajam

**Versi:** 1.0  
**Tanggal:** 19 Januari 2026  
**Status:** Draft untuk Review  
**Penulis:** AI Assistant  
**Stakeholder:** Pengadilan Agama Penajam

---

## Daftar Isi

1. [Dokumen Revisi](#dokumen-revisi)
2. [Ringkasan Eksekutif](#ringkasan-eksekutif)
3. [Tujuan & Sasaran](#tujuan--sasaran)
4. [User Personas](#user-personas)
5. [User Stories & Requirements](#user-stories--requirements)
6. [Arsitektur Sistem](#arsitektur-sistem)
7. [Fitur Utama](#fitur-utama)
8. [Spesifikasi Teknis](#spesifikasi-teknis)
9. [Integrasi & Interoperabilitas](#integrasi--interoperabilitas)
10. [Persyaratan Non-Fungsional](#persyaratan-non-fungsional)
11. [Desain UI/UX](#desain-uiux)
12. [Keamanan & Privasi](#keamanan--privasi)
13. [Roadmap Implementasi](#roadmap-implementasi)
14. [Metrik Keberhasilan](#metrik-keberhasilan)
15. [Risiko & Mitigasi](#risiko--mitigasi)

---

## Dokumen Revisi

| Versi | Tanggal     | Deskripsi Perubahan    | Penulis      | Status       |
| ----- | ----------- | ---------------------- | ------------ | ------------ |
| 1.0   | 19 Jan 2026 | Draft awal PRD lengkap | AI Assistant | Untuk Review |

---

## Ringkasan Eksekutif

### Latar Belakang

Pengadilan Agama Penajam membutuhkan sistem antrian digital yang modern untuk meningkatkan efisiensi pelayanan publik. Sistem saat ini masih manual menyebabkan antrian panjang, waktu tunggu tidak pasti, dan kurangnya transparansi dalam pelayanan.

### Solusi yang Diusulkan

Sistem antrian PTSP digital hybrid yang mengintegrasikan:

- **Platform Online**: Website untuk pendaftaran antrian dari rumah
- **Platform Offline**: Mesin kiosk Android untuk pendaftaran di lokasi
- **Sistem Manajemen**: Dashboard real-time untuk petugas dan admin
- **Digital Signage**: Display antrian untuk ruang tunggu

### Nilai Tambah

1. **Efisiensi Operasional** - Pengurangan waktu tunggu 40-60%
2. **Pengalaman Masyarakat** - Transparansi dan kepastian waktu layanan
3. **Data & Analytics** - Insights untuk pengambilan keputusan
4. **Image Institusi** - Modernisasi pelayanan publik

---

## Tujuan & Sasaran

### Tujuan Utama

Membangun sistem antrian digital terintegrasi yang meningkatkan kualitas pelayanan PTSP Pengadilan Agama Penajam.

### Sasaran Bisnis

| Sasaran                           | Metrik                                  | Target        |
| --------------------------------- | --------------------------------------- | ------------- |
| Mengurangi waktu tunggu rata-rata | Waktu dari registrasi ke pelayanan      | < 30 menit    |
| Meningkatkan kepuasan pengguna    | Rating sistem (1-5)                     | ≥ 4.2         |
| Mengoptimalkan utilisasi petugas  | Antrian per petugas per hari            | 40-60 antrian |
| Mengurangi antrian fisik          | Persentase registrasi online            | ≥ 40%         |
| Meningkatkan transparansi         | Persentase pengguna tahu estimasi waktu | 100%          |

### Sasaran Teknis

1. **Availability**: 99.5% uptime selama jam operasional
2. **Performance**: Response time < 2 detik untuk semua operasi
3. **Scalability**: Support hingga 500 antrian per hari
4. **Security**: Compliance dengan regulasi perlindungan data
5. **Usability**: Intuitive interface untuk semua user groups

---

## User Personas

### 1. Masyarakat (Pengguna Layanan)

- **Nama**: Bapak Ahmad (45 tahun)
- **Profil**: Warga yang mengurus proses cerai
- **Kebutuhan**:
    - Mendaftar antrian tanpa datang pagi-pagi
    - Mengetahui estimasi waktu tunggu yang akurat
    - Notifikasi ketika giliran hampir tiba
    - Panduan dokumen yang diperlukan
- **Pain Points**:
    - Antrian panjang tidak pasti
    - Harus datang berulang kali jika dokumen kurang
    - Tidak tahu progress antrian
- **Tech Literacy**: Sedang (bisa menggunakan smartphone)

### 2. Petugas Loket Umum

- **Nama**: Ibu Siti (35 tahun)
- **Profil**: Petugas yang menangani semua layanan kecuali Posbakum & Pembayaran
- **Kebutuhan**:
    - Dashboard antrian real-time
    - Tombol panggil antrian dengan satu klik
    - Catatan internal per antrian
    - Statistik performa harian
- **Pain Points**:
    - Manual calling antrian
    - Tidak tahu estimasi beban kerja
    - Kesulitan tracking antrian yang ditunda
- **Tech Literacy**: Tinggi (terbiasa dengan aplikasi komputer)

### 3. Petugas Posbakum

- **Nama**: Bapak Budi (40 tahun)
- **Profil**: Konsultan hukum khusus
- **Kebutuhan**:
    - Antrian khusus konsultasi hukum
    - Form intake konsultasi
    - Scheduling untuk konsultasi panjang
    - Template dokumentasi kasus
- **Pain Points**:
    - Konsultasi tercampur antrian umum
    - Tidak ada sistem booking untuk konsultasi panjang
- **Tech Literacy**: Tinggi

### 4. Petugas Pembayaran

- **Nama**: Ibu Rina (38 tahun)
- **Profil**: Kasir khusus transaksi keuangan
- **Kebutuhan**:
    - Integrasi dengan sistem akuntansi
    - Receipt printing otomatis
    - Laporan kas harian
    - Validasi pembayaran
- **Pain Points**:
    - Manual entry ke sistem terpisah
    - Rekonsiliasi harian memakan waktu
- **Tech Literacy**: Sedang-Tinggi

### 5. Administrator Sistem

- **Nama**: Bapak Andi (42 tahun)
- **Profil**: Kepala Subbag TU
- **Kebutuhan**:
    - Monitoring semua loket real-time
    - Laporan harian/bulanan
    - Manajemen user & role
    - Konfigurasi sistem
- **Pain Points**:
    - Tidak ada visibility operasional real-time
    - Manual reporting
    - Kesulitan analisis bottleneck
- **Tech Literacy**: Tinggi

---

## User Stories & Requirements

### EPIC 1: Pendaftaran Antrian

#### User Story 1.1 - Pendaftaran Online

**Sebagai** masyarakat  
**Saya ingin** mendaftar antrian dari rumah via website  
**Agar** tidak perlu datang pagi-pagi untuk mengambil nomor

**Acceptance Criteria:**

- [ ] Website responsive di mobile & desktop
- [ ] Form pendaftaran dengan validasi real-time
- [ ] Pilihan layanan: Umum, Posbakum, Pembayaran
- [ ] Upload dokumen pendukung (optional)
- [ ] Konfirmasi via email/SMS
- [ ] QR Code untuk check-in di lokasi

#### User Story 1.2 - Pendaftaran via Kiosk

**Sebagai** masyarakat tanpa akses internet  
**Saya ingin** mengambil nomor antrian via mesin kiosk di lokasi  
**Agar** tetap bisa menggunakan sistem digital

**Acceptance Criteria:**

- [ ] Interface touch-friendly dengan font besar
- [ ] Pilihan layanan dengan icon jelas
- [ ] Input data via virtual keyboard
- [ ] e-KTP scanner integration (optional)
- [ ] Cetak tiket thermal dengan QR Code
- [ ] Multi-language support (ID, EN)

#### User Story 1.3 - Sistem Prioritas

**Sebagai** lansia/disabilitas/ibu hamil  
**Saya ingin** mendapatkan antrian prioritas  
**Agar** tidak perlu antri terlalu lama

**Acceptance Criteria:**

- [ ] Opsi prioritas di form pendaftaran
- [ ] Validasi kategori prioritas
- [ ] Integrasi dengan antrian regular
- [ ] Notifikasi ke petugas untuk prioritas

### EPIC 2: Manajemen Antrian

#### User Story 2.1 - Dashboard Petugas Real-time

**Sebagai** petugas loket  
**Saya ingin** melihat antrian aktif di dashboard  
**Agar** bisa memanggil antrian berikutnya dengan cepat

**Acceptance Criteria:**

- [ ] Display antrian per layanan (Umum, Posbakum, Pembayaran)
- [ ] Status: Menunggu, Dipanggil, Dalam Proses, Selesai
- [ ] Timer durasi pelayanan
- [ ] Tombol "Panggil Berikutnya" dengan satu klik
- [ ] Sound notification untuk panggilan

#### User Story 2.2 - Panggilan Antrian

**Sebagai** petugas loket  
**Saya ingin** memanggil antrian berikutnya  
**Agar** masyarakat tahu gilirannya

**Acceptance Criteria:**

- [ ] Broadcast panggilan ke digital signage
- [ ] Notifikasi SMS/WhatsApp ke pengguna (jika terdaftar)
- [ ] Display di ruang tunggu
- [ ] Voice announcement (optional)
- [ ] Auto-skip jika tidak merespon dalam X menit

#### User Story 2.3 - Manajemen Layanan Khusus

**Sebagai** admin  
**Saya ingin** mengkonfigurasi jenis layanan dan petugas  
**Agar** sistem bisa beradaptasi dengan perubahan struktur

**Acceptance Criteria:**

- [ ] CRUD master layanan
- [ ] Assign petugas ke layanan
- [ ] Setting jam operasional per layanan
- [ ] Kapasitas antrian per layanan
- [ ] Aturan prioritas per layanan

### EPIC 3: Monitoring & Analytics

#### User Story 3.1 - Real-time Monitoring

**Sebagai** admin  
**Saya ingin** memantau operasional real-time  
**Agar** bisa mengambil tindakan cepat jika ada masalah

**Acceptance Criteria:**

- [ ] Dashboard dengan metrics: antrian aktif, waktu tunggu, petugas aktif
- [ ] Alert untuk bottleneck (antrian > 10 orang)
- [ ] Heatmap antrian per jam
- [ ] Live view semua loket
- [ ] Audit log semua aktivitas

#### User Story 3.2 - Reporting & Analytics

**Sebagai** kepala bagian  
**Saya ingin** mendapatkan laporan kinerja  
**Agar** bisa evaluasi dan perbaikan layanan

**Acceptance Criteria:**

- [ ] Laporan harian/bulanan/tahunan
- [ ] Export PDF/Excel
- [ ] Analytics: peak hours, rata-rata waktu layanan, kategori pengguna
- [ ] KPI tracking: kepuasan, efisiensi, utilisasi
- [ ] Predictive analytics untuk resource planning

### EPIC 4: Notifikasi & Komunikasi

#### User Story 4.1 - Notifikasi Multi-channel

**Sebagai** pengguna  
**Saya ingin** mendapatkan notifikasi status antrian  
**Agar** tidak perlu terus-menerus cek posisi

**Acceptance Criteria:**

- [ ] Notifikasi ketika 5 antrian lagi
- [ ] Notifikasi ketika dipanggil
- [ ] Channel: SMS, WhatsApp, Email, In-app
- [ ] Konfigurasi preferensi notifikasi
- [ ] Multi-language notification

#### User Story 4.2 - Broadcast Informasi

**Sebagai** admin  
**Saya ingin** mengirim broadcast informasi  
**Agar** masyarakat tahu update penting

**Acceptance Criteria:**

- [ ] Broadcast ke digital signage
- [ ] Broadcast ke notifikasi pengguna
- [ ] Scheduling broadcast
- [ ] Template pesan
- [ ] Targeting berdasarkan layanan

### EPIC 5: Integrasi & Interoperabilitas

#### User Story 5.1 - Kiosk Android Integration

**Sebagai** operator kiosk  
**Saya ingin** kiosk Android terintegrasi dengan sistem utama  
**Agar** data konsisten dan real-time

**Acceptance Criteria:**

- [ ] REST API untuk kiosk
- [ ] Offline mode dengan sync later
- [ ] Thermal printer integration
- [ ] e-KTP reader integration
- [ ] Auto-recovery jika connection lost

#### User Story 5.2 - Digital Signage Integration

**Sebagai** admin  
**Saya ingin** menampilkan antrian di TV/monitor  
**Agar** masyarakat di ruang tunggu tahu progress

**Acceptance Criteria:**

- [ ] Web-based display untuk TV
- [ ] Auto-refresh setiap 10 detik
- [ ] Customizable layout
- [ ] Show estimasi waktu
- [ ] Emergency broadcast capability

---

## Arsitektur Sistem

### High-Level Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   PUBLIC USERS  │    │    OFFICERS     │    │   ADMIN STAFF   │
│  (Masyarakat)   │    │   (Petugas)     │    │   (Admin)       │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ • Website       │    │ • Dashboard     │    │ • Admin Panel   │
│ • Mobile Web    │    │ • Calling       │    │ • Analytics     │
│ • Kiosk Android │    │ • Management    │    │ • Configuration │
└────────┬────────┘    └────────┬────────┘    └────────┬────────┘
         │                      │                      │
         └──────────────────────┼──────────────────────┘
                                │
                    ┌───────────▼───────────┐
                    │   API GATEWAY &       │
                    │   AUTHENTICATION      │
                    │  (Laravel + Fortify)  │
                    └───────────┬───────────┘
                                │
                    ┌───────────▼───────────┐
                    │   BUSINESS LOGIC      │
                    │   (Laravel App)       │
                    │  • Queue Management   │
                    │  • Notification       │
                    │  • Reporting          │
                    └───────────┬───────────┘
                                │
                    ┌───────────▼───────────┐
                    │   DATA LAYER          │
                    │  • PostgreSQL/MySQL   │
                    │  • Redis (Cache/Queue)│
                    │  • File Storage       │
                    └───────────┬───────────┘
                                │
                    ┌───────────▼───────────┐
                    │   EXTERNAL SERVICES   │
                    │  • WhatsApp API       │
                    │  • SMS Gateway        │
                    │  • Email Service      │
                    └───────────────────────┘
```

### Komponen Sistem

#### 1. Frontend Layer

- **Public Website**: Inertia.js + React + TypeScript
- **Officer Dashboard**: Inertia.js + React + TailwindCSS
- **Admin Panel**: Inertia.js + React + AdminLTE
- **Kiosk App**: Android Native/Kotlin dengan WebView
- **Digital Signage**: Web-based display untuk TV

#### 2. Backend Layer

- **Core Application**: Laravel 12 (PHP 8.2+)
- **API Gateway**: Laravel Sanctum untuk authentication
- **Real-time Server**: Laravel Echo + WebSocket (Pusher/Soketi)
- **Queue Worker**: Laravel Horizon dengan Redis
- **File Storage**: Local/Cloud storage untuk dokumen

#### 3. Data Layer

- **Primary Database**: PostgreSQL/MySQL
- **Cache & Session**: Redis
- **File Storage**: Local disk atau cloud (S3 compatible)
- **Backup System**: Automated daily backup

#### 4. Integration Layer

- **Notification Service**: WhatsApp Business API, SMS Gateway
- **Printer Service**: Thermal printer via network/USB
- **Kiosk Service**: REST API untuk Android devices
- **Signage Service**: WebSocket untuk real-time updates

### Data Flow Diagram

```
1. REGISTRATION FLOW:
   User → Website/Kiosk → API → Queue Service → Database → Notification Service → User

2. CALLING FLOW:
   Officer → Dashboard → Calling Service → WebSocket → Digital Signage → User Notification

3. MONITORING FLOW:
   System → Metrics Collector → Analytics Engine → Dashboard → Admin

4. SYNC FLOW (Kiosk):
   Kiosk → Local DB → Sync Service → Cloud API → Central DB
```

---

## Fitur Utama

### A. Untuk Masyarakat (Publik)

#### 1. Pendaftaran Antrian Hybrid

- **Online Registration**: Via website responsive
- **Kiosk Registration**: Via mesin Android touchscreen
- **Multi-language**: Indonesia & English
- **Document Upload**: Upload dokumen prasyarat
- **QR Code System**: Untuk check-in dan tracking

#### 2. Antrian Tracking Real-time

- **Live Position**: Cek posisi antrian real-time
- **Estimated Time**: Estimasi waktu tunggu berdasarkan data historis
- **Notification**: SMS/WhatsApp/Email notifikasi
- **History**: Riwayat antrian pribadi

#### 3. Informasi & Panduan

- **Service Catalog**: Detail semua layanan PTSP
- **Document Checklist**: Checklist dokumen per layanan
- **FAQ Section**: Pertanyaan umum dan jawaban
- **Contact Information**: Kontak pengadilan

### B. Untuk Petugas

#### 1. Dashboard Real-time

- **Queue Overview**: Tampilan semua antrian aktif
- **Service-specific View**: Filter berdasarkan layanan
- **Calling Interface**: Tombol panggil dengan satu klik
- **Status Management**: Update status antrian (Proses, Selesai, Tunda)

#### 2. Productivity Tools

- **Quick Notes**: Catatan internal per antrian
- **Document Templates**: Template untuk layanan umum
- **Keyboard Shortcuts**: Shortcut untuk aksi cepat
- **Batch Operations**: Proses multiple antrian sekaligus

#### 3. Service Management

- **Service Switching**: Ganti mode layanan (Umum/Posbakum/Pembayaran)
- **Break Management**: Mode istirahat dengan auto-queue hold
- **Transfer Queue**: Transfer antrian ke petugas lain
- **Priority Handling**: Penanganan antrian prioritas

### C. Untuk Admin

#### 1. System Configuration

- **Service Management**: CRUD jenis layanan
- **Officer Management**: Assign petugas ke layanan
- **Schedule Management**: Atur jam operasional
- **Notification Settings**: Konfigurasi notifikasi

#### 2. Monitoring & Analytics

- **Real-time Dashboard**: Live metrics semua loket
- **Performance Analytics**: Statistik petugas dan layanan
- **Bottleneck Detection**: Alert untuk antrian panjang
- **Audit Trail**: Log semua aktivitas sistem

#### 3. Reporting

- **Daily Reports**: Laporan harian operasional
- **Monthly Analytics**: Trend dan analisis bulanan
- **Export Capability**: Export ke PDF, Excel, CSV
- **Custom Reports**: Report builder untuk kebutuhan khusus

### D. Sistem Pendukung

#### 1. Digital Signage

- **Queue Display**: Tampilan antrian di ruang tunggu
- **Information Board**: Informasi penting dan pengumuman
- **Countdown Timer**: Estimasi waktu untuk antrian aktif
- **Emergency Broadcast**: Pesan darurat ke display

#### 2. Kiosk System

- **Touch Interface**: UI optimized untuk touchscreen
- **Thermal Printing**: Cetak tiket dengan QR Code
- **Offline Mode**: Operasi tanpa internet dengan sync later
- **Maintenance Mode**: Mode maintenance untuk update

#### 3. Notification System

- **Multi-channel**: SMS, WhatsApp, Email, In-app
- **Smart Timing**: Notifikasi berdasarkan estimasi waktu
- **Template System**: Template pesan yang bisa dikustomisasi
- **Delivery Tracking**: Tracking status pengiriman notifikasi

---

## Spesifikasi Teknis

### Stack Teknologi

#### Backend

- **Framework**: Laravel 12 (PHP 8.2+)
- **API Authentication**: Laravel Sanctum
- **Real-time**: Laravel Echo + WebSockets (Pusher/Soketi)
- **Queue Management**: Laravel Horizon + Redis
- **File Storage**: Laravel Filesystem
- **PDF Generation**: DomPDF/Laravel Snappy

#### Frontend

- **Framework**: Inertia.js v2 + React 18
- **Language**: TypeScript 5.x
- **Styling**: TailwindCSS 3.x
- **State Management**: React Context/ Zustand
- **Forms**: Inertia.js useForm
- **Charts**: Recharts/Chart.js

#### Database

- **Primary**: PostgreSQL 15+ / MySQL 8.0+
- **Cache**: Redis 7.x
- **Search**: Laravel Scout (optional)
- **Migrations**: Laravel Migration System

#### Infrastructure

- **Web Server**: Nginx/Apache
- **PHP Runtime**: PHP-FPM 8.2+
- **OS**: Ubuntu 22.04 LTS / CentOS 8
- **Containerization**: Docker (optional)
- **CI/CD**: GitHub Actions

### API Specifications

#### Public API Endpoints

```
GET    /api/v1/services          # List layanan aktif
POST   /api/v1/queues           # Create antrian baru
GET    /api/v1/queues/{id}      # Get antrian detail
GET    /api/v1/queues/{id}/status # Cek status antrian
POST   /api/v1/notifications/subscribe # Subscribe notifikasi
```

#### Officer API Endpoints

```
GET    /api/v1/officer/queues           # List antrian aktif
POST   /api/v1/officer/queues/{id}/call # Panggil antrian
PUT    /api/v1/officer/queues/{id}/status # Update status
GET    /api/v1/officer/stats            # Statistik petugas
```

#### Kiosk API Endpoints

```
POST   /api/v1/kiosk/queues            # Create antrian dari kiosk
GET    /api/v1/kiosk/services          # List layanan untuk kiosk
POST   /api/v1/kiosk/print/test        # Test printer
GET    /api/v1/kiosk/sync              # Sync offline data
```

### Database Schema

#### Core Tables

```sql
-- Tabel utama antrian
CREATE TABLE queues (
    id BIGINT PRIMARY KEY,
    number VARCHAR(20) NOT NULL, -- Format: A-001, P-001, B-001
    service_id BIGINT NOT NULL,
    user_id BIGINT NULL, -- Jika registered user
    officer_id BIGINT NULL, -- Petugas yang menangani
    name VARCHAR(255) NOT NULL,
    nik VARCHAR(16) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    priority BOOLEAN DEFAULT FALSE,
    status ENUM('waiting', 'called', 'processing', 'completed', 'skipped', 'cancelled'),
    estimated_time INTEGER NULL, -- Dalam menit
    called_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_service_status (service_id, status),
    INDEX idx_created (created_at)
);

-- Tabel layanan
CREATE TABLE services (
    id BIGINT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL, -- UMUM, POSBAKUM, BAYAR
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    average_time INTEGER DEFAULT 15, -- Rata-rata waktu layanan (menit)
    max_daily_queue INTEGER DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    requires_documents BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel petugas
CREATE TABLE officers (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL, -- Link ke users table
    service_id BIGINT NULL, -- NULL jika bisa handle semua
    current_queue_id BIGINT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    max_concurrent INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Supporting Tables

- `queue_documents` - Dokumen pendukung antrian
- `queue_notes` - Catatan internal per antrian
- `queue_transfers` - History transfer antrian
- `notifications` - Log notifikasi
- `service_schedules` - Jadwal layanan
- `kiosk_devices` - Device kiosk terdaftar
- `audit_logs` - Log semua aktivitas

### Performance Requirements

#### Response Time

- **Public Pages**: < 2 detik (90th percentile)
- **Officer Dashboard**: < 1 detik untuk operasi utama
- **API Responses**: < 500ms (95th percentile)
- **Real-time Updates**: < 100ms untuk WebSocket events

#### Throughput

- **Concurrent Users**: Support hingga 100 user simultan
- **Queue Creation**: 50-100 antrian per jam peak
- **Notifications**: 200+ notifikasi per jam
- **Data Export**: Export 10,000 records dalam < 30 detik

#### Availability

- **Uptime**: 99.5% selama jam operasional (08:00-16:00)
- **Maintenance Window**: Di luar jam operasional
- **Recovery Time**: < 15 menit untuk minor issues
- **Backup Recovery**: < 2 jam untuk full recovery

---

## Integrasi & Interoperabilitas

### 1. Kiosk Android Integration

#### Technical Approach

- **Communication**: REST API dengan authentication token
- **Data Sync**: Periodic sync dengan conflict resolution
- **Offline Mode**: Local SQLite database dengan sync queue
- **Printer**: Network thermal printer via TCP/IP

#### Kiosk App Requirements

```kotlin
// Sample Android App Structure
class KioskActivity : AppCompatActivity() {
    // Features:
    // - Touch-friendly UI dengan besar font
    // - Virtual keyboard custom
    // - e-KTP scanner integration
    // - Thermal printer service
    // - Offline data storage
    // - Auto-sync ketika online
}
```

#### Sync Mechanism

```
[Kiosk] → [Local SQLite] → [Sync Service] → [Cloud API] → [Central DB]
      Offline Mode            When Online        HTTPS         PostgreSQL
```

### 2. Thermal Printer Integration

#### Printer Specifications

- **Type**: Thermal printer 58mm/80mm
- **Connection**: Network (TCP/IP) atau USB
- **Paper**: Thermal paper roll with auto-cutter
- **Brand**: Epson, Star Micronics, Citizen

#### Printing Service

```php
class ThermalPrinterService
{
    public function printTicket(Queue $queue): bool
    {
        // Generate ticket content
        $content = $this->generateTicketContent($queue);

        // Send to printer via network
        $result = $this->networkPrint($content);

        // Log printing activity
        $this->logPrintActivity($queue, $result);

        return $result;
    }
}
```

#### Ticket Format

```
╔══════════════════════════════════╗
║     PENGADILAN AGAMA PENAJAM     ║
║        SISTEM ANTRIAN DIGITAL    ║
╠══════════════════════════════════╣
║ NOMOR    : A-025                 ║
║ LAYANAN  : UMUM                  ║
║ TANGGAL  : 19 Jan 2026 09:30     ║
║ ESTIMASI : 30-45 menit           ║
║ SAAT INI : A-018                 ║
╠══════════════════════════════════╣
║           [QR CODE]              ║
║    Scan untuk cek posisi         ║
╚══════════════════════════════════╝
```

### 3. WhatsApp Notification Integration

#### Implementation Options

1. **WhatsApp Business API** (Official - Recommended)
    - Cost: ~$0.01-0.05 per message
    - Features: Templates, analytics, webhook
    - Requirements: Business verification

2. **Third-party Gateway** (Alternative)
    - Providers: Twilio, MessageBird, etc.
    - Cost: Similar to official API
    - Easier setup

3. **WhatsApp Web Automation** (Not recommended)
    - Risk: Account banning
    - Unreliable for production

#### Notification Flow

```
[System] → [Notification Service] → [WhatsApp API] → [User WhatsApp]
      ↓           ↓                      ↓
  [Database]  [Logging]           [Delivery Status]
```

### 4. Digital Signage Integration

#### Display Technology

- **Hardware**: TV/Monitor dengan Raspberry Pi/Android TV Box
- **Software**: Web-based display dengan auto-refresh
- **Content**: Real-time queue data + informational content

#### Signage Application

```html
<!-- Digital Signage Web App -->
<div class="signage-display">
    <div class="header">PENGADILAN AGAMA PENAJAM</div>
    <div class="current-queues">
        <!-- Real-time queue data via WebSocket -->
    </div>
    <div class="information">
        <!-- Rotating informational messages -->
    </div>
</div>
```

### 5. Potential Future Integrations

#### Court Management System

- **Integration Type**: Bidirectional sync
- **Data Points**: Case numbers, party information
- **Benefits**: Single source of truth

#### Payment Gateway

- **Providers**: Bank transfer, e-wallet, QRIS
- **Use Case**: Online payment for court fees
- **Requirements**: PCI DSS compliance

#### Document Management System

- **Integration**: Automatic document indexing
- **Workflow**: Queue → Service → Document upload → Archiving
- **Benefits**: End-to-end digital workflow

---

## Persyaratan Non-Fungsional

### 1. Performance

#### Load Handling

- **Concurrent Users**: 100+ users simultan
- **Daily Transactions**: 500+ antrian per hari
- **Peak Hour Capacity**: 100 antrian per jam
- **Data Volume**: 2+ years data retention online

#### Response Time SLAs

- **Page Load**: < 3 seconds (first contentful paint)
- **API Response**: < 500ms (95th percentile)
- **Search Operations**: < 2 seconds for 10k records
- **Report Generation**: < 30 seconds for daily reports

### 2. Scalability

#### Vertical Scaling

- **Database**: Read replicas for reporting
- **Application**: Multiple app servers with load balancer
- **Cache**: Redis cluster for session and cache
- **Queue**: Multiple queue workers

#### Horizontal Scaling

- **Stateless Design**: All servers stateless
- **Session Management**: Centralized session storage
- **File Storage**: Cloud storage with CDN
- **Database Sharding**: By service type or date

### 3. Reliability

#### Availability Targets

- **Production**: 99.5% uptime during business hours
- **Maintenance**: Scheduled outside 08:00-16:00
- **Backup**: Daily automated backups with 30-day retention
- **Recovery**: RTO < 4 hours, RPO < 15 minutes

#### Monitoring & Alerting

- **Application Monitoring**: Laravel Telescope + Logging
- **Infrastructure Monitoring**: Server metrics, disk space
- **Business Monitoring**: Queue length, wait times
- **Alert Channels**: Email, SMS, Telegram

### 4. Security

#### Authentication & Authorization

- **Multi-factor Auth**: Optional for admin users
- **Role-based Access**: Granular permissions
- **Session Management**: Secure session handling
- **Password Policy**: Minimum 8 chars, complexity requirements

#### Data Protection

- **Encryption**: TLS 1.3 for data in transit
- **Data at Rest**: Encryption for sensitive data
- **PII Protection**: Masking for display, encryption for storage
- **Audit Trail**: Comprehensive logging of all data access

#### Compliance

- **Data Privacy**: Compliance with Indonesian data protection laws
- **Record Keeping**: 5+ years retention for transaction records
- **Access Logs**: 1+ year retention for audit purposes
- **Backup Compliance**: Regular backup testing and verification

### 5. Usability

#### Accessibility

- **WCAG 2.1**: Level AA compliance for public interfaces
- **Screen Reader**: Compatibility with major screen readers
- **Keyboard Navigation**: Full keyboard accessibility
- **Color Contrast**: Minimum 4.5:1 ratio for text

#### Localization

- **Languages**: Indonesian (primary), English (secondary)
- **Date/Time**: Indonesian timezone and format
- **Currency**: Indonesian Rupiah formatting
- **Cultural Considerations**: Local naming conventions

### 6. Maintainability

#### Code Quality

- **Testing Coverage**: 80%+ test coverage for critical paths
- **Code Standards**: PSR-12 compliance, Laravel Pint
- **Documentation**: API docs, deployment guides, runbooks
- **Dependency Management**: Regular security updates

#### Operational Excellence

- **Deployment Automation**: CI/CD pipeline
- **Configuration Management**: Environment-based configuration
- **Logging Standardization**: Structured logging with context
- **Health Checks**: Comprehensive health check endpoints

---

## Desain UI/UX

### Design Principles

#### 1. User-Centered Design

- **Simplicity**: Minimal steps to complete tasks
- **Consistency**: Uniform patterns across all interfaces
- **Feedback**: Clear feedback for all user actions
- **Forgiveness**: Easy recovery from errors

#### 2. Accessibility First

- **Color Palette**: High contrast for readability
- **Typography**: Legible font sizes (min 16px for mobile)
- **Navigation**: Clear hierarchy and breadcrumbs
- **Interactive Elements**: Adequate touch targets (min 44px)

### Public Interface Design

#### Homepage Layout

```
┌─────────────────────────────────────┐
│  Header: Logo, Language Selector    │
├─────────────────────────────────────┤
│  Hero Section:                      │
│  • Main CTA: "Ambil Antrian"       │
│  • Secondary: "Cek Antrian"        │
│  • Tertiary: "Info Layanan"        │
├─────────────────────────────────────┤
│  Service Categories:                │
│  [UMUM] [POSBAKUM] [PEMBAYARAN]    │
├─────────────────────────────────────┤
│  Information Section:               │
│  • Jam Operasional                 │
│  • Dokumen yang diperlukan         │
│  • FAQ                             │
└─────────────────────────────────────┘
```

#### Queue Registration Flow

```
1. SERVICE SELECTION → 2. FORM FILLING → 3. DOCUMENT UPLOAD →
4. CONFIRMATION → 5. TICKET GENERATION → 6. NOTIFICATION SETUP
```

#### Mobile-First Design Considerations

- **Touch Targets**: Minimum 44x44px
- **Form Design**: Single column layout
- **Progressive Enhancement**: Core functionality works on all devices
- **Offline Support**: Basic functionality without internet

### Officer Dashboard Design

#### Main Dashboard Layout

```
┌─────────────────────────────────────────────┐
│  Header: User Info, Logout, Notifications  │
├─────────────────────────────────────────────┤
│  Left Panel:                                │
│  • Active Queue List                        │
│  • Statistics Card                          │
│  • Quick Actions                           │
├─────────────────────────────────────────────┤
│  Main Panel:                                │
│  • Current Queue Details                   │
│  • Calling Controls                        │
│  • Notes Section                           │
└─────────────────────────────────────────────┘
```

#### Calling Interface

- **Large Call Button**: Prominent "Panggil Berikutnya" button
- **Queue Details**: Clear display of current queue information
- **Status Controls**: Easy status update buttons
- **Quick Notes**: Fast note-taking for common scenarios

### Admin Panel Design

#### Dashboard Layout

```
┌─────────────────────────────────────────────┐
│  Top Navigation: Modules, User, Settings   │
├─────────────────────────────────────────────┤
│  Sidebar: Module Navigation                │
├─────────────────────────────────────────────┤
│  Main Content:                             │
│  • Real-time Metrics Dashboard             │
│  • Alert Panel                             │
│  • Quick Access Tools                      │
└─────────────────────────────────────────────┘
```

### Kiosk Interface Design

#### Touch-Optimized Design

- **Full-screen Mode**: No browser chrome
- **Large Elements**: Big buttons and text
- **Minimal Input**: Reduce typing with smart defaults
- **Clear Progression**: Step-by-step with progress indicator

#### Kiosk Flow

```
WELCOME → LANGUAGE SELECT → SERVICE SELECT → DATA ENTRY →
CONFIRMATION → PRINTING → THANK YOU → RETURN TO START
```

### Design System Components

#### Color Palette

- **Primary**: Blue (#2563eb) - Trust, professionalism
- **Secondary**: Green (#10b981) - Success, completion
- **Accent**: Amber (#f59e0b) - Warning, attention
- **Neutral**: Gray (#6b7280) - Text, borders

#### Typography

- **Primary Font**: Inter (Google Fonts) - Modern, readable
- **Heading Scale**: 1.5rem, 2rem, 2.5rem, 3rem
- **Body Text**: 1rem (16px) with 1.5 line height
- **Monospace**: Roboto Mono for codes and numbers

#### Spacing System

- **Base Unit**: 4px (0.25rem)
- **Scale**: 4, 8, 12, 16, 24, 32, 48, 64, 96, 128
- **Container Widths**: 320px (mobile), 768px (tablet), 1024px (desktop)

#### Component Library

- **Buttons**: Primary, Secondary, Danger, Success variants
- **Forms**: Input, Select, Checkbox, Radio with validation states
- **Cards**: Metric cards, queue cards, information cards
- **Tables**: Sortable, paginated, with action buttons
- **Modals**: Confirmation, information, form modals

### Prototype & Testing Plan

#### Wireframing Phase

1. **Low-fidelity**: Paper sketches for core flows
2. **Mid-fidelity**: Digital wireframes with basic layout
3. **High-fidelity**: Interactive prototypes with real content

#### User Testing

- **Methodology**: Usability testing with 5-8 participants per user group
- **Metrics**: Task completion rate, time on task, error rate
- **Feedback**: Qualitative feedback through think-aloud protocol
- **Iteration**: Minimum 2 design iterations based on feedback

#### Accessibility Testing

- **Automated**: axe-core for automated accessibility checks
- **Manual**: Screen reader testing (NVDA, VoiceOver)
- **Color Contrast**: Color contrast analyzer
- **Keyboard Navigation**: Full keyboard operability test

---

## Keamanan & Privasi

### Security Architecture

#### Defense in Depth Strategy

```
Layer 1: Network Security (Firewall, DDoS protection)
Layer 2: Application Security (Authentication, Authorization)
Layer 3: Data Security (Encryption, Masking)
Layer 4: Audit & Monitoring (Logging, Alerting)
```

#### Authentication System

- **Multi-factor Authentication**: Optional for admin users
- **Session Management**: Secure session handling with rotation
- **Password Policy**: Minimum 8 characters with complexity
- **Account Lockout**: Temporary lock after failed attempts

### Data Protection Measures

#### Sensitive Data Handling

```php
// Example: Data encryption in Laravel
class Queue extends Model
{
    protected function casts(): array
    {
        return [
            'nik' => 'encrypted', // Automatic encryption
            'phone' => 'encrypted',
            'email' => 'encrypted',
        ];
    }

    // Accessor for masked display
    public function getMaskedNikAttribute(): string
    {
        return maskString($this->nik, 4, 4);
    }
}
```

#### Data Classification

- **Public**: Service information, queue numbers
- **Internal**: Officer names, queue statistics
- **Confidential**: Personal data (NIK, phone, email)
- **Restricted**: System credentials, API keys

#### Data Retention Policy

- **Queue Data**: 2 years online, then archive
- **Audit Logs**: 1 year online, 5 years archived
- **Backup Data**: 30 days rolling backup
- **PII Data**: Automatic anonymization after retention period

### Compliance Requirements

#### Indonesian Regulations

- **UU ITE**: Compliance with electronic transaction laws
- **Kemenkominfo**: Data protection requirements
- **Peraturan Pengadilan**: Internal court regulations
- **Archive Laws**: Document retention requirements

#### International Standards

- **ISO 27001**: Information security management
- **GDPR Principles**: Privacy by design and default
- **OWASP Top 10**: Protection against common web vulnerabilities

### Security Monitoring

#### Real-time Monitoring

- **Intrusion Detection**: Log analysis for suspicious patterns
- **Anomaly Detection**: Unusual access patterns or data exports
- **Compliance Monitoring**: Regular security compliance checks
- **Vulnerability Scanning**: Automated vulnerability assessment

#### Incident Response

- **Response Team**: Designated security response team
- **Communication Plan**: Stakeholder communication during incidents
- **Recovery Procedures**: Documented recovery steps
- **Post-mortem Analysis**: Learning from security incidents

### Privacy by Design

#### Data Minimization

- **Collect Only Necessary**: Only collect data needed for service
- **Purpose Limitation**: Clear purpose for each data element
- **Storage Limitation**: Data deletion after retention period
- **Accuracy**: Mechanisms to keep data accurate

#### User Rights

- **Access**: Users can access their data
- **Correction**: Users can correct inaccurate data
- **Deletion**: Users can request data deletion (where applicable)
- **Portability**: Data export in standard format

---

## Roadmap Implementasi

### Phase 1: Foundation & MVP (Bulan 1-2)

#### Sprint 1: Project Setup & Core Architecture

- [ ] Environment setup (Dev, Staging, Production)
- [ ] Database schema design and implementation
- [ ] Laravel project setup with Inertia.js
- [ ] Authentication system (Fortify integration)
- [ ] Basic admin panel structure

#### Sprint 2: Queue Management Core

- [ ] Queue model and CRUD operations
- [ ] Service management (Umum, Posbakum, Pembayaran)
- [ ] Officer management and assignment
- [ ] Basic queue status workflow

#### Sprint 3: Public Interface

- [ ] Public website for queue registration
- [ ] Queue status checking interface
- [ ] Service information pages
- [ ] Responsive design implementation

#### Sprint 4: Officer Dashboard MVP

- [ ] Real-time queue display
- [ ] Basic calling functionality
- [ ] Queue status management
- [ ] Officer authentication and session management

### Phase 2: Enhanced Features (Bulan 3-4)

#### Sprint 5: Kiosk Integration

- [ ] Kiosk API development
- [ ] Thermal printer integration
- [ ] Kiosk web interface optimization
- [ ] Offline mode with sync capability

#### Sprint 6: Notification System

- [ ] Multi-channel notification system
- [ ] WhatsApp Business API integration
- [ ] SMS gateway integration
- [ ] Notification templates and scheduling

#### Sprint 7: Digital Signage

- [ ] Digital signage web application
- [ ] Real-time updates via WebSocket
- [ ] Content management for displays
- [ ] Emergency broadcast functionality

#### Sprint 8: Advanced Features

- [ ] Priority queue system
- [ ] Queue transfer functionality
- [ ] Break management for officers
- [ ] Basic reporting and analytics

### Phase 3: Polish & Scale (Bulan 5-6)

#### Sprint 9: Admin & Management

- [ ] Advanced admin panel
- [ ] Comprehensive reporting system
- [ ] User management and permissions
- [ ] System configuration interface

#### Sprint 10: Performance & Security

- [ ] Performance optimization
- [ ] Security hardening
- [ ] Backup and recovery system
- [ ] Monitoring and alerting setup

#### Sprint 11: User Experience

- [ ] User testing and feedback incorporation
- [ ] Accessibility improvements
- [ ] Mobile experience optimization
- [ ] Documentation and training materials

#### Sprint 12: Deployment & Training

- [ ] Production deployment
- [ ] Staff training program
- [ ] User acceptance testing
- [ ] Launch preparation and communication

### Phase 4: Post-Launch & Enhancement

#### Month 7: Stabilization

- Bug fixes and performance tuning
- User feedback collection and analysis
- System monitoring and optimization

#### Month 8: Feature Enhancements

- Additional reporting features
- Integration with court management system
- Mobile app development (if needed)

#### Month 9+: Continuous Improvement

- Regular feature updates
- Security updates and compliance
- Performance monitoring and scaling

### Success Criteria per Phase

#### Phase 1 Completion

- [ ] Basic queue system operational
- [ ] Public registration working
- [ ] Officer dashboard functional
- [ ] Core data model implemented

#### Phase 2 Completion

- [ ] Kiosk system integrated
- [ ] Notification system working
- [ ] Digital signage operational
- [ ] All core features implemented

#### Phase 3 Completion

- [ ] System ready for production
- [ ] Performance and security validated
- [ ] Training materials complete
- [ ] UAT passed successfully

---

## Metrik Keberhasilan

### Operational Metrics

#### Queue Efficiency

- **Average Wait Time**: Target < 30 minutes
- **Queue Length**: Maximum 10 people per service
- **Service Time**: Average 15-20 minutes per queue
- **Queue Completion Rate**: > 95% of queues completed

#### System Usage

- **Online Registration Rate**: > 40% of total queues
- **Kiosk Usage Rate**: > 50% of walk-in queues
- **Notification Opt-in**: > 70% of users
- **User Return Rate**: > 60% for repeat users

### User Satisfaction Metrics

#### Quantitative Measures

- **System Usability Scale (SUS)**: Target > 75
- **Net Promoter Score (NPS)**: Target > 50
- **Customer Satisfaction (CSAT)**: Target > 4.2/5
- **Task Completion Rate**: > 90% for key tasks

#### Qualitative Measures

- User feedback analysis
- Support ticket analysis
- Focus group discussions
- Suggestion box implementation

### Technical Metrics

#### Performance

- **API Response Time**: < 500ms (95th percentile)
- **System Uptime**: > 99.5% during business hours
- **Error Rate**: < 0.1% of total requests
- **Page Load Time**: < 3 seconds (first contentful paint)

#### Security

- **Vulnerability Scan Results**: Zero critical vulnerabilities
- **Security Incident Count**: Zero data breaches
- **Compliance Audit Results**: 100% compliance
- **Backup Success Rate**: 100% backup completion

### Business Impact Metrics

#### Efficiency Gains

- **Staff Productivity**: 20-30% improvement
- **Queue Processing Time**: 30-40% reduction
- **Paper Usage Reduction**: 80-90% reduction
- **Customer Service Capacity**: 20-25% increase

#### Cost Savings

- **Operational Cost Reduction**: 15-20% reduction
- **Resource Optimization**: Better staff allocation
- **Error Reduction**: Lower rework and corrections
- **Training Cost**: Reduced training time for new staff

### Measurement Frequency

#### Daily Monitoring

- System uptime and performance
- Queue metrics and wait times
- Error rates and system alerts
- User registration statistics

#### Weekly Reporting

- User satisfaction trends
- System usage patterns
- Performance metrics review
- Incident and issue tracking

#### Monthly Analysis

- Comprehensive performance review
- User feedback analysis
- Business impact assessment
- Security and compliance check

#### Quarterly Review

- Strategic performance review
- Feature usage and adoption
- ROI calculation
- Roadmap adjustment and planning

---

## Risiko & Mitigasi

### Technical Risks

#### 1. System Performance Under Load

**Risk**: System slows down during peak hours  
**Impact**: High - Affects user experience and operational efficiency  
**Probability**: Medium  
**Mitigation**:

- Load testing during development
- Horizontal scaling architecture
- Caching strategy for frequent queries
- Performance monitoring and alerting

#### 2. Integration Failures

**Risk**: Kiosk or printer integration fails  
**Impact**: Medium - Affects offline registration  
**Probability**: Medium  
**Mitigation**:

- Robust error handling and retry mechanisms
- Offline mode with sync capability
- Regular integration testing
- Fallback to manual process if needed

#### 3. Data Loss or Corruption

**Risk**: Database failure or data corruption  
**Impact**: High - Loss of operational data  
**Probability**: Low  
**Mitigation**:

- Automated daily backups with verification
- Database replication for high availability
- Point-in-time recovery capability
- Regular backup restoration testing

### Operational Risks

#### 1. User Adoption Resistance

**Risk**: Staff or public resist using new system  
**Impact**: High - System underutilization  
**Probability**: Medium  
**Mitigation**:

- Comprehensive training program
- Phased rollout with support
- User feedback incorporation
- Champion users to promote adoption

#### 2. Network Connectivity Issues

**Risk**: Internet outage affects system operation  
**Impact**: Medium - Disruption to online features  
**Probability**: Low-Medium  
**Mitigation**:

- Offline capability for critical functions
- Local network redundancy
- Mobile hotspot as backup
- Graceful degradation of features

#### 3. Security Breach

**Risk**: Unauthorized access to sensitive data  
**Impact**: High - Reputational damage and compliance issues  
**Probability**: Low  
**Mitigation**:

- Regular security audits and penetration testing
- Multi-layered security approach
- Incident response plan
- Employee security training

### Project Risks

#### 1. Timeline Slippage

**Risk**: Project delays due to scope creep or technical challenges  
**Impact**: Medium - Delayed benefits realization  
**Probability**: Medium  
**Mitigation**:

- Agile methodology with regular deliverables
- Clear scope definition and change control
- Risk-aware project planning
- Regular progress tracking and reporting

#### 2. Budget Overrun

**Risk**: Costs exceed allocated budget  
**Impact**: Medium - Financial constraints  
**Probability**: Low-Medium  
**Mitigation**:

- Detailed cost estimation and tracking
- Phased implementation approach
- Contingency budget allocation
- Regular financial review

#### 3. Technical Debt Accumulation

**Risk**: Quick fixes lead to long-term maintenance issues  
**Impact**: Medium - Increased future costs  
**Probability**: Medium  
**Mitigation**:

- Code quality standards and reviews
- Technical debt tracking and management
- Regular refactoring sprints
- Comprehensive test coverage

### Mitigation Strategy Framework

#### Proactive Measures

1. **Risk Identification**: Regular risk assessment workshops
2. **Preventive Actions**: Address root causes before issues occur
3. **Contingency Planning**: Backup plans for critical components
4. **Training & Preparedness**: Staff training for risk scenarios

#### Reactive Measures

1. **Incident Response**: Clear procedures for issue resolution
2. **Communication Plan**: Stakeholder communication during issues
3. **Recovery Procedures**: Documented recovery steps
4. **Post-mortem Analysis**: Learning from incidents

#### Monitoring & Adjustment

1. **Risk Monitoring**: Regular review of risk indicators
2. **Adaptive Planning**: Adjust plans based on changing risks
3. **Stakeholder Communication**: Transparent risk reporting
4. **Continuous Improvement**: Learn and improve risk management

---

## Appendices

### Appendix A: Glossary

| Term            | Definition                                               |
| --------------- | -------------------------------------------------------- |
| PTSP            | Pelayanan Terpadu Satu Pintu                             |
| Kiosk           | Mesin layar sentuh untuk pendaftaran antrian offline     |
| Digital Signage | Tampilan digital untuk informasi antrian di ruang tunggu |
| Queue           | Antrian dalam sistem                                     |
| Service         | Jenis layanan (Umum, Posbakum, Pembayaran)               |
| Officer         | Petugas yang melayani antrian                            |
| Priority Queue  | Antrian prioritas untuk kelompok tertentu                |
| Thermal Printer | Printer tiket thermal tanpa tinta                        |

### Appendix B: References

1. Laravel Documentation (v12)
2. Inertia.js Documentation (v2)
3. Indonesian Data Protection Regulations
4. Court Service Standards and Guidelines
5. Accessibility Standards (WCAG 2.1)

### Appendix C: Contact Information

**Project Stakeholders:**

- Pengadilan Agama Penajam
- Development Team
- Implementation Partners

**Support Contacts:**

- Technical Support: [To be defined]
- User Support: [To be defined]
- Security Contact: [To be defined]

---

## Approval

This Product Requirements Document requires approval from the following stakeholders:

| Role             | Name | Signature | Date |
| ---------------- | ---- | --------- | ---- |
| Project Sponsor  |      |           |      |
| Technical Lead   |      |           |      |
| Operations Lead  |      |           |      |
| Security Officer |      |           |      |

**Approval Status:** Pending

---

_Document ID: PRD-PTSP-PANJ-001_  
_Confidentiality: Internal Use Only_  
_Last Updated: 19 January 2026_

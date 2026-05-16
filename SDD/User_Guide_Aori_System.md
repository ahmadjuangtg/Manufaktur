# USER GUIDE AORI SYSTEM

---

## 1. Halaman Login

**[Placeholder Screenshot: Halaman Login]**

Keterangan:
1) Buka aplikasi **Aori System** melalui browser.
2) Masukkan **username / email** pada kolom yang tersedia.
3) Masukkan **kata sandi** pada kolom password.
4) Klik tombol **Login**.
5) Jika data yang dimasukkan benar, pengguna akan diarahkan ke **halaman utama sistem (Dashboard)**.

---

## 2. Dashboard

**[Placeholder Screenshot: Halaman Dashboard Utama]**

Keterangan:
1) Setelah login berhasil, pengguna akan masuk ke menu **Dashboard**.
2) Halaman ini menampilkan ringkasan informasi, grafik, dan metrik penting terkait operasional inventaris, produksi, dan logistik.

---

## 3. Master Data

Menu Master Data digunakan untuk mengelola data referensi utama sistem.

### 3.1. Item

#### 3.1.1. Create Item (Tambah Item)
**[Placeholder Screenshot: Form Tambah Item]**

Keterangan:
1) Masuk ke menu **Master Data**.
2) Pilih **Item**.
3) Klik tombol **Tambah Item**.
4) Pada halaman form, lengkapi data item baru (seperti Nama Item, Kategori, Tipe, Satuan, Harga, dll).
5) Klik tombol **Simpan**.

#### 3.1.2. Edit / Delete Item
**[Placeholder Screenshot: Daftar Item & Aksi Edit/Delete]**

Keterangan:
1) Pada halaman **Daftar Item**, cari item yang ingin diubah atau dihapus.
2) Klik ikon/tombol **Ubah** untuk mengedit data, atau klik **Hapus/Void** untuk menghapus data.
3) Sistem akan memproses pembaruan atau penghapusan data dari daftar item.

---

### 3.2. Kategori

#### 3.2.1. Create Category
**[Placeholder Screenshot: Form Tambah Kategori]**

Keterangan:
1) Masuk ke menu **Master Data**.
2) Pilih **Kategori**.
3) Klik kotak/tombol **Tambah Data Kategori**.
4) Lengkapi Kode Kategori, Prefix, dan Nama Kategori.
5) Klik tombol **Simpan**.

#### 3.2.2. Edit / Delete Category
**[Placeholder Screenshot: Halaman Daftar Kategori]**

Keterangan:
1) Masuk ke menu **Master Data** -> **Kategori**.
2) Pada halaman **Daftar Kategori**, cari item yang ingin dikelola.
3) Klik tombol **Ubah** untuk memodifikasi atau **Void** untuk menghapus kategori.
4) Konfirmasi tindakan jika sistem memunculkan pop-up peringatan.

---

### 3.3. Master Data Lainnya

Langkah-langkah yang sama berlaku untuk submenu Master Data lainnya (Tipe Item, Satuan, Manufaktur, Supplier, Customer, Mesin, Gudang, dll).

**[Placeholder Screenshot: Tampilan Daftar Master Data & Form Tambah]**

Keterangan Umum:
1) Masuk ke menu **Master Data** lalu pilih sub-menu yang diinginkan.
2) Klik **Tambah Data** untuk memasukkan record baru.
3) Klik **Ubah / Hapus** pada kolom aksi untuk memanajemen data.

---

## 4. Production (Produksi)

### 4.1. Work Orders (SPK) & Scheduling (Alur Proses Produksi)
**[Placeholder Screenshot: Halaman Work Orders & Scheduling]**

**Alur Flow Sistem Produksi (End-to-End):**
- **Tahap 1 (Pembuatan WO):** Work Order (WO) dicreate oleh PPIC di menu **Work Orders**. Status awal WO adalah `Pending`. Setelah dipastikan siap bahan, PPIC melakukan aksi *Mark as Ready*.
- **Tahap 2 (Penjadwalan):** PPIC masuk ke menu **Scheduling Production**. Di sini, WO yang sudah *Ready* diset prioritas urutannya. Setelah diset prioritasnya, status WO secara otomatis berubah menjadi `Ready for Production`.
- **Tahap 3 (Proses Produksi):** Operator di lantai pabrik melihat daftar WO pada **Shop Floor Dashboard**. Operator mengklik "Start" pada mesin yang relevan. Status WO berubah menjadi `In Progress`.
- **Tahap 4 (Selesai):** Saat mesin selesai memproses atau operator menekan "Finish", dilakukan proses serah terima hasil produksi (Menu **Laporan -> Serah Terima / NPB**). Status akhir WO akan berubah menjadi `Completed`, dan stok barang jadi otomatis bertambah di gudang.

Keterangan Penggunaan:
1) Masuk ke menu **Production -> Work Orders** untuk membuat SPK.
2) Pilih item barang jadi, kuantitas, dan rute master template. Simpan.
3) Masuk ke **Production -> Scheduling Production** untuk mengatur Gantt Chart dan prioritas urutan mesin.

---

## 5. Shop Floor & Laporan

### 5.1. Shop Floor Dashboard
**[Placeholder Screenshot: Shop Floor Dashboard]**

Keterangan:
1) Masuk ke menu **Shop Floor & Laporan -> Shop Floor Dashboard**.
2) Halaman ini khusus untuk operator mesin. Klik **Mulai/Start** pada antrean *Ready for Production* untuk mulai menghitung waktu aktual.

### 5.2. Laporan LHP & Serah Terima
**[Placeholder Screenshot: Laporan Harian Produksi & NPB]**

Keterangan:
1) Pilih **Laporan LHP** untuk memonitor hasil harian produksi.
2) Pilih **Serah Terima (NPB/PHP)** untuk proses mutasi hasil produksi menjadi stok Barang Jadi (Finished Goods) di gudang.

---

## 6. Transaksi (Inventaris & Mutasi)

### 6.1. Alur Mutasi Gudang & Request Mutasi
**[Placeholder Screenshot: Halaman Request Mutasi & Approval]**

**Alur Flow Mutasi Barang:**
- **Tahap 1 (Pengajuan):** Pihak peminta (misal Gudang B atau Produksi) membuat pengajuan perpindahan barang di menu **Request Mutasi**. Status dokumen menjadi `Pending`.
- **Tahap 2 (Persetujuan):** Atasan/Gudang Utama masuk ke menu **Approval Mutasi**. Jika ditolak, wajib mengisi kolom `Rejection Reason` (Alasan Penolakan) dan status menjadi `Rejected`.
- **Tahap 3 (Selesai):** Jika disetujui (Approved), maka barang fisik akan diproses pindah, sistem otomatis memotong stok dari gudang asal, menambah stok di gudang tujuan, dan tercatat selamanya di **Kartu Stock** serta daftar **Mutasi Gudang**.

Keterangan Penggunaan:
1) Di menu **Transaksi**, klik **Request Mutasi** lalu **Tambah Data**.
2) Isi item, jumlah, asal, dan tujuan. Simpan.
3) Pihak berwenang membuka **Approval Mutasi** lalu mengeklik `Approve` atau `Reject`.

### 6.2. Alur Stock Opname
**[Placeholder Screenshot: Halaman Input Stock Opname & Approval]**

**Alur Flow Stock Opname:**
- **Tahap 1 (Drafting):** Petugas gudang melakukan penghitungan fisik dan memasukkannya di menu **Stock Opname**. Sistem akan menghitung selisih (Variance) antara Fisik vs Sistem. Status menjadi `Draft/Pending`.
- **Tahap 2 (Verifikasi):** Dokumen diajukan ke Atasan. Atasan mengecek di halaman **Approval Stock Opname**. Jika ditolak, Atasan mengisi *Rejection Reason* dan status menjadi `Rejected`.
- **Tahap 3 (Penyesuaian):** Jika disetujui (Approved), maka stok sistem akan otomatis tergantikan (ter-adjust) dengan angka kuantitas fisik terbaru.

Keterangan Penggunaan:
1) Masuk ke **Transaksi -> Stock Opname**, klik **Tambah Opname**.
2) Pilih item dan masukkan nilai `Audit` (Fisik).
3) Atasan memproses lewat menu **Approval Stock Opname**.

---

## 7. Order & Purchasing

### 7.1. Alur Proses Order (Request & PO)
**[Placeholder Screenshot: Form Request Item & Purchase Order]**

**Alur Flow Order Material:**
- **Tahap 1 (Request):** Departemen terkait membuat pengajuan beli material di menu **Request Items**. Status dokumen `Pending`.
- **Tahap 2 (Approval Request):** Manajer memeriksa pengajuan di menu **Approval Request**. Jika sesuai, diubah menjadi `Approved`.
- **Tahap 3 (Pembuatan PO):** Departemen Purchasing menarik data Request yang *Approved* untuk dibuatkan Purchase Order (PO) resmi kepada Supplier melalui menu **Create PO**. Status pesanan menjadi `PO Created`.
- **Tahap 4 (Penerimaan):** Saat truk Supplier tiba, orang gudang masuk ke menu **Receive Material** (Good Receipt) untuk memverifikasi kesesuaian fisik dan PO. Setelah diterima, stok bahan baku akan langsung bertambah di inventory.

Keterangan Penggunaan:
1) Buat pengajuan di **Order & Purchasing -> Request Items**.
2) Verifikasi di **Approval Request**.
3) Terbitkan dokumen resmi di **Create PO**.
4) Konfirmasi kedatangan di **Receive Material**.

---

## 8. Logistics & Delivery

### 8.1. Alur Pengiriman Barang (Packing & Delivery)
**[Placeholder Screenshot: Halaman Delivery & Packing List]**

**Alur Flow Pengiriman:**
- **Tahap 1 (Persiapan):** Barang jadi yang ada di gudang akan dikelompokkan ke dalam kemasan lewat menu **Packing List**.
- **Tahap 2 (Pengalokasian Kendaraan):** *Packing List* tersebut dimasukkan ke dalam jadwal kirim di menu **Delivery Batch**. Pada tahap ini, armada dan sopir ditugaskan. Status barang menjadi `In Transit`.
- **Tahap 3 (Pemantauan):** Ekspedisi / logistik memantau pengiriman di menu **Tracking Delivery**.
- **Tahap 4 (Diterima):** Setelah *Customer* menerima barang dan dokumen surat jalan (SJ) kembali, status di-update menjadi `Delivered` dan stok resmi dikurangi dari gudang (jika belum dipotong saat *loading*).

Keterangan Penggunaan:
1) Buat list barang yang akan dikemas di **Packing List**.
2) Jadwalkan angkutan di **Delivery Batch**.
3) Pantau status harian di **Tracking Delivery**.

---

## 9. Security & Access

### 9.1. Access Roles & Account Management
**[Placeholder Screenshot: Setting Akun dan Hak Akses]**

Keterangan:
1) Masuk ke menu **Security & Access**.
2) Pilih **Access Roles** untuk mengatur hak akses tiap grup wewenang (Role).
3) Pilih **Account Management** untuk membuat akun pengguna, mengatur password, dan menempelkan role masing-masing pengguna.

# Architecture

Diagram arsitektur SILK-Swarakarna: struktur direktori + request lifecycle.

## 1. Project Layout

```
silk-swarakarna/
├── public/                          ← Document root (Apache/Nginx point ke sini)
│   ├── index.php                    ← Front controller + router
│   ├── .htaccess                    ← URL rewrite (semua → index.php)
│   └── assets/
│       ├── css/                     ← Tailwind output
│       ├── js/
│       └── img/
│
├── src/                             ← Domain layer (OOP classes)
│   ├── Database.php                 ← Class Database (PDO singleton)
│   ├── Pasien.php                   ← Class Pasien (master)
│   ├── Dokter.php                   ← Class Dokter (master)
│   ├── Layanan.php                  ← Class Layanan (master)
│   └── Pemeriksaan.php              ← Class Pemeriksaan (transaksi)
│
├── includes/                        ← Bootstrap + config
│   ├── bootstrap.php                ← Autoload + session + error handler
│   └── config.php                   ← DB credentials, base URL
│
├── views/                           ← Presentation layer
│   ├── layout/
│   │   ├── header.php               ← <html> + Tailwind + navbar
│   │   └── footer.php               ← </body> + scripts
│   ├── dashboard.php
│   ├── pasien/
│   │   ├── index.php                ← List + search
│   │   ├── create.php               ← Form tambah
│   │   ├── edit.php                 ← Form edit
│   │   └── delete.php               ← Handler hapus
│   ├── dokter/
│   ├── layanan/
│   └── pemeriksaan/
│       ├── index.php                ← List + JOIN 3 master + search
│       ├── create.php               ← Form dropdown 3 master
│       ├── update_status.php        ← Quick action ganti status
│       └── delete.php
│
├── database/
│   ├── silk_swarakarna.sql          ← Schema + seed
│   └── migrations/                  ← Optional: incremental SQL
│
├── .env                             ← DB creds (GITIGNORED)
├── .env.example                     ← Template
├── .gitignore
├── README.md
└── composer.json                    ← PSR-4 autoload "Silk\\"
```

## 2. Request Lifecycle

Setiap HTTP request dari browser flow-nya gini:

```mermaid
graph TB
    subgraph Browser["Browser"]
        UI[User klik menu/<br/>submit form]
    end

    subgraph Public["public/ (Document Root)"]
        HC[".htaccess<br/>rewrite ke index.php"]
        Router["index.php<br/>Front Controller + Router"]
    end

    subgraph Bootstrap["includes/"]
        Boot["bootstrap.php<br/>- session_start<br/>- require autoload<br/>- require config"]
        Cfg["config.php<br/>DB_HOST, DB_NAME,<br/>DB_USER, DB_PASS"]
    end

    subgraph Domain["src/ (Domain Layer)"]
        DBClass["Database<br/>PDO singleton<br/>query / execute"]
        Master["Master classes<br/>Pasien / Dokter / Layanan<br/>- create<br/>- read<br/>- update<br/>- delete<br/>- generateKodeOtomatis (Pasien)"]
        Trans["Pemeriksaan<br/>- create<br/>- readWithJoin<br/>- updateStatus"]
    end

    subgraph View["views/"]
        Layout["layout/<br/>header.php + footer.php"]
        Page["page file<br/>mis. pasien/index.php"]
    end

    subgraph Data["MySQL"]
        T1[(pasien)]
        T2[(dokter)]
        T3[(layanan)]
        T4[(pemeriksaan)]
    end

    UI -->|"GET /pasien<br/>POST form"| HC
    HC --> Router
    Router --> Boot
    Boot --> Cfg
    Boot --> Router
    Router -->|route = 'pasien.index'| Page
    Router -->|route = 'pasien.create'| Master
    Master -->|"new Pasien()"| DBClass
    DBClass --> T1
    DBClass --> T2
    DBClass --> T3
    Trans --> DBClass
    DBClass --> T4
    Master -->|return data array| Page
    Trans -->|return data + JOIN| Page
    Page --> Layout
    Layout -->|HTML + Tailwind| UI

    style Browser fill:#e3f2fd
    style Public fill:#fff3e0
    style Domain fill:#f3e5f5
    style View fill:#e8f5e9
    style Data fill:#fce4ec
```

## 3. Class Responsibilities

| Class | Tanggung Jawab | Methods |
|---|---|---|
| `Database` | Koneksi PDO, eksekusi query global | `getInstance()`, `query()`, `execute()`, `lastInsertId()` |
| `Pasien` | CRUD master pasien + kode otomatis | `create()`, `read()`, `update()`, `delete()`, `generateKodeOtomatis()`, `search()` |
| `Dokter` | CRUD master dokter | `create()`, `read()`, `update()`, `delete()`, `search()` |
| `Layanan` | CRUD master layanan THT | `create()`, `read()`, `update()`, `delete()` |
| `Pemeriksaan` | Transaksi + JOIN + status | `create()`, `readWithJoin()`, `updateStatus()`, `search()`, `getById()` |

## 4. Database Connection Pattern

```mermaid
sequenceDiagram
    participant P as Page Handler
    participant C as Class (mis. Pasien)
    participant DB as Database
    participant MySQL as MySQL

    P->>C: new Pasien()
    C->>DB: Database::getInstance()
    DB->>DB: Cek apakah instance ada
    alt Instance belum ada
        DB->>MySQL: new PDO(host, db, user, pass)
        MySQL-->>DB: PDO object
        DB->>DB: setAttribute(ERRMODE_EXCEPTION)
    end
    DB-->>C: PDO connection
    C->>DB: $this->db->prepare("SELECT * FROM pasien")
    C->>DB: execute([$params])
    DB->>MySQL: query
    MySQL-->>DB: rows
    DB-->>C: array of rows
    C-->>P: return data
```

Pattern: **Singleton PDO**. 1 koneksi shared di semua class — hemat resource, gampang di-mock untuk test.

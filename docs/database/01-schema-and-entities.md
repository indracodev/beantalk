# Database: Schema & Entity Models

Dokumen ini mendefinisikan skema lengkap database MySQL 8.0+ untuk core platform chat multi-tenant.

---

## 1. Entity Overview

Sistem memetakan 18 entitas relasional:
1. `tenants`: Akun organisasi / entitas bisnis
2. `users`: Anggota tim (Owner, Admin, Agent)
3. `projects`: Project situs web / toko (Shopify, WordPress, dll)
4. `project_domains`: Daftar domain yang diizinkan untuk project
5. `api_keys`: Kunci publik (`pk_live_xxx`) dan rahasia project
6. `widget_settings`: Konfigurasi tampilan & teks widget per project
7. `contacts`: Data profil pelanggan yang telah memberikan identitas (email/nama)
8. `visitors`: Identitas browser anonim (UUID lokal) per project
9. `conversations`: Thread percakapan antara visitor dan agent
10. `conversation_participants`: Relasi partisipan percakapan
11. `messages`: Record pesan individual (teks, gambar, file, sistem)
12. `message_attachments`: File gambar/dokumen yang diunggah
13. `conversation_assignments`: Riwayat penugasan agen pada percakapan
14. `realtime_events`: Buffer antrean event transien untuk polling/SSE
15. `webhooks`: Endpoint webhook tenant
16. `webhook_deliveries`: Log pengiriman dan status retry webhook
17. `audit_logs`: Pencatatan aktivitas sensitif tim
18. `failed_jobs` / `personal_access_tokens`: Tabel standar Laravel

---

## 2. Table DDL Definitions

### 2.1 Tenant & Identity
```sql
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    plan VARCHAR(50) DEFAULT 'starter',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('owner', 'admin', 'agent') DEFAULT 'agent',
    status ENUM('online', 'busy', 'offline') DEFAULT 'offline',
    avatar_url VARCHAR(500) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_email (tenant_id, email),
    INDEX idx_user_tenant_role (tenant_id, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.2 Project, Key, Domain & Widget Configuration
```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_project_tenant (tenant_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    public_key VARCHAR(64) NOT NULL UNIQUE,      -- Format: pk_live_xxxxx
    secret_hash VARCHAR(255) NULL,
    name VARCHAR(100) DEFAULT 'Default Key',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_api_key_public (public_key, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_domains (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    domain VARCHAR(255) NOT NULL,               -- e.g. "mystore.com", "myshopify.com"
    is_verified BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_domain_lookup (project_id, domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE widget_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL UNIQUE,
    title VARCHAR(100) DEFAULT 'Customer Support',
    support_title VARCHAR(100) DEFAULT 'Customer Support',
    find_us_title VARCHAR(100) DEFAULT 'Find Us Somewhere Else',
    greeting_title VARCHAR(100) DEFAULT 'Hallo!',
    greeting_subtitle VARCHAR(255) DEFAULT 'Apakah ada yang bisa kami bantu?',
    primary_color VARCHAR(20) DEFAULT '#0F172A',
    accent_color VARCHAR(20) DEFAULT '#FFFFFF',
    position ENUM('bottom-right', 'bottom-left') DEFAULT 'bottom-right',
    avatar_url VARCHAR(500) NULL,
    show_branding BOOLEAN DEFAULT TRUE,
    is_online BOOLEAN DEFAULT TRUE,
    social_channels JSON NULL,                   -- WhatsApp, Instagram, FB Messenger, Telegram, Shopee, Tokopedia
    bot_enabled BOOLEAN DEFAULT FALSE,           -- Master bot toggle for this website
    bot_name VARCHAR(100) DEFAULT 'BeanBot',     -- Display name for the automated assistant
    bot_welcome_message TEXT NULL,               -- Initial auto-greeting on first contact
    bot_offline_message TEXT NULL,               -- Auto-responder when outside operating hours
    bot_rules JSON NULL,                         -- Array of {keywords, response} FAQ rules
    bot_ai_enabled BOOLEAN DEFAULT FALSE,        -- AI Smart Responder flag
    bot_ai_prompt TEXT NULL,                     -- Optional system prompt for LLM integration
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.3 Visitor & Contact
```sql
CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    name VARCHAR(255) NULL,
    custom_attributes JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_contact_email (tenant_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visitors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    visitor_uuid VARCHAR(64) NOT NULL,
    customer_code VARCHAR(20) NULL,              -- Secondary identifier e.g. "CUS-8F21"
    name VARCHAR(100) NULL,                      -- Display name e.g. "Budi"
    contact_id BIGINT UNSIGNED NULL,
    current_page VARCHAR(1000) NULL,
    source VARCHAR(100) DEFAULT 'website',       -- 'shopify', 'wordpress', 'website'
    browser VARCHAR(100) NULL,
    os VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    first_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    UNIQUE KEY uk_proj_visitor (project_id, visitor_uuid),
    INDEX idx_visitor_customer_code (customer_code),
    INDEX idx_visitor_seen (project_id, last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.4 Conversations, Messages & Attachments
```sql
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    visitor_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NULL,
    assigned_user_id BIGINT UNSIGNED NULL,
    status ENUM('open', 'pending', 'closed') DEFAULT 'open',
    channel VARCHAR(50) DEFAULT 'widget',
    is_bot_active BOOLEAN DEFAULT TRUE,          -- Per-ticket Bot active status (toggled by CS or auto-yield)
    bot_handoff_at TIMESTAMP NULL,               -- Timestamp when conversation transferred to human agent
    unread_agent_count INT UNSIGNED DEFAULT 0,
    unread_visitor_count INT UNSIGNED DEFAULT 0,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_preview VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_conv_inbox (tenant_id, status, last_message_at DESC),
    INDEX idx_conv_visitor (project_id, visitor_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,   -- Monotonic ordering
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_type ENUM('visitor', 'agent', 'system') NOT NULL,
    sender_id BIGINT UNSIGNED NULL,
    client_message_id VARCHAR(64) NULL,             -- Idempotency key
    type ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
    body TEXT NOT NULL,
    status ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    INDEX idx_msg_poll (conversation_id, id ASC),
    INDEX idx_msg_idempotent (conversation_id, client_message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    disk VARCHAR(50) DEFAULT 'public',
    path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.5 Realtime Buffer, Webhooks & Audits
```sql
CREATE TABLE realtime_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    conversation_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_events_stream (conversation_id, id ASC),
    INDEX idx_events_cleanup (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    url VARCHAR(500) NOT NULL,
    secret VARCHAR(100) NOT NULL,
    events JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    actor_type VARCHAR(50) NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    resource VARCHAR(100) NOT NULL,
    resource_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_audit_lookup (tenant_id, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

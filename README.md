# 🎬 ACP_Proposals — Wedding Proposal PDF Builder

> Premium fixed-layout wedding proposal PDF generator for **Concord CRM**.  
> Build luxury A4 proposals with background images, schedule tables, deliverables and pricing — in minutes.

---

## 📋 Requirements

| Requirement | Version |
|---|---|
| Concord CRM | v1.0+ |
| PHP | 8.1+ |
| Node.js | 18+ |
| `barryvdh/laravel-dompdf` | Already bundled in Concord CRM |

---

## 🚀 Installation (Production Server)

### Option A — Script (Recommended)

```bash
# 1. Clone this repo anywhere on your server
git clone https://github.com/mukeshh02/Concord-ACP-Proposal.git /opt/acp-modules

# 2. Run the install script, pointing it at your CRM
cd /opt/acp-modules
bash install.sh /var/www/sales
```

The script handles:
- ✅ Copying module to `modules/`
- ✅ Running database migrations
- ✅ Clearing all caches
- ✅ Creating `storage:link`
- ✅ Building frontend assets (`npm run build`)

---

### Option B — Manual

```bash
# 1. Copy module folder
cp -r ACP_Proposals/ /var/www/sales/modules/

# 2. Enter CRM directory
cd /var/www/sales

# 3. Run migrations
php artisan migrate --force

# 4. Clear caches
php artisan optimize:clear
php artisan core:clear-cache

# 5. Storage symlink (skip if already exists)
php artisan storage:link

# 6. Create template directory
mkdir -p storage/app/acp-proposals/templates

# 7. Build frontend
npm run build
```

---

## 🔄 Updating (When New Version Released)

```bash
cd /opt/acp-modules        # wherever you cloned this repo
bash update.sh /var/www/sales
```

Or manually:
```bash
git pull origin main
cp -r ACP_Proposals/ /var/www/sales/modules/
cd /var/www/sales
php artisan migrate --force
php artisan optimize:clear
php artisan view:clear
npm run build
```

---

## ⚙️ First-Time Setup (After Install)

1. **Assign Permission** → CRM Admin → Settings → Roles  
   → Enable `"View & Manage Proposals"` for relevant roles

2. **Upload Background Images** → `/acp-proposals` → click **▼ Manage Images**  
   → Upload one JPG per page (5 pages total)  
   → Recommended: A4 size (210×297mm) at 150–300 DPI, max 10 MB each

3. **Create a Proposal** → Click `+ New Proposal`

---

## 📁 Module Structure

```
ACP_Proposals/
├── bootstrap/
│   └── module.php              ← lifecycle hooks (enabled/disabled/deleted)
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── ProposalController.php
│   │   ├── ProposalPdfController.php
│   │   └── TemplateImageController.php
│   ├── Models/
│   │   └── Proposal.php
│   └── Providers/
│       ├── ACPProposalsServiceProvider.php
│       └── RouteServiceProvider.php
├── database/migrations/
│   └── 2026_05_26_000001_create_acp_proposals_table.php
├── resources/
│   ├── js/
│   │   ├── app.js              ← Vue Router + component registration
│   │   └── views/
│   │       ├── ProposalIndex.vue   (list + template image manager)
│   │       └── ProposalEditor.vue  (5-page form editor)
│   └── views/
│       ├── index.blade.php     ← SPA shell
│       └── pdf/
│           └── proposal.blade.php  ← DomPDF 5-page A4 template
├── routes/
│   ├── api.php                 ← All REST endpoints (auth:sanctum)
│   └── web.php                 ← SPA catch-all (auth)
├── module.json
└── composer.json
```

---

## 🛣️ API Routes

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/acp-proposals` | List all proposals |
| POST | `/api/acp-proposals` | Create new proposal |
| GET | `/api/acp-proposals/{id}` | Get single proposal |
| PUT | `/api/acp-proposals/{id}` | Update proposal |
| DELETE | `/api/acp-proposals/{id}` | Delete proposal |
| POST | `/api/acp-proposals/{id}/generate-pdf` | Generate & save PDF |
| GET | `/api/acp-proposals/{id}/preview-pdf` | Stream PDF to browser |
| GET | `/api/acp-proposals/templates/status` | Check uploaded backgrounds |
| POST | `/api/acp-proposals/templates/{page}` | Upload background image |
| DELETE | `/api/acp-proposals/templates/{page}` | Remove background image |

All routes require Sanctum authentication.

---

## 🗄️ Database

Single table: `acp_proposals`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `deal_id` | bigint nullable | Link to CRM deal |
| `title` | string | Proposal title |
| `status` | string | `draft` / `ready` / `sent` |
| `data` | json | All form fields |
| `pdf_path` | string nullable | Last generated PDF path |
| `created_by` | bigint nullable | User who created it |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## 📂 Storage Paths

| Path | Purpose |
|------|---------|
| `storage/app/acp-proposals/templates/` | Page background images (page1_cover.jpg → page5_back.jpg) |
| `storage/app/public/acp-proposals/` | Generated PDFs (served via `/storage/acp-proposals/`) |

---

## 🤖 Auto-Deploy via GitHub Actions

Every push to `main`:
1. ✅ Validates module structure
2. 📦 Creates a downloadable `.zip` release
3. 📣 Prints deploy instructions

To set up auto-deploy on your server:

```bash
# On your server — add a cron or webhook that runs:
cd /opt/acp-modules && bash update.sh /var/www/sales
```

Or use a GitHub webhook → point to a deploy endpoint on your server.

---

## 🏠 No Core CRM Dependencies

This module:
- ✅ Uses only Concord CRM's framework (`ModuleServiceProvider`, `MenuItem`, `Innoclapps`)
- ✅ Uses `Innoclapps.request()` (built-in CRM axios wrapper) in frontend
- ✅ Uses `barryvdh/laravel-dompdf` which is already bundled in Concord CRM
- ✅ Has no external API dependencies
- ✅ All images stored locally (base64 encoded for DomPDF)
- ✅ Works on any server running Concord CRM

---

## 📧 Support

Built by [Akash Camera Production](https://akashcameraproduction.com)  
GitHub: [@mukeshh02](https://github.com/mukeshh02)

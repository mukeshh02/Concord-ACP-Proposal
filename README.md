# 🎬 Akash Camera Production — Concord CRM Custom Modules

Custom Laravel + Vue 3 modules built on top of **Concord CRM (Innoclapps)** for a professional wedding photography & videography sales team.

---

## 📦 Modules

### 1. `ACP_Proposals` — Premium Proposal Builder
Fixed-layout PDF proposal system for luxury wedding proposals.
- 5-page A4 proposal with background image templates
- DomPDF-based PDF generation with text overlay at pixel-perfect positions
- Full form editor: Cover, Package, Work Scope, Deliverables, Charges, Why Us
- Day-wise schedule table, 2-column deliverables grid, pricing box

### 2. `ACP_Sales_Guide` — Sales Deal Panel
Deal-level sales guide injected into the CRM's Deal detail page.
- Stage-based action suggestions
- WhatsApp message templates
- Follow-ups scheduler (Today's Follow-ups dashboard)
- Checklist progress per stage

### 3. `AkashSalesPipeline` — Pipeline Configuration
Admin config panel for stage-to-action mapping.
- Per-stage toolbox JSON config
- WhatsApp template manager
- Sales content setup (call script, portfolio links)

---

## 🚀 Installation

### Requirements
- [Concord CRM 1.7.x](https://www.concordcrm.com/) installed
- PHP 8.2+, Laravel 11, Node 18+
- `barryvdh/laravel-dompdf` (already in Concord CRM)

### Steps

```bash
# 1. Copy modules to your CRM installation
cp -r ACP_Proposals      /path/to/crm/modules/
cp -r ACP_Sales_Guide    /path/to/crm/modules/
cp -r AkashSalesPipeline /path/to/crm/modules/

# 2. Enable modules
# Add to modules_statuses.json:
# "ACP_Proposals": true,
# "ACP_Sales_Guide": true,
# "AkashSalesPipeline": true

# 3. Register module JS imports in resources/js/app.js
# (See app-imports.js for the 3 lines to add)

# 4. Fix Vite alias regex to support underscores
# In vite.config.js, change:
#   [a-zA-Z]+   →   [a-zA-Z0-9_]+

# 5. Clear module cache (IMPORTANT!)
rm bootstrap/cache/modules.php
rm bootstrap/cache/module_autoload.php
php artisan core:clear-cache

# 6. Run migrations
php artisan migrate

# 7. Build frontend
npm install
npm run build

# 8. Upload proposal templates (for ACP_Proposals)
# Put 5 blank A4 JPG files in:
#   storage/app/acp-proposals/templates/
# Filenames: page1_cover.jpg  page2_package.jpg  page3_scope.jpg
#            page4_why_us.jpg  page5_back.jpg
```

---

## 📁 Template Images (ACP_Proposals)

Place your 5 blank page background images here (not in public/):
```
storage/app/acp-proposals/templates/
├── page1_cover.jpg
├── page2_package.jpg
├── page3_scope.jpg
├── page4_why_us.jpg
└── page5_back.jpg
```

> Images are base64-encoded at PDF generation time — no web server access needed.

---

## 🗄️ Database Tables

| Table | Module | Purpose |
|-------|--------|---------|
| `acp_proposals` | ACP_Proposals | Proposal records with JSON data |
| `akash_stage_mappings` | AkashSalesPipeline | Per-stage toolbox config |
| `akash_sales_followups` | ACP_Sales_Guide | Scheduled follow-ups |
| `akash_sales_pipeline_logs` | ACP_Sales_Guide | Activity log |
| `akash_sales_templates` | ACP_Sales_Guide | WhatsApp message templates |
| `akash_sales_settings` | ACP_Sales_Guide | Key-value settings |
| `akash_checklist_completions` | ACP_Sales_Guide | Checklist per deal+stage |

---

## 🔗 Routes

| URL | Description |
|-----|-------------|
| `/acp-proposals` | Proposal list & builder |
| `/acp-sales-guide` | Sales Guide admin |
| `/akash-sales-pipeline` | Pipeline config |
| `/akash-sales-pipeline/today` | Today's follow-ups |
| `GET /api/acp-proposals` | Proposals API |
| `POST /api/acp-proposals/{id}/generate-pdf` | Generate PDF |

---

## 🛠️ Local Dev

```bash
# Recommended: full dev stack
composer dev
# Starts: php artisan serve + queue + pail logs + vite HMR

# Frontend only
npm run dev

# Build for production
npm run build
```

---

## 📸 Built With

- **Concord CRM** — [concordcrm.com](https://www.concordcrm.com/)
- **Laravel 11** + **Vue 3** + **Vite**
- **DomPDF** (barryvdh/laravel-dompdf)
- **Tailwind CSS** (via Concord CRM's build pipeline)

---

*Built for Akash Camera Production, Rajnandgaon*

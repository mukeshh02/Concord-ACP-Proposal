# Installation Guide — ACP Proposals

## Method 1: ZIP Install via CRM Settings (Recommended)

1. Download `ACP_Proposals.zip` from the releases page
2. Go to **Settings → Modules** in your Concord CRM
3. Upload the zip and click Install
4. Run migrations: `php artisan migrate`
5. **Register the JS** (one-time step — see below)
6. Run: `npm run build`
7. Run: `php artisan core:clear-cache`

## Method 2: Manual Install

```bash
# 1. Copy module to modules directory
cp -r ACP_Proposals /path/to/crm/modules/

# 2. Enable the module
php artisan module:enable ACP_Proposals

# 3. Run migrations
php artisan migrate

# 4. Register JS (see below)

# 5. Build frontend
npm run build

# 6. Clear cache
php artisan core:clear-cache
```

## JS Registration (Required)

Open `resources/js/app.js` and add this line:

```js
import '@/ACP_Proposals/app.js'
```

Add it near other module imports (around line 40-45).

## Verify Storage Symlink

Ensure the public storage symlink exists:
```bash
php artisan storage:link
```

## Uninstall

Go to **Settings → Modules → ACP Proposals → Delete**.

This will automatically:
- Drop `acp_proposals` and `acp_proposal_sets` tables
- Delete all uploaded background images
- Delete all generated PDF files
- Remove all routes and menu entries

> ⚠️ This action is irreversible. Back up your storage directory first if needed.

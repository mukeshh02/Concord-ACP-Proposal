# ACP Proposals — Premium PDF Proposal Builder

A luxury wedding proposal PDF generator module for **Concord CRM** (Innoclapps).

## Features
- Multi-design system — upload different background images per design set
- Drag-and-drop text zone editor (A4 canvas, 2px/mm scale)
- 6 fixed content pages: Cover, Package, Work Scope, Deliverables, Why Us, Back Cover
- Unlimited extra background-only pages (reorderable via drag-and-drop)
- DomPDF-based PDF generation with base64-embedded backgrounds
- Clean uninstall — removes all tables and uploaded files

## Requirements
- PHP 8.1+
- Concord CRM (Innoclapps) v1.x
- `barryvdh/laravel-dompdf` ^3.0 (already in Concord CRM)
- `npm` package: `vuedraggable` (already in Concord CRM)

## Tables Created
| Table | Purpose |
|---|---|
| `acp_proposals` | Proposal records (title, data JSON, PDF path, status) |
| `acp_proposal_sets` | Design sets (name, slug, layout, page_order) |

## Storage
All files stored at `storage/app/public/acp-proposals/`:
- `sets/{slug}/cover.jpg` — design backgrounds
- `sets/{slug}/extra_N.jpg` — extra pages
- `proposal_N_timestamp.pdf` — generated PDFs

## Uninstall
Deleting the module from CRM Settings will:
1. Drop both database tables
2. Delete all uploaded images and generated PDFs
3. Remove all routes and menu items automatically

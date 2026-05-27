<?php

namespace Modules\ACP_Proposals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProposalSet extends Model
{
    protected $table    = 'acp_proposal_sets';
    protected $fillable = ['name', 'slug', 'is_active', 'layout', 'page_order', 'page_settings'];
    protected $casts    = [
        'is_active'     => 'boolean',
        'layout'        => 'array',
        'page_order'    => 'array',
        'page_settings' => 'array',
    ];

    // ── Fixed content slots ───────────────────────────────────────────
    // Always in this order, always in PDF, each tied to specific content zones.
    // 'legacy' = old file name (page1.jpg) — auto-migrated on first access.

    const CONTENT_SLOTS = [
        'cover'              => ['file' => 'cover.jpg',              'label' => '📄 Cover',          'legacy' => 'page1'],
        'package'            => ['file' => 'package.jpg',            'label' => '📦 Our Package',    'legacy' => 'page2'],
        'scope_schedule'     => ['file' => 'scope_schedule.jpg',     'label' => '📋 Work Scope',     'legacy' => 'scope'],   // auto-migrates from scope.jpg / page3.jpg
        'scope_deliverables' => ['file' => 'scope_deliverables.jpg', 'label' => '📊 Deliverables',   'legacy' => null],      // new slot — no legacy file
        'why_us'             => ['file' => 'why_us.jpg',             'label' => '⭐ Why Choose Us',  'legacy' => 'page4'],
        'back'               => ['file' => 'back.jpg',               'label' => '🔚 Back Cover',     'legacy' => 'page5'],
    ];

    // ── Default layout (text zone positions in mm, A4 = 210×297mm) ────

    public static function defaultLayout(): array
    {
        return [
            'cover' => [
                'client_name' => ['top' => 222, 'left' => 25, 'width' => 160],
                'event_date'  => ['top' => 234, 'left' => 25, 'width' => 160],
            ],
            'package' => [
                'package_name' => ['top' => 135, 'left' => 20, 'width' => 170],
                'package_desc' => ['top' => 152, 'left' => 20, 'width' => 170],
            ],
            'scope_schedule' => [
                'scope_header' => ['top' => 42,  'left' => 0,  'width' => 210],
                'scope_table'  => ['top' => 51,  'left' => 10, 'width' => 190],
            ],
            'scope_deliverables' => [
                'deliverables' => ['top' => 30,  'left' => 15, 'width' => 180],
                'charges'      => ['top' => 195, 'left' => 15, 'width' => 180],
            ],
            'why_us' => [
                'why_us_points' => ['top' => 130, 'left' => 12, 'width' => 95],
            ],
        ];
    }

    /** Page-level display settings (crop offsets etc.) */
    public function getPageSettings(): array
    {
        return array_merge([
            'scope_schedule_crop' => null,   // null = full 297 mm (no crop)
        ], $this->page_settings ?? []);
    }

    /** Merge saved layout with defaults. */
    public function getLayout(): array
    {
        $defaults = self::defaultLayout();
        $saved    = $this->layout ?? [];
        $result   = [];
        foreach ($defaults as $slot => $zones) {
            $result[$slot] = [];
            foreach ($zones as $key => $def) {
                $result[$slot][$key] = array_merge($def, $saved[$slot][$key] ?? []);
            }
        }
        return $result;
    }

    // ── Directory ─────────────────────────────────────────────────────

    public function dir(): string
    {
        $dir = storage_path('app/public/acp-proposals/sets/' . $this->slug);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    // ── Page status ───────────────────────────────────────────────────

    /**
     * Returns all pages in display order:
     *   1. Fixed content slots (cover, package, scope, why_us, back) — always shown
     *   2. Extra pages (extra_1, extra_2…) — draggable, background-only
     *
     * Legacy page1.jpg → cover.jpg auto-migration happens here on first access.
     */
    public function pageStatus(): array
    {
        $dir    = $this->dir();
        $result = [];

        // ── 1. Fixed content slots ────────────────────────────────────
        foreach (self::CONTENT_SLOTS as $key => $info) {
            $base     = pathinfo($info['file'], PATHINFO_FILENAME); // e.g. "cover"
            $path     = $this->findOrMigrateLegacy($dir, $base, $info['legacy']);

            if ($path && file_exists($path)) {
                $filename = basename($path);
                $result[$key] = [
                    'label'    => $info['label'],
                    'slot'     => 'content',
                    'file'     => $filename,
                    'uploaded' => true,
                    'size'     => round(filesize($path) / 1024) . ' KB',
                    'url'      => asset("storage/acp-proposals/sets/{$this->slug}/{$filename}") . '?v=' . filemtime($path),
                ];
            } else {
                $result[$key] = [
                    'label'    => $info['label'],
                    'slot'     => 'content',
                    'file'     => $info['file'],
                    'uploaded' => false,
                    'size'     => null,
                    'url'      => null,
                ];
            }
        }

        // ── 2. Extra pages ────────────────────────────────────────────
        $extraFiles = glob("{$dir}/extra_*.{jpg,jpeg,png}", GLOB_BRACE) ?: [];
        $extraMap   = [];
        foreach ($extraFiles as $file) {
            preg_match('/extra_(\d+)/', basename($file), $m);
            $num = (int)($m[1] ?? 0);
            $key = "extra_{$num}";
            $filename = basename($file);
            $extraMap[$key] = [
                'label'    => "Extra {$num}",
                'slot'     => 'extra',
                'file'     => $filename,
                'uploaded' => true,
                'size'     => round(filesize($file) / 1024) . ' KB',
                'url'      => asset("storage/acp-proposals/sets/{$this->slug}/{$filename}") . '?v=' . filemtime($file),
            ];
        }

        // Apply stored order for extra pages
        foreach ($this->page_order ?? [] as $k) {
            if (isset($extraMap[$k])) {
                $result[$k] = $extraMap[$k];
                unset($extraMap[$k]);
            }
        }
        // Append remaining extras (not yet in stored order) — numeric sort
        uksort($extraMap, fn ($a, $b) => (int)substr($a, 6) <=> (int)substr($b, 6));
        foreach ($extraMap as $k => $v) {
            $result[$k] = $v;
        }

        return $result;
    }

    /** Auto-rename legacy file (e.g. scope.jpg → scope_schedule.jpg) on first access. */
    private function findOrMigrateLegacy(string $dir, string $baseName, ?string $legacy): ?string
    {
        // Try new name first
        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            $path = "{$dir}/{$baseName}.{$ext}";
            if (file_exists($path)) return $path;
        }
        // Try legacy name and rename
        if ($legacy) {
            foreach (['jpg', 'jpeg', 'png'] as $ext) {
                $legacyPath = "{$dir}/{$legacy}.{$ext}";
                if (file_exists($legacyPath)) {
                    $newPath = "{$dir}/{$baseName}.{$ext}";
                    rename($legacyPath, $newPath);
                    return $newPath;
                }
            }
        }
        return null;
    }

    /** Next available extra page number. */
    public function nextExtraNumber(): int
    {
        $dir   = $this->dir();
        $files = glob("{$dir}/extra_*.{jpg,jpeg,png}", GLOB_BRACE) ?: [];
        if (empty($files)) return 1;
        $nums  = [];
        foreach ($files as $f) {
            preg_match('/extra_(\d+)/', basename($f), $m);
            if (isset($m[1])) $nums[] = (int)$m[1];
        }
        return max($nums) + 1;
    }

    /** Count uploaded pages (content + extra). */
    public function pageCount(): int
    {
        return count(array_filter($this->pageStatus(), fn ($p) => $p['uploaded']));
    }

    /** Base64-encoded backgrounds for DomPDF, in correct PDF order. */
    public function backgroundPaths(): array
    {
        $result = [];
        foreach ($this->pageStatus() as $key => $info) {
            if (! $info['uploaded']) continue;
            $path = $this->dir() . '/' . $info['file'];
            if (file_exists($path)) {
                $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                $result[$key] = "data:{$mime};base64," . base64_encode(file_get_contents($path));
            }
        }
        return $result;
    }

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'design';
        $slug = $base;
        $i    = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}

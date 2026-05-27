<?php

namespace Modules\ACP_Proposals\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ACP_Proposals\Models\ProposalSet;

class ProposalSetController extends Controller
{
    /** GET /api/acp-proposals/sets */
    public function index()
    {
        return response()->json(
            ProposalSet::orderBy('name')->get()->map(fn ($s) => $this->setResource($s))
        );
    }

    /** POST /api/acp-proposals/sets */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $set = ProposalSet::create([
            'name'      => $request->name,
            'slug'      => ProposalSet::makeSlug($request->name),
            'is_active' => true,
        ]);

        return response()->json($this->setResource($set), 201);
    }

    /** PUT /api/acp-proposals/sets/{set} */
    public function update(Request $request, ProposalSet $set)
    {
        $set->update($request->validate([
            'name'      => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]));
        return response()->json($this->setResource($set));
    }

    /** DELETE /api/acp-proposals/sets/{set} */
    public function destroy(ProposalSet $set)
    {
        $dir = $set->dir();
        if (is_dir($dir)) {
            foreach (glob("{$dir}/*") as $f) {
                if (is_file($f)) unlink($f);
            }
            @rmdir($dir);
        }
        $set->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/acp-proposals/sets/{set}/upload/{page}
     *
     * {page} accepts:
     *   'cover' | 'package' | 'scope' | 'why_us' | 'back'  → content slot
     *   'new'                                                 → add new extra page
     *   'extra_N'                                             → replace extra page N
     */
    public function upload(Request $request, ProposalSet $set, string $page)
    {
        $request->validate(['image' => 'required|file|mimes:jpg,jpeg,png|max:10240']);

        $dir = $set->dir();
        $ext = strtolower($request->file('image')->getClientOriginalExtension());
        $ext = in_array($ext, ['jpg', 'jpeg']) ? 'jpg' : 'png';

        $slots = ProposalSet::CONTENT_SLOTS;

        if (isset($slots[$page])) {
            // ── Content slot ──────────────────────────────────────────
            $base = pathinfo($slots[$page]['file'], PATHINFO_FILENAME); // 'cover'
            // Remove any existing file for this slot
            foreach (['jpg', 'jpeg', 'png'] as $e) {
                $old = "{$dir}/{$base}.{$e}";
                if (file_exists($old)) unlink($old);
            }
            $filename = "{$base}.{$ext}";
            $pageKey  = $page;

        } elseif ($page === 'new') {
            // ── New extra page ────────────────────────────────────────
            $num      = $set->nextExtraNumber();
            $filename = "extra_{$num}.{$ext}";
            $pageKey  = "extra_{$num}";

        } elseif (preg_match('/^extra_(\d+)$/', $page, $m)) {
            // ── Replace existing extra page ───────────────────────────
            $num = (int)$m[1];
            foreach (['jpg', 'jpeg', 'png'] as $e) {
                $old = "{$dir}/extra_{$num}.{$e}";
                if (file_exists($old)) unlink($old);
            }
            $filename = "extra_{$num}.{$ext}";
            $pageKey  = $page;

        } else {
            return response()->json(['ok' => false, 'msg' => 'Invalid page key.'], 422);
        }

        $request->file('image')->move($dir, $filename);
        $fullPath = "{$dir}/{$filename}";

        return response()->json([
            'ok'   => true,
            'page' => $pageKey,
            'file' => $filename,
            'size' => round(filesize($fullPath) / 1024) . ' KB',
            'url'  => asset("storage/acp-proposals/sets/{$set->slug}/{$filename}") . '?v=' . filemtime($fullPath),
        ]);
    }

    /**
     * DELETE /api/acp-proposals/sets/{set}/pages/{page}
     * {page} = 'cover' | 'package' | ... | 'extra_N'
     */
    public function deletePage(ProposalSet $set, string $page)
    {
        $dir   = $set->dir();
        $slots = ProposalSet::CONTENT_SLOTS;

        if (isset($slots[$page])) {
            $base = pathinfo($slots[$page]['file'], PATHINFO_FILENAME);
            foreach (['jpg', 'jpeg', 'png'] as $e) {
                $path = "{$dir}/{$base}.{$e}";
                if (file_exists($path)) unlink($path);
            }
        } elseif (preg_match('/^extra_(\d+)$/', $page, $m)) {
            $num = (int)$m[1];
            foreach (['jpg', 'jpeg', 'png'] as $e) {
                $path = "{$dir}/extra_{$num}.{$e}";
                if (file_exists($path)) unlink($path);
            }
            // Remove from page_order
            $order = array_values(array_filter($set->page_order ?? [], fn ($k) => $k !== $page));
            $set->update(['page_order' => $order]);
        } else {
            return response()->json(['ok' => false, 'msg' => 'Invalid page key.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    /** PUT /api/acp-proposals/sets/{set}/reorder — reorder extra pages only */
    public function reorder(Request $request, ProposalSet $set)
    {
        $request->validate(['order' => 'required|array']);
        $set->update(['page_order' => $request->order]);
        return response()->json(['ok' => true]);
    }

    /** PUT /api/acp-proposals/sets/{set}/page-settings */
    public function updatePageSettings(Request $request, ProposalSet $set)
    {
        $request->validate(['page_settings' => 'required|array']);
        $set->update([
            'page_settings' => array_merge($set->page_settings ?? [], $request->page_settings),
        ]);
        return response()->json(['ok' => true, 'page_settings' => $set->getPageSettings()]);
    }

    /** PUT /api/acp-proposals/sets/{set}/layout */
    public function updateLayout(Request $request, ProposalSet $set)
    {
        $request->validate(['layout' => 'required|array']);
        $set->update(['layout' => $request->layout]);
        return response()->json(['ok' => true, 'layout' => $set->getLayout()]);
    }

    // ── Resource helper ───────────────────────────────────────────────

    private function setResource(ProposalSet $set): array
    {
        $pages     = $set->pageStatus();
        $pagesList = array_values(
            array_map(fn ($k, $v) => array_merge(['key' => $k], $v), array_keys($pages), array_values($pages))
        );

        return [
            'id'            => $set->id,
            'name'          => $set->name,
            'slug'          => $set->slug,
            'is_active'     => $set->is_active,
            'page_count'    => $set->pageCount(),
            'pages'         => $pages,
            'pages_list'    => $pagesList,
            'layout'        => $set->getLayout(),
            'page_settings' => $set->getPageSettings(),
        ];
    }
}

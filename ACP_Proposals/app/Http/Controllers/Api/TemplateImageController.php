<?php

namespace Modules\ACP_Proposals\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TemplateImageController extends Controller
{
    private const PAGES = [
        'page1' => ['file' => 'page1_cover.jpg',    'label' => 'Page 1 — Cover'],
        'page2' => ['file' => 'page2_package.jpg',  'label' => 'Page 2 — Our Package'],
        'page3' => ['file' => 'page3_scope.jpg',    'label' => 'Page 3 — Work Scope'],
        'page4' => ['file' => 'page4_why_us.jpg',   'label' => 'Page 4 — Why Choose Us'],
        'page5' => ['file' => 'page5_back.jpg',     'label' => 'Page 5 — Back Cover'],
    ];

    private function templateDir(): string
    {
        $dir = storage_path('app/acp-proposals/templates');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * GET /api/acp-proposals/templates/status
     * Returns which pages have a background image uploaded.
     */
    public function status()
    {
        $dir    = $this->templateDir();
        $result = [];

        foreach (self::PAGES as $key => $info) {
            $path          = "{$dir}/{$info['file']}";
            $result[$key]  = [
                'label'    => $info['label'],
                'file'     => $info['file'],
                'uploaded' => file_exists($path),
                'size'     => file_exists($path) ? round(filesize($path) / 1024) . ' KB' : null,
            ];
        }

        return response()->json($result);
    }

    /**
     * POST /api/acp-proposals/templates/{page}
     * Upload a JPG background image for the given page key (page1–page5).
     */
    public function upload(Request $request, string $page)
    {
        if (! isset(self::PAGES[$page])) {
            return response()->json(['ok' => false, 'msg' => 'Invalid page key.'], 422);
        }

        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        $dir      = $this->templateDir();
        $filename = self::PAGES[$page]['file'];

        // Always store as JPEG — rename if PNG was uploaded
        $request->file('image')->move($dir, $filename);

        return response()->json([
            'ok'   => true,
            'page' => $page,
            'file' => $filename,
            'size' => round(filesize("{$dir}/{$filename}") / 1024) . ' KB',
        ]);
    }

    /**
     * DELETE /api/acp-proposals/templates/{page}
     * Remove the background image for the given page.
     */
    public function delete(string $page)
    {
        if (! isset(self::PAGES[$page])) {
            return response()->json(['ok' => false, 'msg' => 'Invalid page key.'], 422);
        }

        $dir  = $this->templateDir();
        $path = "{$dir}/" . self::PAGES[$page]['file'];

        if (file_exists($path)) {
            unlink($path);
        }

        return response()->json(['ok' => true, 'page' => $page]);
    }
}

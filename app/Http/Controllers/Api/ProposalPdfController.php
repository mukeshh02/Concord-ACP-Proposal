<?php

namespace Modules\ACP_Proposals\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\ACP_Proposals\Models\Proposal;
use Modules\ACP_Proposals\Models\ProposalSet;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalPdfController extends Controller
{
    /**
     * Generate PDF and return download URL.
     * POST /api/acp-proposals/{id}/generate-pdf
     */
    public function generate(Proposal $proposal)
    {
        try {
            $data    = $proposal->data ?? [];
            $bgPaths = $this->buildBgPaths($proposal, $data);
            $layout  = $this->getLayout($proposal);

            $html = view('acpproposals::pdf.proposal', [
                'data'   => $data,
                'bg'     => $bgPaths,
                'layout' => $layout,
            ])->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'dpi'                       => 150,
                    'defaultFont'               => 'serif',
                    'isRemoteEnabled'           => true,
                    'isHtml5ParserEnabled'      => true,
                    'isFontSubsettingEnabled'   => true,
                    'chroot'                    => public_path(),
                ]);

            $filename    = 'proposal_' . $proposal->id . '_' . time() . '.pdf';
            $storagePath = "acp-proposals/{$filename}";
            $fullPath    = storage_path("app/public/{$storagePath}");

            @mkdir(dirname($fullPath), 0755, true);
            file_put_contents($fullPath, $pdf->output());

            $proposal->update([
                'pdf_path' => $storagePath,
                'status'   => 'ready',
            ]);

            return response()->json([
                'ok'       => true,
                'url'      => asset("storage/{$storagePath}"),
                'filename' => $filename,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'ok'  => false,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream PDF directly to browser.
     * GET /api/acp-proposals/{id}/preview-pdf
     */
    public function preview(Proposal $proposal)
    {
        $data    = $proposal->data ?? [];
        $bgPaths = $this->buildBgPaths($proposal, $data);
        $layout  = $this->getLayout($proposal);

        $html = view('acpproposals::pdf.proposal', [
            'data'   => $data,
            'bg'     => $bgPaths,
            'layout' => $layout,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi'                    => 120,
                'defaultFont'            => 'serif',
                'isRemoteEnabled'        => true,
                'isHtml5ParserEnabled'   => true,
                'chroot'                 => public_path(),
            ]);

        return $pdf->stream("proposal_{$proposal->id}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get backgrounds and apply any per-proposal crops.
     */
    private function buildBgPaths(Proposal $proposal, array $data): array
    {
        $bgPaths = $this->getBackgroundPaths($proposal);

        // Apply scope_schedule crop if the user set one
        $cropMm = isset($data['scope']['crop_mm']) ? (float) $data['scope']['crop_mm'] : 0;
        if ($cropMm > 0 && $cropMm < 297 && !empty($bgPaths['scope_schedule'])) {
            $bgPaths['scope_schedule'] = $this->cropImageToMm($bgPaths['scope_schedule'], $cropMm);
        }

        return $bgPaths;
    }

    /**
     * Physically crop a base64 image to show only the top N mm.
     * Uses PHP GD — result is a shorter image (DomPDF renders it at natural height).
     * Assumes the source image represents full A4 height (297 mm).
     */
    private function cropImageToMm(string $base64Data, float $cropMm): string
    {
        try {
            if (!preg_match('/^data:([^;]+);base64,(.+)$/s', $base64Data, $m)) {
                return $base64Data;
            }
            $mime = $m[1];
            $raw  = base64_decode($m[2]);

            $src = @imagecreatefromstring($raw);
            if (!$src) return $base64Data;

            $srcW  = imagesx($src);
            $srcH  = imagesy($src);
            $keepH = (int) round($srcH * min($cropMm / 297.0, 1.0));

            if ($keepH >= $srcH) {
                imagedestroy($src);
                return $base64Data;   // nothing to crop
            }

            $dst = imagecreatetruecolor($srcW, $keepH);
            imagecopy($dst, $src, 0, 0, 0, 0, $srcW, $keepH);
            imagedestroy($src);

            ob_start();
            if ($mime === 'image/png') {
                imagepng($dst);
            } else {
                imagejpeg($dst, null, 92);
                $mime = 'image/jpeg';
            }
            $out = ob_get_clean();
            imagedestroy($dst);

            return "data:{$mime};base64," . base64_encode($out);

        } catch (\Throwable $e) {
            return $base64Data;   // fallback: original image
        }
    }

    /**
     * Get the text zone layout for the proposal.
     */
    private function getLayout(Proposal $proposal): array
    {
        if ($proposal->set_id) {
            $set = ProposalSet::find($proposal->set_id);
            if ($set) return $set->getLayout();
        }
        return ProposalSet::defaultLayout();
    }

    /**
     * Build base64 encoded background image paths for each page.
     */
    private function getBackgroundPaths(Proposal $proposal): array
    {
        if ($proposal->set_id) {
            $set = ProposalSet::find($proposal->set_id);
            if ($set) return $set->backgroundPaths();
        }

        // Legacy fallback
        $templateDir = storage_path('app/public/acp-proposals/templates');
        $pages = [
            'page1' => 'page1_cover.jpg',
            'page2' => 'page2_package.jpg',
            'page3' => 'page3_scope.jpg',
            'page4' => 'page4_why_us.jpg',
            'page5' => 'page5_back.jpg',
        ];

        $result = [];
        foreach ($pages as $key => $filename) {
            $filePath = "{$templateDir}/{$filename}";
            $result[$key] = file_exists($filePath)
                ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($filePath))
                : null;
        }
        return $result;
    }
}

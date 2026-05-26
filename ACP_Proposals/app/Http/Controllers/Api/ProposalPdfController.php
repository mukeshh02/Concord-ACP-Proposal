<?php

namespace Modules\ACP_Proposals\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\ACP_Proposals\Models\Proposal;
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
            $bgPaths = $this->getBackgroundPaths();

            // Render the blade template
            $html = view('acpproposals::pdf.proposal', [
                'data'    => $data,
                'bg'      => $bgPaths,
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

            // Save to storage
            $filename  = 'proposal_' . $proposal->id . '_' . time() . '.pdf';
            $storagePath = "acp-proposals/{$filename}";
            $fullPath    = storage_path("app/public/{$storagePath}");

            @mkdir(dirname($fullPath), 0755, true);
            file_put_contents($fullPath, $pdf->output());

            // Update proposal record
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
        $bgPaths = $this->getBackgroundPaths();

        $html = view('acpproposals::pdf.proposal', [
            'data' => $data,
            'bg'   => $bgPaths,
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

    /**
     * Build base64 encoded background image paths for each page.
     * Using base64 ensures DomPDF always finds the images.
     */
    private function getBackgroundPaths(): array
    {
        $templateDir = storage_path('app/acp-proposals/templates');
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
            if (file_exists($filePath)) {
                $imageData   = base64_encode(file_get_contents($filePath));
                $mimeType    = 'image/jpeg';
                $result[$key] = "data:{$mimeType};base64,{$imageData}";
            } else {
                // Fallback: plain ivory background if image not uploaded yet
                $result[$key] = null;
            }
        }

        return $result;
    }
}

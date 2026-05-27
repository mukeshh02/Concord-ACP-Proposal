<?php

namespace Modules\ACP_Proposals\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ACP_Proposals\Models\Proposal;

class ProposalController extends Controller
{
    /** GET /api/acp-proposals */
    public function index(Request $request)
    {
        $proposals = Proposal::latest()
            ->when($request->deal_id, fn($q, $id) => $q->where('deal_id', $id))
            ->get(['id', 'deal_id', 'title', 'status', 'pdf_path', 'created_at']);

        return response()->json($proposals);
    }

    /** POST /api/acp-proposals */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'deal_id' => 'nullable|integer',
            'set_id'  => 'nullable|exists:acp_proposal_sets,id',
            'title'   => 'required|string|max:255',
            'data'    => 'nullable|array',
        ]);

        $proposal = Proposal::create([
            'deal_id'    => $validated['deal_id'] ?? null,
            'set_id'     => $validated['set_id'] ?? null,
            'title'      => $validated['title'],
            'status'     => 'draft',
            'data'       => $validated['data'] ?? Proposal::defaultData(),
            'created_by' => auth()->id(),
        ]);

        return response()->json($proposal, 201);
    }

    /** GET /api/acp-proposals/{id} */
    public function show(Proposal $proposal)
    {
        return response()->json($proposal);
    }

    /** PUT /api/acp-proposals/{id} */
    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'title'  => 'sometimes|string|max:255',
            'set_id' => 'nullable|exists:acp_proposal_sets,id',
            'status' => 'sometimes|in:draft,ready,sent',
            'data'   => 'sometimes|array',
        ]);

        $proposal->update($validated);

        return response()->json($proposal);
    }

    /** DELETE /api/acp-proposals/{id} */
    public function destroy(Proposal $proposal)
    {
        // Delete old PDF if exists
        if ($proposal->pdf_path && file_exists(storage_path("app/public/{$proposal->pdf_path}"))) {
            unlink(storage_path("app/public/{$proposal->pdf_path}"));
        }
        $proposal->delete();
        return response()->json(['ok' => true]);
    }

    /** GET /api/acp-proposals/defaults */
    public function defaults()
    {
        return response()->json(Proposal::defaultData());
    }
}

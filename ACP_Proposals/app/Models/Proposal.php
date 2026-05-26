<?php

namespace Modules\ACP_Proposals\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $table = 'acp_proposals';

    protected $fillable = [
        'deal_id',
        'title',
        'status',
        'data',
        'pdf_path',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get a nested value from proposal data.
     */
    public function getData(string $section, string $key, mixed $default = null): mixed
    {
        return $this->data[$section][$key] ?? $default;
    }

    /**
     * Default empty data structure for a new proposal.
     */
    public static function defaultData(): array
    {
        return [
            'cover' => [
                'client_name' => '',
                'event_date'  => '',
            ],
            'package' => [
                'items' => [
                    'Photography + Videography',
                    'Cinematic Highlight Film',
                    'Same Day AI Gallery Access',
                ],
            ],
            'scope' => [
                'package_type' => 'SENIOR DIRECTOR',
                'schedule'     => [
                    ['date' => '', 'event' => '', 'team' => ''],
                ],
                'deliverables' => [
                    ['label' => 'SAME DAY ACCESS',    'title' => 'AI Face Recognition Photos'],
                    ['label' => '3+ MIN CINEMATIC',   'title' => 'Wedding Highlight Film'],
                    ['label' => '150 SELECTED PHOTOS','title' => 'Family Album'],
                    ['label' => 'ALL CINEMATIC',      'title' => '3 Instagram Reels'],
                    ['label' => '50 PREMIUM PHOTOS',  'title' => 'Couple Album'],
                    ['label' => '3-HOUR TRADITIONAL', 'title' => 'Wedding Film'],
                ],
                'actual_price' => '',
                'offer_price'  => '',
                'savings'      => '',
                'offer_note'   => 'This offer is available only for this month.',
            ],
            'why_us' => [
                'points' => [
                    '10+ years of cinematic wedding experience',
                    'AI-powered same day face recognition gallery',
                    'Professional team of directors & assistants',
                    'Premium editing with luxury colour grading',
                    'Dedicated album production & delivery',
                ],
            ],
        ];
    }
}

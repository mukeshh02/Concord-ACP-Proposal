<?php

namespace Modules\ACP_Proposals\Providers;

use Modules\Core\Facades\Innoclapps;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Support\ModuleServiceProvider;

class ACPProposalsServiceProvider extends ModuleServiceProvider
{
    // ── Flags ─────────────────────────────────────────────────────
    protected bool $withViews        = true;   // load resources/views
    protected bool $withMigrations   = true;   // load database/migrations
    protected bool $withConfig       = false;  // no config/config.php
    protected bool $withTranslations = false;  // no lang/ folder

    // ── Register ──────────────────────────────────────────────────
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    // ── Setup (called after boot) ─────────────────────────────────
    protected function setup(): void
    {
        $this->registerPermissions();
        $this->registerModuleScript();
    }

    // ── Register pre-built IIFE script ────────────────────────────
    protected function registerModuleScript(): void
    {
        $publicPath = public_path('modules/acpproposals/acpproposals.iife.js');
        if (file_exists($publicPath)) {
            Innoclapps::script('acpproposals', asset('modules/acpproposals/acpproposals.iife.js'));
        }
    }

    // ── Permissions ───────────────────────────────────────────────
    protected function registerPermissions(): void
    {
        Innoclapps::permissions(function ($manager) {
            $manager->group(
                ['name' => 'acp-proposals', 'as' => 'Proposals'],
                function ($manager) {
                    $manager->view('view acp proposals', [
                        'as'          => 'View & Manage Proposals',
                        'permissions' => [
                            'view acp proposals' => 'Can create, view and manage all proposals',
                        ],
                    ]);
                }
            );
        });
    }

    // ── Sidebar menu ──────────────────────────────────────────────
    protected function menu(): array
    {
        return [
            MenuItem::make('Proposals', '/acp-proposals')
                ->icon('DocumentText')
                ->position(52),
        ];
    }

    // ── Required by ModuleServiceProvider ─────────────────────────
    protected function moduleName(): string
    {
        return 'ACP_Proposals';
    }

    protected function moduleNameLower(): string
    {
        return 'acpproposals';
    }
}

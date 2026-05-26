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

    // ── Boot ──────────────────────────────────────────────────────
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Called after the application is fully booted.
     * No CRM-specific calls needed here for this module.
     */
    protected function setup(): void
    {
        // Nothing extra needed — routes + menu handle everything
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

    // ── Required by ModuleServiceProvider (abstract) ──────────────
    protected function moduleName(): string
    {
        return 'ACP_Proposals';
    }

    protected function moduleNameLower(): string
    {
        return 'acpproposals';
    }
}

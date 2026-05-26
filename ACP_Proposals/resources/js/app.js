import ProposalIndex  from './views/ProposalIndex.vue'
import ProposalEditor from './views/ProposalEditor.vue'

if (window.Innoclapps) {
  Innoclapps.booting((app, router) => {

    // Proposals list page
    router.addRoute({
      path:      '/acp-proposals',
      name:      'acp-proposals',
      component: ProposalIndex,
      meta:      { title: 'Premium Proposals' },
    })

    // Create new proposal
    router.addRoute({
      path:      '/acp-proposals/new',
      name:      'acp-proposal-create',
      component: ProposalEditor,
      meta:      { title: 'New Proposal' },
    })

    // Edit existing proposal
    router.addRoute({
      path:      '/acp-proposals/:id',
      name:      'acp-proposal-edit',
      component: ProposalEditor,
      meta:      { title: 'Edit Proposal' },
    })
  })
}

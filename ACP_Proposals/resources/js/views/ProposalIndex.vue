<template>
  <MainLayout>
  <div class="max-w-5xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-neutral-800">💼 Premium Proposals</h1>
        <p class="text-sm text-neutral-500 mt-0.5">
          Fixed-layout luxury wedding proposal PDF builder
        </p>
      </div>
      <IButton variant="primary" @click="createNew">
        + New Proposal
      </IButton>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-16 text-neutral-400">
      Loading proposals…
    </div>

    <!-- Empty state -->
    <div v-else-if="!proposals.length" class="text-center py-20">
      <div class="text-5xl mb-4">📄</div>
      <h3 class="text-lg font-semibold text-neutral-700 mb-2">No proposals yet</h3>
      <p class="text-neutral-400 text-sm mb-6">
        Create your first premium wedding proposal in minutes.
      </p>
      <IButton variant="primary" @click="createNew">
        Create First Proposal
      </IButton>
    </div>

    <!-- Proposals list -->
    <div v-else class="space-y-3">
      <div
        v-for="p in proposals"
        :key="p.id"
        class="bg-white border border-neutral-200 rounded-xl px-5 py-4 flex items-center justify-between hover:border-amber-300 hover:shadow-sm transition"
      >
        <div class="flex items-center gap-4">
          <div class="text-3xl">📋</div>
          <div>
            <div class="font-semibold text-neutral-800">{{ p.title }}</div>
            <div class="text-xs text-neutral-400 mt-0.5">
              Created {{ formatDate(p.created_at) }}
              <span v-if="p.deal_id"> · Deal #{{ p.deal_id }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <IBadge :variant="statusVariant(p.status)">{{ p.status }}</IBadge>

          <IButton variant="secondary" size="sm" @click="edit(p.id)">
            ✏️ Edit
          </IButton>

          <a v-if="p.pdf_path" :href="`/storage/${p.pdf_path}`" target="_blank">
            <IButton variant="success" size="sm">⬇ PDF</IButton>
          </a>

          <IButton variant="danger" size="sm" @click="destroy(p)">
            🗑
          </IButton>
        </div>
      </div>
    </div>

  </div>
  </MainLayout>
</template>

<script>
export default {
  name: 'ProposalIndex',

  data() {
    return {
      proposals: [],
      loading:   true,
    }
  },

  async mounted() {
    await this.fetchProposals()
  },

  methods: {
    async fetchProposals() {
      this.loading = true
      try {
        const { data } = await Innoclapps.request().get('/acp-proposals')
        this.proposals = data
      } catch {
        Innoclapps.error('Failed to load proposals')
      } finally {
        this.loading = false
      }
    },

    createNew() {
      this.$router.push('/acp-proposals/new')
    },

    edit(id) {
      this.$router.push(`/acp-proposals/${id}`)
    },

    async destroy(proposal) {
      if (!confirm(`Delete "${proposal.title}"? This cannot be undone.`)) return
      try {
        await Innoclapps.request().delete(`/acp-proposals/${proposal.id}`)
        this.proposals = this.proposals.filter(p => p.id !== proposal.id)
        Innoclapps.success('Deleted.')
      } catch {
        Innoclapps.error('Delete failed')
      }
    },

    statusVariant(status) {
      return { draft: 'neutral', ready: 'success', sent: 'primary' }[status] || 'neutral'
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric', month: 'short', year: 'numeric',
      })
    },
  },
}
</script>

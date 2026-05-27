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

    <!-- ═══════════════════════════════════════════════════════════
         DESIGN SETS
         ═══════════════════════════════════════════════════════════ -->
    <div class="mt-10">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-lg font-bold text-neutral-800">🎨 PDF Design Templates</h2>
          <p class="text-xs text-neutral-400 mt-0.5">
            Multiple designs (Gold, White, Dark…) — each has its own page backgrounds.
            Add as many pages as needed per design.
          </p>
        </div>
        <button
          @click="showSets = !showSets"
          class="text-sm text-amber-600 hover:text-amber-800 font-medium"
        >
          {{ showSets ? '▲ Hide' : '▼ Manage Designs' }}
        </button>
      </div>

      <div v-if="showSets">
        <!-- New design button -->
        <div class="flex justify-end mb-4">
          <IButton variant="primary" size="sm" @click="showNewSet = true">
            + New Design
          </IButton>
        </div>

        <!-- Empty -->
        <div
          v-if="!sets.length"
          class="text-center py-10 text-neutral-400 text-sm border border-dashed border-neutral-200 rounded-xl"
        >
          No designs yet. Click "+ New Design" to create your first template.
        </div>

        <!-- Design set cards -->
        <div v-else class="space-y-4">
          <div
            v-for="set in sets"
            :key="set.id"
            class="bg-white border rounded-xl overflow-hidden"
            :class="set.is_active ? 'border-amber-200' : 'border-neutral-200 opacity-60'"
          >
            <!-- Set header -->
            <div class="flex items-center justify-between px-5 py-3 bg-neutral-50 border-b border-neutral-200">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-neutral-800">{{ set.name }}</span>
                <span class="text-xs text-neutral-400 font-mono">· {{ set.slug }}</span>
                <span
                  class="text-xs px-2 py-0.5 rounded-full font-medium"
                  :class="set.is_active ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-400'"
                >
                  {{ set.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="text-xs text-neutral-400">
                  · {{ set.page_count }} page{{ set.page_count !== 1 ? 's' : '' }}
                </span>
              </div>
              <div class="flex items-center gap-3">
                <button
                  class="text-xs text-amber-600 hover:text-amber-800"
                  @click="toggleSetActive(set)"
                >
                  {{ set.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button
                  class="text-xs text-indigo-500 hover:text-indigo-700 font-medium"
                  @click="openLayoutEditor(set)"
                >
                  🎯 Edit Zones
                </button>
                <button
                  class="text-xs text-red-400 hover:text-red-600"
                  @click="confirmDeleteSet(set)"
                >
                  🗑 Delete
                </button>
              </div>
            </div>

            <!-- ── Content slots (fixed order, semantic names) ────── -->
            <div class="px-5 pt-4">
              <div class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold mb-2">
                Content Pages <span class="normal-case font-normal">(fixed order — each has its own text zones)</span>
              </div>
              <div class="flex flex-wrap gap-3">
                <div
                  v-for="page in contentPages(set)"
                  :key="page.key"
                  class="text-center w-24"
                >
                  <div
                    class="relative w-24 aspect-[3/4] rounded-lg border-2 overflow-hidden mb-1 transition group cursor-pointer"
                    :class="page.uploaded ? 'border-green-300' : 'border-dashed border-neutral-300 hover:border-amber-400'"
                    @click="triggerSetUpload(set.id, page.key)"
                  >
                    <img v-if="page.uploaded" :src="page.url" class="w-full h-full object-cover pointer-events-none" />
                    <div v-else class="absolute inset-0 flex items-center justify-center text-neutral-300 text-2xl">+</div>

                    <div
                      v-if="page.uploaded"
                      class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition"
                    >
                      <span class="text-white text-xs font-medium">Replace</span>
                    </div>

                    <input
                      :ref="`setFile_${set.id}_${page.key}`"
                      type="file" accept="image/jpeg,image/jpg,image/png" class="hidden"
                      @change="onSetFileChange($event, set.id, page.key)"
                    />
                  </div>
                  <div class="text-xs text-neutral-700 font-medium leading-tight">{{ page.label }}</div>
                  <div v-if="page.uploaded" class="text-[10px] text-green-500">{{ page.size }}</div>
                  <div v-else class="text-[10px] text-neutral-300">Not uploaded</div>
                  <button v-if="page.uploaded" class="mt-0.5 text-[10px] text-red-300 hover:text-red-500"
                    @click.stop="deleteSetPage(set.id, page.key)">remove</button>

                </div>
              </div>
            </div>

            <!-- ── Extra pages (draggable, background-only) ──────── -->
            <div class="px-5 pb-4 mt-4">
              <div class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold mb-2">
                Extra Pages <span class="normal-case font-normal">(background only — drag to reorder)</span>
              </div>
              <div class="flex flex-wrap gap-3 items-start">

                <draggable
                  :list="extraPages(set)"
                  item-key="key"
                  class="flex flex-wrap gap-3"
                  ghost-class="opacity-30"
                  chosen-class="ring-2 ring-amber-400"
                  handle=".drag-handle"
                  @end="onPagesDragEnd(set)"
                >
                  <template #item="{ element: page }">
                    <div class="text-center w-24 select-none">
                      <div class="relative w-24 aspect-[3/4] rounded-lg border-2 border-amber-200 overflow-hidden mb-1 transition group">
                        <img :src="page.url" class="w-full h-full object-cover pointer-events-none" />

                        <div class="drag-handle absolute top-0 left-0 right-0 h-6 flex items-center justify-center cursor-grab active:cursor-grabbing bg-gradient-to-b from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition z-10">
                          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 2a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 8a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 14a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z"/>
                          </svg>
                        </div>
                        <div class="absolute inset-0 top-6 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition cursor-pointer z-10"
                          @click="triggerSetUpload(set.id, page.key)">
                          <span class="text-white text-xs font-medium">Replace</span>
                        </div>
                        <input :ref="`setFile_${set.id}_${page.key}`" type="file"
                          accept="image/jpeg,image/jpg,image/png" class="hidden"
                          @change="onSetFileChange($event, set.id, page.key)" />
                      </div>
                      <div class="text-xs text-neutral-600 font-medium">{{ page.label }}</div>
                      <div class="text-[10px] text-amber-500">{{ page.size }}</div>
                      <button class="mt-0.5 text-[10px] text-red-300 hover:text-red-500"
                        @click.stop="deleteSetPage(set.id, page.key)">remove</button>
                    </div>
                  </template>
                </draggable>

                <!-- Add extra page slot -->
                <div class="text-center w-24">
                  <div class="relative w-24 aspect-[3/4] rounded-lg border-2 border-dashed border-neutral-300 hover:border-amber-400 overflow-hidden mb-1 cursor-pointer transition flex items-center justify-center"
                    @click="triggerNewPage(set.id)">
                    <div class="text-center">
                      <div class="text-3xl text-neutral-300">+</div>
                      <div class="text-[10px] text-neutral-400 mt-0.5 leading-tight px-1">Add<br>Extra</div>
                    </div>
                    <input :ref="`setFileNew_${set.id}`" type="file"
                      accept="image/jpeg,image/jpg,image/png" class="hidden"
                      @change="onNewPageFileChange($event, set.id)" />
                  </div>
                  <div class="text-xs text-neutral-400">Portfolio, Terms…</div>
                  <div class="text-[10px] text-neutral-300">Background only</div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  </MainLayout>

  <!-- New Design Set Modal -->
  <IModal
    v-if="showNewSet"
    :visible="showNewSet"
    title="New Design Template"
    ok-text="Create"
    @ok="createSet"
    @cancel="showNewSet = false"
    @update:visible="showNewSet = $event"
  >
    <IFormGroup label="Design Name">
      <IFormInput
        v-model="newSetName"
        placeholder="e.g. Premium Gold, Classic White…"
        autofocus
        @keyup.enter="createSet"
      />
    </IFormGroup>
  </IModal>

  <!-- Layout / Zone Editor Modal -->
  <IModal
    v-if="layoutEditorSet"
    :visible="!!layoutEditorSet"
    :title="`🎯 Edit Text Zones — ${layoutEditorSet.name}`"
    :ok="false"
    cancel-text="Close"
    size="xl"
    @cancel="layoutEditorSet = null"
    @update:visible="v => { if (!v) layoutEditorSet = null }"
  >
    <DesignLayoutEditor
      :set="layoutEditorSet"
      @saved="onLayoutSaved"
    />
  </IModal>

</template>

<script>
import draggable from 'vuedraggable'

export default {
  name: 'ProposalIndex',
  components: { draggable },

  data() {
    return {
      proposals:   [],
      loading:     true,
      // Design Sets
      sets:            [],
      showSets:        false,
      showNewSet:      false,
      newSetName:      '',
      layoutEditorSet: null,  // set object being edited in zone editor
    }
  },

  async mounted() {
    await Promise.all([
      this.fetchProposals(),
      this.fetchSets(),
    ])
  },

  methods: {
    /* ── Proposals ─────────────────────────────────────────── */

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

    /* ── Design Sets ────────────────────────────────────────── */

    async fetchSets() {
      try {
        const { data } = await Innoclapps.request().get('/acp-proposals/sets')
        this.sets = data
      } catch { /* non-fatal */ }
    },

    async refreshSet(setId) {
      try {
        const { data } = await Innoclapps.request().get('/acp-proposals/sets')
        const updated  = data.find(s => s.id === setId)
        if (updated) {
          const idx = this.sets.findIndex(s => s.id === setId)
          if (idx !== -1) this.sets.splice(idx, 1, updated)  // splice triggers Vue reactivity
        }
      } catch { /* non-fatal */ }
    },

    async createSet() {
      if (!this.newSetName.trim()) return
      try {
        const { data } = await Innoclapps.request().post('/acp-proposals/sets', {
          name: this.newSetName.trim(),
        })
        this.sets.push(data)
        this.newSetName  = ''
        this.showNewSet  = false
        this.showSets    = true
        Innoclapps.success(`Design "${data.name}" created!`)
      } catch {
        Innoclapps.error('Failed to create design')
      }
    },

    async toggleSetActive(set) {
      try {
        const { data } = await Innoclapps.request().put(`/acp-proposals/sets/${set.id}`, {
          is_active: !set.is_active,
        })
        const idx = this.sets.findIndex(s => s.id === data.id)
        if (idx !== -1) this.sets[idx] = data
      } catch {
        Innoclapps.error('Update failed')
      }
    },

    async confirmDeleteSet(set) {
      if (!confirm(`Delete design "${set.name}" and all its uploaded images?`)) return
      try {
        await Innoclapps.request().delete(`/acp-proposals/sets/${set.id}`)
        this.sets = this.sets.filter(s => s.id !== set.id)
        Innoclapps.success('Design deleted.')
      } catch {
        Innoclapps.error('Delete failed')
      }
    },

    /* ── Page upload (existing page — replace) ──────────────── */

    triggerSetUpload(setId, pageKey) {
      const refKey = `setFile_${setId}_${pageKey}`
      const input  = this.$refs[refKey]
      if (!input) return
      const el = Array.isArray(input) ? input[0] : input
      el.value = ''
      el.click()
    },

    async onSetFileChange(event, setId, pageKey) {
      const file = event.target.files[0]
      if (!file) return
      await this._uploadPage(setId, pageKey, file)
    },

    /* ── Page upload (new page) ─────────────────────────────── */

    triggerNewPage(setId) {
      const refKey = `setFileNew_${setId}`
      const input  = this.$refs[refKey]
      if (!input) return
      const el = Array.isArray(input) ? input[0] : input
      el.value = ''
      el.click()
    },

    async onNewPageFileChange(event, setId) {
      const file = event.target.files[0]
      if (!file) return
      // Pass "new" so the server auto-assigns the next page number
      await this._uploadPage(setId, 'new', file)
    },

    /* ── Shared upload helper ───────────────────────────────── */

    async _uploadPage(setId, pageKey, file) {
      try {
        const formData = new FormData()
        formData.append('image', file)

        const { data } = await Innoclapps.request().post(
          `/acp-proposals/sets/${setId}/upload/${pageKey}`,
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        )

        if (data.ok) {
          await this.refreshSet(setId)
          Innoclapps.success(pageKey === 'new'
            ? `Page ${data.page} added!`
            : `Page updated!`
          )
        }
      } catch (e) {
        Innoclapps.error('Upload failed: ' + (e?.response?.data?.message || e.message))
      }
    },

    /* ── Page helpers ───────────────────────────────────────── */

    // Returns only the 6 fixed content slots for a set
    contentPages(set) {
      const SLOTS = ['cover', 'package', 'scope_schedule', 'scope_deliverables', 'why_us', 'back']
      return (set.pages_list || []).filter(p => SLOTS.includes(p.key))
    },

    // Returns only the draggable extra pages for a set
    extraPages(set) {
      return (set.pages_list || []).filter(p => p.key.startsWith('extra_'))
    },

    /* ── Page reorder (extra pages only) ────────────────────── */

    async onPagesDragEnd(set) {
      // extraPages array is mutated in-place by vuedraggable
      const extras = this.extraPages(set)
      const order  = extras.map(p => p.key)
      try {
        await Innoclapps.request().put(`/acp-proposals/sets/${set.id}/reorder`, { order })
      } catch {
        Innoclapps.error('Reorder failed — please try again')
        await this.refreshSet(set.id)
      }
    },

    /* ── Layout Zone Editor ─────────────────────────────────── */

    openLayoutEditor(set) {
      this.layoutEditorSet = set
    },

    onLayoutSaved(newLayout) {
      // Update the set in the list so the editor reflects saved state
      const idx = this.sets.findIndex(s => s.id === this.layoutEditorSet?.id)
      if (idx !== -1) {
        this.sets[idx] = { ...this.sets[idx], layout: newLayout }
        this.layoutEditorSet = { ...this.layoutEditorSet, layout: newLayout }
      }
    },

    /* ── Delete a single page ───────────────────────────────── */

    async deleteSetPage(setId, pageKey) {
      if (!confirm(`Remove ${pageKey}?`)) return
      try {
        await Innoclapps.request().delete(`/acp-proposals/sets/${setId}/pages/${pageKey}`)
        await this.refreshSet(setId)
        Innoclapps.success('Page removed.')
      } catch {
        Innoclapps.error('Delete failed')
      }
    },
  },
}
</script>

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
         TEMPLATE BACKGROUND IMAGES
         ═══════════════════════════════════════════════════════════ -->
    <div class="mt-10">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-lg font-bold text-neutral-800">🖼 Page Background Images</h2>
          <p class="text-xs text-neutral-400 mt-0.5">
            Upload one JPG per page — these become the full-page backgrounds in the PDF
          </p>
        </div>
        <button
          @click="showTemplates = !showTemplates"
          class="text-sm text-amber-600 hover:text-amber-800 font-medium"
        >
          {{ showTemplates ? '▲ Hide' : '▼ Manage Images' }}
        </button>
      </div>

      <div v-if="showTemplates">
        <!-- Status indicators strip -->
        <div class="grid grid-cols-5 gap-3 mb-4">
          <div
            v-for="(info, key) in templates"
            :key="key"
            class="rounded-lg border text-center py-2 px-1 text-xs font-medium"
            :class="info.uploaded
              ? 'border-green-300 bg-green-50 text-green-700'
              : 'border-neutral-200 bg-neutral-50 text-neutral-400'"
          >
            <div class="text-lg mb-1">{{ info.uploaded ? '✅' : '⬜' }}</div>
            <div class="leading-tight">{{ info.label }}</div>
            <div v-if="info.size" class="text-green-500 mt-0.5">{{ info.size }}</div>
          </div>
        </div>

        <!-- Upload cards -->
        <div class="space-y-3">
          <div
            v-for="(info, key) in templates"
            :key="key"
            class="bg-white border rounded-xl px-5 py-4 flex items-center justify-between"
            :class="info.uploaded ? 'border-green-200' : 'border-neutral-200'"
          >
            <!-- Left: info -->
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl"
                :class="info.uploaded ? 'bg-green-100' : 'bg-neutral-100'">
                {{ info.uploaded ? '🖼' : '📷' }}
              </div>
              <div>
                <div class="font-semibold text-neutral-800 text-sm">{{ info.label }}</div>
                <div class="text-xs text-neutral-400 mt-0.5">
                  {{ info.file }}
                  <span v-if="info.size" class="text-green-600 ml-2">· {{ info.size }}</span>
                  <span v-else class="text-amber-500 ml-2">· Not uploaded yet</span>
                </div>
              </div>
            </div>

            <!-- Right: actions -->
            <div class="flex items-center gap-3">
              <!-- Hidden file input -->
              <input
                :ref="`fileInput_${key}`"
                type="file"
                accept="image/jpeg,image/jpg,image/png"
                class="hidden"
                @change="onFileChange($event, key)"
              />

              <!-- Upload button -->
              <IButton
                variant="secondary"
                size="sm"
                :loading="uploading[key]"
                @click="triggerUpload(key)"
              >
                {{ info.uploaded ? '🔄 Replace' : '⬆ Upload' }}
              </IButton>

              <!-- Delete button (only if uploaded) -->
              <IButton
                v-if="info.uploaded"
                variant="danger"
                size="sm"
                @click="deleteTemplate(key)"
              >
                🗑
              </IButton>
            </div>
          </div>
        </div>

        <!-- Hint -->
        <p class="text-xs text-neutral-400 mt-3 text-center">
          Upload A4-sized JPG backgrounds (210×297mm at 150–300 dpi recommended).
          Accepted: JPG, JPEG, PNG · Max 10 MB each.
        </p>
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
      proposals:     [],
      loading:       true,
      showTemplates: false,
      templates:     {},      // keyed by page1–page5
      uploading:     {
        page1: false,
        page2: false,
        page3: false,
        page4: false,
        page5: false,
      },
    }
  },

  async mounted() {
    await Promise.all([
      this.fetchProposals(),
      this.fetchTemplateStatus(),
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

    /* ── Template images ────────────────────────────────────── */

    async fetchTemplateStatus() {
      try {
        const { data } = await Innoclapps.request().get('/acp-proposals/templates/status')
        this.templates = data
      } catch {
        // Non-fatal — templates section just won't show statuses
      }
    },

    triggerUpload(key) {
      const input = this.$refs[`fileInput_${key}`]
      if (input) {
        // $refs may be an array if inside v-for
        const el = Array.isArray(input) ? input[0] : input
        el.value = ''   // allow re-selecting same file
        el.click()
      }
    },

    async onFileChange(event, key) {
      const file = event.target.files[0]
      if (!file) return

      this.uploading[key] = true
      try {
        const formData = new FormData()
        formData.append('image', file)

        const { data } = await Innoclapps.request().post(
          `/acp-proposals/templates/${key}`,
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        )

        if (data.ok) {
          // Refresh status for this page
          this.templates[key] = {
            ...this.templates[key],
            uploaded: true,
            size:     data.size,
          }
          Innoclapps.success(`${this.templates[key].label} uploaded!`)
        }
      } catch (e) {
        Innoclapps.error('Upload failed: ' + (e?.response?.data?.message || e.message))
      } finally {
        this.uploading[key] = false
      }
    },

    async deleteTemplate(key) {
      if (!confirm(`Remove background image for ${this.templates[key]?.label}?`)) return
      try {
        await Innoclapps.request().delete(`/acp-proposals/templates/${key}`)
        this.templates[key] = { ...this.templates[key], uploaded: false, size: null }
        Innoclapps.success('Image removed.')
      } catch {
        Innoclapps.error('Delete failed')
      }
    },
  },
}
</script>

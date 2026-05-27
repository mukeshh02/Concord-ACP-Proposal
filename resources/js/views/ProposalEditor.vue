<template>
  <MainLayout>
  <div class="max-w-6xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">
          {{ isNew ? 'New Proposal' : form.title || 'Edit Proposal' }}
        </h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
          Akash Camera Production · Premium Proposal Builder
        </p>
      </div>
      <div class="flex items-center gap-3">
        <IBadge :variant="statusVariant">{{ form.status }}</IBadge>
        <IButton variant="secondary" @click="$router.push('/acp-proposals')">
          ← Back
        </IButton>
        <IButton variant="secondary" @click="save" :loading="saving">
          💾 Save Draft
        </IButton>
        <IButton variant="secondary" @click="previewPdf" :loading="previewing" :disabled="isNew">
          👁️ Preview
        </IButton>
        <IButton variant="primary" @click="generatePdf" :loading="generating">
          📄 Generate PDF
        </IButton>
      </div>
    </div>

    <!-- Title + Design row -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <IFormGroup label="Proposal Title">
        <IFormInput v-model="form.title" placeholder="e.g. Rahul & Priya – Wedding Proposal" />
      </IFormGroup>

      <IFormGroup label="PDF Design Template">
        <select
          v-model="form.set_id"
          class="w-full text-sm border border-neutral-200 dark:border-neutral-600 rounded-lg px-3 py-2.5 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-amber-300"
        >
          <option :value="null">— No design selected —</option>
          <option
            v-for="s in sets"
            :key="s.id"
            :value="s.id"
          >
            {{ s.name }}
            ({{ s.page_count }} page{{ s.page_count !== 1 ? 's' : '' }})
          </option>
        </select>
        <p class="text-xs text-neutral-400 mt-1">
          Select which background design to use when generating PDF.
          <a class="text-amber-600 underline cursor-pointer" @click="$router.push('/acp-proposals')">
            Manage designs →
          </a>
        </p>
      </IFormGroup>
    </div>

    <!-- Two-column layout: Page nav + Form -->
    <div class="grid grid-cols-12 gap-6">

      <!-- LEFT: Page Navigator -->
      <div class="col-span-3">
        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
          <div class="px-4 py-3 bg-neutral-100 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
            <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Pages</p>
          </div>
          <nav class="divide-y divide-neutral-100 dark:divide-neutral-700">
            <button
              v-for="page in pages"
              :key="page.id"
              @click="activePage = page.id"
              class="w-full text-left px-4 py-3.5 flex items-center gap-3 transition"
              :class="activePage === page.id
                ? 'bg-white dark:bg-neutral-700 border-l-2 border-amber-500 text-neutral-900 dark:text-white'
                : 'text-neutral-600 dark:text-neutral-400 hover:bg-white dark:hover:bg-neutral-700'"
            >
              <span class="text-lg">{{ page.icon }}</span>
              <div>
                <div class="text-sm font-medium">{{ page.label }}</div>
                <div class="text-xs text-neutral-400 dark:text-neutral-500">{{ page.sub }}</div>
              </div>
            </button>
          </nav>
        </div>

        <!-- PDF link if generated -->
        <div v-if="proposal?.pdf_path" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
          <p class="text-xs text-green-700 dark:text-green-400 font-semibold mb-2">✅ PDF Ready</p>
          <a :href="pdfUrl" target="_blank"
             class="text-sm text-green-600 dark:text-green-400 underline break-all">
            Open / Download PDF →
          </a>
        </div>
      </div>

      <!-- RIGHT: Form for active page -->
      <div class="col-span-9">
        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">

          <!-- ─── PAGE 1: COVER ─────────────────────────── -->
          <div v-if="activePage === 'cover'">
            <h2 class="section-heading dark:text-white">📄 Cover Page</h2>
            <p class="section-desc">Client name and event date shown on the cover.</p>
            <div class="grid grid-cols-2 gap-4 mt-4">
              <IFormGroup label="Client / Couple Name *">
                <IFormInput
                  v-model="form.data.cover.client_name"
                  placeholder="e.g. Rahul & Priya Sharma"
                />
              </IFormGroup>
              <IFormGroup label="Event Date">
                <IFormInput
                  v-model="form.data.cover.event_date"
                  placeholder="e.g. 15th February 2025"
                />
              </IFormGroup>
            </div>
          </div>

          <!-- ─── PAGE 2: PACKAGE ───────────────────────── -->
          <div v-if="activePage === 'package'">
            <h2 class="section-heading dark:text-white">📦 Our Package</h2>
            <p class="section-desc">
              Package name shown as large italic title · Description shown as paragraph below it.
            </p>

            <!-- Preview card -->
            <div class="mt-4 mb-5 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-6 py-5 text-center">
              <div class="text-xs text-amber-600 dark:text-amber-400 font-semibold tracking-widest uppercase mb-1">OUR PACKAGE</div>
              <div class="text-2xl font-serif italic text-neutral-800 dark:text-white mt-1">
                {{ form.data.package.name || 'Royal Experience' }}
              </div>
              <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-2 leading-relaxed max-w-xs mx-auto">
                {{ form.data.package.description || 'Your package description will appear here.' }}
              </div>
            </div>

            <IFormGroup label="Package Name *" class="mt-4">
              <IFormInput
                v-model="form.data.package.name"
                placeholder="e.g. Royal Experience"
              />
              <p class="text-xs text-neutral-400 mt-1">Shown as large italic heading on page 2</p>
            </IFormGroup>

            <IFormGroup label="Description" class="mt-4">
              <textarea
                v-model="form.data.package.description"
                rows="3"
                class="w-full text-sm border border-neutral-200 dark:border-neutral-600 rounded-lg px-3 py-2.5 resize-none focus:outline-none focus:ring-2 focus:ring-amber-300 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400"
                placeholder="e.g. Photography & cinematography tailored exclusively for your most special day."
              ></textarea>
              <p class="text-xs text-neutral-400 mt-1">Shown as paragraph text below the package name</p>
            </IFormGroup>
          </div>

          <!-- ─── PAGE 3: SCOPE ─────────────────────────── -->
          <div v-if="activePage === 'scope'">
            <h2 class="section-heading dark:text-white">📋 Work Scope + Deliverables + Charges</h2>
            <p class="section-desc">Day-wise schedule, deliverables grid, and pricing.</p>

            <!-- Package Type -->
            <IFormGroup label="Package Type (shown in header)" class="mt-4">
              <IFormInput
                v-model="form.data.scope.package_type"
                placeholder="e.g. SENIOR DIRECTOR"
              />
            </IFormGroup>

            <!-- Schedule Table -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">
                📅 Day-wise Schedule
              </h3>
              <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg overflow-hidden">
                <table class="w-full">
                  <thead class="bg-neutral-900 dark:bg-neutral-950 text-white">
                    <tr>
                      <th class="text-left px-4 py-3 text-xs font-semibold tracking-wider w-2/5">DAY</th>
                      <th class="text-left px-4 py-3 text-xs font-semibold tracking-wider">TEAM DETAILS</th>
                      <th class="w-24 px-3 text-xs font-semibold tracking-wider text-neutral-400 text-right">ACTIONS</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    <tr v-for="(row, idx) in form.data.scope.schedule" :key="idx"
                        class="bg-white dark:bg-neutral-800">
                      <td class="px-4 py-3 align-top">
                        <input
                          v-model="row.date"
                          class="w-full text-sm border border-neutral-200 dark:border-neutral-600 rounded px-2 py-1.5 mb-1.5 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400"
                          placeholder="e.g. 30 June 2026"
                        />
                        <input
                          v-model="row.event"
                          class="w-full text-sm border border-neutral-200 dark:border-neutral-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400"
                          placeholder="e.g. Royal Palace (Wedding)"
                        />
                      </td>
                      <td class="px-4 py-3 align-top">
                        <textarea
                          v-model="row.team"
                          :rows="row._expanded ? 6 : 3"
                          class="w-full text-sm border border-neutral-200 dark:border-neutral-600 rounded px-2 py-1.5 resize-none transition-all bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400"
                          placeholder="1 Photographer&#10;1 Videographer&#10;1 Cinematographer"
                        ></textarea>
                        <button
                          @click="row._expanded = !row._expanded"
                          class="mt-1 text-xs text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 flex items-center gap-1"
                        >
                          <span>{{ row._expanded ? '▲ Less' : '▼ More rows' }}</span>
                        </button>
                      </td>
                      <td class="px-3 align-middle">
                        <div class="flex flex-col items-center gap-2">
                          <button
                            @click="duplicateScheduleRow(idx)"
                            class="text-xs bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/50 rounded px-2 py-1 font-medium"
                          >📋 Copy</button>
                          <button
                            @click="removeItem('scope.schedule', idx)"
                            class="text-xs text-red-400 hover:text-red-600"
                          >✕ Del</button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <IButton variant="secondary" size="sm" class="mt-2"
                @click="addItem('scope.schedule', {date:'',event:'',team:'',_expanded:false})">
                + Add Day
              </IButton>

              <!-- ✂️ Background crop for this proposal -->
              <div class="mt-3 flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <span class="text-sm">✂️</span>
                <span class="text-xs text-neutral-600 font-medium">Background crop height:</span>
                <input
                  type="number"
                  v-model.number="form.data.scope.crop_mm"
                  min="50" max="297" step="1"
                  placeholder="297 (full page)"
                  class="w-32 text-xs border border-neutral-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-300"
                />
                <span class="text-xs text-neutral-400">mm &nbsp;(preview to check, then adjust)</span>
                <button
                  v-if="form.data.scope.crop_mm"
                  @click="form.data.scope.crop_mm = null"
                  class="text-[10px] text-red-400 hover:text-red-600 ml-1"
                >✕ clear</button>
              </div>
            </div>

            <!-- Deliverables -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1">🎬 Deliverables</h3>
              <p class="text-xs text-neutral-400 mb-3">6 items displayed in 2 columns (left: items 1,3,5 / right: items 2,4,6)</p>
              <div class="space-y-2">
                <div
                  v-for="(d, idx) in form.data.scope.deliverables"
                  :key="idx"
                  class="flex items-center gap-2 p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg border border-neutral-200 dark:border-neutral-600"
                >
                  <span class="text-xs text-neutral-400 font-mono w-5">{{ idx + 1 }}</span>
                  <input
                    v-model="d.label"
                    class="w-2/5 text-xs border border-neutral-200 dark:border-neutral-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-700 text-amber-700 dark:text-amber-400"
                    placeholder="Label (e.g. SAME DAY ACCESS)"
                  />
                  <input
                    v-model="d.title"
                    class="flex-1 text-sm border border-neutral-200 dark:border-neutral-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100"
                    placeholder="Title (e.g. AI Face Recognition Photos)"
                  />
                  <button
                    @click="removeItem('scope.deliverables', idx)"
                    class="text-red-400 hover:text-red-600 text-sm"
                  >✕</button>
                </div>
              </div>
              <IButton variant="secondary" size="sm" class="mt-2"
                @click="addItem('scope.deliverables', {label:'',title:''})">
                + Add Deliverable
              </IButton>
            </div>

            <!-- Pricing -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">💰 Charges</h3>
              <div class="grid grid-cols-3 gap-4">
                <IFormGroup label="Actual Price (strikethrough)">
                  <IFormInput v-model="form.data.scope.actual_price" placeholder="e.g. Rs. 1,60,000" />
                </IFormGroup>
                <IFormGroup label="Offer Price (highlighted)">
                  <IFormInput v-model="form.data.scope.offer_price" placeholder="e.g. Rs. 1,25,000" />
                </IFormGroup>
                <IFormGroup label="Total Savings (badge)">
                  <IFormInput v-model="form.data.scope.savings" placeholder="e.g. Rs. 35,000" />
                </IFormGroup>
              </div>
              <IFormGroup label="Offer Note" class="mt-2">
                <IFormInput
                  v-model="form.data.scope.offer_note"
                  placeholder="e.g. This offer is available only for this month."
                />
              </IFormGroup>
            </div>
          </div>

          <!-- ─── PAGE 4: WHY CHOOSE US ─────────────────── -->
          <div v-if="activePage === 'why_us'">
            <h2 class="section-heading dark:text-white">⭐ Why Choose Us</h2>
            <p class="section-desc">Bullet points shown on the left side of the page.</p>
            <div class="mt-4 space-y-3">
              <div
                v-for="(point, idx) in form.data.why_us.points"
                :key="idx"
                class="flex items-center gap-2"
              >
                <span class="text-amber-500 flex-shrink-0">•</span>
                <IFormInput
                  v-model="form.data.why_us.points[idx]"
                  :placeholder="`Point ${idx + 1}`"
                  class="flex-1"
                />
                <button
                  @click="removeItem('why_us.points', idx)"
                  class="text-red-400 hover:text-red-600 text-lg flex-shrink-0"
                >✕</button>
              </div>
              <IButton variant="secondary" size="sm"
                @click="addItem('why_us.points', '')">
                + Add Point
              </IButton>
            </div>
          </div>

          <!-- ─── PAGE 5: BACK COVER ─────────────────────── -->
          <div v-if="activePage === 'back'">
            <h2 class="section-heading dark:text-white">🎬 Back Cover</h2>
            <p class="section-desc">This page is fully static — your brand photography and tagline.</p>
            <div class="mt-6 p-8 bg-neutral-900 rounded-xl text-center">
              <div class="text-white text-sm font-bold tracking-widest mb-1">AKASH CAMERA PRODUCTION</div>
              <div class="text-amber-400 text-xs mb-6">Rajnandgaon</div>
              <div class="text-neutral-500 text-xs italic">[ Your cinematic photo fills this page ]</div>
              <div class="text-amber-400 text-sm italic mt-6">Let's Create Magic Together</div>
            </div>
            <p class="text-xs text-neutral-400 mt-3 text-center">
              Upload <code class="dark:text-neutral-300">page5_back.jpg</code> via the Proposals list page to set this background.
            </p>
          </div>

        </div>

        <!-- Bottom action bar -->
        <div class="flex justify-between items-center mt-4">
          <div class="text-xs text-neutral-400">
            Auto-save: changes saved when you click "Save Draft"
          </div>
          <div class="flex gap-3">
            <IButton variant="secondary" @click="save" :loading="saving">
              💾 Save Draft
            </IButton>
            <IButton variant="secondary" @click="previewPdf" :loading="previewing" :disabled="isNew">
              👁️ Preview
            </IButton>
            <IButton variant="primary" @click="generatePdf" :loading="generating">
              📄 Generate PDF
            </IButton>
            <a v-if="proposal?.pdf_path" :href="pdfUrl" target="_blank">
              <IButton variant="success">⬇ Download PDF</IButton>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  </MainLayout>
</template>

<script>
export default {
  name: 'ProposalEditor',

  data() {
    return {
      activePage: 'cover',
      saving:     false,
      generating: false,
      previewing: false,
      proposal:   null,
      sets:       [],
      form: {
        title:  '',
        set_id: null,
        status: 'draft',
        data: {
          cover:   { client_name: '', event_date: '' },
          package: {
            name:        'Royal Experience',
            description: 'Photography & cinematography tailored exclusively for your most special day.',
          },
          scope: {
            package_type: 'SENIOR DIRECTOR',
            crop_mm:      null,
            schedule:     [{ date: '', event: '', team: '' }],
            deliverables: [
              { label: 'SAME DAY ACCESS',     title: 'AI Face Recognition Photos' },
              { label: '3+ MIN CINEMATIC',    title: 'Wedding Highlight Film' },
              { label: '150 SELECTED PHOTOS', title: 'Family Album' },
              { label: 'ALL CINEMATIC',       title: '3 Instagram Reels' },
              { label: '50 PREMIUM PHOTOS',   title: 'Couple Album' },
              { label: '3-HOUR TRADITIONAL',  title: 'Wedding Film' },
            ],
            actual_price: '',
            offer_price:  '',
            savings:      '',
            offer_note:   'This offer is available only for this month.',
          },
          why_us: {
            points: [
              '10+ years of cinematic wedding experience',
              'AI-powered same day face recognition gallery',
              'Professional team of directors & assistants',
              'Premium editing with luxury colour grading',
              'Dedicated album production & delivery',
            ],
          },
        },
      },
      pages: [
        { id: 'cover',   icon: '📄', label: 'Cover Page',   sub: 'Client name + date' },
        { id: 'package', icon: '📦', label: 'Our Package',   sub: 'What\'s included' },
        { id: 'scope',   icon: '📋', label: 'Work Scope',    sub: 'Schedule + Pricing' },
        { id: 'why_us',  icon: '⭐', label: 'Why Choose Us', sub: 'Bullet points' },
        { id: 'back',    icon: '🎬', label: 'Back Cover',    sub: 'Static page' },
      ],
    }
  },

  computed: {
    isNew()         { return !this.$route.params.id || this.$route.params.id === 'new' },
    proposalId()    { return this.$route.params.id },
    statusVariant() {
      return { draft: 'neutral', ready: 'success', sent: 'primary' }[this.form.status] || 'neutral'
    },
    pdfUrl() {
      return this.proposal?.pdf_path ? `/storage/${this.proposal.pdf_path}` : null
    },
  },

  async mounted() {
    await this.loadSets()
    if (!this.isNew) await this.loadProposal()
  },

  methods: {
    async loadSets() {
      try {
        const { data } = await Innoclapps.request().get('/acp-proposals/sets')
        this.sets = data.filter(s => s.is_active)
      } catch { /* non-fatal */ }
    },

    async loadProposal() {
      try {
        const { data } = await Innoclapps.request().get(`/acp-proposals/${this.proposalId}`)
        this.proposal      = data
        this.form.title    = data.title
        this.form.set_id   = data.set_id
        this.form.status   = data.status
        this.form.data     = data.data
        // Ensure crop_mm exists for Vue reactivity (old proposals may not have it)
        if (this.form.data?.scope && this.form.data.scope.crop_mm === undefined) {
          this.form.data.scope.crop_mm = null
        }
      } catch {
        Innoclapps.error('Failed to load proposal')
      }
    },

    async save() {
      this.saving = true
      try {
        const payload = {
          title:  this.form.title || 'Untitled Proposal',
          set_id: this.form.set_id,
          status: this.form.status,
          data:   this.form.data,
        }
        if (this.isNew) {
          const { data } = await Innoclapps.request().post('/acp-proposals', payload)
          this.proposal  = data
          this.$router.replace({ params: { id: data.id } })
          Innoclapps.success('Proposal created!')
        } else {
          const { data } = await Innoclapps.request().put(`/acp-proposals/${this.proposalId}`, payload)
          this.proposal  = data
          Innoclapps.success('Saved!')
        }
      } catch {
        Innoclapps.error('Save failed')
      } finally {
        this.saving = false
      }
    },

    async previewPdf() {
      this.previewing = true
      try {
        await this.save()
        if (!this.proposal?.id) return
        // Open preview in new tab — browser renders PDF inline
        window.open(`/api/acp-proposals/${this.proposal.id}/preview-pdf`, '_blank')
      } finally {
        this.previewing = false
      }
    },

    async generatePdf() {
      await this.save()
      if (!this.proposal?.id) return
      this.generating = true
      try {
        const { data } = await Innoclapps.request().post(
          `/acp-proposals/${this.proposal.id}/generate-pdf`
        )
        if (data.ok) {
          this.proposal.pdf_path = `acp-proposals/${data.filename}`
          Innoclapps.success('PDF generated!')
          window.open(data.url, '_blank')
        } else {
          Innoclapps.error('PDF error: ' + data.msg)
        }
      } catch {
        Innoclapps.error('PDF generation failed')
      } finally {
        this.generating = false
      }
    },

    addItem(path, defaultValue) {
      const [section, key] = path.split('.')
      const val = typeof defaultValue === 'object' ? { ...defaultValue } : defaultValue
      this.form.data[section][key].push(val)
    },

    removeItem(path, idx) {
      const [section, key] = path.split('.')
      this.form.data[section][key].splice(idx, 1)
    },

    duplicateScheduleRow(idx) {
      const original = this.form.data.scope.schedule[idx]
      const copy = { ...original, _expanded: false }
      this.form.data.scope.schedule.splice(idx + 1, 0, copy)
    },
  },
}
</script>

<style scoped>
.section-heading {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 0.25rem;
}
.section-desc {
  font-size: 0.8rem;
  color: #9a9a9a;
}
</style>

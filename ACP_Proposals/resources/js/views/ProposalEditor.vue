<template>
  <MainLayout>
  <div class="max-w-6xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-neutral-800">
          {{ isNew ? 'New Proposal' : form.title || 'Edit Proposal' }}
        </h1>
        <p class="text-sm text-neutral-500 mt-0.5">
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
        <IButton variant="primary" @click="generatePdf" :loading="generating">
          📄 Generate PDF
        </IButton>
      </div>
    </div>

    <!-- Title field -->
    <IFormGroup label="Proposal Title" class="mb-6">
      <IFormInput v-model="form.title" placeholder="e.g. Rahul & Priya – Wedding Proposal" />
    </IFormGroup>

    <!-- Two-column layout: Page nav + Form -->
    <div class="grid grid-cols-12 gap-6">

      <!-- LEFT: Page Navigator -->
      <div class="col-span-3">
        <div class="bg-neutral-50 rounded-xl border border-neutral-200 overflow-hidden">
          <div class="px-4 py-3 bg-neutral-100 border-b border-neutral-200">
            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Pages</p>
          </div>
          <nav class="divide-y divide-neutral-100">
            <button
              v-for="page in pages"
              :key="page.id"
              @click="activePage = page.id"
              class="w-full text-left px-4 py-3.5 flex items-center gap-3 transition"
              :class="activePage === page.id
                ? 'bg-white border-l-2 border-amber-500 text-neutral-900'
                : 'text-neutral-600 hover:bg-white'"
            >
              <span class="text-lg">{{ page.icon }}</span>
              <div>
                <div class="text-sm font-medium">{{ page.label }}</div>
                <div class="text-xs text-neutral-400">{{ page.sub }}</div>
              </div>
            </button>
          </nav>
        </div>

        <!-- PDF link if generated -->
        <div v-if="proposal?.pdf_path" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
          <p class="text-xs text-green-700 font-semibold mb-2">✅ PDF Ready</p>
          <a :href="pdfUrl" target="_blank"
             class="text-sm text-green-600 underline break-all">
            Open / Download PDF →
          </a>
        </div>
      </div>

      <!-- RIGHT: Form for active page -->
      <div class="col-span-9">
        <div class="bg-white rounded-xl border border-neutral-200 p-6">

          <!-- ─── PAGE 1: COVER ─────────────────────────── -->
          <div v-if="activePage === 'cover'">
            <h2 class="section-heading">📄 Cover Page</h2>
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
            <h2 class="section-heading">📦 Our Package</h2>
            <p class="section-desc">List what's included in this package. Each line = one item.</p>
            <div class="mt-4 space-y-3">
              <div
                v-for="(item, idx) in form.data.package.items"
                :key="idx"
                class="flex items-center gap-2"
              >
                <span class="text-amber-500 font-bold text-lg flex-shrink-0">—</span>
                <IFormInput
                  v-model="form.data.package.items[idx]"
                  :placeholder="`Item ${idx + 1}`"
                  class="flex-1"
                />
                <button
                  @click="removeItem('package.items', idx)"
                  class="text-red-400 hover:text-red-600 text-lg flex-shrink-0"
                  title="Remove"
                >✕</button>
              </div>
              <IButton variant="secondary" size="sm" @click="addItem('package.items', '')">
                + Add Item
              </IButton>
            </div>
          </div>

          <!-- ─── PAGE 3: SCOPE ─────────────────────────── -->
          <div v-if="activePage === 'scope'">
            <h2 class="section-heading">📋 Work Scope + Deliverables + Charges</h2>
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
              <h3 class="text-sm font-semibold text-neutral-700 mb-3">
                📅 Day-wise Schedule
              </h3>
              <div class="border border-neutral-200 rounded-lg overflow-hidden">
                <table class="w-full">
                  <thead class="bg-neutral-900 text-white">
                    <tr>
                      <th class="text-left px-4 py-3 text-xs font-semibold tracking-wider w-2/5">DAY</th>
                      <th class="text-left px-4 py-3 text-xs font-semibold tracking-wider">TEAM DETAILS</th>
                      <th class="w-10"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-neutral-100">
                    <tr v-for="(row, idx) in form.data.scope.schedule" :key="idx">
                      <td class="px-4 py-3 align-top">
                        <input
                          v-model="row.date"
                          class="w-full text-sm border border-neutral-200 rounded px-2 py-1.5 mb-1.5"
                          placeholder="e.g. 22nd November"
                        />
                        <input
                          v-model="row.event"
                          class="w-full text-sm border border-neutral-200 rounded px-2 py-1.5"
                          placeholder="e.g. Mehendi Ceremony (At Home)"
                        />
                      </td>
                      <td class="px-4 py-3 align-top">
                        <textarea
                          v-model="row.team"
                          rows="2"
                          class="w-full text-sm border border-neutral-200 rounded px-2 py-1.5 resize-none"
                          placeholder="e.g. 1 Photographer · 1 Videographer"
                        ></textarea>
                      </td>
                      <td class="px-3 align-middle">
                        <button
                          @click="removeItem('scope.schedule', idx)"
                          class="text-red-400 hover:text-red-600 text-sm"
                        >✕</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <IButton variant="secondary" size="sm" class="mt-2"
                @click="addItem('scope.schedule', {date:'',event:'',team:''})">
                + Add Day
              </IButton>
            </div>

            <!-- Deliverables -->
            <div class="mt-6">
              <h3 class="text-sm font-semibold text-neutral-700 mb-1">🎬 Deliverables</h3>
              <p class="text-xs text-neutral-400 mb-3">6 items displayed in 2 columns (left: items 1,3,5 / right: items 2,4,6)</p>
              <div class="space-y-2">
                <div
                  v-for="(d, idx) in form.data.scope.deliverables"
                  :key="idx"
                  class="flex items-center gap-2 p-3 bg-neutral-50 rounded-lg border border-neutral-200"
                >
                  <span class="text-xs text-neutral-400 font-mono w-5">{{ idx + 1 }}</span>
                  <input
                    v-model="d.label"
                    class="w-2/5 text-xs border border-neutral-200 rounded px-2 py-1.5 bg-white text-amber-700"
                    placeholder="Label (e.g. SAME DAY ACCESS)"
                  />
                  <input
                    v-model="d.title"
                    class="flex-1 text-sm border border-neutral-200 rounded px-2 py-1.5 bg-white"
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
              <h3 class="text-sm font-semibold text-neutral-700 mb-3">💰 Charges</h3>
              <div class="grid grid-cols-3 gap-4">
                <IFormGroup label="Actual Price (strikethrough)">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm">Rs.</span>
                    <IFormInput
                      v-model="form.data.scope.actual_price"
                      placeholder="1,60,000"
                      class="pl-10"
                    />
                  </div>
                </IFormGroup>
                <IFormGroup label="Offer Price (highlighted)">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm">Rs.</span>
                    <IFormInput
                      v-model="form.data.scope.offer_price"
                      placeholder="1,25,000"
                      class="pl-10"
                    />
                  </div>
                </IFormGroup>
                <IFormGroup label="Total Savings (badge)">
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm">RS</span>
                    <IFormInput
                      v-model="form.data.scope.savings"
                      placeholder="35,000"
                      class="pl-10"
                    />
                  </div>
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
            <h2 class="section-heading">⭐ Why Choose Us</h2>
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
            <h2 class="section-heading">🎬 Back Cover</h2>
            <p class="section-desc">This page is fully static — your brand photography and tagline.</p>
            <div class="mt-6 p-8 bg-neutral-900 rounded-xl text-center">
              <div class="text-white text-sm font-bold tracking-widest mb-1">AKASH CAMERA PRODUCTION</div>
              <div class="text-amber-400 text-xs mb-6">Rajnandgaon</div>
              <div class="text-neutral-500 text-xs italic">[ Your cinematic photo fills this page ]</div>
              <div class="text-amber-400 text-sm italic mt-6">Let's Create Magic Together</div>
            </div>
            <p class="text-xs text-neutral-400 mt-3 text-center">
              Upload <code>page5_back.jpg</code> to <code>storage/app/acp-proposals/templates/</code> to set this page.
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
      proposal:   null,  // loaded proposal record
      form: {
        title:  '',
        status: 'draft',
        data: {
          cover:   { client_name: '', event_date: '' },
          package: { items: ['Photography + Videography', 'Cinematic Highlight Film', 'Same Day AI Gallery'] },
          scope: {
            package_type: 'SENIOR DIRECTOR',
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
        { id: 'cover',   icon: '📄', label: 'Cover Page',       sub: 'Client name + date' },
        { id: 'package', icon: '📦', label: 'Our Package',       sub: 'What\'s included' },
        { id: 'scope',   icon: '📋', label: 'Work Scope',        sub: 'Schedule + Pricing' },
        { id: 'why_us',  icon: '⭐', label: 'Why Choose Us',     sub: 'Bullet points' },
        { id: 'back',    icon: '🎬', label: 'Back Cover',        sub: 'Static page' },
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
      return this.proposal?.pdf_path
        ? `/storage/${this.proposal.pdf_path}`
        : null
    },
  },

  async mounted() {
    if (!this.isNew) {
      await this.loadProposal()
    }
  },

  methods: {
    async loadProposal() {
      try {
        const { data } = await Innoclapps.request().get(`/acp-proposals/${this.proposalId}`)
        this.proposal  = data
        this.form.title  = data.title
        this.form.status = data.status
        this.form.data   = data.data
      } catch {
        Innoclapps.error('Failed to load proposal')
      }
    },

    async save() {
      this.saving = true
      try {
        const payload = {
          title:  this.form.title || 'Untitled Proposal',
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

    async generatePdf() {
      // Save first, then generate
      await this.save()
      if (!this.proposal?.id) return

      this.generating = true
      try {
        const { data } = await Innoclapps.request().post(
          `/acp-proposals/${this.proposal.id}/generate-pdf`
        )
        if (data.ok) {
          this.proposal.pdf_path = `acp-proposals/${data.filename}`
          Innoclapps.success('PDF generated! Click Download to get it.')
          window.open(data.url, '_blank')
        } else {
          Innoclapps.error('PDF error: ' + data.msg)
        }
      } catch (e) {
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

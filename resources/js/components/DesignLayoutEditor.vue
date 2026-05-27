<template>
  <div>
    <!-- Slot tabs — semantic names, not page numbers -->
    <div class="flex gap-1.5 mb-5 flex-wrap">
      <button
        v-for="slot in CONTENT_SLOTS"
        :key="slot.key"
        @click="activeSlot = slot.key"
        class="px-3 py-1.5 text-sm rounded-lg border font-medium transition"
        :class="activeSlot === slot.key
          ? 'bg-amber-500 text-white border-amber-500 shadow-sm'
          : 'border-neutral-200 text-neutral-600 hover:border-amber-300 bg-white'"
      >
        {{ slot.label }}
      </button>
      <span class="text-xs text-neutral-400 self-center ml-2">
        Extra pages have no text zones (background only)
      </span>
    </div>

    <!-- Editor body -->
    <div class="flex gap-5">

      <!-- ── A4 Canvas ──────────────────────────────────────────────── -->
      <div class="flex-shrink-0">
        <div
          ref="canvas"
          class="relative border border-neutral-200 rounded-xl overflow-hidden shadow-sm"
          :style="`width:${CW}px; height:${CH}px; background:#FAF8F5;`"
          @pointermove.prevent="onPointerMove"
          @pointerup="onPointerUp"
          @pointerleave="onPointerUp"
        >
          <img
            v-if="bgUrl"
            :src="bgUrl"
            class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none"
            draggable="false"
          />
          <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-neutral-300 text-sm pointer-events-none gap-2">
            <div class="text-4xl">🖼️</div>
            <div>No background uploaded for this slot</div>
          </div>

          <!-- Zone boxes -->
          <div
            v-for="zone in currentZones"
            :key="zone.key"
            class="absolute rounded border-2 select-none flex items-start overflow-hidden touch-none"
            :style="zoneBoxStyle(zone)"
            @pointerdown.prevent="startDrag($event, zone.key)"
          >
            <span
              class="text-white text-[9px] font-bold leading-none px-1.5 py-1 pointer-events-none"
              :style="`background:${zone.color}; white-space:nowrap; border-bottom-right-radius:4px;`"
            >{{ zone.label }}</span>

            <!-- Right-edge resize handle -->
            <div
              class="absolute right-0 top-0 bottom-0 w-3 cursor-ew-resize flex items-center justify-center"
              :style="`background: linear-gradient(to right, transparent, ${zone.color}88);`"
              @pointerdown.prevent.stop="startResize($event, zone.key)"
            >
              <div class="w-0.5 h-4 rounded-full bg-white/60"></div>
            </div>
          </div>
        </div>

        <p class="text-[11px] text-neutral-400 mt-2 text-center">
          Drag zone to move · Right edge to resize width
        </p>
      </div>

      <!-- ── Fine-tune inputs ───────────────────────────────────────── -->
      <div class="flex-1 min-w-0 overflow-y-auto" :style="`max-height:${CH}px;`">
        <p class="text-[11px] text-neutral-400 uppercase tracking-wider font-semibold mb-3">
          Fine-tune (mm)
        </p>

        <div
          v-for="zone in currentZones"
          :key="zone.key"
          class="mb-4 rounded-xl p-3 border border-neutral-100 bg-neutral-50"
        >
          <div class="flex items-center gap-2 mb-2.5">
            <div class="w-3 h-3 rounded flex-shrink-0" :style="`background:${zone.color};`"></div>
            <span class="text-sm font-semibold text-neutral-700">{{ zone.label }}</span>
          </div>
          <div class="grid grid-cols-3 gap-2">
            <label class="block">
              <span class="text-[10px] text-neutral-400 block mb-0.5">Top (mm)</span>
              <input type="number" v-model.number="localLayout[activeSlot][zone.key].top"
                min="0" max="290" step="0.5"
                class="w-full text-xs border border-neutral-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-300" />
            </label>
            <label class="block">
              <span class="text-[10px] text-neutral-400 block mb-0.5">Left (mm)</span>
              <input type="number" v-model.number="localLayout[activeSlot][zone.key].left"
                min="0" max="200" step="0.5"
                class="w-full text-xs border border-neutral-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-300" />
            </label>
            <label class="block">
              <span class="text-[10px] text-neutral-400 block mb-0.5">Width (mm)</span>
              <input type="number" v-model.number="localLayout[activeSlot][zone.key].width"
                min="10" max="210" step="0.5"
                class="w-full text-xs border border-neutral-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-amber-300" />
            </label>
          </div>
        </div>

        <button
          @click="resetSlot"
          class="text-xs text-neutral-400 hover:text-red-500 underline mt-1"
        >
          Reset "{{ activeSlotLabel }}" to defaults
        </button>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-between mt-5 pt-4 border-t border-neutral-100">
      <p class="text-xs text-neutral-400">
        Positions apply to <strong>all proposals</strong> using the "{{ set.name }}" design.
      </p>
      <IButton variant="primary" @click="save" :loading="saving">
        💾 Save Layout
      </IButton>
    </div>
  </div>
</template>

<script>
// A4 canvas: 420×594 px = exactly 2px/mm
const CW    = 420
const CH    = 594
const SCALE = 2

// Content slots with zones (must match ProposalSet::CONTENT_SLOTS keys)
// 'back' is background-only → no zones, so not listed here
const CONTENT_SLOTS = [
  { key: 'cover',              label: '📄 Cover'           },
  { key: 'package',            label: '📦 Our Package'     },
  { key: 'scope_schedule',     label: '📋 Work Scope'      },
  { key: 'scope_deliverables', label: '📊 Deliverables'    },
  { key: 'why_us',             label: '⭐ Why Choose Us'   },
]

// Zone definitions per slot
const SLOT_ZONES = {
  cover: [
    { key: 'client_name', label: '👤 Client Name', height_mm: 10, color: '#3B82F6' },
    { key: 'event_date',  label: '📅 Event Date',  height_mm: 7,  color: '#10B981' },
  ],
  package: [
    { key: 'package_name', label: '📦 Package Name',        height_mm: 15, color: '#F59E0B' },
    { key: 'package_desc', label: '📝 Package Description', height_mm: 22, color: '#6366F1' },
  ],
  scope_schedule: [
    { key: 'scope_header', label: '📋 Scope Header',   height_mm: 7,  color: '#EF4444' },
    { key: 'scope_table',  label: '📊 Schedule Table', height_mm: 80, color: '#8B5CF6' },
    // ↑ height_mm = 80 to show the table can grow freely on this page
  ],
  scope_deliverables: [
    { key: 'deliverables', label: '✅ Deliverables', height_mm: 100, color: '#F97316' },
    { key: 'charges',      label: '💰 Charges',      height_mm: 55,  color: '#EC4899' },
  ],
  why_us: [
    { key: 'why_us_points', label: '⭐ Why Us Points', height_mm: 70, color: '#14B8A6' },
  ],
}

// Default positions (must match ProposalSet::defaultLayout())
const DEFAULT_LAYOUT = {
  cover:   {
    client_name:  { top:222, left:25,  width:160 },
    event_date:   { top:234, left:25,  width:160 },
  },
  package: {
    package_name: { top:135, left:20,  width:170 },
    package_desc: { top:152, left:20,  width:170 },
  },
  scope_schedule: {
    scope_header: { top:42,  left:0,   width:210 },
    scope_table:  { top:51,  left:10,  width:190 },
  },
  scope_deliverables: {
    deliverables: { top:30,  left:15,  width:180 },
    charges:      { top:195, left:15,  width:180 },
  },
  why_us: {
    why_us_points:{ top:130, left:12,  width:95  },
  },
}

export default {
  name: 'DesignLayoutEditor',

  props: {
    set: { type: Object, required: true },
  },

  emits: ['saved'],

  data() {
    return {
      CONTENT_SLOTS,
      CW, CH,
      activeSlot:  'cover',
      localLayout: this.buildLayout(),
      dragging:    null,
      resizing:    null,
      saving:      false,
    }
  },

  computed: {
    currentZones() {
      return SLOT_ZONES[this.activeSlot] || []
    },

    activeSlotLabel() {
      return CONTENT_SLOTS.find(s => s.key === this.activeSlot)?.label || this.activeSlot
    },

    // Background image for the currently active content slot
    bgUrl() {
      return this.set.pages?.[this.activeSlot]?.url || null
    },
  },

  methods: {
    buildLayout() {
      const saved = this.set.layout || {}
      const result = {}
      for (const [slot, zones] of Object.entries(DEFAULT_LAYOUT)) {
        result[slot] = {}
        for (const [key, def] of Object.entries(zones)) {
          result[slot][key] = { ...def, ...(saved?.[slot]?.[key] || {}) }
        }
      }
      return result
    },

    zoneBoxStyle(zone) {
      const pos = this.localLayout[this.activeSlot]?.[zone.key]
      if (!pos) return { display: 'none' }
      return {
        top:             (pos.top   * SCALE) + 'px',
        left:            (pos.left  * SCALE) + 'px',
        width:           (pos.width * SCALE) + 'px',
        height:          (zone.height_mm * SCALE) + 'px',
        backgroundColor: zone.color + '28',
        borderColor:     zone.color,
        cursor:          'move',
      }
    },

    startDrag(e, zoneKey) {
      const pos = this.localLayout[this.activeSlot]?.[zoneKey]
      if (!pos) return
      e.currentTarget.setPointerCapture(e.pointerId)
      this.dragging = { key: zoneKey, startMouseX: e.clientX, startMouseY: e.clientY, startLeft: pos.left, startTop: pos.top }
    },

    startResize(e, zoneKey) {
      const pos = this.localLayout[this.activeSlot]?.[zoneKey]
      if (!pos) return
      e.currentTarget.setPointerCapture(e.pointerId)
      this.resizing = { key: zoneKey, startMouseX: e.clientX, startWidth: pos.width }
    },

    onPointerMove(e) {
      if (this.dragging) {
        const { key, startMouseX, startMouseY, startLeft, startTop } = this.dragging
        const pos = this.localLayout[this.activeSlot]?.[key]
        if (!pos) return
        const dxMm = (e.clientX - startMouseX) / SCALE
        const dyMm = (e.clientY - startMouseY) / SCALE
        pos.left = Math.round(Math.max(0, Math.min(210 - pos.width, startLeft + dxMm)) * 2) / 2
        pos.top  = Math.round(Math.max(0, Math.min(290,             startTop  + dyMm)) * 2) / 2
      }
      if (this.resizing) {
        const { key, startMouseX, startWidth } = this.resizing
        const pos = this.localLayout[this.activeSlot]?.[key]
        if (!pos) return
        const dxMm = (e.clientX - startMouseX) / SCALE
        pos.width = Math.round(Math.max(20, Math.min(210 - pos.left, startWidth + dxMm)) * 2) / 2
      }
    },

    onPointerUp() { this.dragging = null; this.resizing = null },

    resetSlot() {
      for (const [key, def] of Object.entries(DEFAULT_LAYOUT[this.activeSlot] || {})) {
        this.localLayout[this.activeSlot][key] = { ...def }
      }
    },

    async save() {
      this.saving = true
      try {
        await Innoclapps.request().put(`/acp-proposals/sets/${this.set.id}/layout`, { layout: this.localLayout })
        Innoclapps.success('Layout saved!')
        this.$emit('saved', this.localLayout)
      } catch {
        Innoclapps.error('Failed to save layout')
      } finally {
        this.saving = false
      }
    },
  },
}
</script>

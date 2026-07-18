<template>
  <div class="searchable-select" ref="rootEl">
    <input
      type="text"
      class="form-control form-control-sm"
      :class="{ 'is-invalid': invalid }"
      :placeholder="placeholder"
      v-model="searchQuery"
      :disabled="disabled"
      autocomplete="off"
      @focus="openDropdown"
      @click="openDropdown"
      @keydown.down.prevent="highlightNext"
      @keydown.up.prevent="highlightPrev"
      @keydown.enter.prevent="selectHighlighted"
      @keydown.esc="closeDropdown"
    />

    <ul v-if="isOpen" class="searchable-select__menu list-group">
      <li v-if="filteredOptions.length === 0" class="list-group-item text-muted small">
        Aucun résultat
      </li>
      <li
        v-for="(option, idx) in filteredOptions"
        :key="getValue(option)"
        class="list-group-item list-group-item-action"
        :class="{ active: idx === highlightedIndex }"
        @mousedown.prevent="selectOption(option)"
        @mouseenter="highlightedIndex = idx"
      >
        {{ getLabel(option) }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, required: true },
  optionLabel: { type: String, default: 'label' },
  optionValue: { type: String, default: 'value' },
  placeholder: { type: String, default: '-- Sélectionner --' },
  invalid: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const rootEl = ref(null)
const searchQuery = ref('')
const isOpen = ref(false)
const highlightedIndex = ref(-1)

const getLabel = (option) => option[props.optionLabel]
const getValue = (option) => option[props.optionValue]

function normalize(str) {
  return (str ?? '')
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // ignore les accents dans la recherche
}

const selectedLabel = computed(() => {
  const found = props.options.find((o) => getValue(o) === props.modelValue)
  return found ? getLabel(found) : ''
})

const filteredOptions = computed(() => {
  const needle = normalize(searchQuery.value)
  if (!needle || needle === normalize(selectedLabel.value)) return props.options
  return props.options.filter((o) => normalize(getLabel(o)).includes(needle))
})

// Garde l'input synchronisé avec la sélection tant qu'on ne cherche pas activement
watch(
  () => props.modelValue,
  () => {
    if (!isOpen.value) searchQuery.value = selectedLabel.value
  },
  { immediate: true },
)

function openDropdown() {
  if (props.disabled) return
  isOpen.value = true
  highlightedIndex.value = filteredOptions.value.findIndex(
    (o) => getValue(o) === props.modelValue,
  )
}

function closeDropdown() {
  isOpen.value = false
  searchQuery.value = selectedLabel.value
  highlightedIndex.value = -1
}

function selectOption(option) {
  emit('update:modelValue', getValue(option))
  emit('change', option)
  searchQuery.value = getLabel(option)
  isOpen.value = false
}

function highlightNext() {
  if (!isOpen.value) return openDropdown()
  if (highlightedIndex.value < filteredOptions.value.length - 1) highlightedIndex.value++
}
function highlightPrev() {
  if (!isOpen.value) return openDropdown()
  if (highlightedIndex.value > 0) highlightedIndex.value--
}
function selectHighlighted() {
  const option = filteredOptions.value[highlightedIndex.value]
  if (option) selectOption(option)
}

function handleClickOutside(e) {
  if (rootEl.value && !rootEl.value.contains(e.target)) closeDropdown()
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<style scoped>
.searchable-select {
  position: relative;
}
.searchable-select__menu {
  position: absolute;
  z-index: 1000;
  width: 100%;
  max-height: 220px;
  overflow-y: auto;
  margin-top: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}
.searchable-select__menu .list-group-item {
  cursor: pointer;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}
.searchable-select__menu .list-group-item.active {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: #fff;
}
</style>

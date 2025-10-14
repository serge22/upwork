<template>
  <div class="w-full">
    <Select v-model="internalValue" multiple>
      <SelectTrigger class="h-auto min-h-10 py-2">
        <div class="flex flex-wrap gap-1">
          <!-- Display tags for selected values -->
          <template v-if="internalValue.length > 0">
            <TagsInput 
              :model-value="internalValue" 
              class="border-0 p-0" 
              :displayValue="getOptionLabel"
            >
              <TagsInputItem
                v-for="value in internalValue"
                :value="value"
              >
                <TagsInputItemText>{{ getOptionLabel(value) }}</TagsInputItemText>
                <TagsInputItemDelete
                  @pointerdown.stop.prevent
                  @click.stop.prevent="removeValue(value)"
                />
              </TagsInputItem>
            </TagsInput>
          </template>
          <!-- Placeholder when no values selected -->
          <span v-else class="text-muted-foreground">
            {{ placeholder }}
          </span>
        </div>
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectLabel v-if="label">{{ label }}</SelectLabel>
          <SelectItem 
            v-for="option in options" 
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
} from '@/components/ui/select'
import { TagsInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from "@/components/ui/tags-input"


interface Option {
  value: string;
  label: string;
}

const props = withDefaults(defineProps<{
  modelValue: string[];
  options: Option[];
  label?: string;
  placeholder?: string;
}>(), {
  modelValue: () => [],
  label: '',
  placeholder: 'Select items...'
})

const emit = defineEmits<{
  'update:modelValue': [value: string[]];
}>()

// Create a computed property to handle v-model
const internalValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Helper function to get label from value
const getOptionLabel = (value: string | Record<string, any>) => {
  let val: string;
  if (typeof value === 'string') {
    val = value;
  } else if (value && typeof value === 'object' && 'value' in value) {
    val = value.value;
  } else {
    val = String(value);
  }
  const option = props.options.find(opt => opt.value === val)
  return option?.label || val
}

// Remove a selected value
const removeValue = (value: string) => {
  const newValues = internalValue.value.filter(v => v !== value)
  emit('update:modelValue', newValues)
}
</script>
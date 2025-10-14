<template>
    <div>
        <div v-for="(item, index) in modelValue" :key="index" class="mb-4 p-2 space-y-4 border rounded">
            <div class="grid w-full max-w-sm items-center gap-1.5">
                <Label>Keywords</Label>

                <TagsInput v-model="item.keywords">
                    <TagsInputItem v-for="i in item.keywords" :key="i" :value="i">
                        <TagsInputItemText />
                        <TagsInputItemDelete />
                    </TagsInputItem>

                    <TagsInputInput placeholder="Enter keywords" />
                </TagsInput>
            </div>
            
            <div class="grid w-full max-w-sm items-center gap-1.5">
                <Label for="condition">Condition</Label>
                <Select v-model="item.condition">
                    <SelectTrigger id="condition">
                        <SelectValue placeholder="Select" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All words</SelectItem>
                        <SelectItem value="any">Any word</SelectItem>
                        <SelectItem value="none">None</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid w-full max-w-sm items-center gap-1.5">
                <Label for="location">Location</Label>
                <SelectTags
                    v-model="item.location"
                    :options="locationOptions"
                    placeholder="Anywhere" />
            </div>

            <Button @click="emit('update:modelValue', modelValue.filter((_, i) => i !== index))" type="button" variant="outline" class="mt-2">
                Remove condition
            </Button>
        </div>

        <Button @click="addBlock" type="button" variant="outline">Add condition</Button>
    </div>
</template>

<script setup lang="ts">
import { Label } from "@/components/ui/label"
import { Button } from '@/components/ui/button';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from "@/components/ui/tags-input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup } from "@/components/ui/select";
import SelectTags from "./SelectTags.vue";

const props = defineProps<{
    modelValue: Array<{
        keywords: string[];
        location: string[];
        condition: string;
    }>;
}>();

const emit = defineEmits(['update:modelValue']);

const addBlock = () => {
    const newBlock = {
        keywords: [],
        location: [],
        condition: 'any',
    };
    const updatedValue = [...props.modelValue, newBlock];
    // Emit the updated value to the parent component
    emit('update:modelValue', updatedValue);
};

const locationOptions = [
    { value: 'title', label: 'Job title' },
    { value: 'description', label: 'Job description' },
    { value: 'skills', label: 'Required skills' },
];
</script>

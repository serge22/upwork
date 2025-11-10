<template>
    <Head :title="isEditMode ? 'Edit Feed' : 'Create Feed'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Basic Information Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup>
                            <Field>
                                <FieldLabel>Feed Name *</FieldLabel>
                                <Input v-model="form.name" required placeholder="Enter a descriptive name for your feed" />
                                <InputError class="mt-1" :message="form.errors.name" />
                            </Field>

                            <Field>
                                <FieldLabel>Categories</FieldLabel>
                                <Select v-model="form.category_ids" multiple>
                                    <SelectTrigger>
                                        <div class="flex flex-wrap gap-1">
                                            <!-- Display tags for selected values -->
                                            <template v-if="form.category_ids.length > 0">
                                                <TagsInput v-model="form.category_ids" class="border-0 p-0" :displayValue="getCategoryLabel">
                                                    <TagsInputItem v-for="value in form.category_ids" :key="value" :value="value">
                                                        <TagsInputItemText>{{ getCategoryLabel(value) }}</TagsInputItemText>
                                                        <TagsInputItemDelete @pointerdown.stop.prevent @click.stop.prevent="removeCategory(value)" />
                                                    </TagsInputItem>
                                                </TagsInput>
                                            </template>
                                            <!-- Placeholder when no values selected -->
                                            <span v-else class="text-muted-foreground"> Select categories </span>
                                        </div>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <template v-for="category in props.categories" :key="category.id">
                                            <SelectGroup v-if="!category.parent_id">
                                                <SelectLabel>{{ category.label }}</SelectLabel>
                                                <SelectItem v-for="child in getChildCategories(category.id)" :key="child.id" :value="child.id">
                                                    {{ child.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </template>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-1" :message="form.errors.category_ids" />
                                <FieldDescription>
                                    Optional: Select multiple categories to filter jobs. Leave empty to include all categories.
                                </FieldDescription>
                            </Field>
                        </FieldGroup>
                    </CardContent>
                </Card>

                <!-- Search Criteria Card -->
                <Card>
                    <CardHeader>
                        <CardTitle> Search Criteria </CardTitle>
                        <CardDescription> Define keywords and conditions to match relevant jobs </CardDescription>
                    </CardHeader>
                    <CardContent class="p-6">
                        <KeywordSearch v-model="form.search_query" />
                    </CardContent>
                </Card>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700">
                    <Button type="button" variant="outline" @click="$inertia.visit(route('feeds.index'))" class="px-6 py-2"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing">
                        <svg v-if="form.processing" class="mr-2 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import KeywordSearch from '@/components/KeywordSearch.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { TagsInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { computed } from 'vue';

interface Category {
    id: string;
    parent_id: string | null;
    label: string;
}

type Feed = {
    id: number;
    name: string;
    category_ids: string[];
    search_query: Array<{
        keywords: string[];
        location: string[];
        condition: string;
    }>;
};

interface Props {
    feed: Feed;
    categories: Category[];
}

const props = defineProps<Props>();
const isEditMode = computed(() => !!props.feed?.id);

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: 'Feeds',
        href: '/feeds',
    },
    {
        title: isEditMode.value ? 'Edit' : 'Create',
        href: '#',
    },
]);

const form = useForm({
    name: props.feed?.name ?? '',
    category_ids: props.feed?.category_ids ?? [],
    search_query: props.feed?.search_query ?? [],
});

const getChildCategories = (parentId: string) => {
    return props.categories.filter((category) => category.parent_id === parentId);
};

const getCategoryLabel = (value: any): string => {
    const category = props.categories.find((cat) => cat.id === value);
    return category ? category.label : String(value);
};

const removeCategory = (id: string) => {
    form.category_ids = form.category_ids.filter((catId) => catId !== id);
};

const submit = () => {
    if (isEditMode.value) {
        form.put(route('feeds.update', props.feed.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('feeds.store'), {
            preserveScroll: true,
        });
    }
};
</script>

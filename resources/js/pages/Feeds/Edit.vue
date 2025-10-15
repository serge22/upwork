<template>
    <Head title="Edit Feed" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Feed</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Label for="name">Feed name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />

                    </CardContent>
                    <CardFooter class="space-x-2">
                        <Button :disabled="form.processing">
                            Save Changes
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="my-4">
                    <CardHeader>
                        <CardTitle>Keywords Search</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <KeywordSearch v-model="form.search_query" />
                    </CardContent>
                    <CardFooter class="space-x-2">
                    </CardFooter>
                </Card>
            </form>
        </div>
        
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from "@/components/ui/card";

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import KeywordSearch from '@/components/KeywordSearch.vue';

type Feed = {
    id: number;
    name: string;
    search_query: Array<{
        keywords: string[],
        location: string[],
        condition: string,
    }>,
};
const props = defineProps<{ feed: Feed }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feeds',
        href: '/feeds',
    },
    {
        title: 'Edit',
        href: '#',
    },
];

const form = useForm({
  name: props.feed?.name ?? '',
  search_query: props.feed?.search_query ?? [],
});

const submit = () => {
    if (props.feed?.id) {
        form.put(route('feeds.update', props.feed.id), {
            preserveScroll: true,
        });
    }
};
</script>
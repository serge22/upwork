<template>
    <Head title="Create Feed" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Create Feed</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Label for="name">Feed name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </CardContent>
                    <CardFooter class="space-x-2">
                        <Button :disabled="form.processing"> Create Feed </Button>
                    </CardFooter>
                </Card>

                <Card class="my-4">
                    <CardHeader>
                        <CardTitle>Keywords Search</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <KeywordSearch v-model="form.search_query" />
                    </CardContent>
                    <CardFooter class="space-x-2"> </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import KeywordSearch from '@/components/KeywordSearch.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feeds',
        href: '/feeds',
    },
    {
        title: 'Create',
        href: '/feeds/create',
    },
];

const form = useForm({
    name: '',
    search_query: [],
});

const submit = () => {
    form.post(route('feeds.store'), {
        preserveScroll: true,
    });
};
</script>

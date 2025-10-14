<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardFooter,
} from "@/components/ui/card";
import { Button } from '@/components/ui/button';
import { Trash, Pencil } from "lucide-vue-next"

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feeds',
        href: '/feeds',
    },
];

interface Feed {
    id: number;
    name: string;
    search_query: object;
    is_active: boolean;
    created_at: string;
}

// Define props
defineProps<{
    feeds: Feed[];
}>();

// Methods
const deleteFeed = (id: number) => {
    if (confirm('Are you sure you want to delete this feed?')) {
        router.delete(route('feeds.destroy', id));
    }
};
</script>

<template>
    <Head title="Feeds" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4">
                <Button as-child>
                    <a :href="route('feeds.create')">New Feed</a>
                </Button>
            </div>
            
            <div v-if="feeds.length === 0" class="text-center py-8 text-gray-500">
                No feeds found. Create your first feed to get started!
            </div>
            <div v-else class="space-y-4">
                <template v-for="feed in feeds">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ feed.name }}</CardTitle>
                        </CardHeader>
                        <CardContent>qwerty</CardContent>
                        <CardFooter class="space-x-2">
                            <Button variant="outline" as-child>
                                <a :href="route('feeds.edit', feed.id)">
                                    <Pencil class="w-4 h-4 mr-2" />
                                    Edit
                                </a>
                            </Button>
                            <Button variant="outline" @click="deleteFeed(feed.id)">
                                <Trash class="w-4 h-4 mr-2" />
                                Delete
                            </Button>
                        </CardFooter>
                    </Card>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

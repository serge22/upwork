<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">My Feeds</h1>
            <a
              :href="route('feeds.create')"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Create Feed
            </a>
          </div>

          <div v-if="feeds.length === 0" class="text-center py-8 text-gray-500">
            No feeds found. Create your first feed to get started!
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="feed in feeds"
              :key="feed.id"
              class="border border-gray-200 p-4 rounded-lg hover:shadow-md transition"
            >
              <div class="flex justify-between items-center">
                <div>
                  <h2 class="text-xl font-medium">{{ feed.name }}</h2>
                  <div class="mt-2 text-sm text-gray-600">
                    <div class="flex items-center">
                      <span
                        :class="feed.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        class="px-2 py-1 rounded text-xs mr-2"
                      >
                        {{ feed.is_active ? 'Active' : 'Inactive' }}
                      </span>
                      <p>Created: {{ new Date(feed.created_at).toLocaleDateString() }}</p>
                    </div>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <a
                    :href="route('feeds.edit', feed.id)"
                    class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                  >
                    Edit
                  </a>
                  <button
                    @click="deleteFeed(feed.id)"
                    class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                  >
                    Delete
                  </button>
                </div>
              </div>

              <div class="mt-3 text-sm">
                <p class="font-medium">Search Query:</p>
                <pre class="mt-1 bg-gray-50 p-2 rounded overflow-auto">{{ JSON.stringify(feed.search_query, null, 2) }}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';

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

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'; // Siguraduhin ang path ng layout mo
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, CheckCircle, Clock, ExternalLink } from 'lucide-vue-next';
import { formatDistanceToNow } from 'date-fns'; // Opsyonal: para sa "2 mins ago"

defineProps<{
    notifications: any; // Mula sa controller paginate
}>();

const markAsRead = (id: number, url: string | null) => {
    // Gagamit tayo ng Inertia router para alam ng backend na Inertia request ito
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Pagkatapos ma-mark as read sa DB, doon lang tayo lilipat ng page
            if (url && url !== '#') {
                router.visit(url);
            }
        },
    });
};

const markAllAsRead = () => {
    router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
};


</script>

<template>
    <Head title="Notifications" />

    <AppLayout :breadcrumbs="[{ title: 'Notifications', href: '/notifications' }]">
   
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <!-- <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <Bell class="w-6 h-6" />
                    </div>
                    <h1 class="text-2xl font-bold text-neutral-900">All Notifications</h1>
                </div> -->
                
                <button 
                    @click="markAllAsRead"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors"
                >
                    Mark all as read
                </button>
            </div>

            <div class="bg-white border border-neutral-200 rounded-xl shadow-sm overflow-hidden">
                <div v-if="notifications.data.length === 0" class="p-12 text-center">
                    <div class="inline-flex p-4 bg-neutral-50 rounded-full mb-4">
                        <Clock class="w-8 h-8 text-neutral-300" />
                    </div>
                    <p class="text-neutral-500">No notifications found.</p>
                </div>

                <div v-else class="divide-y divide-neutral-100">
                    <div 
                        v-for="notif in notifications.data" 
                        :key="notif.id"
                        class="p-4 md:p-6 transition-colors hover:bg-neutral-50 flex items-start justify-between gap-4"
                        :class="{'bg-blue-50/20 border-l-4 border-l-blue-500': !notif.is_read}"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-bold text-neutral-900 truncate">
                                    {{ notif.title }}
                                </span>
                                <span v-if="!notif.is_read" class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></span>
                            </div>
                            <p class="text-sm text-neutral-600 mb-2">{{ notif.message }}</p>
                            <span class="text-xs text-neutral-400">
                                {{ notif.created_at }} </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button 
                                 v-if="!notif.is_read"
                                @click="markAsRead(notif.id, notif.url)"
                                class="p-2 text-neutral-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                            >
                                <CheckCircle class="w-5 h-5" />
                            </button>
                            
                            <Link 
                                v-if="notif.url"
                                :href="notif.url"
                              
                                class="p-2 text-neutral-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                            >
                                <ExternalLink class="w-5 h-5" />
                                
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="notifications.links.length > 3" class="p-4 border-t border-neutral-100 flex justify-center gap-2">
                    <Link 
                        v-for="link in notifications.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 text-sm rounded border transition-colors"
                        :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-neutral-600 border-neutral-200 hover:bg-neutral-50'"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
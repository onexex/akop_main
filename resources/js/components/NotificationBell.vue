<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const page = usePage();

/**
 * 1. INITIALIZE LOCAL STATE
 * Kinukuha natin ang initial data mula sa Inertia props.
 */
const localNotifications = ref([...(page.props.auth as any).user.notifications || []]);
const localUnreadCount = ref((page.props.auth as any).user.unread_notifications_count || 0);

/**
 * 2. SYNC WITH INERTIA
 * Mahalaga ito! Kapag nag-navigate ka, kailangang mag-update ng local state 
 * kapag may bagong data na galing sa server via Inertia.
 */
watch(
    () => (page.props.auth as any).user.notifications,
    (newVal) => {
        localNotifications.value = [...newVal];
    },
    { deep: true }
);

watch(
    () => (page.props.auth as any).user.unread_notifications_count,
    (newVal) => {
        localUnreadCount.value = newVal;
    }
);

/**
 * 3. HANDLE CLICK LOGIC
 */
const handleNotificationClick = async (notif: any) => {
    const targetUrl = notif.url;

    // A. Optimistic Update: Gawin nating 'read' agad sa UI para instant ang feeling
    if (!notif.is_read) {
        notif.is_read = true;
        if (localUnreadCount.value > 0) localUnreadCount.value--;
    }

    try {
        // B. Silent Background Update via Axios
        // Siguraduhin na ang route na ito ay tumatanggap ng POST sa Laravel
        await axios.post(`/notifications/${notif.id}/read`);
        
        // C. Navigation
        // Kapag natapos ang axios, itutuloy natin ang paglipat ng page
        if (targetUrl && targetUrl !== '#') {
            router.visit(targetUrl);
        }
    } catch (error) {
        console.error('Notification Error:', error);
        // Kahit mag-fail ang axios (halimbawa: 404), ituloy pa rin ang paglipat ng page
        if (targetUrl && targetUrl !== '#') {
            router.visit(targetUrl);
        }
    }
};
</script>

<template>
    <div class="relative group flex items-center h-full">
        <button class="relative p-2 transition-all rounded-full hover:bg-neutral-100 focus:outline-none">
            <Bell class="w-5 h-5 text-neutral-500 group-hover:text-neutral-900" />
            
            <span 
                v-if="localUnreadCount > 0" 
                class="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center border-2 border-white shadow-sm"
            >
                {{ localUnreadCount }}
            </span>
        </button>

        <div class="absolute right-0 top-full z-50 hidden w-80 pt-2 group-hover:block animate-in fade-in slide-in-from-top-1 text-left">
            <div class="bg-white border border-neutral-200 rounded-lg shadow-xl overflow-hidden">
                
                <div class="flex items-center justify-between p-4 border-b bg-neutral-50/50">
                    <h3 class="text-sm font-semibold text-neutral-800">Notifications</h3>
                    <span v-if="localUnreadCount > 0" class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">
                        {{ localUnreadCount }} New
                    </span>
                </div>

                <div class="max-h-[350px] overflow-y-auto">
                    <div v-if="localNotifications.length === 0" class="p-8 text-center text-neutral-400 text-xs italic">
                        No new notifications
                    </div>
                    
                    <button 
                        v-for="notif in localNotifications" 
                        :key="notif.id"
                        @click="handleNotificationClick(notif)"
                        class="w-full text-left block p-4 transition-colors border-b border-neutral-50 last:border-0 hover:bg-neutral-50 focus:outline-none"
                        :class="{'bg-blue-50/30': !notif.is_read}"
                    >
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-start">
                                <span class="text-xs" :class="notif.is_read ? 'text-neutral-600 font-medium' : 'font-bold text-neutral-900'">
                                    {{ notif.title }}
                                </span>
                                <span v-if="!notif.is_read" class="w-2 h-2 bg-blue-600 rounded-full mt-1 shrink-0"></span>
                            </div>
                            <p class="text-[11px] text-neutral-500 leading-tight">
                                {{ notif.message }}
                            </p>
                        </div>
                    </button>
                </div>

                <div class="p-3 text-center border-t border-neutral-100 bg-neutral-50">
                    <Link href="/notifications" class="text-xs font-medium text-neutral-600 hover:text-blue-600 transition-colors">
                        View All Activity
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
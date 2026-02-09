<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,    
} from '@/components/ui/sidebar';
// import dashboard from '@/routes';
// import { sms } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, FileText, Folder, LayoutGrid, ListIcon, Minus } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { MessageSquare } from 'lucide-vue-next';
import { MapPinMinus } from 'lucide-vue-next';
import { UserRoundCog } from 'lucide-vue-next';
import { ChartNoAxesCombined } from 'lucide-vue-next';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: 'dashboard',
        icon: LayoutGrid,
    },
     {
        title: 'Incoming Alerts',
        href: '/sms',
        icon: MessageSquare,
    },

     {
        title: 'Processed Messages',
        href: '/processed-messages',
        icon: MessageSquare,
    },
     {
        title: 'Geospatial Voting Overview',
        href: '/mapping',
        icon: MapPinMinus,
    },
   

    
];

const footerNavItems: NavItem[] = [
    {
        title: 'Risk Assessment Engine',
        href: '/assessments',
        icon: BookOpen,
    },
    
     {
        title: 'Users Management',
        href: '/users',
        icon: UserRoundCog,
    },
     {
        title: 'Classifications',
        href: '/classifications',
        icon: ListIcon,
    },
    {
        title: 'Incident Watermarks',
        href: '/incidentwatermarks',
        icon: FileText,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="'/dashboard'">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

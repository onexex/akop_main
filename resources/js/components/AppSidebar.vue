<script setup lang="ts">
import { computed } from 'vue'; // Idagdag ito
import { usePage, Link } from '@inertiajs/vue3'; // Idagdag ang usePage
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
import { 
    BookOpen, LayoutGrid, ListIcon, ShieldCheckIcon, 
    MapPinMinus, UserRoundCog ,Bell
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

interface NavItem {
    title: string;
    href: string;
    icon: any;
    // Idagdag ito:
    permissions: string[];
}

interface User {
    permissions?: string[];
    // Add other user properties as needed
}

const page = usePage();

// Helper function para sa permission check
const can = (permissions?: string[]) => {
    if (!permissions || permissions.length === 0) return true;
    const user = page.props.auth.user as User;
    const userPermissions = user.permissions ?? [];
    return permissions.some(permission => userPermissions.includes(permission)) || userPermissions.includes('admin');
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',  
        icon: LayoutGrid,
        permissions: ['access main dashboard'],  
    },
    {
        title: 'Request Dashboard',
        href: '/cash-advances',  
        icon: LayoutGrid,
        permissions: ['access request dashboard'], 
    },
    {
        title: 'Geospatial Voting Overview',
        href: '/mapping',
        icon: MapPinMinus,
        permissions: ['access geospatial'],  
    },
    {
        title: 'Notifications',
        href: '/notifications',
        icon: Bell, // I-import ang Bell icon mula sa lucide-vue-next
        permissions: ['view notifications'],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'AI Assessment Engine',
        href: '/assessments',
        icon: BookOpen,
        permissions: ['access ai assessment'],
    },
    {
        title: 'Roles & Permissions',
        href: '/permissions',
        icon: ShieldCheckIcon,
        permissions: ['access role management'],
    },
    {
        title: 'Users Management',
        href: '/users',
        icon: UserRoundCog,
        permissions: ['access user management'],
    },
    {
        title: 'Survey Settings',
        href: '/survey-settings',
        icon: ListIcon,
        permissions: ['access survey'],
    },
];

 
const filteredMainMav = computed(() => mainNavItems.filter(item => can(item.permissions)));
const filteredFooterNav = computed(() => footerNavItems.filter(item => can(item.permissions)));

console.log('User object:', page.props.auth.user);
console.log('Raw Permissions:', (page.props.auth.user as User).permissions);
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
            <NavMain :items="filteredMainMav" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="filteredFooterNav" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

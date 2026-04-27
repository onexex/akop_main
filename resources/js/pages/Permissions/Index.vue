<template>
    <Head title="User Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Access Control</h2>
                    <p class="text-sm text-muted-foreground">Manage user roles and their associated permissions.</p>
                </div>
                <Button 
                    class="bg-black text-white cursor-pointer hover:bg-slate-800" 
                    @click="openRoleDialog()"
                >
                    <PlusIcon class="mr-2 h-4 w-4" />
                    New Role
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="md:col-span-1 flex flex-col gap-4">
                    <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                        <div class="p-4 border-b bg-muted/20">
                            <h3 class="font-semibold">User Roles</h3>
                        </div>
                        <div class="divide-y">
                            <div 
                                v-for="role in roles" 
                                :key="role.id"
                                :class="['flex items-center justify-between p-4 cursor-pointer transition-colors hover:bg-muted/50', 
                                         selectedRole?.id === role.id ? 'bg-muted border-r-4 border-primary' : '']"
                                @click="selectRole(role)"
                            >
                                <div>
                                    <div class="font-medium capitalize">{{ role.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ role.permissions_count }} Permissions</div>
                                </div>
                                <ChevronRightIcon class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div v-if="selectedRole" class="rounded-xl border bg-card shadow-sm animate-in fade-in duration-300">
                        <div class="flex items-center justify-between border-b bg-muted/20 px-6 py-4">
                            <div>
                                <h3 class="font-semibold capitalize">Permissions for {{ selectedRole.name }}</h3>
                                <p class="text-xs text-muted-foreground">I-check ang mga permissions para sa module na ito.</p>
                            </div>
                            <Button 
                                size="sm" 
                                @click="updateRolePermissions" 
                                :disabled="permissionForm.processing"
                            >
                                <SaveIcon v-if="!permissionForm.processing" class="mr-2 h-4 w-4" />
                                <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                                Save Changes
                            </Button>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                <div v-for="(group, category) in groupedPermissions" :key="category" class="space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-primary border-b pb-2">
                                        {{ category }} Module
                                    </h4>
                                    <div class="grid gap-3">
                                        <div 
                                            v-for="permission in group" 
                                            :key="permission.id"
                                            class="flex items-center space-x-3 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                        >
                                            <input 
                                                type="checkbox" 
                                                :id="'perm-' + permission.id"
                                                v-model="permissionForm.permissions"
                                                :value="permission.name"
                                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer"
                                            />
                                            <label 
                                                :for="'perm-' + permission.id" 
                                                class="flex flex-col cursor-pointer flex-1"
                                            >
                                                <span class="text-sm font-medium leading-none capitalize">
                                                   <label 
                                                        :for="'perm-' + permission.id" 
                                                        class="flex flex-col cursor-pointer flex-1"
                                                    >
                                                        <span class="text-sm font-medium leading-none capitalize">
                                                            {{ permission.name }}
                                                        </span>
                                                    </label>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex h-[400px] flex-col items-center justify-center rounded-xl border border-dashed text-muted-foreground bg-muted/5">
                        <ShieldCheckIcon class="mb-2 h-12 w-12 opacity-20" />
                        <p class="font-medium">Pumili ng Role para i-manage ang permissions.</p>
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:open="roleDialog.open">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Add New User Role</DialogTitle>
                    <p class="text-sm text-muted-foreground">Maglagay ng bagong role name (e.g. Approver, Encoder)</p>
                </DialogHeader>
                <div class="py-4">
                    <Input 
                        v-model="roleForm.name" 
                        label="Role Name" 
                        placeholder="Enter role name..." 
                        :error="roleForm.errors.name"
                    />
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="roleDialog.open = false">Cancel</Button>
                    <Button @click="saveRole" :disabled="roleForm.processing" class="bg-black text-white">
                        Create Role
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { PlusIcon, SaveIcon, Loader2, ChevronRightIcon, ShieldCheckIcon } from 'lucide-vue-next';

// Interfaces
interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions_count?: number;
    permissions: Permission[];
}

const props = defineProps<{
    roles: Role[];
    allPermissions: Permission[];
}>();

const breadcrumbs = [
    { title: 'User Management', href: '#' },
    { title: 'Roles & Permissions', href: '/permissions' },
];

const selectedRole = ref<Role | null>(null);
const roleDialog = ref({ open: false });

const roleForm = useForm({
    name: '',
});

const permissionForm = useForm({
    permissions: [] as string[],
});

// Logic para i-group by Module (Pangalawang salita sa Enum)
const groupedPermissions = computed(() => {
    const groups: Record<string, Permission[]> = {};
    if (!props.allPermissions) return groups;

    props.allPermissions.forEach((perm) => {
        const parts = perm.name.split(' ');
        
        // Kung "approve request level 1", ang category ay "request" (parts[1])
        // Kung "view user", ang category ay "user" (parts[1])
        const category = parts.length > 1 ? parts[1] : 'General';
        
        if (!groups[category]) groups[category] = [];
        groups[category].push(perm);
    });
    return groups;
});

const selectRole = (role: Role) => {
    selectedRole.value = role;
    // I-sync ang checkboxes sa current permissions ng role
    permissionForm.permissions = role.permissions.map(p => p.name);
};

const openRoleDialog = () => {
    roleForm.reset();
    roleDialog.value.open = true;
};

const saveRole = () => {
    roleForm.post('/roles', {
        onSuccess: () => {
            roleDialog.value.open = false;
        }
    });
};

const updateRolePermissions = () => {
    if (!selectedRole.value) return;
    
    permissionForm.put(`/roles/${selectedRole.value.id}/permissions`, {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Magpakita ng toast notification dito
        }
    });
};
</script>
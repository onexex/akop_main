<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';  
import debounce from 'lodash/debounce';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

// Shadcn Imports
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps({ 
    users: Object, 
    filters: Object,
    breadcrumbs: Array,
    roles: Array // List of roles from Backend
});

// Search Logic
const search = ref(props.filters.search || '');
watch(
    search,
    debounce((v) => {
        router.get('/users', { search: v }, { preserveState: true, replace: true });
    }, 300)
);

// Create User Form
const showCreateModal = ref(false);
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '', // Added role
});

const submit = () => {
    form.post('/users/store', {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

// Edit Logic
const editingUser = ref(null);
const showEditModal = ref(false);
const editForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '', // Added role
});

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    // Spatie returns roles as an array, we get the first one
    editForm.role = user.roles.length > 0 ? user.roles[0].name : '';
    editForm.password = '';
    editForm.password_confirmation = '';
    showEditModal.value = true;
};

const updateSubmit = () => {
    editForm.put('/users/update/' + editingUser.value.id, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const deleteUser = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/users/delete/${id}`, {
                onError: () => {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        }
    });
};
</script>

<template>
    <Head title="User Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">User Management</h1>
                <p class="text-muted-foreground">Manage your system users and assign security roles.</p>
            </div>

            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-1 items-center space-x-2">
                    <Input
                        v-model="search"
                        placeholder="Filter users..."
                        class="h-9 w-[150px] lg:w-[250px]"
                    />
                </div>

                <Dialog v-model:open="showCreateModal">
                    <DialogTrigger as-child>
                        <Button size="sm" class="bg-black text-white hover:bg-slate-800">+ Add User</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Create New User</DialogTitle>
                            <DialogDescription>Add a new member and assign their role.</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submit" class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="name">Full Name</Label>
                                <Input id="name" v-model="form.name" />
                                <div v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" />
                                <div v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="role">Assigned Role</Label>
                                <select v-model="form.role" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                                    <option value="" disabled>Select a role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.role" class="text-xs text-red-500">{{ form.errors.role }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="password">Password</Label>
                                <Input id="password" type="password" v-model="form.password" />
                                <div v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing">Create User</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="rounded-md border bg-card p-3 shadow-sm">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>User</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Joined</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell>
                                <div class="font-medium">{{ user.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                            </TableCell>
                            <TableCell>
                                <Badge v-for="role in user.roles" :key="role.id" variant="outline" class="capitalize">
                                    {{ role.name }}
                                </Badge>
                                <span v-if="user.roles.length === 0" class="text-xs text-muted-foreground italic">No role</span>
                            </TableCell>
                            <TableCell>
                                <Badge v-if="user.email_verified_at" variant="secondary" class="bg-green-100 text-green-700">Verified</Badge>
                                <Badge v-else variant="outline">Unverified</Badge>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ user.created_at }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" @click="openEditModal(user)">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-red-600 hover:text-red-700" @click="deleteUser(user.id)">Delete</Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-center space-x-1">
                <Link
                    v-for="(link, k) in users.links"
                    :key="k"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-3 py-1 text-sm border rounded-md"
                    :class="{ 'bg-black text-white': link.active, 'text-muted-foreground opacity-50': !link.url }"
                />
            </div>

            <Dialog v-model:open="showEditModal">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Edit User</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="updateSubmit" class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label>Full Name</Label>
                            <Input v-model="editForm.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Email</Label>
                            <Input type="email" v-model="editForm.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Role</Label>
                            <select v-model="editForm.role" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label>New Password (Optional)</Label>
                            <Input type="password" v-model="editForm.password" />
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="editForm.processing">Update User</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
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
import { Badge } from '@/components/ui/badge'; // Added Badge
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'; // Added Table

const props = defineProps({ 
    users: Object, 
    filters: Object,
    breadcrumbs: Array // Ensure this is handled
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
});

const submit = () => {
    form.post('/users/store', {
        onSuccess: () => {
            showCreateModal.value = false; // FIXED: .value instead of .ref
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
});

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
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
        confirmButtonColor: '#ef4444', // Tailwind red-500
        cancelButtonColor: '#6b7280',  // Tailwind gray-500
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Trigger Inertia delete
            router.delete(`/users/delete/${id}`, {
                // onSuccess: () => {
                //     Swal.fire(
                //         'Deleted!',
                //         'The user has been removed.',
                //         'success'
                //     );
                // },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
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
                <p class="text-muted-foreground">Manage your system users and security settings.</p>
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
                        <Button size="sm">+ Add User</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Create New User</DialogTitle>
                            <DialogDescription>Fill in the details to add a new user.</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submit" class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="name">Full Name</Label>
                                <Input id="name" v-model="form.name" placeholder="John Doe" />
                                <div v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" />
                                <div v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</div>
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
                                <Button type="submit" :disabled="form.processing">Save User</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="rounded-md border bg-card p-3">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>User</TableHead>
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
                                <Badge v-if="user.is_verified" variant="secondary" class="bg-green-100 text-green-700">Verified</Badge>
                                <Badge v-else variant="outline">Unverified</Badge>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ user.created_at }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" @click="openEditModal(user)">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-red-600 hover:text-red-700" @click="deleteUser(user.id)">Delete</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="users.data.length === 0">
                            <TableCell colspan="4" class="h-24 text-center text-muted-foreground">No users found.</TableCell>
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
                    :class="{ 'bg-primary text-white': link.active, 'text-muted-foreground': !link.url }"
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
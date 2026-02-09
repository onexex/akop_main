
<template>
    <Head title="Classifications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" p-6">
            <div class="flex mb-2">
                <div>
                    <Button 
                        variant="outline" 
                        size="lg" 
                        class="cursor-pointer bg-black text-white"
                        @click="createClassification()"
                    >
                        Create Classification
                    </Button>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div class="relative h-[650px] overflow-auto">
                        <Table>
                            <TableHeader
                                class="sticky top-0 z-10 bg-muted/90 shadow-sm backdrop-blur-md"
                            >
                                <TableRow>
                                    <TableHead
                                        >Code</TableHead
                                    >
                                    <TableHead class="font-semibold"
                                        >Description</TableHead
                                    >
                                    <TableHead
                                        >Action</TableHead
                                    >
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <!-- <TableRow
                                    v-for="sms in messages"
                                    :key="sms.id"
                                    class="group border-b transition-colors last:border-0 hover:bg-muted/30"
                                > -->
                                <TableRow
                                        v-for="classification in classifications"
                                        :key="classification.id"
                                        class="group border-b transition-colors last:border-0 hover:bg-muted/30"
                                >
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                                {{ classification.name }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                                {{ classification.description }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    
                                    <TableCell class="px-6 text-right">
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            class="h-8 gap-2 px-3 cursor-pointer transition-all hover:bg-primary hover:text-primary-foreground"
                                            @click="editClassification(classification)"
                                        >
                                            <PencilIcon class="h-3.5 w-3.5" />
                                            <span>Edit</span>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
        <Dialog v-model:open="form.dialogOpen">
            <DialogContent
                class="flex max-h-[95vh] max-w-2xl  flex-col overflow-hidden p-0"
            >
                <DialogHeader class="border-b bg-muted/20 px-6 py-4">
                    <DialogTitle class="text-xl font-bold tracking-tight"
                        >Classification</DialogTitle
                    >
                    <label class="text-sm text-muted-foreground"
                        >Create New Classification.</label
                    >
                </DialogHeader>
                <div class="gird grid-cols-1 px-4">
                    <Input
                        v-model="form.code"
                        label="Code"
                        placeholder="Code"
                        :error="form.errors.code"
                        class="mb-2"
                    />
                    <Input
                        v-model="form.description"
                        label="Description"
                        placeholder="Description"
                        :error="form.errors.description"
                    />
                </div>
                <DialogFooter class="border-t bg-muted/20 px-6 py-4">
                    <DialogClose asChild>
                        <Button variant="ghost">Cancel</Button>
                    </DialogClose>
                    <Button
                        @click="submit()"
                        :disabled="form.processing"
                        class="px-8 cursor-pointer shadow-lg shadow-primary/20 transition-all hover:scale-[1.02]"
                    >
                        <span v-if="!form.processing">Save</span>
                        <span v-else class="flex items-center gap-2">
                            <Loader2 class="h-4 w-4 animate-spin" />
                            Saving...
                        </span>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
<script setup lang="ts">   
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import { type BreadcrumbItem } from '@/types';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    import Button from '@/components/ui/button/Button.vue';
    import {
        Dialog,
        DialogClose,
        DialogContent,
        DialogFooter,
        DialogHeader,
        DialogTitle,
    } from '@/components/ui/dialog';
    import Input from '@/components/ui/input/Input.vue';
import { Loader2, PencilIcon } from 'lucide-vue-next';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Classifications',
            href: '/classifications',
        },
    ];

    defineProps({
        classifications: {
            type: Object,
            default: () => {}
        },
    })

    const form = useForm({
        code: '',
        description: '',
        dialogOpen: false,
        id: 0,
    })

    const createClassification = () => {
        form.id = 0
        form.dialogOpen = true
        form.code = ""
        form.description = ""
    }

    const submit = () => {
        form.errors = {}
        if (form.code == "") {
            form.errors = {
                'code': 'Please input code'
            }
            return
        }
        if (form.description == "") {
            form.errors = {
                'description': 'Please input description'
            }
            return
        }

        if (form.id == 0) {
            form.post('/classifications', {
                onSuccess: () => {
                    form.reset();
                },
            })
        } else {
            form.put(`/classifications/${form.id}`, {
                onSuccess: () => {
                    form.reset();
                },
            })
        }
    }

    const editClassification = (item: {
            name: string;
            description: string;
            id: number;
        }) => {
        form.code = item.name
        form.description = item.description
        form.id = item.id
        form.dialogOpen = true
    }
</script>
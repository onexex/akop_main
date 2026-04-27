<template>
    <Head title="Classifications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-2 flex">
                <div>
                    <Button
                        variant="outline"
                        size="lg"
                        class="cursor-pointer bg-black text-white"
                        @click="CreateSurvey()"
                    >
                        Create Survey
                    </Button>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <div
                    class="overflow-hidden rounded-xl border bg-card shadow-sm"
                >
                    <div class="relative h-[650px] overflow-auto">
                        <Table>
                            <TableHeader
                                class="sticky top-0 z-10 bg-muted/90 shadow-sm backdrop-blur-md"
                            >
                                <TableRow>
                                    <TableHead>Caption</TableHead>
                                    <TableHead class="font-semibold"
                                        >Description</TableHead
                                    >
                                    <TableHead class="font-semibold"
                                        >Remarks</TableHead
                                    >
                                    <TableHead class="font-semibold"
                                        >Address Tag</TableHead
                                    >
                                    <TableHead>Action</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <!-- <TableRow
                                    v-for="sms in messages"
                                    :key="sms.id"
                                    class="group border-b transition-colors last:border-0 hover:bg-muted/30"
                                > -->
                                <TableRow
                                    v-for="survey in surveys"
                                    :key="survey.id"
                                    class="group border-b transition-colors last:border-0 hover:bg-muted/30"
                                >
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                                {{ survey.id }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                                {{ survey.caption }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                                {{ survey.remarks }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                               {{ 
                                                `${survey.address_property?.barangay || ''}, ${survey.address_property?.city_municipality || ''}, ${survey.address_property?.province || ''}` 
                                                }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="px-6 text-right">
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            class="h-8 cursor-pointer gap-2 px-3 transition-all hover:bg-primary hover:text-primary-foreground"
                                            @click="editClassification(survey)"
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
                class="flex max-h-[95vh] max-w-2xl flex-col overflow-hidden p-0"
            >
                <DialogHeader class="border-b bg-muted/20 px-6 py-4">
                    <DialogTitle class="text-xl font-bold tracking-tight"
                        >Survey</DialogTitle
                    >
                    <label class="text-sm text-muted-foreground"
                        >Create New Survey.</label
                    >
                </DialogHeader>
                <div class="gird grid-cols-1 px-4">
                    <Input
                        v-model="form.caption"
                        label="Tittle"
                        placeholder="Title"
                        :error="form.errors.caption"
                        class="mb-2"
                    />
                    <Input
                        v-model="form.remarks"
                        label="Remarks"
                        placeholder="Remarks"
                        :error="form.errors.remarks"
                    />

                    <CustomDropDown
                        label="Select Province"
                        v-model="form.address_tag"
                        :options="provinceOptions"
                        placeholder="Piliin ang Probinsya"
                        @change="fetchCities"
                        class="mb-2 mt-2"
                    />

                    <div v-if="form.errors.city_tag" class="text-red-500 text-xs mt-1">
                        {{ form.errors.city_tag }}
                    </div>

                    
                      <!-- @change="cityOptions = provinceOptions.find((option) => option.value === form.address_tag)?.cities || []" -->
                    <CustomDropDown
                        label="Select City/Municipality"
                        v-model="form.city_tag"
                        :options="cityOptions"
                        :placeholder="form.address_tag ? 'Piliin ang Lungsod' : 'Pumili muna ng Probinsya'"
                        :disabled="!form.address_tag"
                         class="mb-2"
                         @change="fetchBarangays"
                    />
                    <div v-if="form.errors.city_tag" class="text-red-500 text-xs mt-1">
                        {{ form.errors.city_tag }}
                    </div>

                    <!-- <CustomDropDown 
                        label="Barangay"
                        v-model="form.brgy_tag" 
                        :options="brgyOptions" 
                        :disabled="brgyOptions.length === 0"
                        placeholder="Piliin ang Barangay"
                    /> -->

                    <CustomDropDown 
                        label="Barangay"
                        v-model="form.brgy_tag" 
                        :options="brgyOptions" 
                        :disabled="!form.city_tag" 
                        :placeholder="!form.city_tag ? 'Select City first' : 'Select Barangay'"
                    />
                    <div v-if="form.errors.brgy_tag" class="text-red-500 text-xs mt-1">
                        {{ form.errors.brgy_tag }}
                    </div>

                </div>
                <DialogFooter class="border-t bg-muted/20 px-6 py-4">
                    <DialogClose asChild>
                        <Button variant="ghost">Cancel</Button>
                    </DialogClose>
                    <Button
                        @click="submit()"
                        :disabled="form.processing"
                        class="cursor-pointer px-8 shadow-lg shadow-primary/20 transition-all hover:scale-[1.02]"
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
    import { ref } from 'vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Head, router, useForm } from '@inertiajs/vue3';

    import CustomDropDown from '@/components/CustomDropDown.vue';
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
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    import { type BreadcrumbItem } from '@/types';
    import { Loader2, PencilIcon } from 'lucide-vue-next';
    import axios from 'axios';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Survey Settings',
            href: '/survey-settings',
        },
    ];

    interface DropDownOption {
        label: string;
        value: string | number;
    }

    const props = defineProps({
        surveys: {
            type: Object,
            default: () => {},
        },
    provinceOptions: {
            type: Array as () => DropDownOption[],
            default: () => []
        }
    });

    const cityOptions = ref<DropDownOption[]>([]); 
    const brgyOptions = ref<DropDownOption[]>([]); 

    const form = useForm({
        caption: '',
        remarks: '',
        address_tag: '',
        city_tag: '',
        brgy_tag: '',
        dialogOpen: false,
        id: 0,
    });

    const CreateSurvey = () => {
        form.id = 0;
        form.dialogOpen = true;
        form.caption = '';
        form.remarks = '';
        form.brgy_tag = '';
        
        form.address_tag = '';
    };

    const submit = () => {
        
        form.clearErrors();
 
        let hasError = false;

        if (!form.caption) {
            form.setError('caption', 'Please input caption');
            hasError = true;
        }
        if (!form.remarks) {
            form.setError('remarks', 'Please input remarks');
            hasError = true;
        }
        if (!form.address_tag) {
            form.setError('address_tag', 'Please select a location');
            hasError = true;
        }
         if (!form.city_tag) {
            form.setError('city_tag', 'Please select a city');
            hasError = true;
        }
        if (!form.brgy_tag) {
            form.setError('brgy_tag', 'Please select a barangay');
            hasError = true;
        }

        if (hasError) return;

        const method = form.id === 0 ? 'post' : 'put';
        const url = form.id === 0 ? '/survey-settings' : `/survey-settings/${form.id}`;

        form.submit(method, url, {
            onSuccess: () => {
                form.reset();
                // Pwede ka mag-add ng toast notification dito para sa Akop Member app mo
            },
            onError: (errors) => {
                console.log("Server validation failed:", errors);
            },
            preserveScroll: true,
        });
    };

    const editClassification = (item: {
        caption: string;
        remarks: string;
        address_tag: string;
        id: number;
    }) => {
        form.caption = item.caption;
        form.remarks = item.remarks;
        form.address_tag = item.address_tag;
        form.city_tag = item.address_tag;
        form.id = item.id;
        form.dialogOpen = true;
    };

    const fetchCities = async (provinceId: any) => {
        const selected = props.provinceOptions.find((p: any) => p.value === provinceId);
        
        if (selected) {
            try {
                const response = await axios.get('/survey-settings/fetch-cities', {
                    params: { province_name: selected.label }
                });

                cityOptions.value = response.data;
                form.city_tag = ''; 
            } catch (error) {
                console.error("Maling pag-fetch:", error);
            }
        }
    };

    const fetchBarangays = async (cityId: any) => {
   
    const selected = cityOptions.value.find((c: any) => c.value === cityId);
        
        if (selected) {
            try {
                const response = await axios.get('/survey-settings/fetch-barangays', {
                    params: { city_name: selected.label }
                });
                brgyOptions.value = response.data;
                form.brgy_tag = ''; // I-reset ang brgy field sa form
            } catch (error) {
                console.error("Error fetching barangays:", error);
            }
        }
    };
</script>

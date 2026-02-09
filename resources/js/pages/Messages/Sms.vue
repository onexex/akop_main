<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';
    import { Head, router, useForm } from '@inertiajs/vue3';
    import { Archive, PlusIcon } from 'lucide-vue-next';
    import { Loader2 } from 'lucide-vue-next';
    import type { AcceptableValue } from 'reka-ui'
    import { XIcon} from 'lucide-vue-next';


    // shadcn/ui table components
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
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    
    import {
        Pagination,
        PaginationContent,
        PaginationItem,
        PaginationPrevious,
        PaginationNext,
        PaginationEllipsis,
    } from '@/components/ui/pagination'
   
    import SubjectCombobox from './Partials/SubjectCombobox.vue'
    import { Textarea } from '@/components/ui/textarea';
    import { reactive, computed, ref, onBeforeUnmount } from 'vue';
    import axios from 'axios';
    import Swal from 'sweetalert2';

    interface Messages {
        data?: any,
        per_page?: number,
        total?: number,
        current_page?: number,
    }

    const props=defineProps<{
        messages: Messages;
        provinces: {
            province: string;
            region: string;
        }[];
        regions: {
            region: string;
        }[];
        barangays: {
            barangay: string;
            id: number;
            city_municipality: string;
        }[];
        cities: {
            city_municipality: string;
            province: string;
        }[];
        classifications: {
            id: number;
            name: string;
            description: string;
            other: number;
        }[];
        filenumber: string,
        subjects: string[];
        methodofcollections: string[];
        reporters: string[];
        sources: string[];
        filters: {
            status?: string,
            dateFrom?: string,
            dateTo?: string,
        }
    }>();

const form = useForm({
    dialogueOpen: false,
    smsId: 0,
    smsinformation: '',
    receivedAt: '',
    file_number: props.filenumber,
    reference: '',
    subject: '',
    date_of_report: '',
    reporter: '',
    designation: '',
    evaluation: '',
    source: '',
    dateAcquired: '',
    mannerAcquired: '',
    informationProper: '',
    analysis: '',
    actions: '',
    attachments: [] as File[],
    classificationId: null as number | null,
    selectedRegion: '',
    selectedProvince: '',
    selectedCity: '',
    selectedBarangay: '',
});

const previews = ref<string[]>([])

const addToSMS = (item: {
    message: string;
    received_at: string;
    id: number;
}) => {
    form.dialogueOpen = true;
    form.smsinformation = item.message;
    form.receivedAt = item.received_at;
    form.smsId = item.id;
};

const submit = () => {
    form.errors = {};
    if (!form.classificationId) {
        form.errors.classificationId = 'Classification is required.';
        return;
    }

    if (!form.selectedBarangay) {
        form.errors.selectedBarangay = 'Barangay is required.';
        return;
    }

    form.post('/sms/fetch-message', {
        onSuccess: () => {
            form.reset();
        },
    });
};

    const filters = reactive({
        date_from: props.filters.dateFrom,
        date_to: props.filters.dateTo,
        status: props.filters.status ?? 'unprocessed',  
    });

    function onAttachmentsChange(event: Event) {
        const target = event.target as HTMLInputElement
        if (!target.files) return

        const files = Array.from(target.files)

        form.attachments.push(...files)

        files.forEach(file => {
            previews.value.push(URL.createObjectURL(file))
        })

        target.value = ''
    }

    function removeImage(index: number) {
        URL.revokeObjectURL(previews.value[index]) 
        previews.value.splice(index, 1)
        form.attachments.splice(index, 1)
    }

    onBeforeUnmount(() => {
        previews.value.forEach(url => URL.revokeObjectURL(url))
    })

    async function changedClassification(value: AcceptableValue) {
        const response = await axios.get('/processed-sms-get-reference', {
            params: {
                classification_id: value,
            }
        })
        if (response.data) {
            form.reference = response.data
        }
    }

    
    function changePage(page: number) {
        router.get(
            `/sms?page=${page}`,
            {
                status: filters.status,
                dateFrom: filters.date_from,
                dateTo: filters.date_to
            },
            { preserveScroll: true }
        )
    }

    function clearFilter() {
        Object.assign(filters, { date_from: '', date_to: '', status: 'unprocessed' })
        router.get(
            `/sms`,
            { preserveScroll: true }
        )
    }

    function changeStatus() {
        router.get(
            `/sms`,
            {
                status: filters.status,
                dateFrom: filters.date_from,
                dateTo: filters.date_to
            },
            { preserveScroll: true }
        )
    }

    function changeDate() {
        if (filters.date_from && filters.date_to) {
            router.get(
                `/sms`,
                {
                    status: filters.status,
                    dateFrom: filters.date_from,
                    dateTo: filters.date_to
                },
                { preserveScroll: true }
            )
        }
    }

    function archiveSMS(sms: any, status: string, title: string) {
        Swal.fire({
            title: 'Are you sure to ' + title + ' this message?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#239117', // Tailwind red-500
            cancelButtonColor: '#6b7280',  // Tailwind gray-500
            confirmButtonText: 'Yes, ' + title +  ' it!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                router.put(
                    `/sms/updateStatus`, {
                        sms_id: sms.id,
                        status: status,
                    }
                )
            }
        })
    }

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Messages',
            href: '/sms',
        },
    ];
</script>

<template>
    <Head title="SMS Inbox" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="mb-4 text-2xl font-bold">Messages</h1>

           <div class="flex flex-col gap-6">
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border bg-card p-4 ">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Status</span>
                            <select 
                                v-model="filters.status"
                                @change="changeStatus"
                                class="h-10 w-[140px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-accent/50 cursor-pointer"
                            >
                                <option value="unprocessed">Unprocessed</option>
                                <option value="processed">Processed</option>
                                <option value="archived">Archive</option>
                            </select>
                        </div>

                        <div class="hidden h-10 w-px bg-border md:block mx-1 mt-4"></div>

                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground ml-1">Date Range</span>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="date" 
                                    @change="changeDate"
                                    v-model="filters.date_from"
                                    class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-accent/50"
                                />
                                <span class="text-muted-foreground text-xs">to</span>
                                <input 
                                    type="date" 
                                    @change="changeDate"
                                    v-model="filters.date_to"
                                    class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background transition-all focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-accent/50"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-auto pb-0.5">
                        <Button 
                            v-if="filters.date_from || filters.date_to || filters.status !== 'unprocessed'"
                            variant="outline" 
                            size="sm" 
                            class="h-9 px-3 text-xs font-medium transition-all hover:bg-destructive hover:text-destructive-foreground"
                            @click="clearFilter"
                        >
                            <XIcon class="mr-2 h-3.5 w-3.5" />
                            Clear Filters
                        </Button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                    </div>
            </div>
            
            <div class="flex flex-col gap-4">
                <div class="overflow-hidden rounded-xl border bg-card ">
                    <div class="relative h-[550px] overflow-auto">
                        <Table>
                            <TableHeader
                                class="sticky top-0 z-10 bg-muted/90  backdrop-blur-md"
                            >
                                <TableRow>
                                    <TableHead
                                        class="w-[220px] px-6 py-4 font-semibold"
                                        >Sender</TableHead
                                    >
                                    <TableHead class="font-semibold"
                                        >Message</TableHead
                                    >
                                    <TableHead class="w-[180px] font-semibold"
                                        >Received At</TableHead
                                    >
                                    <TableHead class="w-[180px] font-semibold"
                                        >Status</TableHead
                                    >
                                    <TableHead
                                        class="w-[100px] px-6 text-right font-semibold"
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
                                        v-for="sms in messages.data"
                                        :key="sms.id"
                                        class="group border-b transition-colors last:border-0 hover:bg-muted/30"
                                    >
                                    <TableCell class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary ring-1 ring-primary/20"
                                            >
                                                {{
                                                    sms.sender
                                                        .substring(0, 2)
                                                        .toUpperCase()
                                                }}
                                            </div>
                                            <span
                                                class="truncate font-semibold tracking-tight text-foreground"
                                            >
                                                {{ sms.sender }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="max-w-md lg:max-w-xl">
                                        <p
                                            class="line-clamp-2 text-sm text-muted-foreground transition-colors group-hover:text-foreground"
                                        >
                                            {{ sms.message }}
                                        </p>
                                    </TableCell>

                                    <TableCell
                                        class="text-sm font-medium whitespace-nowrap"
                                    >
                                        {{ sms.received_at }}
                                    </TableCell>

                                <TableCell class="text-sm font-medium whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                                            {
                                                'bg-green-50 text-green-700 border-green-200': sms.status === 'processed',
                                                'bg-amber-50 text-amber-700 border-amber-200': sms.status === 'unprocessed',
                                                'bg-gray-100 text-gray-700 border-gray-300': sms.status === 'archived'
                                            }
                                        ]"
                                    >
                                        {{ 
                                            sms.status === 'processed' ? 'Processed' 
                                            : sms.status === 'archived' ? 'Archived' 
                                            : 'Unprocessed' 
                                        }}
                                    </span>
                                </TableCell>

                                    <TableCell class="px-6 text-right">
                                        <div class="gap-2 flex ">
                                            <Button
                                                v-if="sms.status == 'unprocessed'"
                                                variant="secondary"
                                                size="sm"
                                                class="h-8 gap-2 px-3 transition-all hover:bg-primary hover:text-primary-foreground"
                                                @click="addToSMS(sms)"
                                            >
                                                <PlusIcon class="h-3.5 w-3.5" />
                                                <span>Evaluate</span>
                                            </Button>
                                            <Button
                                                v-if="sms.status == 'unprocessed'"
                                                variant="secondary"
                                                size="sm"
                                                class="h-8 gap-2 px-3 mr-2 transition-all hover:bg-primary hover:text-primary-foreground"
                                                @click="archiveSMS(sms, 'archived', 'archive')"
                                            >
                                                <Archive class="h-3.5 w-3.5" />
                                                <span>Archive</span>
                                            </Button>
                                            <Button
                                                v-if="sms.status == 'archived'"
                                                variant="secondary"
                                                size="sm"
                                                class="h-8 gap-2 px-3 mr-2 transition-all hover:bg-primary hover:text-primary-foreground"
                                                @click="archiveSMS(sms, 'unprocessed', 'unarchive')"
                                            >
                                                <Archive class="h-3.5 w-3.5" />
                                                <span>Unarchive</span>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                    <TableRow v-if="messages.data?.length === 0">
                                    <TableCell colspan="5" class="h-[400px] text-center">
                                        </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        
                    </div>
                    <p class="text-xs text-muted-foreground italic p-4">
                        Showing {{ messages.data.length }} of {{ props.messages.total }} messages
                    </p>
                    
                    <Pagination
                        :items-per-page="messages.per_page ?? 0"
                        :total="messages.total"
                        :default-page="messages.current_page"
                        v-slot="{ page }"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious @click="changePage(page - 1)" />

                            <template v-for="(item, index) in items" :key="index">
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === page"
                                    class="cursor-pointer"
                                    @click="changePage(item.value)"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                                <PaginationEllipsis v-else :index="index" />
                            </template>

                            <PaginationNext @click="changePage(page + 1)" />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>

            <Dialog v-model:open="form.dialogueOpen">
                <DialogContent
                    class="flex max-h-[95vh] max-w-3/4  flex-col overflow-hidden p-0"
                >
                    <DialogHeader class="border-b bg-muted/20 px-6 py-4">
                        <DialogTitle class="text-xl font-bold tracking-tight"
                            >Information Report</DialogTitle
                        >
                        <label class="text-sm text-muted-foreground"
                            >Review source data and categorize the intelligence
                            report.</label
                        >
                    </DialogHeader>

                    <div
                        class="flex flex-1 flex-col overflow-hidden md:flex-row"
                    >
                        <aside
                            class="w-full space-y-6 overflow-y-auto border-r bg-muted/30 p-6 md:w-80"
                        >
                            <div>
                                <h3
                                    class="mb-3 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    SMS Metadata
                                </h3>
                                <div class="space-y-1">
                                    <p class="text-xs text-muted-foreground">
                                        Received At
                                    </p>
                                    <p class="text-sm font-medium">
                                        {{ form.receivedAt }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="rounded-xl border bg-background p-4 shadow-sm"
                            >
                                <h3
                                    class="mb-2 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Original Content
                                </h3>
                                <p
                                    class="text-sm leading-relaxed text-foreground/90 italic"
                                >
                                    "{{ form.smsinformation }}"
                                </p>
                            </div>
                        </aside>

                        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div
                                    class="flex items-center gap-2 md:col-span-2"
                                >
                                    <div class="h-px flex-1 bg-border"></div>
                                    <span
                                        class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                                        >General Details</span
                                    >
                                    <div class="h-px flex-1 bg-border"></div>
                                </div>

                                <Input
                                    v-model="form.file_number"
                                    label="File Number"
                                    placeholder="REQ-2024-001"
                                    :required="true"
                                    :readonly="true"
                                />
                                <Input
                                    v-model="form.reference"
                                    label="Reference"
                                    placeholder="Ref code..."
                                    :required="true"
                                    :readonly="true"

                                />
                                <div class="w-full">
                                    <label
                                        class="mb-2 block text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        for="label"
                                    >
                                        Subject
                                    </label>
                                    <SubjectCombobox
                                        v-model="form.subject"
                                        :subjects="subjects"
                                        placeholder="Input or Select Subject"
                                        :error="form.errors.subject"
                                    />
                                </div>

                                <Input
                                    v-model="form.date_of_report"
                                    label="Date of Report"
                                    type="date"
                                    :required="true"
                                    :error="form.errors.date_of_report"
                                />
                                <div class="w-full">
                                    <label
                                        class="mb-2 block text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        for="label"
                                    >
                                        Reporter
                                    </label>
                                    <SubjectCombobox
                                        v-model="form.reporter"
                                        :subjects="reporters"
                                        placeholder="Input or Select Reporter"
                                        :error="form.errors.reporter"
                                    />
                                </div>
                                <Input
                                    v-model="form.designation"
                                    label="Designation"
                                    placeholder="Rank/Position"
                                    :error="form.errors.designation"
                                />
                                
                                <div class="w-full">
                                    <label
                                        class="mb-2 block text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        for="label"
                                    >
                                        Source
                                    </label>
                                    <SubjectCombobox
                                        v-model="form.source"
                                        :subjects="sources"
                                        placeholder="Input or Select Source"
                                        :error="form.errors.source"
                                    />
                                </div>

                                <Input
                                    v-model="form.dateAcquired"
                                    label="Date Acquired"
                                    type="date"
                                    :error="form.errors.dateAcquired"
                                />

                                <div class="w-full">
                                    <label
                                        class="mb-2 block text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        for="label"
                                    >
                                        Manner Acquired
                                    </label>
                                    <SubjectCombobox
                                        v-model="form.mannerAcquired"
                                        placeholder="Method of collection"
                                        :subjects="methodofcollections"
                                        :error="form.errors.mannerAcquired"
                                    />
                                </div>
                                <div
                                    class="mt-4 flex items-center gap-2 md:col-span-2"
                                >
                                    <div class="h-px flex-1 bg-border"></div>
                                    <span
                                        class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase"
                                        >Classification & Status</span
                                    >
                                    <div class="h-px flex-1 bg-border"></div>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="text-sm leading-none font-medium"
                                        >Classification</label
                                    >
                                    <Select
                                        v-model="form.classificationId"
                                        :error="form.errors.classificationId"
                                        @update:modelValue="changedClassification"
                                        class="w-full"
                                    >
                                        <SelectTrigger
                                            class="w-full"
                                        >
                                            <SelectValue
                                                placeholder="Select classification"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="item in classifications"
                                                :key="item.id"
                                                :value="item.id"
                                                class="w-full"
                                            >
                                                {{ item.description }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <Input
                                    v-model="form.evaluation"
                                    label="Evaluation"
                                    placeholder="Initial assessment score"
                                    :error="form.errors.evaluation"
                                />

                                <div
                                    class="mt-2 grid grid-cols-2 gap-4 rounded-lg border border-dashed bg-muted/20 p-4 md:col-span-2 lg:grid-cols-4"
                                >
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-bold text-muted-foreground uppercase"
                                            >Region</label
                                        >
                                        <Select v-model="form.selectedRegion">
                                            <SelectTrigger class="bg-background"
                                                ><SelectValue
                                                    placeholder="Region"
                                            /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="r in regions"
                                                    :key="r.region"
                                                    :value="r.region"
                                                    >{{ r.region }}</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-bold text-muted-foreground uppercase"
                                            >Province</label
                                        >
                                        <Select v-model="form.selectedProvince">
                                            <SelectTrigger class="bg-background"
                                                ><SelectValue
                                                    placeholder="Province"
                                            /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="p in provinces.filter(
                                                        (prov) =>
                                                            prov.region ===
                                                            form.selectedRegion,
                                                    )"
                                                    :key="p.province"
                                                    :value="p.province"
                                                >
                                                    {{ p.province }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-bold text-muted-foreground uppercase"
                                            >City</label
                                        >
                                        <Select v-model="form.selectedCity">
                                            <SelectTrigger class="bg-background"
                                                ><SelectValue
                                                    placeholder="City"
                                            /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="c in cities.filter(
                                                        (city) =>
                                                            city.province ===
                                                            form.selectedProvince,
                                                    )"
                                                    :key="c.city_municipality"
                                                    :value="c.city_municipality"
                                                >
                                                    {{ c.city_municipality }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-bold text-muted-foreground uppercase"
                                            >Barangay</label
                                        >
                                        <Select
                                            v-model="form.selectedBarangay"
                                            :error="
                                                form.errors.selectedBarangay
                                            "
                                        >
                                            <SelectTrigger class="bg-background"
                                                ><SelectValue
                                                    placeholder="Barangay"
                                            /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="b in barangays.filter(
                                                        (bar) =>
                                                            bar.city_municipality ===
                                                            form.selectedCity,
                                                    )"
                                                    :key="b.id"
                                                    :value="b.id"
                                                >
                                                    {{ b.barangay }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-4 md:col-span-2">
                                    <Textarea
                                        v-model="form.informationProper"
                                        label="Information Proper"
                                        placeholder="Enter detailed narrative..."
                                        :required="true"
                                        class="min-h-[120px]"
                                        :error="form.errors.informationProper"
                                    />
                                    <Textarea
                                        v-model="form.analysis"
                                        label="Analysis"
                                        placeholder="Analyst comments..."
                                        :error="form.errors.analysis"
                                    />
                                    <Textarea
                                        v-model="form.actions"
                                        label="Actions"
                                        placeholder="Recommended next steps..."
                                        :error="form.errors.actions"
                                    />
                                </div>
                                <div class="mt-4">
                                    <Input 
                                        label="Attachments"
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        @change="onAttachmentsChange"
                                    />
                                    <div class="flex flex-wrap gap-3 mt-2">
                                        <div
                                            v-for="(preview, index) in previews"
                                            :key="index"
                                            class="relative w-24 h-24"
                                        >
                                        <img
                                            :src="preview"
                                            class="w-full h-full object-cover rounded border"
                                        />

                                        <button
                                            type="button"
                                            @click="removeImage(index)"
                                            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm"
                                        >
                                            ×
                                        </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>

                    <DialogFooter class="border-t bg-muted/20 px-6 py-4">
                        <DialogClose asChild>
                            <Button variant="ghost">Cancel</Button>
                        </DialogClose>
                        <Button
                            @click="submit()"
                            :disabled="form.processing"
                            class="px-8 shadow-lg shadow-primary/20 transition-all hover:scale-[1.02]"
                        >
                            <span v-if="!form.processing">Save Report</span>
                            <span v-else class="flex items-center gap-2">
                                <Loader2 class="h-4 w-4 animate-spin" />
                                Saving...
                            </span>
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

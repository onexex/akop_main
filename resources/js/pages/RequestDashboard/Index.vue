<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Trash2, Save, Send, Calculator, Search, Calendar, Eye, Edit2, CheckCircle, XCircle } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps<{
    requests: any[];
}>();

// --- UI STATES ---
const isDialogOpen = ref(false);
const modalMode = ref<'create' | 'view' | 'edit'>('create');
const selectedId = ref<number | null>(null);
const searchQuery = ref('');
const startDate = ref('');
const endDate = ref('');

const page = usePage();

// I-define ang function para maging "exist" ito sa type ng component mo
const can = (permission: string) => {
    const user = page.props.auth?.user as any; // Gamitin ang 'any' para mabilis o i-import ang User interface
    return user?.permissions?.includes(permission) ?? false;
};

interface CAItem {
    details: string;
    qty: number;
    amount_usd: number;
    amount_peso: number;
    total: number;
}

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    district_office: '',
    purpose: '',
    beneficiaries: '',
    amount_in_figure: 0,
    amount_in_words: '',
    items: [
        { details: '', qty: 1, amount_usd: 0, amount_peso: 0, total: 0 }
    ] as CAItem[]
});

const breadcrumbs = [
    { title: 'Dashboard', href: '#' },
    { title: 'Request Cash Advance', href: '#' }
];

// --- LOGIC FUNCTIONS ---

const openCreateModal = () => {
    modalMode.value = 'create';
    selectedId.value = null;
    form.reset();
    form.date = new Date().toISOString().split('T')[0]; // Default date
    isDialogOpen.value = true;
};

const openViewModal = (req: any) => {
    modalMode.value = 'view';
    fillForm(req);
    isDialogOpen.value = true;
};

const openEditModal = (req: any) => {
    modalMode.value = 'edit';
    selectedId.value = req.id;
    fillForm(req);
    isDialogOpen.value = true;
};

const fillForm = (req: any) => {
    form.date = req.date;
    form.district_office = req.district_office;
    form.purpose = req.purpose;
    form.beneficiaries = req.beneficiaries;
    form.amount_in_figure = req.amount_in_figure;
    form.amount_in_words = req.amount_in_words;
    // Siguraduhing deep copy ang items para hindi ma-mutate ang original props
    form.items = JSON.parse(JSON.stringify(req.items));
};

const addItem = () => {
    if (modalMode.value === 'view') return;
    form.items.push({ details: '', qty: 1, amount_usd: 0, amount_peso: 0, total: 0 });
};

const removeItem = (index: number) => {
    if (modalMode.value === 'view') return;
    if (form.items.length > 1) form.items.splice(index, 1);
};

watch(() => form.items, (newItems) => {
    let grandTotal = 0;
    newItems.forEach((item) => {
        item.total = (item.qty || 0) * (item.amount_peso || 0);
        grandTotal += item.total;
    });
    form.amount_in_figure = grandTotal;
}, { deep: true });

const submit = () => {
    if (modalMode.value === 'view') return;

    if (modalMode.value === 'create') {
        form.post('/cash-advances', {
            onSuccess: () => {
                form.reset();
                isDialogOpen.value = false;
            },
        });
    } else {
        form.put(`/cash-advances/${selectedId.value}`, {
            onSuccess: () => {
                form.reset();
                isDialogOpen.value = false;
            },
        });
    }
};

// Gumawa ng dedicated form para sa status updates
const statusForm = useForm({
    status: '',
});

const handleApprove = (req: any, newStatus: string) => {
    Swal.fire({
        title: 'Confirm Approval',
        text: `Are you sure you want to set this to ${newStatus.replace(/_/g, ' ')}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        confirmButtonColor: '#10b981',
    }).then((result) => {
        if (result.isConfirmed) {
            // I-set ang value sa form bago i-submit
            statusForm.status = newStatus;

            // Gamitin ang form.put pattern gaya ng submit function mo
            statusForm.put(`/cash-advance/${req.id}/status`, {
                preserveScroll: true,
                onSuccess: () => {
                    // Swal.fire('Success!', 'Status updated successfully.', 'success');
                    statusForm.reset();
                },
                onError: () => {
                    // Swal.fire('Error!', 'Failed to update status.', 'error');
                }
            });
        }
    });
};

const filteredRequests = computed(() => {
    return props.requests.filter(req => {
        // Search filter
        const matchesSearch = req.ca_number.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                             req.purpose.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        // Date Range filter
        const reqDate = req.date; // Assuming YYYY-MM-DD format
        const matchesStart = !startDate.value || reqDate >= startDate.value;
        const matchesEnd = !endDate.value || reqDate <= endDate.value;

        return matchesSearch && matchesStart && matchesEnd;
    });
});
</script>

<template>
    <Head title="Request Cash Advance" />
    <AppLayout :breadcrumbs="breadcrumbs"> 
        
        <div class="w-full p-4 md:p-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="flex flex-1 w-full gap-2">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input v-model="searchQuery" type="text" placeholder="Search CA Number or Purpose..." class="pl-10 w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500" />
                    </div>
                    <div class="relative w-48">
                        <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input v-model="startDate" type="date" class="pl-10 w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500" />
                    </div>
                    <div class="relative w-48">
                        <Calendar class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input v-model="endDate" type="date" class="pl-10 w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500" />
                    </div>
                    <button 
                v-if="startDate || endDate" 
                @click="startDate = ''; endDate = ''" 
                class="text-xs text-red-500 hover:underline font-bold ml-2"
            >
                Clear
            </button>
                </div>
                <!-- <button @click="openCreateModal" class="w-full md:w-auto bg-green-600 text-white px-6 py-2.5 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-blue-700 transition">
                    <Plus class="w-4 h-4" /> New Cash Advance Request
                </button> -->

                <button 
                    v-if="can('create request')"
                    @click="openCreateModal" 
                    class="w-full md:w-auto bg-green-600 text-white px-6 py-2.5 rounded-lg font-bold flex items-center justify-center gap-2 hover:bg-blue-700 transition"
                >
                    <Plus class="w-4 h-4" /> 
                    New Cash Advance Request
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[11px] tracking-wider">
                            <tr>
                                <th class="p-4">CA Number</th>
                                <th class="p-4">Date</th>
                                <th class="p-4">Purpose / Beneficiaries</th>
                                <th class="p-4 text-right">Grand Total</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-50 transition">
                                <td class="p-4 font-mono font-bold text-blue-600">{{ req.ca_number }}</td>
                                <td class="p-4 text-slate-500">{{ req.date }}</td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-900">{{ req.purpose }}</div>
                                    <div class="text-xs text-slate-400">{{ req.beneficiaries }}</div>
                                </td>
                                <td class="p-4 text-right font-bold text-slate-800 text-base">
                                    ₱{{ req.amount_in_figure.toLocaleString() }}
                                </td>
                                <td class="p-4 text-center">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-700': req.status === 'pending',
                                        'bg-blue-100 text-blue-700': req.status === 'first_approved',
                                        'bg-green-100 text-green-700': req.status === 'approved',
                                        'bg-red-100 text-red-700': req.status === 'rejected'
                                    }" class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">
                                        {{ req.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openViewModal(req)" class="p-2 text-slate-400 hover:text-blue-600 border rounded-md transition" title="View">
                                            <Eye class="w-4 h-4" />
                                        </button>

                                        <button 
                                            v-if="(req.status === 'pending' || req.status === 'disapproved_by_l1' || req.status === 'disapproved_by_l2') && can('edit request')" 
                                            @click="openEditModal(req)" 
                                            class="p-2 text-slate-400 hover:text-green-600 border rounded-md transition flex items-center gap-1" 
                                            :title="req.status.includes('disapproved') ? 'Fix & Resubmit' : 'Edit'"
                                        >
                                            <Edit2 class="w-4 h-4" />
                                            <span v-if="req.status.includes('disapproved')" class="text-[10px] font-bold">FIX</span>
                                        </button>

                                        <template v-if="req.status === 'pending' && can('approve request level 1')">
                                            <button 
                                                @click="handleApprove(req, 'approved_by_l1')" 
                                                class="p-2 text-blue-500 hover:bg-blue-50 border border-blue-200 rounded-md transition flex items-center gap-1"
                                                title="Approve L1"
                                            >
                                                <CheckCircle class="w-4 h-4" />
                                                <!-- <span class="text-xs font-bold">Approve</span> -->
                                            </button>
                                            <button 
                                                @click="handleApprove(req, 'disapproved_by_l1')" 
                                                class="p-2 text-red-500 hover:bg-red-50 border border-red-200 rounded-md transition flex items-center gap-1"
                                                title="Disapprove L1"
                                            >
                                                <XCircle class="w-4 h-4" />
                                                <!-- <span class="text-xs font-bold">Disapprove</span> -->
                                            </button>
                                        </template>

                                        <template v-if="req.status === 'approved_by_l1' && can('approve request level 2')">
                                            <button 
                                                @click="handleApprove(req, 'approved_by_l2')" 
                                                class="p-2 text-purple-500 hover:bg-purple-50 border border-purple-200 rounded-md transition flex items-center gap-1"
                                                title="Approve L2"
                                            >
                                                <CheckCircle class="w-4 h-4" />
                                                <!-- <span class="text-xs font-bold">Approve</span> -->
                                            </button>
                                            <button 
                                                @click="handleApprove(req, 'disapproved_by_l2')" 
                                                class="p-2 text-red-500 hover:bg-red-50 border border-red-200 rounded-md transition flex items-center gap-1"
                                                title="Disapprove L2"
                                            >
                                                <XCircle class="w-4 h-4" />
                                                <!-- <span class="text-xs font-bold">Disapprove</span> -->
                                            </button>
                                        </template>

                                        <button 
                                            v-if="req.status === 'approved_by_l2' && can('release request')" 
                                            @click="handleApprove(req, 'released')" 
                                            class="p-2 text-green-500 hover:bg-green-50 border border-green-200 rounded-md transition flex items-center gap-1" 
                                            title="Release Cash"
                                        >
                                            <Banknote class="w-4 h-4" />
                                            <!-- <span class="text-xs font-bold">Release</span> -->
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredRequests.length === 0">
                                <td colspan="6" class="p-20 text-center text-slate-400 italic text-lg">
                                    No records available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="isDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden transition-all transform scale-100">
                
                <div class="p-4 border-b flex justify-between items-center bg-white sticky top-0 z-30">
                    <div class="flex items-center gap-2 px-4">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">
                            {{ modalMode === 'create' ? 'New Cash Advance Request' : (modalMode === 'edit' ? 'Edit Request' : 'View Voucher Details') }}
                        </span>
                    </div>
                    <button @click="isDialogOpen = false" class="text-slate-400 hover:text-red-500 transition-colors font-bold text-2xl px-4">
                        &times;
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50">
                    <fieldset :disabled="modalMode === 'view'" class="w-full">
                        <div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            
                            <div class="bg-slate-900 p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div>
                                    <h1 class="text-xl font-black tracking-tight uppercase">Cash Advance Voucher</h1>
                                    <p class="text-slate-400 text-xs mt-1">
                                        {{ modalMode === 'view' ? 'Reviewing request details' : 'Submit this form for fund processing and approval.' }}
                                    </p>
                                </div>
                                <div class="text-left md:text-right bg-white/5 px-4 py-2 rounded-lg border border-white/10">
                                    <span class="block text-[10px] uppercase text-slate-400 font-bold tracking-tighter">Reference No.</span>
                                    <span class="font-mono font-bold text-lg text-yellow-500 uppercase tracking-widest leading-none">
                                        {{ modalMode === 'create' ? 'AUTO-GENERATED' : requests.find(r => r.id === selectedId || (modalMode === 'view' && r.ca_number))?.ca_number }}
                                    </span>
                                </div>
                            </div>

                            <form @submit.prevent="submit" class="p-6 md:p-8">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                                    <div class="space-y-1.5">
                                        <label class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Date of Request</label>
                                        <input v-model="form.date" type="date" class="w-full border-slate-200 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none disabled:bg-slate-50" required />
                                    </div>
                                    <div class="md:col-span-3 space-y-1.5">
                                        <label class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">District / Office Location</label>
                                        <input v-model="form.district_office" type="text" placeholder="e.g., Regional Office" class="w-full border-slate-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-500 disabled:bg-slate-50" required />
                                    </div>
                                    <div class="md:col-span-2 space-y-1.5">
                                        <label class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Purpose of Fund</label>
                                        <textarea v-model="form.purpose" rows="2" placeholder="State reason..." class="w-full border-slate-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-500 disabled:bg-slate-50" required></textarea>
                                    </div>
                                    <div class="md:col-span-2 space-y-1.5">
                                        <label class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Beneficiaries</label>
                                        <textarea v-model="form.beneficiaries" rows="2" placeholder="Who will receive the fund?" class="w-full border-slate-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-500 disabled:bg-slate-50" required></textarea>
                                    </div>
                                </div>

                                <div class="mb-10">
                                    <div class="flex justify-between items-center mb-4 px-1">
                                        <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                            <Calculator class="w-4 h-4 text-blue-600" />
                                            ITEMIZED BREAKDOWN
                                        </h3>
                                        <button v-if="modalMode !== 'view'" type="button" @click="addItem" class="text-[11px] bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 font-bold flex items-center gap-1.5 transition-colors">
                                            <Plus class="w-3.5 h-3.5" /> ADD ROW
                                        </button>
                                    </div>

                                    <div class="border rounded-xl overflow-hidden border-slate-200 shadow-sm bg-white">
                                        <table class="w-full text-[13px] text-left border-collapse">
                                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold">
                                                <tr>
                                                    <th class="p-3">PARTICULARS</th>
                                                    <th class="p-3 w-20 text-center">QTY</th>
                                                    <th class="p-3 w-32 text-right">USD</th>
                                                    <th class="p-3 w-32 text-right">PHP</th>
                                                    <th class="p-3 w-36 text-right">TOTAL (PHP)</th>
                                                    <th v-if="modalMode !== 'view'" class="p-3 w-12"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="p-2">
                                                        <input v-model="item.details" type="text" class="w-full border-none focus:ring-0 text-sm placeholder:text-slate-300 disabled:bg-transparent" placeholder="Description..." />
                                                    </td>
                                                    <td class="p-2">
                                                        <input v-model.number="item.qty" type="number" class="w-full border-none text-center focus:ring-0 font-bold disabled:bg-transparent" />
                                                    </td>
                                                    <td class="p-2">
                                                        <input v-model.number="item.amount_usd" type="number" step="0.01" class="w-full border-none text-right focus:ring-0 disabled:bg-transparent" placeholder="0.00" />
                                                    </td>
                                                    <td class="p-2">
                                                        <input v-model.number="item.amount_peso" type="number" step="0.01" class="w-full border-none text-right focus:ring-0 font-bold text-blue-600 disabled:bg-transparent" placeholder="0.00" />
                                                    </td>
                                                    <td class="p-2 text-right font-black text-slate-900 pr-4">
                                                        ₱{{ item.total.toLocaleString() }}
                                                    </td>
                                                    <td v-if="modalMode !== 'view'" class="p-2 text-center">
                                                        <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="text-slate-300 hover:text-red-500 transition-colors">
                                                            <Trash2 class="w-4 h-4" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end bg-slate-900 p-6 md:p-8 rounded-2xl text-white">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-[0.1em]">Amount in Words</label>
                                        <textarea v-model="form.amount_in_words" rows="2" class="w-full bg-white/5 border-white/10 rounded-xl italic text-sm text-white p-3 focus:border-blue-500 outline-none transition disabled:bg-slate-800/50" placeholder="e.g. Ten Thousand Pesos Only"></textarea>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="flex justify-between items-end border-b border-white/10 pb-4">
                                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Total Amount</span>
                                            <div class="text-right">
                                                <span class="text-2xl font-black text-white leading-none">₱ {{ form.amount_in_figure.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="isDialogOpen = false" class="px-6 py-2 text-xs font-bold text-slate-400 hover:text-white transition uppercase">
                                                {{ modalMode === 'view' ? 'Close' : 'Cancel' }}
                                            </button>
                                            <button v-if="modalMode !== 'view'" type="submit" :disabled="form.processing" class="px-8 py-3 text-xs font-black text-white bg-blue-600 rounded-xl hover:bg-blue-500 transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20 disabled:opacity-50">
                                                <Send class="w-4 h-4" /> 
                                                {{ modalMode === 'edit' ? 'Update Voucher' : 'Submit Request' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
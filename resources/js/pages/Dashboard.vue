<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Bar, Doughnut, Line, Pie } from 'vue-chartjs'; 
import { computed } from 'vue';
// import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler, // Add these for Line Chart
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import {
    Activity,
    BarChart3,
    ChevronRight,
    FileSearch,
    LineChart,
    MapPin,
    MessageSquare,
    ShieldAlert,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';

    import {
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
    } from "@/components/ui/dialog"

import ChartDataLabels from 'chartjs-plugin-datalabels'; 
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Calendar, Tag, FilterX, User2, Share2 } from 'lucide-vue-next';
import { FileDown } from 'lucide-vue-next';  

import RiskModal from './RiskModal.vue'; // We will create this below
import axios from 'axios';


const props = defineProps<{
    stats: any;
    chartData: any;
    recent: any[];
    filters: { start_date?: string; end_date?: string; classification?: string };  
    classifications: string[];  
}>();

const isRiskModalOpen = ref(false);

const downloadPDF = () => {
    const params = new URLSearchParams(form).toString();
    window.location.href = `/dashboard/export?${params}`;
};

// Initialize the filter form
const form = reactive({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    classification: props.filters.classification || '',
});

// Function to send filters to the controller
const applyFilters = () => {
    router.get('/dashboard', form, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
};

// Reset function
const resetFilters = () => {
    form.start_date = '';
    form.end_date = '';
    form.classification = '';
    applyFilters();
};

    const chartView = ref<'bar' | 'line'>('bar');
    const trendValue = computed(() => props.stats.trend_value || 0);
    const isTrendUp = computed(() => trendValue.value > 0);
    const chartBarangay = ref({ ...props.chartData.barangay }); 
    
interface IncidentAttachment {
        id: number
        url: string
    }    

    const incidentattachments = ref<IncidentAttachment[]>([])
    const formDetail = useForm({
        dialogueOpen: false,
        smsId: 0,
        smsinformation: '',
        receivedAt: '',
        file_number: '',
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
        classification: '',
        selectedRegion: '',
        selectedProvince: '',
        selectedCity: '',
        selectedBarangay: '',
    });


const leaderLinePlugin = {
    id: 'leaderLinePlugin',
    afterDraw: (chart: any) => {
        const {
            ctx,
            chartArea: { top, bottom, left, right },
        } = chart;
        ctx.save();

        chart.data.datasets.forEach((dataset: any, i: number) => {
            chart
                .getDatasetMeta(i)
                .data.forEach((datapoint: any, index: number) => {
                    const { x, y, startAngle, endAngle, outerRadius } =
                        datapoint;
                    const midAngle = (startAngle + endAngle) / 2;

                    // Calculate line start (edge of the slice)
                    const startX = x + Math.cos(midAngle) * outerRadius;
                    const startY = y + Math.sin(midAngle) * outerRadius;

                    // Calculate line end (where the label is)
                    const endX = x + Math.cos(midAngle) * (outerRadius + 25);
                    const endY = y + Math.sin(midAngle) * (outerRadius + 25);

                    // Draw the line
                    ctx.beginPath();
                    ctx.moveTo(startX, startY);
                    ctx.lineTo(endX, endY);
                    ctx.strokeStyle = dataset.backgroundColor[index];
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                });
        });
        ctx.restore();
    },
};

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
    ChartDataLabels,
    leaderLinePlugin,
);

// Chart Setup
const barangayChart = computed(() => {
    const chartData = chartBarangay.value || {};
    const labels = Object.keys(chartData);
    const data = Object.values(chartData) as number[];
    const totalIncidents = data.reduce((sum, val) => sum + val, 0);

    const backgroundColors = data.map((value) => {
        if (totalIncidents === 0) return '#6366f1';
        const percentage = (value / totalIncidents) * 100;
        if (percentage >= 25) return '#dc2626';
        if (percentage >= 10) return '#f59e0b';
        return '#6366f1';
    });


    return {
        labels: labels,
        datasets: [
            {
                label: 'Incidents',
                data: data,
                // Bar styles
                backgroundColor: backgroundColors,
                borderRadius: 8,
                // Line styles (only applies when chartView is 'line')
                borderColor: '#6366f1',
                pointBackgroundColor: backgroundColors,
                pointRadius: 6,
                tension: 0.4, // Makes the line curved
                fill: true,
                backgroundColora:
                    chartView.value === 'line'
                        ? 'rgba(99, 102, 241, 0.1)' // Light blue fill for line
                        : backgroundColors, // Dynamic bar colors
            },
        ],
    };
});

const typeChart = computed(() => {
    const labels = Object.keys(props.chartData.types || {});
    return {
        labels: labels,
        datasets: [
            {
                data: Object.values(props.chartData.types || {}) as number[],
                backgroundColor: labels.map((label) => getIncidentColor(label)),
                borderWidth: 0,
                hoverOffset: 30,
            },
        ],
    };
});

const typeChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',  
    layout: {
        padding: 50 // Increased slightly to prevent text clipping
    },
    plugins: {
        legend: { display: false },
        tooltip: { enabled: true },
        datalabels: {
            display: true,
            color: (context: any) => context.dataset.backgroundColor[context.dataIndex],
            anchor: 'end',
            align: 'end',
            offset: 20,  
            font: {
                weight: '400', // Made bolder for better readability
                size: 12
            },
            formatter: (value: number, context: any) => {
                const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
                const percentage = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
                const label = context.chart.data.labels[context.dataIndex];
                
                // Returns: 25% (12)
                //          Classification Name
                return `${percentage}% (${value})\n${label}`; 
            },
            textAlign: 'center'
        },
        customPiePlugin: { display: true } 
    }
};

const mannerChart = computed(() => {
    const labels = Object.keys(props.chartData.manner || {});
    const data = Object.values(props.chartData.manner || {}) as number[];

    return {
        labels: labels,
        datasets: [
            {
                data: data,
                backgroundColor: [
                    '#64748b',
                    '#6366f1',
                    '#0ea5e9',
                    '#f59e0b',
                    '#10b981',
                ],
                borderWidth: 2,
                borderColor: 'white',
                // Explode the first slice (e.g., Red 20% in your image)
                offset: data.map((_, i) => (i === 0 ? 30 : 0)),
            },
        ],
    };
});

const getIncidentColor = (type: string) => {
    const t = (type || '').toLowerCase().trim();
    if (t === 'tg1(ltg)') return '#dc2626';
    if (t === 'tg2(ctg)') return '#2563eb';
    if (t === 'tg3(cg)') return '#000000';
    if (t === 'pags/ppags') return '#eab308';
    if (t === 'cfo(cppnpandf)') return '#980404';
    if (t === 'mwp/owp') return '#FDAAAA';
    if (t === 'ciw') return '#DE802B';
    if (t === 'milf/mnlf') return '#15c799';
    return '#F1E6C9';
};

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: function (context: any) {
                    const value = context.raw;
                    const total = context.dataset.data.reduce(
                        (a: number, b: number) => a + b,
                        0,
                    );
                    const percentage =
                        total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return ` ${value} Incidents (${percentage}%)`;
                },
            },
        },
    },
};

const mannerOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: 20,  
    },
    plugins: {
        legend: { display: false },
         
        datalabels: {
            color: (context: any) =>
                context.dataset.backgroundColor[context.dataIndex],
            anchor: 'end',
            align: 'end',
            offset: 28, // Pushes text past the end of our line
            font: { weight: '400', size: 12 },
            formatter: (value: number, context: any) => {
                const total = context.dataset.data.reduce(
                    (a: number, b: number) => a + b,
                    0,
                );
                const percentage =
                    total > 0 ? ((value / total) * 100).toFixed(0) : 0;
                return `${percentage}%\n${context.chart.data.labels[context.dataIndex]}`;
            },
            textAlign: 'center',
        },
    },
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const reporterChart = computed(() => {
    const labels = Object.keys(props.chartData.reporter || {});
    return {
        labels: labels,
        datasets: [{
            label: 'Incidents Reported',
            data: Object.values(props.chartData.reporter || {}) as number[],
            backgroundColor: '#6366f1', // Indigo
            borderRadius: 6,
            barThickness: 20,
        }]
    };
});

const reporterOptions = {
    indexAxis: 'y', 
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        datalabels: {
            anchor: 'end',
            align: 'right',
            color: '#6366f1',
            font: { weight: '900', size: 11 },
            formatter: (value: number) => Math.floor(value)
        }
    },
    scales: {
        x: { 
            display: true, 
            grid: { display: false },
            beginAtZero: true,
            ticks: { 
                stepSize: 1, 
                precision: 0,
                font: { weight: '700', size: 10 },
                color: '#64748b',
                callback: (value: any) => (value % 1 === 0 ? value : null)
            }
        },
        y: { 
            display: true, 
            grid: { display: false },
            ticks: { 
                font: { weight: '700', size: 10 },
                color: '#64748b' 
            } 
        }
    }
};

const sourceChart = computed(() => {
    const labels = Object.keys(props.chartData.sources || {});
    return {
        labels: labels,
        datasets: [{
            label: 'Incidents by Source',
            data: Object.values(props.chartData.sources || {}) as number[],
            backgroundColor: '#10b981', 
            borderRadius: 4,
            barThickness: 20, // Adjusted for a cleaner horizontal-look bar
        }],
    };
});

 const sourceOptions = {
    indexAxis: 'y', 
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        datalabels: {
            anchor: 'end',
            align: 'right',
            color: '#10b981',
            font: { weight: '900', size: 11 },
            // Ensure datalabels are also whole numbers
            formatter: (value: number) => Math.floor(value)
        }
    },
    scales: {
        x: { 
            display: true, 
            grid: { display: false },
            beginAtZero: true, // Start at 0
            ticks: { 
                stepSize: 1, // Forces increments of 1
                precision: 0, // Removes decimals
                font: { weight: '700', size: 10 },
                color: '#64748b',
                // Explicitly return only whole numbers
                callback: function(value: any) {
                    if (value % 1 === 0) {
                        return value;
                    }
                }
            }
        },
        y: { 
            display: true, 
            grid: { display: false },
            ticks: { 
                font: { weight: '700', size: 10 },
                color: '#64748b' 
            } 
        }
    }
};

    function viewDetails(item: any) {
        formDetail.dialogueOpen = true
        formDetail.file_number = item.file_number
        formDetail.reference = item.reference
        formDetail.reporter = item.reporter
        formDetail.designation = item.designation
        formDetail.source = item.source
        formDetail.subject = item.subject
        formDetail.evaluation = item.evaluation
        formDetail.dateAcquired = item.date_acquired
        formDetail.date_of_report = item.date_of_report
        formDetail.mannerAcquired = item.manner_acquired
        formDetail.classification = item.classification.name
        formDetail.informationProper = item.information_proper
        formDetail.analysis = item.analysis
        formDetail.actions = item.actions
        formDetail.selectedBarangay = item.barangay.barangay
        formDetail.selectedCity = item.barangay.city_municipality
        formDetail.selectedProvince = item.barangay.province
        incidentattachments.value = item.attachments.flat()
    }

    async function addressFilter(item: any) {
        const filter = item.value

        try {
            const response = await axios.get('/dashboard/filter-address', {
                params: { addressType: filter }
            });
            chartBarangay.value = response.data || {}; // update reactive copy

        } catch (error) {
            console.error('Error fetching barangay data:', error);
        }
    }

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">

            <div class="flex flex-wrap items-center justify-between gap-6 rounded-2xl border border-sidebar-border/60 bg-card/50 p-3 pl-5 shadow-sm backdrop-blur-md">
            <div class="flex flex-wrap items-center gap-5">
                <div class="group flex flex-col gap-1.5">
                    <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">Start Date</span>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                        <input type="date" v-model="form.start_date" @change="applyFilters"
                            class="rounded-xl border border-sidebar-border bg-background py-2 pl-10 pr-3 text-xs font-bold outline-none ring-offset-background transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" />
                    </div>
                </div>

                <div class="group flex flex-col gap-1.5">
                    <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">End Date</span>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                        <input type="date" v-model="form.end_date" @change="applyFilters"
                            class="rounded-xl border border-sidebar-border bg-background py-2 pl-10 pr-3 text-xs font-bold outline-none ring-offset-background transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" />
                    </div>
                </div>

                <div class="group flex flex-col gap-1.5 min-w-[220px]">
                    <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">Classification</span>
                    <div class="relative">
                        <Tag class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                        <select v-model="form.classification" @change="applyFilters"
                            class="w-full appearance-none rounded-xl border border-sidebar-border bg-background py-2 pl-10 pr-10 text-xs font-bold outline-none ring-offset-background transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">All Types</option>
                            <option v-for="c in classifications" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <!-- <ChevronDown class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground pointer-events-none" /> -->
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button @click="resetFilters" 
                    class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-[10px] font-black tracking-widest text-muted-foreground transition-all hover:bg-rose-50 hover:text-rose-500 group">
                    <FilterX class="h-4 w-4 transition-transform group-hover:rotate-12" />
                    RESET
                </button>

                <div class="h-8 w-1px bg-sidebar-border/50 mx-1"></div>

                <button @click="downloadPDF" 
                    class="flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-[10px] font-black tracking-widest text-white shadow-md transition-all hover:bg-slate-900 active:scale-95">
                    <FileDown class="h-4 w-4" />
                    EXPORT PDF
                </button>

                <button @click="isRiskModalOpen = true"
                    class="relative flex items-center gap-2 overflow-hidden rounded-xl bg-indigo-600 px-6 py-2.5 text-[10px] font-black tracking-widest text-white shadow-lg shadow-indigo-500/30 transition-all hover:bg-indigo-700 hover:shadow-indigo-500/50 active:scale-95 group">
                    <div class="absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                    
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <span class="relative">AI RISK AUDIT</span>
                </button>
            </div>
        </div>
            <!-- <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-sidebar-border/70 bg-card p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="ml-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground">Start Date</span>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-indigo-500" />
                            <input type="date" v-model="form.start_date" @change="applyFilters"
                                class="rounded-xl border border-sidebar-border bg-muted/20 py-1.5 pl-9 pr-3 text-xs font-bold outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="ml-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground">End Date</span>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-indigo-500" />
                            <input type="date" v-model="form.end_date" @change="applyFilters"
                                class="rounded-xl border border-sidebar-border bg-muted/20 py-1.5 pl-9 pr-3 text-xs font-bold outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 min-w-[200px]">
                        <span class="ml-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground">Classification</span>
                        <div class="relative">
                            <Tag class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-indigo-500" />
                            <select v-model="form.classification" @change="applyFilters"
                                class="w-full appearance-none rounded-xl border border-sidebar-border bg-muted/20 py-1.5 pl-9 pr-8 text-xs font-bold outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Types</option>
                                <option v-for="c in classifications" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button @click="resetFilters" 
                    class="flex items-center gap-2 rounded-xl border border-transparent px-4 py-2 text-[10px] font-black tracking-widest text-rose-500 transition-all hover:bg-rose-500/10 hover:border-rose-500/20">
                    <FilterX class="h-4 w-4" />
                    RESET
                </button>

                <button @click="downloadPDF" 
                    class="flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-[10px] font-black tracking-widest text-white shadow-lg shadow-indigo-500/20 transition-all hover:bg-indigo-700 active:scale-95">
                    <FileDown class="h-4 w-4" />
                    EXPORT PDF
                </button>
                   <button 
                    @click="isRiskModalOpen = true"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-md font-semibold text-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    AI Risk Audit (Filtered)
                </button>
            </div> -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    v-for="(val, label) in {
                        'Report (SMS)': [
                            stats.new_sms,
                            'text-amber-500',
                            MessageSquare,
                        ],
                        'Total Verified': [
                            stats.total_incidents,
                            'text-indigo-500',
                            ShieldAlert,
                        ],
                        'For Evaluation': [
                            stats.pending_analysis,
                            'text-rose-500',
                            FileSearch,
                        ],
                        'Active Feed': [
                            recent.length,
                            'text-emerald-500',
                            Activity,
                        ],
                    }"
                    :key="label"
                    class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border"
                >
                    <div
                        class="mb-4 flex justify-between text-muted-foreground"
                    >
                        <component :is="val[2]" class="h-5 w-5" />
                        <span
                            class="text-[10px] font-black tracking-widest uppercase"
                            >{{ label }}</span
                        >
                    </div>
                    <div class="text-3xl font-black" :class="val[1]">
                        {{ val[0] }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm lg:col-span-2 dark:border-sidebar-border"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <div
                                class="flex items-center gap-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase"
                            >
                                <MapPin class="h-4 w-4 text-indigo-500" />
                                Geographic Distribution
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold tracking-tight">
                                    {{
                                        (Object.values(
                                            props.chartData.barangay || {},
                                        )as number[]).reduce(
                                            (a: number, b: number) => a + b,
                                            0,
                                        )
                                    }}
                                </span>
                                <span
                                    :class="
                                        isTrendUp
                                            ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'
                                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    "
                                    class="flex items-center gap-0.5 rounded px-2 py-0.5 text-[10px] font-black"
                                >
                                    {{ isTrendUp ? '+' : '' }}{{ trendValue }}%
                                </span>
                                <span
                                    class="text-[10px] font-medium text-muted-foreground italic"
                                    >vs last month</span
                                >
                            </div>
                        </div>
                        <div class="group flex flex-col gap-1.5 min-w-[220px]">
                            <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">Address Filter</span>
                            <div class="relative">
                                <Tag class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                                <select  @change="addressFilter($event.target ?? '')"
                                    class="w-full appearance-none rounded-xl border border-sidebar-border bg-background py-2 pl-10 pr-10 text-xs font-bold outline-none ring-offset-background transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    <option value="barangay" active>Barangay</option>
                                    <option value="province">Province</option>
                                    <option value="city">City</option>
                                </select>
                                <!-- <ChevronDown class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground pointer-events-none" /> -->
                            </div>
                        </div>
                        <div
                            class="flex rounded-lg border border-sidebar-border/50 bg-muted p-1"
                        >
                            <button
                                @click="chartView = 'bar'"
                                :class="
                                    chartView === 'bar'
                                        ? 'bg-white text-indigo-600 shadow-sm dark:bg-sidebar-accent'
                                        : 'text-muted-foreground hover:text-foreground'
                                "
                                class="rounded-md p-1.5 transition-all duration-200"
                                title="Bar View"
                            >
                                <BarChart3 class="h-4 w-4" />
                            </button>
                            <button
                                @click="chartView = 'line'"
                                :class="
                                    chartView === 'line'
                                        ? 'bg-white text-indigo-600 shadow-sm dark:bg-sidebar-accent'
                                        : 'text-muted-foreground hover:text-foreground'
                                "
                                class="rounded-md p-1.5 transition-all duration-200"
                                title="Line View"
                            >
                                <LineChart class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="h-[300px] transition-all duration-500">
                        <Bar
                            v-if="chartView === 'bar'"
                            :data="barangayChart"
                            :options="options"
                        />
                        <Line v-else :data="barangayChart" :options="options" />
                    </div>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm">
                    <h3 class="text-center text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-6">
                        Classification Distribution
                    </h3>
                    
                    <div class="h-[300px] relative">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none">
                            <div class="flex flex-col items-center">
                                <span class="text-[9px] font-black text-muted-foreground uppercase">Total</span>
                                <span class="text-2xl font-black leading-none">
                                    {{ (Object.values(props.chartData.types || {}) as number[]).reduce((a, b) => a + b, 0) }}
                                </span>
                            </div>
                        </div>

                        <Doughnut :data="typeChart" :options="typeChartOptions as any" />
                    </div>
                    <div class="mt-4 text-center text-[10px] font-medium text-muted-foreground">
                        <!-- * Based on Incident Types -->
                    </div>
                    <!-- <div class="mt-6 space-y-2 border-t pt-4 border-sidebar-border/50">
                        <div v-for="(val, key) in (chartData.types || {})" :key="key" class="flex justify-between text-[10px] items-center">
                            <span class="text-muted-foreground flex items-center gap-2 font-bold uppercase tracking-tight">
                                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: getIncidentColor(String(key)) }"></span>
                                {{ key }}
                            </span>
                            <span class="font-black">{{ val }}</span>
                        </div>
                    </div> -->
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="rounded bg-amber-500/10 p-1">
                                <MessageSquare
                                    class="h-3.5 w-3.5 text-amber-500"
                                />
                            </div>
                            <h3
                                class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                Manner Acquired
                            </h3>
                        </div>
                    </div>

                    <div class="relative h-80 w-full">
                        <div
                            class="pointer-events-none absolute top-1/2 left-1/2 z-10 -translate-x-1/2 -translate-y-1/2"
                        >
                            <div
                                class="flex min-w-[70px] flex-col items-center rounded-2xl border border-white bg-white/90 p-3 shadow-xl backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/90"
                            >
                                <span
                                    class="text-xs font-black tracking-tighter text-muted-foreground uppercase"
                                    >Total</span
                                >
                                <span class="text-xl leading-none font-black">
                                    {{
                                        (
                                            Object.values(
                                                props.chartData.manner || {},
                                            ) as number[]
                                        ).reduce((a, b) => a + b, 0)
                                    }}
                                </span>
                            </div>
                        </div>

                        <Pie :data="mannerChart"  :options="mannerOptions as any" />
                    </div>
                </div>

                <div class="h-full flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border shadow-sm">
                    <div class="p-6 pb-2 flex items-center gap-2">
                        <div class="p-1 rounded bg-indigo-500/10">
                            <User2 class="w-3.5 h-3.5 text-indigo-500" />
                        </div>
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                            Intelligence Operatives
                        </h3>
                    </div>
                    
                    <div class="flex-1 p-6 min-h-[300px]">
                        <Bar :data="reporterChart" :options="reporterOptions as any" />
                    </div>

                    <!-- <div class="mt-auto p-4 bg-muted/20 border-t border-sidebar-border/50">
                             <div class="flex items-center justify-between text-[9px] font-bold uppercase text-muted-foreground tracking-tighter"> -->
                            <!-- <span class="text-[9px] text-muted-foreground uppercase font-bold">Active Staff</span> -->
                            <!-- <span class=" ">{{ Object.keys(props.chartData.reporter || {}).length }} Reporters</span>
                        </div>
                    </div> -->
                </div>

                <div class="h-full flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm">
                    <div class="p-6 pb-2 flex items-center gap-2">
                        <div class="p-1 rounded bg-emerald-500/10">
                            <Share2 class="w-3.5 h-3.5 text-emerald-500" />
                        </div>
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                             Sources of Information
                        </h3>
                    </div>
                    
                    <div class="flex-1 p-6 min-h-[250px]">
                        <Bar :data="sourceChart" :options="sourceOptions as any" />
                    </div>

                    <!-- <div class="p-4 bg-muted/5 border-t border-sidebar-border/50">
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase text-muted-foreground tracking-tighter"> -->
                            <!-- <span>Data Distribution</span> -->
                            <!-- <span>{{ Object.keys(props.chartData.sources || {}).length }}  Sources </span>
                        </div>
                    </div> -->
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm lg:col-span-3 dark:border-sidebar-border"
                >
                    <div
                        class="flex items-center justify-between border-b border-sidebar-border/70 p-5"
                    >
                        <h3 class="text-sm font-bold uppercase">
                            Recent Information Reports
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="bg-muted/50 text-[10px] font-black text-muted-foreground uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-3">Reference</th>
                                    <th class="px-6 py-3">Barangay</th>
                                    <th class="px-6 py-3">Subject</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/70">
                                <tr
                                    v-for="item in recent"
                                    :key="item.id"
                                    class="group transition-colors hover:bg-muted/50"
                                >
                                    <td
                                        class="px-6 py-4 font-mono font-bold text-indigo-500"
                                    >
                                        #{{ item.reference || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        {{ item.barangay?.barangay }}
                                    </td>
                                    <td
                                        class="max-w-[200px] truncate px-6 py-4 text-muted-foreground"
                                    >
                                        {{ item.subject }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="
                                                item.analysis
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                            "
                                            class="rounded px-2 py-0.5 text-[10px] font-black uppercase"
                                        >
                                            {{
                                                item.analysis
                                                    ? 'Analyzed'
                                                    : 'Pending'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            class="inline-flex rounded bg-muted p-1 transition-colors group-hover:bg-indigo-600 group-hover:text-white"
                                            @click="viewDetails(item)"
                                        >
                                            <ChevronRight class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

         <RiskModal 
            :is-open="isRiskModalOpen" 
            :dashboard-data="{
                stats: props.stats,
                recent: props.recent,
                filters: props.filters
            }"
            @close="isRiskModalOpen = false"
        />
        </div>
        
        <Dialog v-model:open="formDetail.dialogueOpen">
            <DialogContent class="max-w-1/2 max-h-[90vh] flex flex-col p-0 overflow-hidden ">
                <DialogHeader class="p-6 pb-2">
                    <DialogTitle class="text-xl font-semibold tracking-tight">Incident Details</DialogTitle>    
                </DialogHeader>

                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">File Number</span>
                            <p class="font-medium">{{ formDetail.file_number }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Reference</span>
                            <p class="font-medium">{{ formDetail.reference }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Subject</span>
                            <p class="font-medium">{{ formDetail.subject }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Date of Report</span>
                            <p class="font-medium">{{ formDetail.date_of_report }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Reporter</span>
                            <p class="font-medium">{{ formDetail.reporter }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Designation/Unit Assignment</span>
                            <p class="font-medium">{{ formDetail.designation }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Source</span>
                            <p class="font-medium">{{ formDetail.source }} </p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Date Acquired</span>
                            <p class="font-medium">{{ formDetail.dateAcquired }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Manner Acquired</span>
                            <p class="font-medium">({{ formDetail.mannerAcquired }})</p>
                        </div>
                    </div>

                    <Separator class="my-6" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm mt-2">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Classification </span>
                            <p class="font-medium">{{ formDetail.classification }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Evaluation </span>
                            <p class="font-medium">{{ formDetail.evaluation }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Address </span>
                            <p class="font-medium">{{ formDetail.selectedBarangay }} , {{ formDetail.selectedCity }} {{ formDetail.selectedProvince }}</p>

                        </div>
                    </div>
                    <div class="space-y-4">

                        <div class="bg-muted/50 rounded-lg p-4 border border-border">
                            <h4 class="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-2">Information Proper</h4>
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ formDetail.informationProper }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Comments & Analysis</h4>
                        <p class="text-sm text-foreground/80 italic border-l-2 border-primary/30 pl-3">
                        {{ formDetail.analysis }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Actions Taken</h4>
                        <p class="text-sm font-medium">{{ formDetail.actions }}</p>
                    </div>
                    </div>

                    <div class="mt-8">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-3">Attachments</h4>
                    <div v-if="incidentattachments.length" class="flex flex-wrap gap-3">
                        <div
                            v-for="file in incidentattachments"
                            :key="file.id"
                            class="group relative w-24 h-24 rounded-md border bg-background overflow-hidden hover:ring-2 hover:ring-primary transition-all"
                        >
                                <a
                                :href="`/storage/${file.url}`"
                                target="_blank"
                                class="block mt-2 text-sm text-primary hover:underline text-center"
                            >
                                <img :src="`/storage/${file.url}`" class="w-full h-full object-cover" />
                            </a>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground italic bg-muted/30 p-4 rounded-md border border-dashed text-center">
                        No attachments available for this file.
                    </div>
                    </div>
                </div>

                <DialogFooter class="p-6 bg-muted/20 border-t">
                    <Button variant="outline" @click="formDetail.dialogueOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

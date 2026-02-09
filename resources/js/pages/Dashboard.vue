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


 


 
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

 

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
                        <input type="date"
                            class="rounded-xl border border-sidebar-border bg-background py-2 pl-10 pr-3 text-xs font-bold outline-none ring-offset-background transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" />
                    </div>
                </div>

                <div class="group flex flex-col gap-1.5">
                    <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">End Date</span>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                        <input type="date" 
                            class="rounded-xl border border-sidebar-border bg-background py-2 pl-10 pr-3 text-xs font-bold outline-none ring-offset-background transition-all focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" />
                    </div>
                </div>

                <div class="group flex flex-col gap-1.5 min-w-[220px]">
                    <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">Classification</span>
                    <div class="relative">
                        <Tag class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                       
                        <!-- <ChevronDown class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground pointer-events-none" /> -->
                    </div>
                </div>
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
                            
                            </div>
                        </div>
                        <div class="group flex flex-col gap-1.5 min-w-[220px]">
                            <span class="ml-1 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 group-focus-within:text-indigo-500 transition-colors">Address Filter</span>
                            <div class="relative">
                                <Tag class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-indigo-500/70" />
                                <select 
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
                            
                          
                        </div>
                    </div>

                    <div class="h-[300px] transition-all duration-500">
                         
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
                                   
                                </span>
                            </div>
                        </div>

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
                                   
                                </span>
                            </div>
                        </div>

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
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
 
    </AppLayout>
</template>

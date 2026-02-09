<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ollama from 'ollama';

const question = ref('');
const feedback = ref(''); 
const riskData = ref([]); 
const loading = ref(false);
const MODEL_NAME = 'granite3.2:8b';

// Scoring Logic (Matches the UI)
const calculateScore = (severity, likelihood) => {
    const map = { 'high': 3, 'medium': 2, 'low': 1, 'moderate': 2, 'critical': 3 };
    const s = map[severity.toLowerCase()] || 1;
    const l = map[likelihood.toLowerCase()] || 1;
    const total = s * l;
    
    if (total >= 7) return { val: total, label: 'Extreme', color: 'bg-red-600' };
    if (total >= 4) return { val: total, label: 'High', color: 'bg-orange-500' };
    if (total >= 2) return { val: total, label: 'Medium', color: 'bg-amber-500' };
    return { val: total, label: 'Low', color: 'bg-emerald-500' };
};

const parseTable = (text) => {
    const lines = text.split('\n');
    const rows = lines.filter(line => line.includes('|') && !line.includes('---'));
    if (rows.length < 2) return [];

    return rows.slice(1).map(row => {
        const cells = row.split('|').map(c => c.trim()).filter(Boolean);
        const sev = cells[1] || 'Low';
        const lik = cells[2] || 'Low';
        return {
            identified: cells[0] || 'N/A',
            severity: sev,
            likelihood: lik,
            mitigation: cells[3] || 'N/A',
            recommendation: cells[4] || 'N/A',
            monitor: cells[5] || 'N/A',
            score: calculateScore(sev, lik)
        };
    }).filter(r => r.identified.toLowerCase() !== 'risk identified');
};

// RESTORED: Updated Export Function
const exportToCSV = () => {
    if (riskData.value.length === 0) return;
    
    const headers = ["Risk", "Score", "Rating", "Severity", "Likelihood", "Mitigation", "Recommendation", "Monitor"];
    const csvRows = [
        headers.join(','),
        ...riskData.value.map(r => 
            `"${r.identified}","${r.score.val}","${r.score.label}","${r.severity}","${r.likelihood}","${r.mitigation}","${r.recommendation}","${r.monitor}"`
        )
    ];

    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", `Risk_Assessment_${new Date().toISOString().slice(0,10)}.csv`);
    link.click();
};

const getSeverityClass = (sev) => {
    const val = sev.toLowerCase();
    if (val.includes('high') || val.includes('critical')) return 'bg-red-100 text-red-700 border-red-200';
    if (val.includes('medium') || val.includes('moderate')) return 'bg-amber-100 text-amber-700 border-amber-200';
    return 'bg-emerald-100 text-emerald-700 border-emerald-200';
};

async function runRiskAssessment() {
    loading.value = true;
    feedback.value = '';
    riskData.value = [];
    try {
        const response = await ollama.chat({
            model: MODEL_NAME,
            messages: [
                { 
                    role: 'system', 
                    content: `You are a Senior Risk Officer. Output a Markdown table.
                    Headers: | Risk Identified | Severity | Likelihood | Mitigation Strategy | Strategic Recommendation | What to Monitor |
                    Rules: Use labels (High, Medium, Low). Respond ONLY with the table.`
                },
                { role: 'user', content: question.value }
            ]
        });
        feedback.value = response.message.content;
        riskData.value = parseTable(feedback.value);
    } catch (error) {
        alert("Ollama connection failed.");
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Risk Assessment" />
    <AppLayout>
         <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Intelligence Assessment Engine</h1>
                    <p class="text-slate-500 text-sm">Automated scoring and strategic recommendations using local Granite 3.2 LLM</p>
                </div>
                <div v-if="riskData.length" class="flex items-center gap-3">
                    <button @click="exportToCSV" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-3 tracking-widest">Incident/Scenario</label>
                        <textarea v-model="question" class="w-full p-4 border-slate-200 rounded-lg h-64 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Enter scenario..."></textarea>
                        <button @click="runRiskAssessment" :disabled="loading || !question" class="w-full mt-4 flex items-center justify-center gap-3 px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md">
                            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            {{ loading ? 'Analyzing...' : 'Generate Risk Report' }}
                        </button>
                    </div>

                    

                    <div class="bg-white p-4 rounded-lg border border-slate-200">
                        <h4 class="text-xs font-bold text-slate-700 uppercase mb-3">Score Matrix (S × L)</h4>
                        <div class="space-y-2 text-[10px] uppercase font-bold">
                            <div class="flex justify-between items-center p-2 bg-red-50 text-red-700 rounded border border-red-100"><span>7-9 Extreme</span><span>Critical</span></div>
                            <div class="flex justify-between items-center p-2 bg-orange-50 text-orange-700 rounded border border-orange-100"><span>4-6 High</span><span>Urgent</span></div>
                            <div class="flex justify-between items-center p-2 bg-amber-50 text-amber-700 rounded border border-amber-100"><span>2-3 Medium</span><span>Monitor</span></div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-3 space-y-6">
                    <div v-if="riskData.length" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                    <th class="p-4">Risk Identified</th>
                                    <th class="p-4 text-center">Score</th>
                                    <th class="p-4">Impact / Prob.</th>
                                    <th class="p-4 text-blue-600">Strategic Recommendation</th>
                                    <th class="p-4">Monitoring</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(row, idx) in riskData" :key="idx" class="hover:bg-slate-50/50 transition">
                                    <td class="p-4 text-sm font-semibold text-slate-800 leading-tight">{{ row.identified }}</td>
                                    
                                    <td class="p-4 text-center">
                                        <div :class="['inline-block px-3 py-1 rounded-full text-white text-[10px] font-black shadow-sm', row.score.color]">
                                            {{ row.score.val }}
                                        </div>
                                    </td>

                                    <td class="p-4 space-x-1 whitespace-nowrap">
                                        <span :class="['px-2 py-0.5 rounded text-[9px] font-bold border uppercase', getSeverityClass(row.severity)]">{{ row.severity }}</span>
                                        <span class="text-slate-300">/</span>
                                        <span :class="['px-2 py-0.5 rounded text-[9px] font-bold border uppercase', getSeverityClass(row.likelihood)]">{{ row.likelihood }}</span>
                                    </td>
                                    
                                    <td class="p-4 text-xs text-blue-800 bg-blue-50/20 font-medium italic leading-relaxed">{{ row.recommendation }}</td>
                                    <td class="p-4 text-[11px] text-slate-500 leading-tight">{{ row.monitor }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="loading" class="space-y-4">
                        <div v-for="i in 4" :key="i" class="h-20 bg-white animate-pulse rounded-lg border border-slate-200"></div>
                    </div>

                    <div v-else class="h-64 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400">
                        <p class="text-sm">No analysis active. Enter data in the sidebar to begin.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
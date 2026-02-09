<script setup lang="ts">
import { ref, watch } from 'vue';
import ollama from 'ollama';

const props = defineProps<{
    isOpen: boolean,
    dashboardData: {
        stats: any,
        recent: any[],
        filters: any
    }
}>();

const emit = defineEmits(['close']);

interface RiskEntry {
    identified: string;
    severity: string;
    likelihood: string;
    mitigation: string;
    recommendation: string;
    monitor: string;
    score: {
        val: number;
        label: string;
        color: string;
    };
}
const riskData = ref<RiskEntry[]>([]);
const loading = ref(false);
const MODEL_NAME = 'granite3.2:8b';

const calculateScore = (severity: string, likelihood: string) => {
    const map: Record<string, number> = { 
        'high': 3, 'medium': 2, 'low': 1, 'moderate': 2, 'critical': 3 
    };
    
    const s = map[severity.toLowerCase()] || 1;
    const l = map[likelihood.toLowerCase()] || 1;
    const total = s * l;
    
if (total >= 7) return { val: total, label: 'CRITICAL/RED', color: 'bg-red-700' }; // Darker Red
    if (total >= 4) return { val: total, label: 'HIGH/AMBER', color: 'bg-amber-600' };
    if (total >= 2) return { val: total, label: 'MED/YELLOW', color: 'bg-yellow-500 text-black' }; // Yellow contrast
    return { val: total, label: 'LOW/GREEN', color: 'bg-emerald-600' };
};


const getSeverityClass = (sev: string) => {
    const val = sev.toLowerCase();
    if (val.includes('high') || val.includes('critical')) return 'bg-red-100 text-red-700 border-red-200';
    if (val.includes('medium') || val.includes('moderate')) return 'bg-amber-100 text-amber-700 border-amber-200';
    return 'bg-emerald-100 text-emerald-700 border-emerald-200';
};

const parseTable = (text: string) => {
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

const exportToCSV = () => {
    if (riskData.value.length === 0) return;
    const headers = ["Risk", "Score", "Rating", "Severity", "Likelihood", "Mitigation", "Recommendation", "Monitor"];
    const csvRows = [
        headers.join(','),
        ...riskData.value.map(r => `"${r.identified }","${r.score.val}","${r.score.label}","${r.severity}","${r.likelihood}","${r.mitigation}","${r.recommendation}","${r.monitor}"`)
    ];
    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", `AI_Risk_Audit_${new Date().toISOString().slice(0,10)}.csv`);
    link.click();
};

async function runRiskAssessment() {
    loading.value = true;
    riskData.value = [];
    try {

        
        const response = await ollama.chat({
            model: MODEL_NAME,

            // messages: [
            //         { 
            //             role: 'system', 
            //             content: `You are a Lead Intelligence Analyst . 
            //             Your mission: Identify high-value targets (HVT), radicalization patterns, and imminent security breaches.

            //             OPERATIVE FRAMEWORK:
            //             1. THREAT ACTOR PROFILING: Identify if activity suggests coordinated cell behavior vs. lone-wolf actors.
            //             2. KINETIC INDICATORS: Flag spikes in communications, unauthorized geofence entries, or encrypted data spikes.
            //             3. VALIDATION: Cross-reference 'stats' (volume) with 'activity' (behavior) to confirm if a threat is credible.

            //             TABLE HEADERS:
            //             | Target/Threat Identifier | Tier (Severity) | Probability | Tactical Response | Intelligence Gap | Sentinel Indicator |

            //             OPERATIVE RULES:
            //             - Tier Labels: {Tier 1 (Imminent), Tier 2 (Developing), Tier 3 (Monitored)}.
            //             - Tactical Response: Specific action for field operatives (e.g., "Begin Surveillance", "Isolate Endpoint").
            //             - Intelligence Gap: What data is missing to confirm the threat?
            //             - Sentinel Indicator: The specific "tripwire" to watch for next.
            //             - Respond ONLY with the Markdown table.`
            //         },
            //         { 
            //             role: 'user', 
            //             content: `FIELD INTELLIGENCE REPORT:
            //             ---
            //             SECTOR/CLASSIFICATION: ${props.dashboardData.filters.classification || 'Unclassified'}
            //             SURVEILLANCE METRICS: ${JSON.stringify(props.dashboardData.stats)}

            //             Recent Intelligence report: ${JSON.stringify(props.dashboardData.recent)}
            //             ---
            //             Analyze for immediate tactical threats and terrorist indicators.` 
            //         }
            //     ]
         
            // messages: [
            //     { 
            //         role: 'system', 
            //         content: `You are a Senior Risk Officer. Analyze the provided dashboard snapshot.
                    
            //         CRITICAL INSTRUCTIONS:
            //         1. CROSS-REFERENCE: Look for correlations between 'Metric Trends' and 'Recent Activity'. 
            //         2. ANOMALY DETECTION: Flag any metric that deviates from the average or indicates a failure.
            //         3. OUTPUT: Respond ONLY with a Markdown table.
                    
            //         TABLE HEADERS:
            //         | Risk Identified | Severity | Likelihood | Mitigation Strategy | Strategic Recommendation | What to Monitor |
                    
            //         DATA CONTEXT RULES:
            //         - Severity labels: {Critical, High, Medium, Low}
            //         - Likelihood labels: {Frequent, Probable, Remote}
            //         - Mitigation: Immediate technical fix.
            //         - Recommendation: Long-term process improvement.`
            //     },
            //     { 
            //         role: 'user', 
            //         content: `DASHBOARD DATA NORMALIZATION:
            //         ---
            //         ACTIVE FILTERS: 
            //         Category: ${props.dashboardData.filters.classification || 'Global'}
            //         Timeframe: ${props.dashboardData.filters.start_date} to ${props.dashboardData.filters.end_date}
                    
            //         CORE METRICS (SNAPSHOT):
            //         ${JSON.stringify(props.dashboardData.stats)}
                    
            //         RECENT SYSTEM EVENTS:
            //         ${JSON.stringify(props.dashboardData.recent)}
            //         ---
            //         Analyze the data above and identify the top 5 operational or security risks.` 
            //     }
            // ],
            messages: [
                { 
                    role: 'system', 
                    content: `You are a Senior Risk Officer. Analyze the following dashboard JSON data.
                    Identify specific risks based on the stats and recent activity.
                    Output a Markdown table.
                    Headers: | Risk Identified | Severity | Likelihood | Mitigation Strategy | Strategic Recommendation | What to Monitor |
                     RULES:
                    - Do not summarize. 
                    - Respond ONLY with the Markdown table.
                    - Severity labels: {Critical, High, Medium, Low}
                    - If 'recent_activity' shows a pattern, prioritize it.
                    - Ensure recommendations are actionable, not vague.
                     -Please provide a deep-dive risk audit.` 
                },
          { 
                    role: 'user', 
                    content: `DASHBOARD DATA NORMALIZATION:
                    ---
                    ACTIVE FILTERS: 
                    Category: ${props.dashboardData.filters.classification || 'Global'}
                    Timeframe: ${props.dashboardData.filters.start_date} to ${props.dashboardData.filters.end_date}
                    
                    CORE METRICS (SNAPSHOT):
                    ${JSON.stringify(props.dashboardData.stats)}
                    
                    RECENT SYSTEM EVENTS:
                    ${JSON.stringify(props.dashboardData.recent)}
                    ---
                    Analyze the data above and identify the top 5 operational or security risks.` 
                }
            ]

            // messages: [
            //     { 
            //         role: 'system', 
            //         content: `You are a Senior Risk Officer and Strategic Auditor.
                    
            //         TASK:
            //         Analyze the provided Dashboard JSON (Stats, Recent Activity, and Filters).
            //         Identify 5-7 distinct risks by correlating data points (e.g., if incidents are high but mitigation is low).

            //         COLUMN REQUIREMENTS:
            //         1. Risk Identified: Specific title (e.g., "SLA Breach in High-Priority Tickets").
            //         2. Severity: {Critical, High, Medium, Low} based on business impact.
            //         3. Likelihood: {Frequent, Probable, Remote} based on recent activity frequency.
            //         4. Mitigation Strategy: Immediate technical action to stop the leak.
            //         5. Strategic Recommendation: Long-term process improvement using S.M.A.R.T goals.
            //         6. What to Monitor: Specific KPI or threshold to watch in the dashboard.

            //         RULES:
            //         - Do not summarize. 
            //         - Respond ONLY with the Markdown table.
            //         - If 'recent_activity' shows a pattern, prioritize it.
            //         - Ensure recommendations are actionable, not vague.` 
            //     },
            //     { 
            //         role: 'user', 
            //         content: `DASHBOARD CONTEXT:
            //         - Active Filters: ${JSON.stringify(props.dashboardData.filters)}
            //         - Core Metrics: ${JSON.stringify(props.dashboardData.stats)}
            //         - Latest Logs/Events: ${JSON.stringify(props.dashboardData.recent)}
                    
            //         Please provide a deep-dive risk audit.` 
            //     }
            // ]
        });
        riskData.value = parseTable(response.message.content);
    } catch (error) {
        alert("Ollama connection failed.");
    } finally {
        loading.value = false;
    }
}

watch(() => props.isOpen, (newVal) => {
    if (newVal && riskData.value.length === 0) runRiskAssessment();
});
</script>

<template>
   <div v-if="isOpen" class="fixed inset-0 z-60 flex items-center justify-center p-2 lg:p-4 bg-slate-900/70 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-[95vw] xl:max-w-[92vw] h-[90vh] flex flex-col overflow-hidden border border-white/20 animate-in fade-in zoom-in-95 duration-200">
            
            <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-white">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight"> Intelligence Assessments</h2>
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded border border-slate-200 bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>

                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        <span class="text-[9px] font-bold text-slate-500 tracking-widest uppercase">Automated scoring and strategic recommendations using local Granite 3.2 LLM</span>
                    </div>
                    <p class="text-sm text-slate-500">
                        Based on filters: 
                        <span class="font-mono text-blue-600 bg-blue-50 px-2 rounded">
                            {{ dashboardData.filters.classification || 'All' }} 
                            ({{ dashboardData.filters.start_date || 'Start' }} - {{ dashboardData.filters.end_date || 'End' }})
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="riskData.length" @click="exportToCSV" class="text-xs font-bold text-slate-600 hover:text-slate-900 px-3 py-2 border rounded-lg transition">
                        Export CSV
                    </button>
                    <button @click="$emit('close')" class="p-2 hover:bg-slate-100 rounded-full transition text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
                <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="mt-4 text-slate-600 font-medium">Intelligence is analyzing filtered data...</p>
                </div>

                <div v-else-if="riskData.length" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">Risk Identified</th>
                                <th class="p-4 text-center">Score</th>
                                <th class="p-4">Impact / Prob.</th>
                                <th class="p-4 text-indigo-600">Strategic Recommendation</th>
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
                                <td class="p-4 text-xs text-indigo-800 bg-indigo-50/20 font-medium italic leading-relaxed">{{ row.recommendation }}</td>
                                <td class="p-4 text-[11px] text-slate-500 leading-tight">{{ row.monitor }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 border-t bg-white flex justify-between items-center">
                <div class="flex gap-4">
                     <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-400">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span> 7-9 Critical
                        <span class="w-2 h-2 rounded-full bg-orange-500 ml-2"></span> 4-6 High
                        <span class="w-2 h-2 rounded-full bg-amber-500 ml-2"></span> 1-3 Med
                     </div>
                </div>
               <div class="flex gap-3">
                <button @click="$emit('close')" class="px-6 py-2.5 text-xs font-black tracking-widest text-slate-400 hover:text-slate-600 transition-colors uppercase">Cancel</button>
                <button @click="runRiskAssessment" 
                    :disabled="loading"
                    class="group relative flex items-center gap-2 overflow-hidden px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-50">
                    <svg :class="{'animate-spin': loading}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ loading ? 'ANALYZING...' : 'RE-RUN INTELLIGENCE AUDIT' }}
                </button>
            </div>
            </div>
        </div>
    </div>
</template>
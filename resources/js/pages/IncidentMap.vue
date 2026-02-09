<template>
    <Head title="Verified Incident Tracker" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:flex-row">
            <div class="relative min-h-[600px] flex-1 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-2xl">
                
                <div id="map" class="h-full w-full outline-none bg-[#cbd5e1]"></div>

                <div class="pointer-events-auto absolute top-4 left-4 z-1000 w-72">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-xl backdrop-blur-md">
                        <div @click="isSidebarCollapsed = !isSidebarCollapsed"
                             class="flex cursor-pointer items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex flex-col">
                                <label class="text-[10px] font-black tracking-widest text-slate-400 uppercase">Administrative View</label>
                                <!-- <span class="text-[11px] font-bold text-primary">Intelligence Map</span> -->
                            </div>
                            <svg :class="{'rotate-180': isSidebarCollapsed}" class="w-5 h-5 text-slate-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>

                        <div v-show="!isSidebarCollapsed" class="animate-in fade-in slide-in-from-top-2 p-4 pt-0 space-y-4">
                            <hr class="border-slate-100" />
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Province</label>
                                    <select v-model="filters.province" @change="handleProvinceChange" class="filter-select w-full rounded-lg border-slate-200 text-xs font-bold">
                                        <option value="">All Provinces</option>
                                        <option v-for="p in availableProvinces" :key="p" :value="p">{{ p }}</option>
                                    </select>
                                </div>

                                <div v-if="filters.province">
                                    <label class="text-[10px] font-bold text-slate-500 uppercase">City / Municipality</label>
                                    <select v-model="filters.city" @change="applyFilters" class="filter-select w-full rounded-lg border-slate-200 text-xs font-bold">
                                        <option value="">All Cities</option>
                                        <option v-for="c in availableCities" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold tracking-wider text-slate-500 uppercase">Visualization Mode</label>
                                <div class="mt-2 flex items-center justify-between rounded-xl bg-slate-100 p-2">
                                    <span class="text-[10px] font-bold text-slate-600">Heatmap Mode</span>
                                    <button @click="toggleHeatmap" 
                                            :class="['h-6 w-11 rounded-full transition-colors relative', isHeatmapEnabled ? 'bg-primary' : 'bg-slate-300']">
                                        <div :class="['absolute top-1 h-4 w-4 rounded-full bg-white transition-all', isHeatmapEnabled ? 'left-6' : 'left-1']"></div>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold tracking-wider text-slate-500 uppercase">Granularity</label>
                                <div class="mt-2 flex gap-1 rounded-xl bg-slate-100 p-1">
                                    <button v-for="level in (['province', 'city_municipality', 'barangay'] as const)" 
                                        :key="level"
                                        @click="setAdminLevel(level)"
                                        :class="['flex-1 rounded-lg py-2 text-[10px] font-bold uppercase transition-all', 
                                        currentAdminLevel === level ? 'bg-primary text-white shadow-md' : 'text-slate-500 hover:bg-white']">
                                        {{ level === 'city_municipality' ? 'City' : level === 'barangay' ? 'Brgy' : 'Prov' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pointer-events-auto absolute top-4 right-4 z-1000 w-72 md:w-80">
                    <div class="bg-white/95 rounded-2xl border border-slate-200 shadow-xl backdrop-blur-md p-2">
                        <div class="flex items-center gap-2 bg-slate-100 rounded-xl px-3 py-2 transition-all focus-within:ring-2 focus-within:ring-primary/10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" v-model="searchQuery" @input="handleSearch"
                                class="w-full bg-transparent border-none text-xs font-bold text-slate-700 focus:ring-0 placeholder:text-slate-400" 
                                placeholder="Search locality...">
                        </div>
                        <ul v-if="searchResults.length > 0" class="mt-2 max-h-60 overflow-y-auto divide-y divide-slate-50 rounded-xl border border-slate-100 bg-white shadow-inner custom-scroll">
                            <li v-for="res in searchResults" :key="res.id" @click="jumpToLocation(res)"
                                class="p-3 hover:bg-slate-50 cursor-pointer transition-colors">
                                <div class="text-[11px] font-black text-slate-800">{{ res.props.ADM4_EN || 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-500 font-bold">{{ res.props.ADM3_EN }}, {{ res.props.ADM2_EN }}</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="isMapLoading" class="absolute inset-0 z-9999 flex items-center justify-center bg-slate-900/40 backdrop-blur-[2px]">
                    <div class="flex flex-col items-center gap-4 rounded-3xl bg-white p-10 shadow-2xl">
                        <div class="h-10 w-10 animate-spin rounded-full border-[3px] border-primary border-t-transparent"></div>
                        <div class="text-center">
                            <span class="block text-xs font-black uppercase tracking-widest text-slate-800">Processing Geodata</span>
                            <span class="text-[10px] font-bold text-slate-400">Please wait...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { defineComponent, onMounted, ref, reactive, computed } from 'vue';
import * as L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.heat';

export default defineComponent({
    name: 'MapView',
    components: { AppLayout, Head },
    setup() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Intelligence', href: '/mapping' }, 
        { title: 'Map', href: '/mapping' }
    ];
    
    const isMapLoading = ref(true);
    const isSidebarCollapsed = ref(false);
    const currentAdminLevel = ref<'province' | 'city_municipality' | 'barangay'>('barangay');
    
    // Heatmap & Search State
    const isHeatmapEnabled = ref(false);
    const searchQuery = ref('');
    const searchResults = ref<any[]>([]);
    const filters = reactive({ province: '', city: '' });

    let map: L.Map;
    let labelLayer: L.LayerGroup;
    let heatLayer: any = null;
    let debounceTimer: number | null = null;

    // Master list: Stores all data but doesn't mean they are all on the map
    let allGeoJsonLayers: { layer: L.Path, props: any, bounds: L.LatLngBounds }[] = [];

    const availableProvinces = ref<string[]>([]);
    const availableCities = computed(() => {
        if (!filters.province) return [];
        const cities = allGeoJsonLayers
            .filter(item => item.props.ADM2_EN === filters.province)
            .map(item => item.props.ADM3_EN);
        return [...new Set(cities)].sort();
    });

    /**
     * SPATIAL LOADING ENGINE
     * Mounts/Unmounts polygons based on the current screen view (Viewport)
     */
    const updateVisiblePolygons = () => {
        if (!map) return;

        const visibleBounds = map.getBounds();
        
        allGeoJsonLayers.forEach(item => {
            // Safety Check: Skip features with broken geometry
            if (!item.bounds || typeof item.bounds.isValid !== 'function' || !item.bounds.isValid()) return;

            // Check if feature is inside screen + matches sidebar filters
            const isInsideViewport = visibleBounds.intersects(item.bounds);
            const matchProv = !filters.province || item.props.ADM2_EN === filters.province;
            const matchCity = !filters.city || item.props.ADM3_EN === filters.city;

            if (isInsideViewport && matchProv && matchCity) {
                if (!map.hasLayer(item.layer)) item.layer.addTo(map);
            } else {
                if (map.hasLayer(item.layer)) map.removeLayer(item.layer);
            }
        });

        updateLabels();
    };

    /**
     * PERFORMANCE DEBOUNCE
     * Prevents the spatial engine from locking the UI thread during rapid zooming/panning
     */
    const handleMapMove = () => {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => {
            updateVisiblePolygons();
        }, 250); // Execute 250ms after movement stops
    };

    // const updateLabels = () => {
    //     if (!labelLayer || !map) return;
    //     labelLayer.clearLayers();
        
    //     const zoom = map.getZoom();
    //     if (zoom < 13) return; // Only show labels when zoomed in close

    //     const visibleBounds = map.getBounds();
        
    //     allGeoJsonLayers
    //         .filter(item => {
    //             if (!item.bounds || !item.bounds.isValid()) return false;
    //             const isVisible = visibleBounds.intersects(item.bounds);
    //             const matchProv = !filters.province || item.props.ADM2_EN === filters.province;
    //             const matchCity = !filters.city || item.props.ADM3_EN === filters.city;
    //             return isVisible && matchProv && matchCity;
    //         })
    //         .slice(0, 50) // Limit label count to maintain FPS
    //         .forEach(item => {
    //             try {
    //                 const center = item.bounds.getCenter();
    //                 L.marker(center, {
    //                     icon: L.divIcon({
    //                         className: 'map-label',
    //                         html: `<span>${item.props.ADM4_EN || 'Unknown'}</span>`,
    //                         iconSize: [100, 20],
    //                         iconAnchor: [50, 10]
    //                     }),
    //                     interactive: false
    //                 }).addTo(labelLayer);
    //             } catch (e) { /* Ignore invalid centers */ }
    //         });
    // };
const updateLabels = () => {
    if (!labelLayer || !map) return;
    labelLayer.clearLayers();
    
    const zoom = map.getZoom();
    // 1. Raise zoom threshold for Barangays
    // Only show Brgy labels at level 15+; show nothing or only Cities below that
    if (zoom < 13 ) return; 

    const visibleBounds = map.getBounds();
    const placedLabels: L.Point[] = []; // Track pixels of placed labels
    const MIN_DISTANCE = 80; // Minimum pixel distance between label centers

    allGeoJsonLayers
        .filter(item => {
            if (!item.bounds || !item.bounds.isValid()) return false;
            const isVisible = visibleBounds.intersects(item.bounds);
            const matchProv = !filters.province || item.props.ADM2_EN === filters.province;
            const matchCity = !filters.city || item.props.ADM3_EN === filters.city;
            return isVisible && matchProv && matchCity;
        })
        .forEach(item => {
            try {
                const center = item.bounds.getCenter();
                // 2. Convert geographic LatLng to screen Pixel coordinates
                const containerPoint = map.latLngToContainerPoint(center);

                // 3. Collision Detection: Check distance against already placed labels
                const isTooClose = placedLabels.some(point => 
                    containerPoint.distanceTo(point) < MIN_DISTANCE
                );

                if (!isTooClose) {
                    L.marker(center, {
                        icon: L.divIcon({
                            className: 'map-label',
                            html: `<span>${item.props.ADM4_EN || 'Unknown'}</span>`,
                            iconSize: [100, 20],
                            iconAnchor: [50, 10]
                        }),
                        interactive: false
                    }).addTo(labelLayer);

                    // Record this position so the next label checks against it
                    placedLabels.push(containerPoint);
                }
            } catch (e) { /* Ignore invalid geometry */ }
        });
};
    const applyFilters = () => {
        if (!map) return;
        const bounds = L.latLngBounds([]);
        let hasVisible = false;

        allGeoJsonLayers.forEach(item => {
            const matchProv = !filters.province || item.props.ADM2_EN === filters.province;
            const matchCity = !filters.city || item.props.ADM3_EN === filters.city;
            if (matchProv && matchCity) {
                bounds.extend(item.bounds);
                hasVisible = true;
            }
        });

        if (hasVisible && (filters.province || filters.city)) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
        
        updateVisiblePolygons();
        if (isHeatmapEnabled.value) renderHeatmap();
    };

    const handleProvinceChange = () => {
        filters.city = '';
        applyFilters();
    };

    const toggleHeatmap = () => {
        isHeatmapEnabled.value = !isHeatmapEnabled.value;
        renderHeatmap();
    };

    const renderHeatmap = () => {
        if (heatLayer) map.removeLayer(heatLayer);
        if (!isHeatmapEnabled.value) {
            updateVisiblePolygons();
            return;
        }

        // Dim polygons to highlight heat
        allGeoJsonLayers.forEach(item => {
            if(map.hasLayer(item.layer)) {
                item.layer.setStyle({ fillOpacity: 0.05, opacity: 0.1 });
            }
        });

        const heatPoints = allGeoJsonLayers
            .filter(item => {
                const matchProv = !filters.province || item.props.ADM2_EN === filters.province;
                const matchCity = !filters.city || item.props.ADM3_EN === filters.city;
                return matchProv && matchCity;
            })
            .map(item => {
                const center = item.bounds.getCenter();
                return [center.lat, center.lng, 0.5] as [number, number, number];
            });

        heatLayer = (L as any).heatLayer(heatPoints, { radius: 25, blur: 15 }).addTo(map);
    };

    const handleSearch = () => {
        if (searchQuery.value.length < 2) {
            searchResults.value = [];
            return;
        }
        const q = searchQuery.value.toLowerCase();
        searchResults.value = allGeoJsonLayers
            .filter(i => i.props.ADM4_EN?.toLowerCase().includes(q) || i.props.ADM3_EN?.toLowerCase().includes(q))
            .slice(0, 8);
    };

    const jumpToLocation = (item: any) => {
        map.flyToBounds(item.bounds, { maxZoom: 16 });
        item.layer.openPopup();
        searchResults.value = [];
        searchQuery.value = item.props.ADM4_EN;
    };

    const setAdminLevel = (level: 'province' | 'city_municipality' | 'barangay') => {
        currentAdminLevel.value = level;
        applyFilters();
    };

    onMounted(async () => {
        map = L.map('map', { 
            zoomControl: false, 
            preferCanvas: true // Use HTML5 Canvas for drawing polygons (Faster than SVG)
        }).setView([14.5995, 120.9842], 12);

        const ncrMarker = L.marker([14.5995, 120.9842]).addTo(map);
        ncrMarker.bindPopup('<b>NCR Central Hub</b>').openPopup();

        labelLayer = L.layerGroup().addTo(map);

        try {
            const res = await fetch('/geojson/shape.json');
            const data = await res.json();
            const provinces = new Set<string>();

            // Process data but do NOT add to map yet
            L.geoJSON(data.features, {
                style: { color: '#ffffff', weight: 0.2, fillColor: '#64748b', fillOpacity: 0.2 },
                onEachFeature: (f, layer: any) => {
                    const bounds = layer.getBounds();
                    if (bounds && bounds.isValid()) {
                        allGeoJsonLayers.push({ layer, props: f.properties, bounds });
                        if (f.properties.ADM2_EN) provinces.add(f.properties.ADM2_EN);
                        layer.bindPopup(`<div class="font-bold text-xs">${f.properties.ADM4_EN}</div>`);
                    }
                }
            });

            availableProvinces.value = [...provinces].sort();
            isMapLoading.value = false;

            // Initial view load
            updateVisiblePolygons();
        } catch (err) {
            console.error("Map Load Error:", err);
        }

        // Attach debounced spatial loading to map movement
        map.on('moveend zoomend', handleMapMove);
    });

    return { 
        breadcrumbs, isMapLoading, isSidebarCollapsed, currentAdminLevel,
        filters, availableProvinces, availableCities, isHeatmapEnabled,
        searchQuery, searchResults,
        setAdminLevel, handleProvinceChange, applyFilters, toggleHeatmap, handleSearch, jumpToLocation
    };
}
});
</script>

<style scoped>
 


:deep(.map-label) {
    background: transparent !important;
    border: none !important;
}

:deep(.map-label span) {
    background: rgba(255, 255, 255, 0.95);
    color: #1e293b;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
    text-transform: uppercase;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    border: 1px solid #cbd5e1;
    pointer-events: none;
}
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
<template>

    <div class="content container-fluid">

        <div v-if="isLoading" class="text-center py-5">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
        </div>

        <template v-else>

            <!-- Cartes stats -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-1">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-title">Chiffre d'affaires (mois)</div>
                                    <div class="dash-counts">
                                        <p>{{ formatCurrency(stats.ca_mois) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-2">
                                    <i class="fas fa-file-invoice"></i>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-title">Ventes (mois)</div>
                                    <div class="dash-counts">
                                        <p>{{ stats.nb_ventes_mois }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-3">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-title">Clients</div>
                                    <div class="dash-counts">
                                        <p>{{ stats.nb_clients }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon bg-4">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-title">Alertes de stock</div>
                                    <div class="dash-counts">
                                        <p>{{ stats.nb_alertes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row">
                <div class="col-xl-7 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">Chiffre d'affaires par mois</h5>
                        </div>
                        <div class="card-body">
                            <apexchart
                                type="area"
                                height="320"
                                :options="montantChartOptions"
                                :series="montantChartSeries"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-xl-5 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">Top 5 produits les plus vendus</h5>
                        </div>
                        <div class="card-body">
                            <apexchart
                                v-if="topProduits.length > 0"
                                type="donut"
                                height="320"
                                :options="donutChartOptions"
                                :series="donutChartSeries"
                            />
                            <div v-else class="text-center text-muted py-5">
                                Aucune vente enregistrée pour le moment
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableaux -->
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="card mb-0">
                        <div class="card-header">
                            <div class="row align-center">
                                <div class="col">
                                    <h5 class="card-title">Mouvements récents</h5>
                                </div>
                                <div class="col-auto">
                                    <RouterLink to="/mouvement" class="btn-right btn btn-sm btn-outline-primary">
                                        Voir tout
                                    </RouterLink>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-stripped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Produit</th>
                                            <th>Emplacement</th>
                                            <th>Type</th>
                                            <th>Quantité</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="mvt in mouvementsRecents" :key="mvt.id">
                                            <td>{{ mvt.product?.nom }}</td>
                                            <td class="text-capitalize">{{ mvt.emplacement?.nom }}</td>
                                            <td>
                                                <span class="badge" :class="mouvementBadgeClass(mvt.type)">
                                                    {{ mouvementLabel(mvt.type) }}
                                                </span>
                                            </td>
                                            <td>{{ mvt.quantite }}</td>
                                            <td>{{ formatDateTime(mvt.created_at) }}</td>
                                        </tr>
                                        <tr v-if="mouvementsRecents.length === 0">
                                            <td colspan="5" class="text-center text-muted py-3">Aucun mouvement récent</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="card mb-0">
                        <div class="card-header">
                            <div class="row align-center">
                                <div class="col">
                                    <h5 class="card-title">Ventes récentes</h5>
                                </div>
                                <div class="col-auto">
                                    <RouterLink to="/vente" class="btn-right btn btn-sm btn-outline-primary">
                                        Voir tout
                                    </RouterLink>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Client</th>
                                            <th>Montant</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="vente in ventesRecentes" :key="vente.id">
                                            <td>{{ vente.client?.fullname ?? vente.anonymous_customer_name }}</td>
                                            <td>{{ formatCurrency(vente.total_ttc) }}</td>
                                            <td>{{ formatDate(vente.created_at) }}</td>
                                            <td>
                                                <span class="badge" :class="statusBadgeClass(vente.status)">
                                                    {{ statusLabel(vente.status) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <RouterLink :to="'/details-vente/' + vente.id" class="action-icon">
                                                    <i class="far fa-eye"></i>
                                                </RouterLink>
                                            </td>
                                        </tr>
                                        <tr v-if="ventesRecentes.length === 0">
                                            <td colspan="5" class="text-center text-muted py-3">Aucune vente récente</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </template>

    </div>

</template>
<script setup>

    import { ref, computed, onMounted } from 'vue'
    import { getData } from '../../plugins/api'
    import VueApexCharts from 'vue3-apexcharts'

    // Enregistrement local du composant (pas besoin de le déclarer globalement dans main.js)
    const apexchart = VueApexCharts

    const isLoading = ref(true)

    const stats               = ref({ ca_mois: 0, nb_ventes_mois: 0, nb_clients: 0, nb_alertes: 0 })
    const chartMontants       = ref([])
    const topProduits         = ref([])
    const mouvementsRecents   = ref([])
    const ventesRecentes      = ref([])

    // ─── Chargement ───────────────────────────────────────────────

    async function loadDashboard() {
        isLoading.value = true
        await getData('/dashboard').then(res => {
            if (res.status === 200) {
                stats.value             = res.data.stats
                chartMontants.value     = res.data.chart_montants
                topProduits.value       = res.data.top_produits
                mouvementsRecents.value = res.data.mouvements_recents
                ventesRecentes.value    = res.data.ventes_recentes
            }
        }).finally(() => {
            isLoading.value = false
        })
    }

    // ─── Graphique : montant par mois ────────────────────────────

    const montantChartSeries = computed(() => [{
        name: "Chiffre d'affaires",
        data: chartMontants.value.map(item => item.montant),
    }])

    const montantChartOptions = computed(() => ({
        chart: { toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#002D5D'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] },
        },
        xaxis: { categories: chartMontants.value.map(item => item.mois) },
        yaxis: {
            labels: {
                formatter: (val) => new Intl.NumberFormat('fr-FR').format(val),
            },
        },
        tooltip: {
            y: { formatter: (val) => formatCurrency(val) },
        },
    }))

    // ─── Graphique : top produits (donut) ────────────────────────

    const donutChartSeries = computed(() => topProduits.value.map(p => p.total_vendu))

    const donutChartOptions = computed(() => ({
        labels: topProduits.value.map(p => p.nom),
        colors: ['#002D5D', '#28a745', '#ffc107', '#17a2b8', '#dc3545'],
        legend: { position: 'bottom' },
        dataLabels: {
            formatter: (val, opts) => opts.w.config.series[opts.seriesIndex],
        },
        tooltip: {
            y: { formatter: (val) => val + ' unité(s) vendue(s)' },
        },
    }))

    // ─── Helpers d'affichage ──────────────────────────────────────

    function formatCurrency(value) {
        return new Intl.NumberFormat('fr-BJ', { style: 'currency', currency: 'XOF' }).format(value || 0)
    }

    function formatDate(value) {
        if (!value) return '—'
        return new Intl.DateTimeFormat('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(value))
    }

    function formatDateTime(value) {
        if (!value) return '—'
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        }).format(new Date(value))
    }

    function mouvementLabel(type) {
        const labels = { entree: 'Entrée', sortie: 'Sortie', transfert: 'Transfert' }
        return labels[type] ?? type
    }

    function mouvementBadgeClass(type) {
        const classes = { entree: 'bg-success-light', sortie: 'bg-danger-light', transfert: 'bg-info-light text-info' }
        return classes[type] ?? 'bg-secondary'
    }

    function statusLabel(status) {
        const labels = { draft: 'Brouillon', sent: 'Envoyée', paid: 'Payée', overdue: 'En retard', cancelled: 'Annulée' }
        return labels[status] ?? status
    }

    function statusBadgeClass(status) {
        const classes = {
            draft:     'bg-secondary',
            sent:      'bg-info-light text-info',
            paid:      'bg-success-light',
            overdue:   'bg-warning-light text-warning',
            cancelled: 'bg-danger-light',
        }
        return classes[status] ?? 'bg-secondary'
    }

    onMounted(() => {
        loadDashboard()
    })

</script>
<style lang="">

</style>

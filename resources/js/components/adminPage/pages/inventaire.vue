<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Inventaire — {{ emplacementLabel }}</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <div class="btn-group" role="group">
                                <button
                                    type="button"
                                    class="btn"
                                    :class="filters.emplacement === 'magasin' ? 'btn-primary' : 'btn-outline-primary'"
                                    @click="changeEmplacement('magasin')"
                                >Magasin</button>
                                <button
                                    type="button"
                                    class="btn"
                                    :class="filters.emplacement === 'boutique' ? 'btn-primary' : 'btn-outline-primary'"
                                    @click="changeEmplacement('boutique')"
                                >Boutique</button>
                            </div>
                        </li>
                        <li>
                            <input
                                type="date"
                                class="form-control"
                                v-model="filters.date"
                                :max="today"
                                @change="AllInventaireFunction"
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Résumé -->
        <div class="row mb-3" v-if="!isLoading">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="text-muted d-block">Produits</span>
                        <h4 class="mb-0">{{ inventaireData.length }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="text-muted d-block">Total entrées</span>
                        <h4 class="mb-0 text-success">+{{ totalEntrees }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="text-muted d-block">Total sorties</span>
                        <h4 class="mb-0 text-danger">-{{ totalSorties }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="text-muted d-block">Stock global (fin)</span>
                        <h4 class="mb-0">{{ totalStockFin }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card-table">
                    <div class="card-body">
                        <div v-if="isLoading" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                        </div>
                        <div v-else class="table-responsive">
                            <DataTable :data="inventaireData" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script setup>

    import DataTable from '../DataTable/Datatable.vue';
    import { onMounted, ref, computed } from 'vue';
    import { getData } from '../../plugins/api'

    const filters = ref({
        emplacement: 'magasin',
        date: new Date().toISOString().split('T')[0], // aujourd'hui par défaut
    })

    const today = new Date().toISOString().split('T')[0]
    const inventaireData = ref([])
    const isLoading = ref(false)

    const emplacementLabel = computed(() => filters.value.emplacement === 'magasin' ? 'Magasin' : 'Boutique')

    const totalEntrees = computed(() =>
        inventaireData.value.reduce((sum, item) => sum + Number(item.quantite_entree), 0)
    )
    const totalSorties = computed(() =>
        inventaireData.value.reduce((sum, item) => sum + Number(item.quantite_sortie), 0)
    )
    const totalStockFin = computed(() =>
        inventaireData.value.reduce((sum, item) => sum + Number(item.stock_fin), 0)
    )

    function changeEmplacement(emplacement) {
        filters.value.emplacement = emplacement
        AllInventaireFunction()
    }

    async function AllInventaireFunction() {
        isLoading.value = true
        await getData('/inventaire', {
            params: {
                emplacement: filters.value.emplacement,
                date: filters.value.date,
            }
        }).then(res => {
            if (res.status === 200) {
                inventaireData.value = res.data.inventaire
            }
        }).finally(() => {
            isLoading.value = false
        })
    }

    const columns = ref([
        {
            title: '#',
            data: null,
            render: (data, type, row, meta) => meta.row + 1
        },
        {
            title: 'Code barre',
            data: 'code_barre',
            render: (data, type, row) => `#${row.code_barre}`
        },
        {
            title: 'Désignation',
            data: 'nom',
            render: (data, type, row) => `<span class="fw-bold">${row.nom}</span>`
        },
        {
            title: 'Stock début',
            data: 'stock_debut',
            render: (data, type, row) => `<span>${row.stock_debut}</span>`
        },
        {
            title: 'Entrées',
            data: 'quantite_entree',
            render: (data, type, row) => {
                if (row.quantite_entree <= 0) return '<span class="text-muted">—</span>'
                return `<span class="text-success fw-bold">+${row.quantite_entree}</span>`
            }
        },
        {
            title: 'Sorties',
            data: 'quantite_sortie',
            render: (data, type, row) => {
                if (row.quantite_sortie <= 0) return '<span class="text-muted">—</span>'
                return `<span class="text-danger fw-bold">-${row.quantite_sortie}</span>`
            }
        },
        {
            title: 'Stock fin',
            data: 'stock_fin',
            render: (data, type, row) => {
                const alerte = row.stock_fin <= row.seuil_alerte
                return `<span class="badge ${alerte ? 'bg-danger' : 'bg-success'}">${row.stock_fin}</span>`
            }
        },
        {
            title: "Seuil d'alerte",
            data: 'seuil_alerte',
        },
    ])

    onMounted(()=>{
        AllInventaireFunction()
    })

</script>
<style>

</style>

<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des Approvisionnements</h5>
                <div class="list-btn" v-if="can('create', 'approvisionnement')">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Créer un approvisionnement</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <DataTable :data="allAppros" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Modal Nouvel Approvisionnement ───────────────────────────────── -->
        <div class="modal fade" id="approModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <form class="modal-content" @submit.prevent="AddApproFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">
                            <i class="ri-truck-line me-2"></i>Nouvel Approvisionnement
                        </h1>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- Fournisseur -->
                            <div class="col-lg-6">
                                <label class="form-label fw-medium">Fournisseur <span class="text-danger">*</span></label>
                                <select
                                    v-model="data.fournisseur_id"
                                    :class="isEmpty.fournisseur_id ? 'is-invalid' : ''"
                                    class="form-select"
                                >
                                    <option value="" disabled>-- Choisir un fournisseur --</option>
                                    <option v-for="f in fournisseurs" :key="f.id" :value="f.id">
                                        {{ f.nom }}
                                    </option>
                                </select>
                                <div v-if="isEmpty.fournisseur_id" class="invalid-feedback">
                                    Veuillez choisir un fournisseur.
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="col-lg-6">
                                <label class="form-label fw-medium">Date <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    v-model="data.date_approvisionnement"
                                    :class="isEmpty.date_approvisionnement ? 'is-invalid' : ''"
                                    class="form-control"
                                >
                                <div v-if="isEmpty.date_approvisionnement" class="invalid-feedback">
                                    La date est requise.
                                </div>
                            </div>

                            <!-- Articles -->
                            <div class="col-12">
                                <hr class="my-1">
                                <p class="fw-semibold mb-2" style="color:#002D5D;">
                                    <i class="ri-list-check me-1"></i>Articles à approvisionner
                                </p>

                                <!-- Ligne ajout -->
                                <div class="row g-2 align-items-end mb-4">
                                    <div class="col-lg-5">
                                        <label class="form-label small">Produit</label>
                                        <select v-model="newItem.product_id" class="form-select form-select-sm"
                                            @change="onProductSelect">
                                            <option value="" disabled>-- Choisir --</option>
                                            <option v-for="p in products" :key="p.id" :value="p.id">
                                                {{ p.nom }} (stock actuel: {{ p.stocks[0].quantite }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label small">Qté</label>
                                        <input type="number" v-model="newItem.quantite"
                                            class="form-control form-control-sm" min="1" placeholder="1">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label small">Prix unitaire d'achat</label>
                                        <input type="number" v-model="newItem.prix_unitaire"
                                            class="form-control form-control-sm" min="0" step="0.01" placeholder="0">
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-success btn-sm w-100" @click="addItem">
                                            <i class="ri-add-line"></i> Ajouter
                                        </button>
                                    </div>
                                </div>

                                <!-- Erreur item -->
                                <div v-if="itemError" class="alert alert-danger py-1 px-2 small mb-2">
                                    {{ itemError }}
                                </div>

                                <!-- Tableau articles -->
                                <div v-if="data.items.length > 0" class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead style="background:#f0f4ff;">
                                            <tr>
                                                <th>#</th>
                                                <th>Produit</th>
                                                <th>Stock actuel</th>
                                                <th>Qté commandée</th>
                                                <th>Prix unit.</th>
                                                <th>Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in data.items" :key="index">
                                                <td>{{ index + 1 }}</td>
                                                <td class="fw-medium">{{ item.product_nom }}</td>
                                                <td>
                                                    <span class="badge"
                                                        :style="item.stock_actuel === 0
                                                            ? 'background:#f8d7da;color:#dc3545;'
                                                            : item.stock_actuel <= item.seuil_alerte
                                                                ? 'background:#fff3cd;color:#856404;'
                                                                : 'background:#d4edda;color:#28a745;'">
                                                        {{ item.stock_actuel }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number" v-model="item.quantite"
                                                        class="form-control form-control-sm"
                                                        style="width:80px;" min="1"
                                                        @input="updateItemTotal(item)">
                                                </td>
                                                <td>
                                                    <input type="number" v-model="item.prix_unitaire"
                                                        class="form-control form-control-sm"
                                                        style="width:110px;" min="0" step="0.01"
                                                        @input="updateItemTotal(item)">
                                                </td>
                                                <td class="fw-bold" style="color:#002D5D;">
                                                    {{ formatCurrency(item.prix_total) }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        @click="removeItem(index)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Vide -->
                                <div v-else class="text-center text-muted py-3 border rounded"
                                    style="background:#f8f9fa;">
                                    <iconify-icon icon="solar:box-bold" style="font-size:32px;opacity:.4;"></iconify-icon>
                                    <p class="mb-0 small mt-1">Aucun article ajouté</p>
                                </div>

                                <div v-if="isEmpty.items" class="text-danger small mt-1">
                                    Veuillez ajouter au moins un article.
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="col-12" v-if="data.items.length > 0">
                                <div class="card border-0 text-end pe-3 py-2" style="background:#f0f4ff;">
                                    <span class="text-muted small me-3">Montant total de la commande:</span>
                                    <span class="fw-bold fs-4" style="color:#002D5D;">
                                        {{ formatCurrency(montantTotal) }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"
                            :disabled="isLoader || data.items.length === 0">
                            <span v-if="!isLoader">
                                <i class="ri-save-line me-1"></i>Enregistrer la commande
                            </span>
                            <div v-else class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ─── Modal Détail ──────────────────────────────────────────────────── -->
        <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header" >
                        <h5 class="modal-title ">
                            <i class="ri-file-text-line me-2"></i>
                            Détail — {{ detailAppro?.reference }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" v-if="detailAppro">

                        <!-- Infos -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-4">
                                <span class="text-muted small">Référence</span>
                                <p class="fw-bold mb-0">{{ detailAppro.reference }}</p>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small">Fournisseur</span>
                                <p class="fw-bold mb-0">{{ detailAppro.fournisseur?.nom }}</p>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small">Statut</span>
                                <p class="mb-0">
                                    <span class="badge"
                                        :style="detailAppro.status === 'livrer'
                                            ? 'background:#d4edda;color:#28a745;'
                                            : detailAppro.status === 'brouillon'
                                            ? 'background:#e2e3e5;color:#383d41;'
                                            : 'background:#fff3cd;color:#856404;'">
                                        {{ detailAppro.status === 'livrer' ? '✓ Livré' : detailAppro.status === 'brouillon' ? '📝 Brouillon' : '⏳ En attente' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small">Date</span>
                                <p class="fw-bold mb-0">
                                    {{ new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' })
                                        .format(new Date(detailAppro.date_approvisionnement)) }}
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small">Opérateur</span>
                                <p class="fw-bold mb-0">{{ detailAppro.user?.full_name }}</p>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small">Montant total</span>
                                <p class="fw-bold mb-0" style="color:#002D5D;">
                                    {{ formatCurrency(detailAppro.montant_total) }}
                                </p>
                            </div>
                        </div>

                        <!-- Articles -->
                        <table class="table table-sm table-bordered mb-3">
                            <thead style="background:#f0f4ff;">
                                <tr>
                                    <th>#</th>
                                    <th>Produit</th>
                                    <th>Qté</th>
                                    <th>Prix unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, i) in detailAppro.items" :key="i">
                                    <td>{{ i + 1 }}</td>
                                    <td class="fw-medium">{{ item.product?.nom }}</td>
                                    <td>{{ item.quantite }}</td>
                                    <td>{{ formatCurrency(item.prix_unitaire) }}</td>
                                    <td class="fw-bold">{{ formatCurrency(item.prix_total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Montant total</td>
                                    <td class="fw-bold" style="color:#002D5D;">
                                        {{ formatCurrency(detailAppro.montant_total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Bouton Marquer comme livré -->
                        <div v-if="detailAppro.status === 'enAttente'"
                            class="alert d-flex align-items-center justify-content-between gap-2 mb-0"
                            style="background:#fff3cd;border-color:#ffc107;">
                            <div>
                                <iconify-icon icon="solar:clock-circle-bold" style="color:#856404;"></iconify-icon>
                                <span class="ms-2 text-warning-emphasis">
                                    Cette commande est en attente de livraison.
                                </span>
                            </div>
                            <button type="button" class="btn btn-success btn-sm"
                                :disabled="isLivraison"
                                @click="MarquerLivre(detailAppro.id)">
                                <span v-if="!isLivraison">
                                    <i class="ri-check-line me-1"></i>Marquer comme livré
                                </span>
                                <div v-else class="spinner-border spinner-border-sm" role="status"></div>
                            </button>
                        </div>

                        <div v-if="detailAppro.status === 'livrer'" class="alert mb-0" style="background:#d4edda;color:#28a745;">
                            <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                            <span class="ms-2">
                                Cet approvisionnement a été livré — le stock a été mis à jour.
                            </span>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script setup>

    import Swal from 'sweetalert2';
    import DataTable from '../DataTable/Datatable.vue';
    import { onMounted, ref,computed } from 'vue';
    import {postData, getData, getSingleData, putData, deleteData} from '../../plugins/api'
    import { can } from '../../plugins/permissions'

    let addmodal;
    let detailModal;

    // ─── State ───────────────────────────────────────────────────────────────────

    const data = ref({
        fournisseur_id:         '',
        date_approvisionnement: new Date().toISOString().split('T')[0],
        items: [],
    })

    const newItem      = ref({ product_id: '', quantite: 1, prix_unitaire: '' })
    const isEmpty      = ref({})
    const itemError    = ref('')
    const isLoader     = ref(false)
    const isLivraison  = ref(false)
    const allAppros    = ref([])
    const products     = ref([])
    const fournisseurs = ref([])
    const filterStatus = ref('')
    const detailAppro  = ref(null)

    // ─── Computed ────────────────────────────────────────────────────────────────

    const montantTotal = computed(() =>
        data.value.items.reduce((sum, i) => sum + Number(i.prix_total), 0)
    )

    const filteredAppros = computed(() => {
        if (!filterStatus.value) return allAppros.value
        return allAppros.value.filter(a => a.status === filterStatus.value)
    })

    const stats = computed(() => ({
        total:     allAppros.value.length,
        enAttente: allAppros.value.filter(a => a.status === 'brouillon').length,
        enAttente: allAppros.value.filter(a => a.status === 'enAttente').length,
        livres:    allAppros.value.filter(a => a.status === 'livrer').length,
    }))

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    function formatCurrency(val) {
        return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(val || 0)
    }

    // ─── Gestion des items ────────────────────────────────────────────────────────

    function onProductSelect() {
        const p = products.value.find(p => p.id === newItem.value.product_id)
        if (p) newItem.value.prix_unitaire = p.prix_achat // prix d'achat par défaut
    }

    function addItem() {
        itemError.value = ''
        const { product_id, quantite, prix_unitaire } = newItem.value
        const product = products.value.find(p => p.id === product_id)

        if (!product_id || !product)  return itemError.value = 'Veuillez choisir un produit.'
        if (!quantite || quantite < 1) return itemError.value = 'Quantité invalide (min: 1).'
        if (prix_unitaire === '' || prix_unitaire < 0) return itemError.value = 'Prix invalide.'

        // Si produit déjà présent → cumul
        const existing = data.value.items.find(i => i.product_id === product_id)
        if (existing) {
            existing.quantite   = Number(existing.quantite) + Number(quantite)
            existing.prix_total = existing.quantite * existing.prix_unitaire
        } else {
            data.value.items.push({
                product_id,
                product_nom:   product.nom,
                stock_actuel:  product.quantite,
                seuil_alerte:  product.seuil_alerte,
                quantite:      Number(quantite),
                prix_unitaire: Number(prix_unitaire),
                prix_total:    Number(quantite) * Number(prix_unitaire),
            })
        }

        newItem.value = { product_id: '', quantite: 1, prix_unitaire: '' }
    }

    function removeItem(index) {
        data.value.items.splice(index, 1)
    }

    function updateItemTotal(item) {
        item.prix_total = Number(item.quantite) * Number(item.prix_unitaire)
    }

    // ─── Modal ───────────────────────────────────────────────────────────────────

    function showModal() {
        data.value = {
            fournisseur_id:         '',
            date_approvisionnement: new Date().toISOString().split('T')[0],
            items: [],
        }
        newItem.value = { product_id: '', quantite: 1, prix_unitaire: '' }
        isEmpty.value  = {}
        itemError.value = ''
        addmodal.show()
    }

    // ─── Colonnes DataTable ───────────────────────────────────────────────────────

    const columns = ref([
        {
            title: '#', data: null,
            render: (data, type, row, meta) => meta.row + 1
        },
        {
            title: 'Référence', data: 'reference',
            render: (data, type, row) =>
                `<span class="fw-medium" style="color:#002D5D;">${row.reference}</span>`
        },
        {
            title: 'Fournisseur', data: 'fournisseur',
            render: (data, type, row) =>
                `<span class="fw-medium">${row.fournisseur?.nom || '-'}</span>`
        },
        {
            title: 'Articles', data: 'items',
            render: (data, type, row) => {
                const count = row.items?.length || 0
                return `<span class="badge bg-light text-dark border">${count} article(s)</span>`
            }
        },
        {
            title: 'Montant total', data: 'montant_total',
            render: (data, type, row) =>
                `<span class="fw-bold" style="color:#002D5D;">
                    ${new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(row.montant_total)}
                </span>`
        },
        {
            title: 'Statut',
            data: 'status',
            render: (data) => {
                const statuts = {
                    'livrer':    `<span class="badge" style="background:#d4edda;color:#28a745;">✓ Livré</span>`,
                    'enAttente': `<span class="badge" style="background:#fff3cd;color:#856404;">⏳ En attente</span>`,
                    'brouillon': `<span class="badge" style="background:#e2e3e5;color:#383d41;">📝 Brouillon</span>`,
                }
                return statuts[data] ?? `<span class="badge bg-secondary">—</span>`
            }
        },
        {
            title: 'Opérateur', data: 'user',
            render: (data, type, row) =>
                `<span class="badge bg-light text-dark border">${row.user?.full_name || '-'}</span>`
        },
        {
            title: 'Date', data: 'date_approvisionnement',
            render: (data, type, row) => new Intl.DateTimeFormat('fr-FR', {
                year: 'numeric', month: 'short', day: 'numeric'
            }).format(new Date(row.date_approvisionnement))
        },
        {
            title: 'Action', data: null,
            render: (data, type, row) => {

                const peutVoir = can('read', 'approvisionnement')
                const peutModifier = can('update', 'approvisionnement')
                const peutSupprimer = can('delete', 'approvisionnement')

                if (!peutModifier && !peutSupprimer && !peutVoir) {
                    return ''
                }

                return `
                    ${peutVoir ? `
                        <button class="btn btn-info btn-sm me-1"
                            onclick="ShowDetailAppro(${row.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    ` : ``}

                    ${peutModifier ? `

                        ${row.status === 'brouillon' ? `
                            <button class="btn btn-primary btn-sm me-1"
                                onclick="EnAttenteApproFunction(${row.id}, '${row.fournisseur?.email ?? ''}')">
                                <i class="fas fa-clock"></i>
                            </button>
                        `:``}
                        ${row.status === 'enAttente' ? `
                        <button class="btn btn-success btn-sm me-1"
                            onclick="LivrerApproFunction(${row.id})">
                            <i class="fas fa-check"></i>
                        </button>` : ''}

                    ` : ``}

                    ${peutSupprimer ? `

                        <button class="btn btn-danger btn-sm"
                            onclick="DeleteApproFunction(${row.id})">
                            <i class="fas fa-trash"></i>
                        </button>

                    ` : ``}

                `
            }
        }
    ])

    // ─── API calls ───────────────────────────────────────────────────────────────

    async function AllApproFunction() {
        await getData('/aprovisionnements',{
            params: {
                emplacement: 'boutique'
            }
        }).then(res => {
            if (res.status === 200) allAppros.value = res.data
        })
    }

    async function AllProductsFunction() {
        await getData('/products').then(res => {
            if (res.status === 200) products.value = res.data
        })
    }

    async function AllFournisseursFunction() {
        await getData('/fournisseurs').then(res => {
            if (res.status === 200) fournisseurs.value = res.data
        })
    }

    async function AddApproFunction() {
        isEmpty.value = {}

        if (!data.value.fournisseur_id)         isEmpty.value.fournisseur_id         = true
        if (!data.value.date_approvisionnement)  isEmpty.value.date_approvisionnement = true
        if (data.value.items.length === 0)       isEmpty.value.items                  = true

        const hasError = Object.values(isEmpty.value).some(v => v === true)
        if (hasError) return

        isLoader.value = true

        const payload = {
            fournisseur_id:         data.value.fournisseur_id,
            date_approvisionnement: data.value.date_approvisionnement,
            items: data.value.items.map(i => ({
                product_id:    i.product_id,
                quantite:      i.quantite,
                prix_unitaire: i.prix_unitaire,
            }))
        }

        await postData('/aprovisionnements', payload,{
            params: {
                emplacement: 'boutique'
            }
        }).then(res => {
            if (res.status === 201 || res.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Commande enregistrée !',
                    html: `Référence : <strong>${res.data.reference}</strong><br>
                           Total : <strong>${formatCurrency(res.data.montant_total)}</strong><br>
                           <span class="text-warning">⏳ En attente de livraison</span>`,
                    showConfirmButton: false,
                    timer: 2500
                })
                AllApproFunction()
                addmodal.hide()
            }
        }).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: err.response?.data?.message || 'Une erreur est survenue.',
            })
        })

        isLoader.value = false
    }

    // Marquer livré depuis le modal détail
    async function MarquerLivre(id) {
        isLivraison.value = true
        await postData(`/aprovisionnement/${id}/livrer`,null).then(res => {
            if (res.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Livraison confirmée !',
                    text: 'Le stock a été mis à jour automatiquement.',
                    showConfirmButton: false,
                    timer: 1800
                })
                AllApproFunction()
                AllProductsFunction()
                detailModal.hide()
            }
        }).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: err.response?.data?.message || 'Une erreur est survenue.',
            })
        })
        isLivraison.value = false
    }

    window.EnAttenteApproFunction = async function (id, fournisseurEmail) {

        // Construire le contenu du Swal selon si le fournisseur a un email
        const htmlContent = fournisseurEmail
            ? `
                <p>L'approvisionnement sera mis en attente.</p>
                <div class="form-check mt-3 text-start">
                    <input type="checkbox" class="form-check-input" id="sendEmailCheck">
                    <label class="form-check-label" for="sendEmailCheck">
                        Envoyer le bon de commande au fournisseur
                        <br><small class="text-muted">${fournisseurEmail}</small>
                    </label>
                </div>
            `
            : `<p>L'approvisionnement sera mis en attente.</p>`

        const result = await Swal.fire({
            title: 'Mettre en attente ?',
            html: htmlContent,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#002D5D',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmer',
            cancelButtonText: 'Annuler',
        })

        if (!result.isConfirmed) return

        // Récupérer l'état du checkbox après confirmation
        const sendEmail = fournisseurEmail
            ? document.getElementById('sendEmailCheck')?.checked ?? false
            : false

        Swal.fire({
            title: 'Envoi en cours...',
            text: 'Génération et envoi par email.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        })

        await putData(`/aprovisionnement/${id}/enAttente`, { send_email: sendEmail })
            .then(res => {
                if (res.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        text: res.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    AllApproFunction()
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: err.response?.data?.message || 'Une erreur est survenue.',
                })
            })
    }

    // Marquer livré depuis le tableau
    window.LivrerApproFunction = async function (id) {
        Swal.fire({
            title: 'Confirmer la livraison ?',
            text: 'Le stock sera mis à jour automatiquement.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, livré !',
            cancelButtonText: 'Annuler'
        }).then(async result => {
            if (result.isConfirmed) {
                await postData(`/aprovisionnement/${id}/livrer`,null).then(res => {
                    if (res.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            text: 'Livraison confirmée. Stock mis à jour.',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        AllApproFunction()
                        AllProductsFunction()
                    }
                }).catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: err.response?.data?.message || 'Une erreur est survenue.',
                    })
                })
            }
        })
    }

    window.ShowDetailAppro = async function (id) {
        console.log('ok')
        await getSingleData('/aprovisionnements/' + id).then(res => {
            if (res.status === 200) {
                detailAppro.value = res.data
                detailModal.show()
            }
        })
    }

    window.DeleteApproFunction = async function (id) {
        Swal.fire({
            title: 'Supprimer cette commande ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#6c757d',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async result => {
            if (result.isConfirmed) {
                await deleteData('/aprovisionnements/' + id).then(res => {
                    if (res.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            text: 'Commande supprimée.',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        AllApproFunction()
                    }
                }).catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: err.response?.data?.message || 'Une erreur est survenue.',
                    })
                })
            }
        })
    }

    // ─── Lifecycle ───────────────────────────────────────────────────────────────

    onMounted(() => {
        addmodal    = new bootstrap.Modal(document.getElementById('approModal'))
        detailModal = new bootstrap.Modal(document.getElementById('detailModal'))
        AllApproFunction()
        AllProductsFunction()
        AllFournisseursFunction()
    })

</script>
<style >

</style>

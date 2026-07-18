<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5><RouterLink to="/vente" class="btn btn-secondary me-2"><i class="fe fe-arrow-left"></i></RouterLink> {{ isEdite ? 'Modifier la facture' : 'Créer une nouvelle vente' }}</h5>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card-table">
                    <div class="card-body">
                        <form @submit.prevent="submitForm">

                            <!-- Section Informations générales -->
                            <div class="section-title mb-4">
                                <h6 class="mb-3">Informations générales</h6>
                            </div>

                            <!-- Client -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="client" class="form-label">Client</label>
                                    <select
                                        id="client"
                                        v-model="formData.client_id"
                                        class="form-control"
                                        :class="{ 'is-invalid': isEmpty.client_id }"
                                    >
                                        <option value="">-- Client anonyme --</option>
                                        <option
                                            v-for="client in allClients"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.fullname }} ({{ client.phone }})
                                        </option>
                                    </select>
                                    <div v-if="isEmpty.client_id" class="invalid-feedback d-block">
                                        {{ msgInput.client_id }}
                                    </div>
                                </div>


                            </div>

                            <!-- Dates -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="due_at" class="form-label">Date de facturation *</label>
                                    <input
                                        id="due_at"
                                        v-model="formData.due_at"
                                        type="date"
                                        class="form-control"
                                        :class="{ 'is-invalid': isEmpty.due_at }"
                                    />
                                    <div v-if="isEmpty.due_at" class="invalid-feedback d-block">
                                        {{ msgInput.due_at }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="echeance_at" class="form-label">Date d'échéance *</label>
                                    <input
                                        id="echeance_at"
                                        v-model="formData.echeance_at"
                                        type="date"
                                        class="form-control"
                                        :class="{ 'is-invalid': isEmpty.echeance_at }"
                                    />
                                    <div v-if="isEmpty.echeance_at" class="invalid-feedback d-block">
                                        {{ msgInput.echeance_at }}
                                    </div>
                                </div>
                            </div>

                            <!-- Taxe -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input
                                            id="is_taxable"
                                            v-model="formData.is_taxable"
                                            type="checkbox"
                                            class="form-check-input"
                                        />
                                        <label for="is_taxable" class="form-check-label">
                                            Appliquer la TVA (18%)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Articles -->
                            <div class="section-title mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Articles</h6>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        @click="addItem"
                                    >
                                        <i class="fa fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                            </div>

                            <!-- Articles List -->
                            <div v-if="formData.items.length > 0" class="mb-4">
                                <div class="table">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produit</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Total</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in formData.items" :key="index">
                                                <td>
                                                    <SearchableSelect
                                                        v-model="item.product_id"
                                                        :options="allProducts"
                                                        option-value="id"
                                                        option-label="nom"
                                                        placeholder="-- Sélectionner --"
                                                        :invalid="isEmpty[`product_${index}`]"
                                                        @change="onProductSelected(index)"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model.number="item.quantity"
                                                        type="number"
                                                        min="1"
                                                        class="form-control form-control-sm"
                                                        :class="{ 'is-invalid': isEmpty[`quantity_${index}`] }"
                                                    />
                                                </td>
                                                <td>
                                                    <input
                                                        v-model.number="item.unit_price"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        class="form-control form-control-sm"
                                                        :class="{ 'is-invalid': isEmpty[`unit_price_${index}`] }"
                                                    />
                                                </td>
                                                <td class="text-center">
                                                    {{ formatCurrency(item.quantity * item.unit_price) }}
                                                </td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger"
                                                        @click="removeItem(index)"
                                                    >
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="isEmpty.items" class="alert alert-danger">
                                    {{ msgInput.items }}
                                </div>
                            </div>

                            <div v-else class="alert alert-info mb-4">
                                Aucun article ajouté. Cliquez sur "Ajouter un article" pour en ajouter.
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Résumé -->
            <div class="col-lg-4">
                <div class="card-table sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h6 class="mb-4">Résumé de la facture</h6>

                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total HT:</span>
                                <strong>{{ formatCurrency(totalHT) }}</strong>
                            </div>
                            <div v-if="formData.is_taxable" class="d-flex justify-content-between mb-2">
                                <span>TVA (18%):</span>
                                <strong>{{ formatCurrency(totalTVA) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fs-6">Total TTC:</span>
                                <strong class="fs-6 text-primary">{{ formatCurrency(totalTTC) }}</strong>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p v-if="selectedClient" class="mb-2">
                                <small><strong>Client:</strong> {{ selectedClient.nom }}</small>
                            </p>
                            <p v-if="selectedEmplacement" class="mb-2">
                                <small><strong>Emplacement:</strong> {{ selectedEmplacement.nom }}</small>
                            </p>
                            <p class="mb-2">
                                <small><strong>Articles:</strong> {{ formData.items.length }}</small>
                            </p>
                        </div>

                        <div class="d-grid gap-2">
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="submitForm"
                                :disabled="isLoader || formData.items.length === 0"
                            >
                                <span v-if="!isLoader">
                                    <i class="fa fa-check-circle me-2"></i>{{ isEdite ? 'Modifier la vente' : 'Enregistrer la vente' }}
                                </span>
                                <span v-else>
                                    <i class="fa fa-spinner fa-spin me-2"></i>Enregistrement...
                                </span>
                            </button>
                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="resetForm"
                            >
                                <i class="fa fa-redo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import Swal from 'sweetalert2';
    import { postData, getData, getSingleData, putData } from '../../plugins/api';
    import SearchableSelect from '../selectSeach/SearchableSelect.vue'
    import { useRouter, useRoute  } from 'vue-router';

    const isLoader = ref(false);
    const isEmpty = ref({});
    const msgInput = ref({});
    const router = useRouter()
    const route = useRoute();

    const formData = ref({
        client_id: '',
        emplacement_id: '1',
        due_at: new Date().toISOString().split('T')[0],
        echeance_at: '',
        is_taxable: false,
        items: []
    });

    const allClients = ref([]);
    const allEmplacements = ref([]);
    const allProducts = ref([]);
    const titlePage = ref('')

    // Mode édition si un id est présent dans l'URL
    const isEdite = computed(() => !!route.params.id);
    const invoiceId = computed(() => route.params.id);

    // Computed properties
    const selectedClient = computed(() => {
        return allClients.value.find(c => c.id == formData.value.client_id);
    });

    const selectedEmplacement = computed(() => {
        return allEmplacements.value.find(e => e.id == formData.value.emplacement_id);
    });

    const totalHT = computed(() => {
        return formData.value.items.reduce((sum, item) => {
            return sum + (item.quantity * item.unit_price);
        }, 0);
    });

    const totalTVA = computed(() => {
        return formData.value.is_taxable ? Math.round(totalHT.value * 0.18 * 100) / 100 : 0;
    });

    const totalTTC = computed(() => {
        return Math.round((totalHT.value + totalTVA.value) * 100) / 100;
    });

    // Methods
    function addItem() {
        formData.value.items.push({
            product_id: '',
            quantity: 1,
            unit_price: 0
        });
    }

    function onProductSelected(index) {
        const item = formData.value.items[index];
        const product = allProducts.value.find(p => p.id == item.product_id);

        if (product) {
            // Remplir le prix unitaire avec le prix du produit
            item.unit_price = product.prix_unitaire || product.prix || product.unit_price || 0;
        }
    }

    function removeItem(index) {
        formData.value.items.splice(index, 1);
    }

    function resetForm() {
        formData.value = {
            client_id: '',
            emplacement_id: '1',
            due_at: new Date().toISOString().split('T')[0],
            echeance_at: '',
            is_taxable: false,
            items: []
        };
        isEmpty.value = {};
        msgInput.value = {};
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('fr-BJ', {
            style: 'currency',
            currency: 'XOF'
        }).format(value);
    }

    async function submitForm() {
        isEmpty.value = {};
        msgInput.value = {};

        if (!formData.value.due_at) {
            isEmpty.value.due_at = true;
            msgInput.value.due_at = 'Veuillez sélectionner une date de facturation';
            return;
        }
        if (!formData.value.echeance_at) {
            isEmpty.value.echeance_at = true;
            msgInput.value.echeance_at = "Veuillez sélectionner une date d'échéance";
            return;
        }
        if (formData.value.items.length === 0) {
            isEmpty.value.items = true;
            msgInput.value.items = 'Veuillez ajouter au moins un article';
            return;
        }
        for (let i = 0; i < formData.value.items.length; i++) {
            const item = formData.value.items[i];
            if (!item.product_id)  { isEmpty.value[`product_${i}`]    = true; return; }
            if (!item.quantity || item.quantity < 1) { isEmpty.value[`quantity_${i}`]   = true; return; }
            if (item.unit_price === null || item.unit_price === '' || item.unit_price < 0) { isEmpty.value[`unit_price_${i}`] = true; return; }
        }

        // ── Vérification du seuil d'alerte ───────────────────────────────────────
        const produitsSousSeuilLines = [];

        for (const item of formData.value.items) {
            if (!item.product_id) continue;

            const product = allProducts.value.find(p => p.id == item.product_id);
            if (!product) continue;

            // Le stock et le seuil sont dans stocks[0] (filtré par emplacement via /products/boutique)
            const stock        = product.stocks?.[0];
            const stockActuel  = stock?.quantite ?? 0;
            const seuilAlerte  = stock?.seuil_alerte ?? 0;
            const stockApres   = stockActuel - item.quantity;

            if (stockApres <= seuilAlerte) {
                produitsSousSeuilLines.push(`
                    <tr>
                        <td class="text-start">${product.nom}</td>
                        <td class="text-center">${stockActuel}</td>
                        <td class="text-center ${stockApres < 0 ? 'text-danger fw-bold' : 'text-warning fw-bold'}">${stockApres}</td>
                        <td class="text-center">${seuilAlerte}</td>
                    </tr>
                `);
            }
        }

        if (produitsSousSeuilLines.length > 0) {
            const result = await Swal.fire({
                icon: 'warning',
                title: '⚠️ Seuil d\'alerte atteint',
                html: `
                    Après cette vente, les produits suivants passeront sous leur seuil d'alerte :<br><br>
                    <table class="table table-sm table-bordered" style="font-size:13px;">
                        <thead style="background:#fff3cd;">
                            <tr>
                                <th class="text-start">Produit</th>
                                <th>Stock actuel</th>
                                <th>Stock après</th>
                                <th>Seuil</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${produitsSousSeuilLines.join('')}
                        </tbody>
                    </table>
                    Voulez-vous vraiment continuer ?
                `,
                showCancelButton: true,
                confirmButtonText: 'Oui, valider la vente',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#002D5D',
                cancelButtonColor: '#6c757d',
                width: '550px',
            });

            if (!result.isConfirmed) return;
        }
        // ─────────────────────────────────────────────────────────────────────────

        // Loader
        Swal.fire({
            title: isEdite.value ? 'Modification...' : 'Enregistrement...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        })

        const payload = {
            client_id:      formData.value.client_id || null,
            emplacement_id: formData.value.emplacement_id,
            due_at:         formData.value.due_at,
            echeance_at:    formData.value.echeance_at,
            is_taxable:     formData.value.is_taxable,
            items:          formData.value.items
        }

        try {
            if (isEdite.value) {
                await putData('/invoices/' + invoiceId.value, payload)
            } else {
                await postData('/invoices', payload)
            }

            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: isEdite.value ? 'Facture modifiée avec succès' : 'Facture créée avec succès',
                confirmButtonText: 'OK'
            }).then(() => {
                router.push('/vente')
            })

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: error.response?.data?.message || 'Une erreur est survenue',
                confirmButtonText: 'OK'
            })
        }
    }

    onMounted(async () => {
        try {
            // Charger les données
            const [clients, products] = await Promise.all([
                getData('/clients'),
                getData('/products/boutique')
            ]);

            allClients.value = clients.data;
            allProducts.value = products.data;

            // Définir la date d'échéance par défaut à 30 jours
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 30);
            formData.value.echeance_at = dueDate.toISOString().split('T')[0];

            // Si mode édition, charger la facture existante
            if (isEdite.value) {

                Swal.fire({
                    title: 'Chargement...',
                    text: 'Veuillez patienter',
                    allowOutsideClick: false,   // empêche de fermer en cliquant dehors
                    allowEscapeKey: false,       // empêche de fermer avec Echap
                    showConfirmButton: false,    // cache le bouton OK
                    didOpen: () => {
                        Swal.showLoading()       // affiche le spinner
                    }
                })

                const res = await getSingleData('/invoices/' + invoiceId.value);
                if (res.status === 200) {
                    Swal.close()
                    const invoice = res.data;
                    formData.value = {
                        client_id:      invoice.client_id ?? '',
                        emplacement_id: invoice.emplacement_id,
                        due_at:         invoice.due_at.split('T')[0],
                        echeance_at:    invoice.echeance_at.split('T')[0],
                        is_taxable:     invoice.total_tva > 0,
                        items: invoice.items.map(item => ({
                            product_id: item.product_id,
                            quantity:   item.quantity,
                            unit_price: parseFloat(item.unit_price),
                        }))
                    }
                }

            }

        } catch (error) {
            console.error('Error loading data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur lors du chargement des données',
            });
        }
    });
</script>

<style scoped>
    .section-title {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .section-title h6 {
        color: #333;
        font-weight: 600;
    }

    .sticky-top {
        top: 20px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .table-sm td {
        vertical-align: middle;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    .alert {
        border-radius: 0.25rem;
    }
</style>

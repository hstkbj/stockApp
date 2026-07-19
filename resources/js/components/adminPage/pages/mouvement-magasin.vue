<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des Mouvements du magasin</h5>
                <div class="list-btn" v-if="can('create', 'mouvement-magasin')">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Créer un mouvement</button>
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
                            <DataTable :data="allMouvement" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-top fade" id="mouvementModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddMouvementBoutique() : UpdateMouvementFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <!-- Produit -->
                            <div class="col-lg-6 mb-3">
                                <label>Sélectionnez un produit <span class="text-danger">*</span></label>
                                <select class="form-select" :class="{'is-invalid': isEmpty.product_id}" v-model="data.product_id">
                                    <option value="">-- Choisir un produit --</option>
                                    <option v-for="product in allProduct" :key="product.id" :value="product.id">
                                        {{ product.nom }}
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="isEmpty.product_id">{{ msgInput.product_id }}</div>
                            </div>

                            <!-- Quantité -->
                            <div class="col-lg-6 mb-3">
                                <label>Quantité <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control"
                                    :class="{'is-invalid': isEmpty.quantite}"
                                    placeholder="Ex: 100"
                                    v-model="data.quantite"
                                    min="1"
                                />
                                <div class="invalid-feedback" v-if="isEmpty.quantite">{{ msgInput.quantite }}</div>
                            </div>

                            <!-- Type de mouvement -->
                            <div class="col-lg-6 mb-3">
                                <label>Type de mouvement <span class="text-danger">*</span></label>
                                <select class="form-select" :class="{'is-invalid': isEmpty.type}" v-model="data.type">
                                    <option value="">-- Choisir un type --</option>
                                    <option value="entree">Entrée</option>
                                    <option value="sortie">Sortie</option>
                                </select>
                                <div class="invalid-feedback" v-if="isEmpty.type">{{ msgInput.type }}</div>
                            </div>

                            <!-- Date -->
                            <div class="col-lg-6 mb-3">
                                <label>Date <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    disabled
                                    class="form-control"
                                    :class="{'is-invalid': isEmpty.date}"
                                    v-model="data.date"
                                />
                                <div class="invalid-feedback" v-if="isEmpty.date">{{ msgInput.date }}</div>
                            </div>

                            <!-- Motif (optionnel) -->
                            <div class="col-lg-12 mb-3">
                                <label>Motif <span class="text-muted">(optionnel)</span></label>
                                <textarea
                                    class="form-control"
                                    placeholder="Ex: Réapprovisionnement..."
                                    v-model="data.motif"
                                    rows="2"
                                ></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Fermé</button>
                        <button type="submit" class="btn btn-primary" :disabled="isLoader">
                            <span v-if="!isLoader">{{ modalbutton }}</span>
                            <span v-else>
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Détails Mouvement -->
        <div class="modal modal-top fade" id="detailMouvementModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Détails du mouvement</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" v-if="selectedMouvement">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th>Produit</th>
                                    <td>{{ selectedMouvement.product?.nom }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>
                                        <span v-if="selectedMouvement.type === 'entree'" class="badge bg-success">Entrée</span>
                                        <span v-else-if="selectedMouvement.type === 'sortie'" class="badge bg-danger">Sortie</span>
                                        <span v-else class="badge bg-warning text-dark">Transfert</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Quantité</th>
                                    <td>{{ selectedMouvement.quantite }}</td>
                                </tr>
                                <tr v-if="selectedMouvement.type === 'transfert'">
                                    <th>Destination</th>
                                    <td>{{ selectedMouvement.emplacement_destination?.nom ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Motif</th>
                                    <td>{{ selectedMouvement.motif ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Créé par</th>
                                    <td>{{ selectedMouvement.user?.full_name }}</td>
                                </tr>
                                <tr>
                                    <th>Créé le</th>
                                    <td>{{
                                        new Intl.DateTimeFormat('fr-FR', {
                                            year: 'numeric', month: 'short', day: 'numeric',
                                            hour: '2-digit', minute: '2-digit'
                                        }).format(new Date(selectedMouvement.created_at))
                                    }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script setup>

    import Swal from 'sweetalert2';
    import DataTable from '../DataTable/Datatable.vue';
    import { onMounted, ref } from 'vue';
    import {postData, getData, getSingleData, putData, deleteData} from '../../plugins/api'
    import { can } from '../../plugins/permissions'

    let addmodal;
    let detailModal;
    const selectedMouvement = ref(null)

    const today = () => new Date().toISOString().split('T')[0]

    const data = ref({
        id:'',
        product_id:'',
        emplacement_id:'2',
        emplacement_destination_id:'',
        quantite:'',
        type:'',
        date: today(),
        motif:'',
    })

    const isEmpty = ref({})
    const imagePreview = ref('')
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allProduct = ref([])
    const allMouvement = ref([])

    function showModal(){
        addmodal.show();
        data.value = {
            id:'',
            product_id:'',
            emplacement_id:'2',
            emplacement_destination_id:'',
            quantite:'',
            type:'',
            date: today(),
            motif:'',
        }
        modalTitle.value = 'Créer un mouvement'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllMouvementFunction() {
        await getData('/mouvements/filter',{
            params: {
                emplacement: 'magasin'
            }
        }).then(res=>{
            if (res.status === 200) {
                allMouvement.value = res.data
            }
        })
    }

    async function AllProductsFunction() {
        await getData('/products/magasin').then(res=>{
            if (res.status === 200) {
                allProduct.value = res.data
            }
        })
    }

    const columns = ref([
        {
            title: '#',
            data: null,
            render: function (data, type, row, meta) {
                return meta.row + 1; // Index (1-based)
            }
        },
        {
            title: 'Produits',
            data: 'product',
            render: function(data){
                return `<span class="fw-bold">${data.nom}</span>`
            }
        },
        {
            title: 'Type',
            data: 'type',
            render: function(data) {
                const types = {
                    'entree':   '<span class="badge bg-success">Entrée</span>',
                    'sortie':   '<span class="badge bg-danger">Sortie</span>',
                    'transfert':'<span class="badge bg-warning text-dark">Transfert</span>',
                }
                return types[data] ?? '<span class="badge bg-secondary">—</span>'
            }
        },
        {
            title: 'Quantité',
            data: 'quantite',
        },
        {
            title: 'Créer par',
            data: 'user',
            render: function(data){
                return `<span>${data.full_name}</span>`
            }
        },
        {
          title: 'Crée le', data: 'created_at', render: (data, type, row) => {
            // Formater la date
            const date = new Date(row.created_at); // Assure-toi que `created_at` est au format ISO ou timestamp
            return new Intl.DateTimeFormat('en-EN', {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
            }).format(date); // Formater la date à la française
          }
        },

        { title: 'Action', data: null, render: (data,type,row) => {

            const peutModifier = can('update', 'mouvement-magasin')
            const peutSupprimer = can('delete', 'mouvement-magasin')

            if (!peutModifier && !peutSupprimer) {
                return ''
            }

            return `
                ${peutModifier ? `
                    <button class="btn btn-primary me-1" href="#" onclick="ShowMouvementFunction(${row.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                ` : ``}

                ${peutSupprimer ? `
                    <button class="btn btn-danger" href="#" onclick="DeleteMouvementFunction(${row.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                ` : ``}

            `;
            }
        }
    ])

    async function AddMouvementBoutique() {
        const ignoredFields = ['id','motif','emplacement_destination_id'];
        for (const field in data.value) {
            if (ignoredFields.includes(field)) continue;
            isEmpty.value[field] = !data.value[field];
            msgInput.value[field] = `Veuillez remplir le champ ${field.replace('_', ' ')}`;
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false);

        console.log(data.value)

        if (allEmpty) {
            isLoader.value = true;
            await postData('/mouvements',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        product_id:'',
                        emplacement_id:'2',
                        emplacement_destination_id:'',
                        quantite:'',
                        type:'',
                        date:'',
                        motif:'',
                    }
                    Swal.fire({
                        icon: 'success',
                        text: 'Mouvement ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    AllMouvementFunction()
                    addmodal.hide()
                }
            })
        }
    }

    window.ShowMouvementFunction = async function(id) {
        await getSingleData('/mouvements/' + id).then(res => {
            if (res.status === 200) {
                selectedMouvement.value = res.data
                detailModal.show()
            }
        })
    }


    async function UpdateMouvementFunction(){
        isLoader.value = true;
        await putData('/mouvements/'+data.value.id,data.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                data.value = {
                    nom : '',
                    category_id:''
                }
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Modification effectuer',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllMouvementFunction()
                addmodal.hide()
            }
        })
    }

    window.DeleteMouvementFunction = async function (id) {
        Swal.fire({
            title: "Voulez-vous supprimez ce mouvement ?",
            text: "Vous ne pouvez plus revenir en arrière",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#002D5D",
            confirmButtonText: "Supprimé",
            cancelButtonText: "Fermé"
        }).then(async (result) => {
            if (result.isConfirmed) {

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

                await deleteData('/mouvements/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllMouvementFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        addmodal = new bootstrap.Modal(document.getElementById('mouvementModal'));
        detailModal = new bootstrap.Modal(document.getElementById('detailMouvementModal'))
        AllMouvementFunction()
        AllProductsFunction()
    })

</script>
<style>

</style>

<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des Produits du magasin</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouté un produits</button>
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
                            <DataTable :data="allProduct" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-top fade" id="productModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddProductFunction() : UpdateProductFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Code barre</label>
                                <input type="text" :class="isEmpty.code_barre ? 'is-invalid border border-danger' : ''" v-model="data.code_barre" class="form-control" placeholder="Code barre ..." maxlength="100">
                                <div v-if="isEmpty.code_barre" class="invalid-feedback">
                                    {{ msgInput.code_barre }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Nom Produits</label>
                                <input type="text" :class="isEmpty.nom ? 'is-invalid border border-danger' : ''" v-model="data.nom" class="form-control" placeholder="Nom du produits" maxlength="100">
                                <div v-if="isEmpty.nom" class="invalid-feedback">
                                    {{ msgInput.nom }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Prix d'achat</label>
                                <input type="text" :class="isEmpty.prix_achat ? 'is-invalid border border-danger' : ''" v-model="data.prix_achat" class="form-control" placeholder="prix d'achat (FCFA)" maxlength="100">
                                <div v-if="isEmpty.prix_achat" class="invalid-feedback">
                                    {{ msgInput.prix_achat }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Prix de vente</label>
                                <input type="text" :class="isEmpty.prix_unitaire ? 'is-invalid border border-danger' : ''" v-model="data.prix_unitaire" class="form-control" placeholder="Prix de vente (FCFA)" maxlength="100">
                                <div v-if="isEmpty.prix_unitaire" class="invalid-feedback">
                                    {{ msgInput.prix_unitaire }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Quantité</label>
                                <input type="number" :class="isEmpty.quantite ? 'is-invalid border border-danger' : ''" v-model="data.quantite" class="form-control" placeholder="Ex: 100">
                                <div v-if="isEmpty.quantite" class="invalid-feedback">
                                    {{ msgInput.quantite }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Seuil d'alerte</label>
                                <input type="number" :class="isEmpty.seuil_alerte ? 'is-invalid border border-danger' : ''" v-model="data.seuil_alerte" class="form-control" placeholder="Ex: 10" maxlength="100">
                                <div v-if="isEmpty.seuil_alerte" class="invalid-feedback">
                                    {{ msgInput.seuil_alerte }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Date d'expiration (Optionnelle)</label>
                                <input type="date" :class="isEmpty.date_expiration ? 'is-invalid border border-danger' : ''" v-model="data.date_expiration" class="form-control" placeholder="Nom rayon" maxlength="100">
                                <div v-if="isEmpty.date_expiration" class="invalid-feedback">
                                    {{ msgInput.date_expiration }}
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="nameSlideTop" class="form-label">Fournisseur (Optionnelle)</label>
                                <select class="form-select" v-model="data.fournisseur_id">
                                    <option value="">Selectionnez un fournisseur</option>
                                    <option v-for="(four,index) in allFournisseur" :key="index" :value="four.id">{{ four.nom }}</option>
                                </select>
                                <div v-if="isEmpty.fournisseur_id" class="invalid-feedback">
                                    {{ msgInput.fournisseur_id }}
                                </div>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label for="nameSlideTop" class="form-label">Rayon</label>
                                <select class="form-select" v-model="data.rayon_id" :class="isEmpty.rayon_id ? 'is-invalid border border-danger' : ''" name="" id="">
                                    <option value="">Selectionnez un rayon</option>
                                    <option v-for="(ray,index) in allRayon" :key="index" :value="ray.id">{{ ray.nom }}</option>
                                </select>
                                <div v-if="isEmpty.rayon_id" class="invalid-feedback">
                                    {{ msgInput.rayon_id }}
                                </div>
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

    </div>
</template>
<script setup>

    import Swal from 'sweetalert2';
    import DataTable from '../DataTable/Datatable.vue';
    import { onMounted, ref } from 'vue';
    import {postData, getData, getSingleData, putData, deleteData} from '../../plugins/api'

    let addmodal;

    const data = ref({
        id:'',
        code_barre:'',
        nom:'',
        prix_unitaire:'',
        prix_achat:'',
        seuil_alerte:'',
        fournisseur_id:'',
        date_expiration:'',
        quantite:'',
        image:'',
        rayon_id:'',
    })

    const isEmpty = ref({})
    const imagePreview = ref('')
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allProduct = ref([])
    const allRayon = ref([])
    const allFournisseur = ref([])

    function showModal(){
        addmodal.show();
        data.value = {
            id:'',
            code_barre:'',
            nom:'',
            prix_unitaire:'',
            prix_achat:'',
            seuil_alerte:'',
            fournisseur_id:'',
            date_expiration:'',
            quantite:'',
            image:'',
            rayon_id:'',
        }
        modalTitle.value = 'Ajouter un produits'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllProductsFunction() {
        await getData('/products/magasin').then(res=>{
            if (res.status === 200) {
                allProduct.value = res.data
            }
        })
    }

    async function AllRayonFunction() {
        await getData('/rayons').then(res=>{
            if (res.status === 200) {
                allRayon.value = res.data
            }
        })
    }

    async function AllFournisseurFunction() {
        await getData('/fournisseurs').then(res=>{
            if (res.status === 200) {
                allFournisseur.value = res.data
            }
        })
    }

    const columns = ref([
        {
            title: '#',
            data: null,
            render: function (data, type, row, meta) {
                return `#${data.code_barre}`; // Index (1-based)
            }
        },
        {
            title: 'Nom',
            data: 'nom',
            render: function(data, type, row) {
                return `<span class="fw-bold">${row.nom}</span>`
            }
        },
        {
            title:'Prix de Vente',
            data:'prix_unitaire',
            render: function(data, type, row) {
                return `<span>${Number(data).toLocaleString('fr-FR')} FCFA</span>`
            }
        },
        {
            title: 'Quantité',
            data: 'stocks',
            render: function(data, type, row) {
                if (!row.stocks || row.stocks.length === 0) return '<span class="text-muted">—</span>'
                const stock = row.stocks[0]
                return `<span>${stock.quantite}</span>`
            }
        },
        {
            title: 'Seuil d\'alerte',
            data: 'stocks',
            render: function(data, type, row) {
                if (!row.stocks || row.stocks.length === 0) return '<span class="text-muted">—</span>'
                const stock = row.stocks[0]

                // Badge rouge si la quantité est en dessous ou égale au seuil
                if (stock.quantite <= stock.seuil_alerte) {
                    return `<span class="badge bg-danger">${stock.seuil_alerte}</span>`
                }

                return `<span class="badge bg-success">${stock.seuil_alerte}</span>`
            }
        },
        {
            title: 'Rayon',
            data: 'rayon',
            render: function(data, type, row) {
                if (!row.rayon) return '<span class="text-muted">—</span>'
                return `<span class="">${row.rayon.nom}</span>`
            }
        },
        {
            title:"Date d'expiration",
            data:'date_expiration',
            render: function(data) {
                if (!data) return '<span class="text-muted">—</span>'

                return new Intl.DateTimeFormat('fr-FR', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                }).format(new Date(data))
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
            return `

                <div class="dropdown">
                    <a class="btn btn-lg btn-light " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fe fe-more-vertical"></i>
                    </a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fe fe-eye me-2"></i> Détails</a></li>
                        <li><a class="dropdown-item cursor-pointer" onclick="ShowProductFunction(${row.id})"><i class="fe fe-edit me-2"></i> Modifier</a></li>
                        <li><a class="dropdown-item cursor-pointer" onclick="DeleteProductFunction(${row.id})"><i class="fe fe-trash me-2"></i> Supprimer</a></li>
                    </ul>
                </div>

            `;
            }
        }
    ])

    async function AddProductFunction() {
        // 1. Validation visuelle (votre code existant)
        const ignoredFields = ['id','fournisseur_id','image','date_expiration'];
        for (const field in data.value) {
            if (ignoredFields.includes(field)) continue;
            isEmpty.value[field] = !data.value[field];
            msgInput.value[field] = `Veuillez remplir le champ ${field.replace('_', ' ')}`;
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false);

        if (allEmpty) {
            isLoader.value = true;
            await postData('products/magasinStore',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        code_barre:'',
                        nom:'',
                        prix_unitaire:'',
                        prix_achat:'',
                        seuil_alerte:'',
                        fournisseur_id:'',
                        date_expiration:'',
                        quantite:'',
                        image:'',
                        rayon_id:'',
                    }
                    Swal.fire({
                        icon: 'success',
                        text: 'Produits ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    AllProductsFunction()
                    addmodal.hide()
                }
            })
        }
    }

    window.ShowProductFunction = async function(id){
        await getSingleData('/products/'+id).then(res=>{
            if (res.status === 200) {
                const product = res.data
                data.value = {
                    ...product,
                    quantite: product.stocks[0]?.quantite,
                    seuil_alerte: product.stocks[0]?.seuil_alerte,
                    date_expiration: product.date_expiration
                    ? product.date_expiration.split('T')[0]  // "2028-11-22T00:00:00.000000Z" → "2028-11-22"
                    : ''
                }
                isEdite.value = true
                modalTitle.value = 'Modifier un produits'
                modalbutton.value = 'Modifier'
                addmodal.show()
            }
        })
    }

    async function UpdateProductFunction() {
        isLoader.value = true;
        await putData('/products/magasinPut/'+data.value.id,data.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                data.value = {
                    code_barre:'',
                    nom:'',
                    prix_unitaire:'',
                    prix_achat:'',
                    seuil_alerte:'',
                    fournisseur_id:'',
                    date_expiration:'',
                    quantite:'',
                    image:'',
                    rayon_id:'',
                }
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Modification effectuer',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllProductsFunction()
                addmodal.hide()
            }
        })
    }

    window.DeleteProductFunction = async function(id){
        Swal.fire({
            title: "Voulez-vous supprimez ce produit ?",
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

                await deleteData('/products/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllProductsFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        addmodal = new bootstrap.Modal(document.getElementById('productModal'));
        AllProductsFunction()
        AllRayonFunction()
        AllFournisseurFunction()
    })

</script>
<style>

</style>

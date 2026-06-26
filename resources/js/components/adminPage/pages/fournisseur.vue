<template>
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des fournisseurs</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouté un fournisseur</button>
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
                        <div class="table">
                            <DataTable :data="allFournisseur" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Fournisseur -->
        <div class="modal fade" id="fournisseurModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">

                <form class="modal-content"
                    @submit.prevent="!isEdite ? AddFournisseurFunction() : UpdateFournisseurFunction()">

                    <!-- Header -->
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">
                            {{ modalTitle }}
                        </h1>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close">
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">

                        <!-- Nom -->
                        <div class="mb-3">
                            <label class="form-label">
                                Nom du fournisseur
                            </label>

                            <input type="text"
                                class="form-control"
                                :class="isEmpty.nom ? 'is-invalid border border-danger' : ''"
                                v-model="data.nom"
                                placeholder="Nom du fournisseur">

                            <div v-if="isEmpty.nom" class="invalid-feedback">
                                {{ msgInput.nom }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">
                                Adresse email
                            </label>

                            <input type="email"
                                class="form-control"
                                :class="isEmpty.email ? 'is-invalid border border-danger' : ''"
                                v-model="data.email"
                                placeholder="Adresse email">

                            <div v-if="isEmpty.email" class="invalid-feedback">
                                {{ msgInput.email }}
                            </div>
                        </div>

                        <!-- Téléphone -->
                        <div class="mb-3">
                            <label class="form-label">
                                Téléphone
                            </label>

                            <input type="text"
                                class="form-control"
                                :class="isEmpty.telephone ? 'is-invalid border border-danger' : ''"
                                v-model="data.telephone"
                                placeholder="Téléphone">

                            <div v-if="isEmpty.telephone" class="invalid-feedback">
                                {{ msgInput.telephone }}
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="mb-3">
                            <label class="form-label">
                                Adresse
                            </label>

                            <textarea class="form-control"
                                    rows="3"
                                    :class="isEmpty.adresse ? 'is-invalid border border-danger' : ''"
                                    v-model="data.adresse"
                                    placeholder="Adresse du fournisseur">
                            </textarea>

                            <div v-if="isEmpty.adresse" class="invalid-feedback">
                                {{ msgInput.adresse }}
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">

                        <button type="button"
                                class="btn btn-secondary me-3"
                                data-bs-dismiss="modal">
                            Fermé
                        </button>

                        <button type="submit"
                                class="btn btn-primary"
                                :disabled="isLoader">

                            <span v-if="!isLoader">
                                {{ modalbutton }}
                            </span>

                            <div v-else class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">
                                    Loading...
                                </span>
                            </div>

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
        nom: '',
        email: '',
        telephone: '',
        adresse: '',
    })

    const isEmpty = ref({})
    const imagePreview = ref('')
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allFournisseur = ref([])

    function showModal(){
        addmodal.show();
        data.value = {
            id:'',
            nom: '',
            email: '',
            telephone: '',
            adresse: '',
        }
        modalTitle.value = 'Ajouter un Fournisseur'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
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
                return meta.row + 1; // Index (1-based)
            }
        },
        { title: 'Nom', data: 'nom' },
        {
            title: 'Email',
            data: 'email',
            render: function (data, type, row, meta) {
                return `<a href="mailto:${row.email}">${row.email}</a>`
            }
        },
        {
            title: 'Téléphone',
            data: 'telephone',
            render: function (data, type, row, meta) {
                return `<a href="tel:${row.telephone}">${row.telephone}</a>`
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

                <button class="btn btn-primary me-1" href="#" onclick="ShowFournisseurFunction(${row.id})">
                    <i class="fas fa-edit"></i>
                </button>

                <button class="btn btn-danger" href="#" onclick="DeleteFournisseurFunction(${row.id})">
                    <i class="fas fa-trash"></i>
                </button>

            `;
            }
        }
    ])

    async function AddFournisseurFunction() {

        // 1. Validation visuelle (votre code existant)
        const ignoredFields = ['id'];
        for (const field in data.value) {
            if (ignoredFields.includes(field)) continue;
            isEmpty.value[field] = !data.value[field];
            msgInput.value[field] = `Veuillez remplir le champs ${field.replace('_', ' ')}`;
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false);

        if (allEmpty){
            isLoader.value = true;
            await postData('/fournisseurs',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        id:'',
                        nom: '',
                        email: '',
                        telephone: '',
                        adresse: '',
                    }
                    Swal.fire({
                        icon: 'success',
                        text: 'Fournisseur ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    AllFournisseurFunction()
                    addmodal.hide()
                }
            })
        }
    }

    window.ShowFournisseurFunction = async function(id){
        await getSingleData('/fournisseurs/'+id).then(res=>{
            if (res.status === 200) {
                data.value = res.data
                isEdite.value = true
                modalTitle.value = 'Modifier un fournisseur'
                modalbutton.value = 'Modifier'
                addmodal.show()
            }
        })
    }

    async function UpdateFournisseurFunction() {
        isLoader.value = true;
        await putData('/fournisseurs/'+data.value.id,data.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                data.value = {
                    id:'',
                    nom: '',
                    email: '',
                    telephone: '',
                    adresse: '',
                }
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Modification effectuer',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllFournisseurFunction()
                addmodal.hide()
            }
        })
    }

    window.DeleteFournisseurFunction = async function (id) {
        Swal.fire({
            title: "Voulez-vous supprimez ce fournisseur ?",
            text: "Vous ne pouvez plus revenir en arrière",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#002D5D",
            confirmButtonText: "Supprimer",
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

                await deleteData('/fournisseurs/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllFournisseurFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        addmodal = new bootstrap.Modal(document.getElementById('fournisseurModal'));
        AllFournisseurFunction()
    })

</script>
<style >
    
</style>
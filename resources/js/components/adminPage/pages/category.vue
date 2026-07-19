<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des Catégories</h5>
                <div class="list-btn" v-if="can('create', 'category')">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouté une catégorie</button>
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
                            <DataTable :data="allCategory" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-top fade" id="categoryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddCategoryFunction() : UpdateCategoryFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="col-lg-12">
                            <label for="nameSlideTop" class="form-label">Nom Category</label>
                            <input type="text" :class="isEmpty.name ? 'is-invalid border border-danger' : ''" v-model="data.name" class="form-control" placeholder="Nom de la catégorie" maxlength="100">
                            <div v-if="isEmpty.name" class="invalid-feedback">
                                {{ msgInput.name }}
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
    import { can } from '../../plugins/permissions'

    let addmodal;

    const data = ref({
        id:'',
        name : '',
    })

    const isEmpty = ref({})
    const imagePreview = ref('')
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allCategory = ref([])

    function showModal(){
        addmodal.show();
        data.value = {
            id:'',
            name : '',
        }
        modalTitle.value = 'Ajouter une catégorie'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllCategoryFunction() {
        await getData('/categories').then(res=>{
            if (res.status === 200) {
                allCategory.value = res.data
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
        { title: 'Nom', data: 'name' },
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

            const peutModifier = can('update', 'category')
            const peutSupprimer = can('delete', 'category')

            if (!peutModifier && !peutSupprimer) {
                return ''
            }


            return `
                ${peutModifier ? `
                    <button class="btn btn-primary me-1" href="#" onclick="ShowCategoryFunction(${row.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                ` : ``}
                ${peutSupprimer ? `
                    <button class="btn btn-danger" href="#" onclick="DeleteCategoryFunction(${row.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                ` : ``}
            `;
            }
        }
    ])

    async function AddCategoryFunction(){
        // 1. Validation visuelle (votre code existant)
        const ignoredFields = ['id'];
        for (const field in data.value) {
            if (ignoredFields.includes(field)) continue;
            isEmpty.value[field] = !data.value[field];
            msgInput.value[field] = `Veuillez remplir le champ ${field.replace('_', ' ')}`;
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false);

        if (allEmpty) {
            isLoader.value = true;
            await postData('/categories',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        name:''
                    }
                    Swal.fire({
                        icon: 'success',
                        text: 'Catégorie ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    AllCategoryFunction()
                    addmodal.hide()
                }
            })
        }
    }

    window.ShowCategoryFunction = async function(id){
        await getSingleData('/categories/'+id).then(res=>{
            if (res.status === 200) {
                data.value = res.data
                isEdite.value = true
                modalTitle.value = 'Modifier une catégorie'
                modalbutton.value = 'Modifier'
                addmodal.show()
            }
        })
    }

    async function UpdateCategoryFunction(){
        isLoader.value = true;
        await putData('/categories/'+data.value.id,data.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                data.value = {
                    name:''
                }
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Modification effectuer',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllCategoryFunction()
                addmodal.hide()
            }
        })
    }

    window.DeleteCategoryFunction = async function (id) {
        Swal.fire({
            title: "Voulez-vous supprimez cette catégorie ?",
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

                await deleteData('/categories/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllCategoryFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        addmodal = new bootstrap.Modal(document.getElementById('categoryModal'));
        AllCategoryFunction()
    })

</script>
<style>

</style>

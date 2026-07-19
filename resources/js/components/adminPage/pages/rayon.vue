<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des rayons</h5>
                <div class="list-btn" v-if="can('create', 'rayon')">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouté un rayon</button>
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
                            <DataTable :data="allRayon" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-top fade" id="rayonModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddRayonFunction() : UpdateRayonFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="col-lg-12 mb-3">
                            <label for="nameSlideTop" class="form-label">Nom Rayon</label>
                            <input type="text" :class="isEmpty.nom ? 'is-invalid border border-danger' : ''" v-model="data.nom" class="form-control" placeholder="Nom rayon" maxlength="100">
                            <div v-if="isEmpty.nom" class="invalid-feedback">
                                {{ msgInput.nom }}
                            </div>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <label for="nameSlideTop" class="form-label">Catégory</label>
                            <select class="form-select" v-model="data.category_id" :class="isEmpty.category_id ? 'is-invalid border border-danger' : ''" name="" id="">
                                <option value="">Selectionnez une ctégorie</option>
                                <option v-for="(cat,index) in allCategory" :key="index" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div v-if="isEmpty.category_id" class="invalid-feedback">
                                {{ msgInput.category_id }}
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
        nom : '',
        category_id:''
    })

    const isEmpty = ref({})
    const imagePreview = ref('')
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allCategory = ref([])
    const allRayon = ref([])

    function showModal(){
        addmodal.show();
        data.value = {
            id:'',
            nom : '',
            category_id:''
        }
        modalTitle.value = 'Ajouter un rayon'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllRayonFunction() {
        await getData('/rayons').then(res=>{
            if (res.status === 200) {
                allRayon.value = res.data
            }
        })
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
        { title: 'Nom', data: 'nom' },
        { title: 'Catégory', data: 'category.name' },
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

            const peutModifier = can('update', 'rayon')
            const peutSupprimer = can('delete', 'rayon')

            if (!peutModifier && !peutSupprimer) {
                return ''
            }

            return `

                ${peutModifier ? `
                    <button class="btn btn-primary me-1" href="#" onclick="ShowRayonFunction(${row.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                ` : ``}

                ${peutSupprimer ? `
                    <button class="btn btn-danger" href="#" onclick="DeleteRayonFunction(${row.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                ` : ``}

            `;
            }
        }
    ])

    async function AddRayonFunction(){
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
            await postData('/rayons',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        nom : '',
                        category_id:''
                    }
                    Swal.fire({
                        icon: 'success',
                        text: 'Rayon ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    AllRayonFunction()
                    addmodal.hide()
                }
            })
        }
    }

    window.ShowRayonFunction = async function(id){
        await getSingleData('/rayons/'+id).then(res=>{
            if (res.status === 200) {
                data.value = res.data
                isEdite.value = true
                modalTitle.value = 'Modifier un rayon'
                modalbutton.value = 'Modifier'
                addmodal.show()
            }
        })
    }

    async function UpdateRayonFunction(){
        isLoader.value = true;
        await putData('/rayons/'+data.value.id,data.value).then(res=>{
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
                AllRayonFunction()
                addmodal.hide()
            }
        })
    }

    window.DeleteRayonFunction = async function (id) {
        Swal.fire({
            title: "Voulez-vous supprimez ce rayon ?",
            text: "Vous ne pouvez plus revenir en arrière",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#002D5D",
            confirmButtonText: "Supprimé",
            cancelButtonText: "Fermé"
        }).then(async (result) => {

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

            if (result.isConfirmed) {
                await deleteData('/rayons/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllRayonFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        addmodal = new bootstrap.Modal(document.getElementById('rayonModal'));
        AllRayonFunction()
        AllCategoryFunction()
    })

</script>
<style>

</style>

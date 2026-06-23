<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des Mouvements</h5>
                <div class="list-btn">
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
                        <div class="table">
                            <DataTable :data="allMouvement" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-top fade" id="mouvementModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddRayonFunction() : UpdateRayonFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="row">

                            <div class="col-lg-6 mb-3">
                                <label for="">Selectionnez un produits</label>
                                <select class="form-select" name="" id="">
                                    <option value="">Selectionnez un produits</option>
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="">Quantité</label>
                                <input type="number" class="form-control" placeholder="Ex:100">
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="">Type de mouvement</label>
                                <select class="form-select" name="" id="">
                                    <option value="">Selectionnez un produits</option>
                                </select>
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
        product_id:'',
        emplacement_id:'1',
        emplacement_destination_id:'',
        quantite:'',
        type:'',
        date:'',
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
            emplacement_id:'',
            emplacement_destination_id:'',
            quantite:'',
            type:'',
            date:'',
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
                emplacement: 'boutique'
            }
        }).then(res=>{
            if (res.status === 200) {
                allMouvement.value = res.data
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
            data: 'product.nom'
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

                <button class="btn btn-primary me-1" href="#" onclick="ShowMouvementFunction(${row.id})">
                    <i class="fas fa-edit"></i>
                </button>

                <button class="btn btn-danger" href="#" onclick="DeleteMouvementFunction(${row.id})">
                    <i class="fas fa-trash"></i>
                </button>

            `;
            }
        }
    ])

    async function AddMouvementBoutique() {
        const ignoredFields = ['id','emplacement_destination_id','motif'];
        for (const field in data.value) {
            if (ignoredFields.includes(field)) continue;
            isEmpty.value[field] = !data.value[field];
            msgInput.value[field] = `Veuillez remplir le champ ${field.replace('_', ' ')}`;
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false);

        if (allEmpty) {
            isLoader.value = true;
            await postData('/mouvements',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        product_id:'',
                        emplacement_id:'1',
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

    window.ShowMouvementFunction = async function(id){
        await getSingleData('/mouvements/'+id).then(res=>{
            if (res.status === 200) {
                data.value = res.data
                isEdite.value = true
                modalTitle.value = 'Modifier un rayon'
                modalbutton.value = 'Modifier'
                addmodal.show()
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
        AllMouvementFunction()
    })
</script>
<style>

</style>

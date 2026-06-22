<template>
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des clients</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouté un client</button>
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
                            <DataTable :data="allclients" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-top fade" id="clientModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddClientFunction() : UpdateClientFunction()">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTopTitle">{{ modalTitle }}</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="form-group">
                                    <label for="">Nom complet :</label>
                                    <input type="text" class="form-control" v-model="data.fullname" :class="{'is-invalid': isEmpty.fullname}">
                                    <div class="invalid-feedback" v-if="isEmpty.fullname">
                                        {{ msgInput.fullname }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="form-group">
                                    <label for="">Email :</label>
                                    <input type="email" class="form-control" v-model="data.email" :class="{'is-invalid': isEmpty.email}">
                                    <div class="invalid-feedback" v-if="isEmpty.email">
                                        {{ msgInput.email }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="form-group">
                                    <label for="">Téléphone :</label>
                                    <input type="text" class="form-control" v-model="data.phone" :class="{'is-invalid': isEmpty.phone}">
                                    <div class="invalid-feedback" v-if="isEmpty.phone">
                                        {{ msgInput.phone }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="form-group">
                                    <label for="">Numéro IFU :</label>
                                    <input type="text" class="form-control" v-model="data.ifu" :class="{'is-invalid': isEmpty.ifu}">
                                    <div class="invalid-feedback" v-if="isEmpty.ifu">
                                        {{ msgInput.ifu }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <div class="form-group">
                                    <label for="">Adresse du client :</label>
                                    <input type="text" class="form-control" v-model="data.adresse" :class="{'is-invalid': isEmpty.adresse}">
                                    <div class="invalid-feedback" v-if="isEmpty.adresse">
                                        {{ msgInput.adresse }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">
                            Fermé
                        </button>
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

    import { nextTick,ref,onMounted } from 'vue';
    import DataTable from '../DataTable/Datatable.vue'
    import Swal from 'sweetalert2';
    import { postData, getData, getSingleData, putData, deleteData } from '../../plugins/api.js';

    let addmodal;

    const data = ref({
        id:'',
        fullname:'',
        email:'',
        phone:'',
        adresse:'',
        ifu:'',
    })

    const isEmpty = ref({})
    const msgInput = ref({})
    const imagePreview = ref('')
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allclients = ref([])

    function showModal(){
        addmodal.show();
        data.value = {
            id:'',
            fullname:'',
            email:'',
            phone:'',
            adresse:'',
            ifu:'',
        }
        modalTitle.value = 'Créer un clients'
        modalbutton.value = 'Enrégistré'
        imagePreview.value = ''
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllClientFunction(){
        await getData('/clients').then(res=>{
            if (res.status === 200) {
                allclients.value = res.data
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
            title: 'Nom complet', 
            data: 'fullname' 
        },
        { 
            title: 'Adresse Email', 
            data: 'email' 
        },
        { 
            title: 'Téléphone', 
            data: 'phone' 
        },
        { 
            title: 'Numéro IFU', 
            data: 'ifu' 
        },
        {
          title: 'Crée le', 
          data: 'created_at', 
          render: (data, type, row) => {
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

                <button class="btn btn-primary me-1" href="#" onclick="ShowClientFunction(${row.id})">
                    <i class="fas fa-edit"></i>
                </button>

                <button class="btn btn-danger" href="#" onclick="DeleteClientFunction(${row.id})">
                    <i class="fas fa-trash"></i>
                </button>

            `;
            }
        }
    ])

    async function AddClientFunction(){
        // 1. Validation visuelle (votre code existant)
        const ignoredFields = ['id'];
        for (const field in data.value) {
            if (ignoredFields.includes(field)) continue;
            isEmpty.value[field] = !data.value[field];
            msgInput.value[field] = `Veuillez remplir le champ ${field.replace('_', ' ')}`;
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false);

        if (allEmpty){
            isLoader.value = true;
            await postData('/clients',data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false;
                    data.value = {
                        id:'',
                        fullname:'',
                        email:'',
                        phone:'',
                        adresse:'',
                        ifu:'',
                    }
                    Swal.fire({
                        icon: 'success',
                        text: 'Client ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    AllClientFunction()
                    addmodal.hide()
                }
            })
        }
    }

    window.ShowClientFunction = async function (id) {
        await getSingleData(`/clients/${id}`).then(res=>{
            if (res.status === 200) {
                data.value = res.data.data
                isEdite.value = true
                modalTitle.value = 'Modifier un client'
                modalbutton.value = 'Modifier'
                addmodal.show()
            }
        })
    }

    async function UpdateClientFunction() {
        isLoader.value = true;
        await putData(`/clients/${data.value.id}`,data.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false
                data.value = {
                    id:'',
                    fullname:'',
                    email:'',
                    phone:'',
                    adresse:'',
                    ifu:'',
                }
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Modification effectuer',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllClientFunction()
                addmodal.hide()
            }
        })
    }

    window.DeleteClientFunction = async function (id) {
        Swal.fire({
            title: "Voulez-vous supprimez ce client ?",
            text: "Vous ne pouvez plus revenir en arrière",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#002D5D",
            confirmButtonText: "Supprimé",
            cancelButtonText: "Fermé"
        }).then(async (result) => {
            if (result.isConfirmed) {
                await deleteData('/clients/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllClientFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        addmodal = new bootstrap.Modal(document.getElementById('clientModal'))
        AllClientFunction()
    })

</script>
<style lang="">

</style>

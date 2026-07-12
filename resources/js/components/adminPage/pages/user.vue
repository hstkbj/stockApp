<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des Utilisateurs</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouter un utilisateur</button>
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
                            <DataTable :data="allUsers" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Utilisateur -->
        <div class="modal modal-top fade" id="userModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" @submit.prevent="!isEdite ? AddUserFunction() : UpdateUserFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Nom complet</label>
                                <input type="text" :class="isEmpty.full_name ? 'is-invalid border border-danger' : ''" v-model="data.full_name" class="form-control" placeholder="Nom complet" maxlength="255">
                                <div v-if="isEmpty.full_name" class="invalid-feedback">{{ msgInput.full_name }}</div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" :class="isEmpty.email ? 'is-invalid border border-danger' : ''" v-model="data.email" class="form-control" placeholder="Ex: user@mail.com" maxlength="255">
                                <div v-if="isEmpty.email" class="invalid-feedback">{{ msgInput.email }}</div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Rôle</label>
                                <select class="form-select" :class="isEmpty.role_id ? 'is-invalid border border-danger' : ''" v-model="data.role_id">
                                    <option value="">Selectionnez un rôle</option>
                                    <option v-for="(role,index) in allRoles" :key="index" :value="role.id">{{ role.name }}</option>
                                </select>
                                <div v-if="isEmpty.role_id" class="invalid-feedback">{{ msgInput.role_id }}</div>
                            </div>

                            <!-- Champ mot de passe visible uniquement en édition, pour reset optionnel -->
                            <template v-if="isEdite">
                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Nouveau mot de passe (optionnel)</label>
                                    <input type="password" v-model="data.password" class="form-control" placeholder="Laisser vide pour ne pas changer">
                                </div>
                                <div class="col-lg-6 mb-3" v-if="data.password">
                                    <label class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" v-model="data.password_confirmation" class="form-control" placeholder="Confirmer le mot de passe">
                                </div>
                            </template>
                            <div v-else class="col-lg-12">
                                <small class="text-muted"><i class="fe fe-info me-1"></i>Un mot de passe sera généré automatiquement et affiché après la création.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Fermé</button>
                        <button type="submit" class="btn btn-primary" :disabled="isLoader">
                            <span v-if="!isLoader">{{ modalbutton }}</span>
                            <span v-else><i class="fas fa-spinner fa-spin"></i></span>
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

    let usermodal;

    const data = ref({
        id: '',
        full_name: '',
        email: '',
        role_id: '',
        password: '',
        password_confirmation: '',
    })

    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allUsers = ref([])
    const allRoles = ref([])

    function resetData(){
        data.value = {
            id: '',
            full_name: '',
            email: '',
            role_id: '',
            password: '',
            password_confirmation: '',
        }
    }

    function showModal(){
        usermodal.show();
        resetData()
        modalTitle.value = 'Ajouter un utilisateur'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllUsersFunction() {
        await getData('/users').then(res=>{
            if (res.status === 200) {
                allUsers.value = res.data.users
            }
        })
    }

    async function AllRolesFunction() {
        await getData('/roles').then(res=>{
            if (res.status === 200) {
                allRoles.value = res.data
            }
        })
    }

    const columns = ref([
        {
            title: '#',
            data: null,
            render: (data, type, row, meta) => meta.row + 1
        },
        {
            title: 'Nom complet',
            data: 'full_name',
            render: (data, type, row) => `<span class="fw-bold">${row.full_name}</span>`
        },
        {
            title: 'Email',
            data: 'email',
        },
        {
            title: 'Rôle',
            data: null,
            render: (data, type, row) => {
                const roleName = row.role?.name || '—'
                return `<span class="badge bg-primary">${roleName}</span>`
            }
        },
        {
            title: 'Crée le',
            data: 'created_at',
            render: (data, type, row) => {
                const date = new Date(row.created_at)
                return new Intl.DateTimeFormat('fr-FR', {
                    year: 'numeric', month: 'short', day: 'numeric',
                }).format(date)
            }
        },
        {
            title: 'Action', data: null, render: (data, type, row) => {
                return `
                    <div class="dropdown">
                        <a class="btn btn-lg btn-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fe fe-more-vertical"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item cursor-pointer" onclick="ShowUserFunction(${row.id})"><i class="fe fe-edit me-2"></i> Modifier</a></li>
                            <li><a class="dropdown-item cursor-pointer" onclick="DeleteUserFunction(${row.id})"><i class="fe fe-trash me-2"></i> Supprimer</a></li>
                        </ul>
                    </div>
                `;
            }
        }
    ])

    function validateBase(){
        isEmpty.value.full_name = !data.value.full_name
        isEmpty.value.email = !data.value.email
        isEmpty.value.role_id = !data.value.role_id
        msgInput.value.full_name = 'Veuillez indiquer le nom complet'
        msgInput.value.email = 'Veuillez indiquer un email'
        msgInput.value.role_id = 'Veuillez sélectionner un rôle'

        return Object.values(isEmpty.value).every(v => v === false)
    }

    async function AddUserFunction() {
        if (!validateBase()) return;

        isLoader.value = true;
        await postData('/users', {
            full_name: data.value.full_name,
            email: data.value.email,
            role_id: data.value.role_id,
        }).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                usermodal.hide()
                resetData()
                AllUsersFunction()

                // Affiche le mot de passe généré UNE SEULE FOIS
                Swal.fire({
                    icon: 'success',
                    title: 'Utilisateur créé avec succès',
                    confirmButtonText: 'Compris'
                });
            }
        }).catch(err=>{
            isLoader.value = false;
            Swal.fire({
                icon: 'error',
                text: err.response?.data?.message || 'Une erreur est survenue',
            });
        })
    }

    window.ShowUserFunction = async function(id){
        await getSingleData('/users/'+id).then(res=>{
            if (res.status === 200) {
                const user = res.data.user
                data.value = {
                    id: user.id,
                    full_name: user.full_name,
                    email: user.email,
                    role_id: user.role_id,
                    password: '',
                    password_confirmation: '',
                }
                isEdite.value = true
                modalTitle.value = 'Modifier un utilisateur'
                modalbutton.value = 'Modifier'
                isEmpty.value = {}
                msgInput.value = {}
                usermodal.show()
            }
        })
    }

    async function UpdateUserFunction() {
        if (!validateBase()) return;

        // Validation du mot de passe uniquement s'il a été renseigné
        if (data.value.password && data.value.password !== data.value.password_confirmation) {
            Swal.fire({ icon: 'error', text: 'Les mots de passe ne correspondent pas' });
            return;
        }

        const payload = {
            full_name: data.value.full_name,
            email: data.value.email,
            role_id: data.value.role_id,
        }
        if (data.value.password) {
            payload.password = data.value.password
            payload.password_confirmation = data.value.password_confirmation
        }

        isLoader.value = true;
        await putData('/users/'+data.value.id, payload).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Utilisateur modifié avec succès',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllUsersFunction()
                usermodal.hide()
                resetData()
            }
        }).catch(err=>{
            isLoader.value = false;
            Swal.fire({
                icon: 'error',
                text: err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : 'Une erreur est survenue',
            });
        })
    }

    window.DeleteUserFunction = async function(id){
        Swal.fire({
            title: "Voulez-vous supprimer cet utilisateur ?",
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
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading() }
                })

                await deleteData('/users/'+id).then(res=>{
                    if (res.status === 200) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            text: "Suppression effectuer",
                            showConfirmButton: false,
                            timer: 1500
                        })
                        AllUsersFunction()
                    }
                }).catch(err=>{
                    Swal.fire({
                        icon: 'error',
                        text: err.response?.data?.message || 'Impossible de supprimer cet utilisateur',
                    });
                })
            }
        })
    }

    onMounted(async ()=>{
        usermodal = new bootstrap.Modal(document.getElementById('userModal'));
        await AllRolesFunction()
        AllUsersFunction()
    })

</script>
<style>

</style>

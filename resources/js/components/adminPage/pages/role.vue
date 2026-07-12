<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Rôles &amp; Permissions</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <button class="btn btn-primary" @click="showModal"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Ajouter un rôle</button>
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
                            <DataTable :data="allRoles" :columns="columns"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Rôle (nom) -->
        <div class="modal modal-top fade" id="roleModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" @submit.prevent="!isEdite ? AddRoleFunction() : UpdateRoleFunction()">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">{{ modalTitle }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="roleName" class="form-label">Nom du rôle</label>
                        <input type="text" :class="isEmpty.name ? 'is-invalid border border-danger' : ''" v-model="data.name" class="form-control" placeholder="Ex: Vendeur, Magasinier..." maxlength="100">
                        <div v-if="isEmpty.name" class="invalid-feedback">
                            {{ msgInput.name }}
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

        <!-- Modal Permissions (matrice) -->
        <div class="modal modal-top fade" id="permissionModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Permissions — {{ currentRoleName }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Page</th>
                                        <th class="text-center">Accès à la page</th>
                                        <th class="text-center">Créer</th>
                                        <th class="text-center">Lire</th>
                                        <th class="text-center">Modifier</th>
                                        <th class="text-center">Supprimer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(label, routeName) in availableRoutes" :key="routeName">
                                        <td class="fw-bold">{{ label }}</td>
                                        <td class="text-center"><input type="checkbox" v-model="permissionsData[routeName].access_page"></td>
                                        <td class="text-center"><input type="checkbox" v-model="permissionsData[routeName].create"></td>
                                        <td class="text-center"><input type="checkbox" v-model="permissionsData[routeName].read"></td>
                                        <td class="text-center"><input type="checkbox" v-model="permissionsData[routeName].update"></td>
                                        <td class="text-center"><input type="checkbox" v-model="permissionsData[routeName].delete"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Fermé</button>
                        <button type="button" class="btn btn-primary" :disabled="isPermLoader" @click="SavePermissionsFunction">
                            <span v-if="!isPermLoader">Enregistrer</span>
                            <span v-else><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
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

    let rolemodal;
    let permissionmodal;

    // ---------- Rôle (nom) ----------
    const data = ref({ id:'', name:'' })
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const isEdite = ref(false)
    const modalTitle = ref('')
    const modalbutton = ref('')
    const allRoles = ref([])

    // ---------- Permissions ----------
    const availableRoutes = ref({})       // { route_name: 'Label' }
    const permissionsData = ref({})       // { route_name: { create, read, update, delete, access_page } }
    const currentRoleId = ref(null)
    const currentRoleName = ref('')
    const isPermLoader = ref(false)

    function showModal(){
        rolemodal.show();
        data.value = { id:'', name:'' }
        modalTitle.value = 'Ajouter un rôle'
        modalbutton.value = 'Enrégistrer'
        isEmpty.value = {}
        msgInput.value = {}
        isEdite.value = false
    }

    async function AllRolesFunction() {
        await getData('/roles').then(res=>{
            if (res.status === 200) {
                allRoles.value = res.data
            }
        })
    }

    async function AllPagesFunction() {
        await getData('/roles/pages').then(res=>{
            if (res.status === 200) {
                availableRoutes.value = res.data
                permissionsData.value = Object.fromEntries(
                    Object.keys(res.data).map(routeName => [
                        routeName,
                        {
                            create: false,
                            read: false,
                            update: false,
                            delete: false,
                            access_page: false,
                        }
                    ])
                )
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
            title: 'Nom du rôle',
            data: 'name',
            render: (data, type, row) => `<span class="fw-bold">${row.name}</span>`
        },
        {
            title: 'Pages accessibles',
            data: 'permissions',
            render: (data, type, row) => {
                const total = row.permissions ? row.permissions.length : 0
                const actives = row.permissions ? row.permissions.filter(p => p.access_page).length : 0
                return `<span class="badge bg-info">${actives} / ${total}</span>`
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
                            <li><a class="dropdown-item cursor-pointer" onclick="ShowRoleFunction(${row.id})"><i class="fe fe-edit me-2"></i> Modifier le nom</a></li>
                            <li><a class="dropdown-item cursor-pointer" onclick="ShowPermissionsModal(${row.id}, '${row.name.replace(/'/g, "\\'")}')"><i class="fe fe-lock me-2"></i> Gérer les permissions</a></li>
                            <li><a class="dropdown-item cursor-pointer" onclick="DeleteRoleFunction(${row.id})"><i class="fe fe-trash me-2"></i> Supprimer</a></li>
                        </ul>
                    </div>
                `;
            }
        }
    ])

    async function AddRoleFunction() {
        isEmpty.value.name = !data.value.name
        msgInput.value.name = 'Veuillez indiquer un nom de rôle'

        if (isEmpty.value.name) return;

        isLoader.value = true;
        await postData('/roles', data.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                data.value = { id:'', name:'' }
                Swal.fire({
                    icon: 'success',
                    text: 'Rôle ajouté avec succès',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllRolesFunction()
                rolemodal.hide()
            }
        }).catch(()=>{ isLoader.value = false })
    }

    window.ShowRoleFunction = async function(id){
        await getSingleData('/roles/'+id).then(res=>{
            if (res.status === 200) {
                data.value = { id: res.data.id, name: res.data.name }
                isEdite.value = true
                modalTitle.value = 'Modifier le rôle'
                modalbutton.value = 'Modifier'
                isEmpty.value = {}
                msgInput.value = {}
                rolemodal.show()
            }
        })
    }

    async function UpdateRoleFunction() {
        isEmpty.value.name = !data.value.name
        msgInput.value.name = 'Veuillez indiquer un nom de rôle'

        if (isEmpty.value.name) return;

        isLoader.value = true;
        await putData('/roles/'+data.value.id, { name: data.value.name }).then(res=>{
            if (res.status === 200) {
                isLoader.value = false;
                isEdite.value = false
                Swal.fire({
                    icon: 'success',
                    text: 'Rôle modifié avec succès',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllRolesFunction()
                rolemodal.hide()
            }
        }).catch(()=>{ isLoader.value = false })
    }

    window.ShowPermissionsModal = async function(roleId, roleName){
        currentRoleId.value = roleId
        currentRoleName.value = roleName

        await getSingleData('/roles/'+roleId).then(res=>{
            if (res.status === 200) {
                const role = res.data
                const map = {}

                for (const routeName in availableRoutes.value) {
                    const existing = role.permissions.find(p => p.route_name === routeName)
                    map[routeName] = {
                        create:      existing ? !!existing.create : false,
                        read:        existing ? !!existing.read : false,
                        update:      existing ? !!existing.update : false,
                        delete:      existing ? !!existing.delete : false,
                        access_page: existing ? !!existing.access_page : false,
                    }
                }

                permissionsData.value = map
                permissionmodal.show()
            }
        })
    }

    async function SavePermissionsFunction() {
        const permissions = Object.entries(permissionsData.value).map(([route_name, perm]) => ({
            route_name,
            ...perm
        }))

        isPermLoader.value = true;
        await putData('/roles/'+currentRoleId.value+'/permissions', { permissions }).then(res=>{
            if (res.status === 200) {
                isPermLoader.value = false;
                Swal.fire({
                    icon: 'success',
                    text: 'Permissions mises à jour',
                    showConfirmButton: false,
                    timer: 1500
                });
                AllRolesFunction()
                permissionmodal.hide()
            }
        }).catch(()=>{ isPermLoader.value = false })
    }

    window.DeleteRoleFunction = async function(id){
        Swal.fire({
            title: "Voulez-vous supprimer ce rôle ?",
            text: "Toutes les permissions associées seront supprimées. Vous ne pouvez plus revenir en arrière",
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

                await deleteData('/roles/'+id).then(res=>{
                    if (res.status === 200) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            text: "Suppression effectuer",
                            showConfirmButton: false,
                            timer: 1500
                        })
                        AllRolesFunction()
                    }
                })
            }
        })
    }

    onMounted(async ()=>{
        rolemodal = new bootstrap.Modal(document.getElementById('roleModal'));
        permissionmodal = new bootstrap.Modal(document.getElementById('permissionModal'));
        await AllPagesFunction()   // charge la liste des pages AVANT d'afficher les rôles/permissions
        AllRolesFunction()
    })

</script>
<style>

</style>

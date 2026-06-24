<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Liste des ventes</h5>
                <div class="list-btn">
                    <ul class="filter-list">
                        <li>
                            <RouterLink class="btn btn-primary" to="/new-vente"><i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Créer une vente</RouterLink>
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
                            <DataTable :data="allVente" :columns="columns"/>
                        </div>
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
    import { useRouter } from 'vue-router';

    const allVente = ref([])
    const router = useRouter()

    async function AllVenteFunction() {
        await getData('/invoices',{
            params: {
                emplacement: 'boutique'
            }
        }).then(res=>{
            if (res.status === 200) {
                allVente.value = res.data
            }
        })
    }

    const columns = ref([
        {
            title: '#',
            data: 'invoice_number',
            render: function (data) {
                return `<span class="fw-bold">${data}</span>`
            }
        },
        {
            title: 'Client',
            data: null,
            render: function (data, type, row) {
                const nom = row.client?.fullname ?? row.anonymous_customer_name ?? '—'
                return `<span>${nom}</span>`
            }
        },
        {
            title: 'Total TTC',
            data: 'total_ttc',
            render: function (data) {
                return `<span class="fw-bold">${Number(data).toLocaleString('fr-FR')} FCFA</span>`
            }
        },
        {
            title: 'Statut',
            data: 'status',
            render: function (data) {
                const statuts = {
                    'draft'    : '<span class="badge bg-secondary">Brouillon</span>',
                    'sent'     : '<span class="badge bg-info text-dark">Envoyée</span>',
                    'paid'     : '<span class="badge bg-success">Payée</span>',
                    'overdue'  : '<span class="badge bg-warning text-dark">En retard</span>',
                    'cancelled': '<span class="badge bg-danger">Annulée</span>',
                }
                return statuts[data] ?? '<span class="badge bg-secondary">—</span>'
            }
        },
        {
            title: 'Créé par',
            data: 'user',
            render: function (data) {
                return `<span>${data?.full_name ?? '—'}</span>`
            }
        },
        {
            title: 'Créé le',
            data: 'created_at',
            render: function (data) {
                return new Intl.DateTimeFormat('fr-FR', {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit',
                }).format(new Date(data))
            }
        },
        {
            title: 'Action',
            data: null,
            render: (data, type, row) => {
                return `
                    <div class="dropdown">
                    <a class="btn btn-lg btn-light " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fe fe-more-vertical"></i>
                    </a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" onclick="DetailsVenteFunction(${row.id})"><i class="fe fe-eye me-2"></i> Détails</a></li>
                        <li><a class="dropdown-item cursor-pointer" onclick="ShowInvoiceFunction(${row.id})"><i class="fe fe-edit me-2"></i> Modifier</a></li>
                        <li><a class="dropdown-item cursor-pointer" onclick="DeleteInvoiceFunction(${row.id})"><i class="fe fe-trash me-2"></i> Supprimer</a></li>
                    </ul>
                </div>
                `
            }
        }
    ])

    window.ShowInvoiceFunction = function(id){
        router.push(`/edite-vente/${id}`)
    }

    window.DetailsVenteFunction = function(id){
        router.push(`/details-vente/${id}`)
    }

    window.DeleteInvoiceFunction = async function (id) {
        Swal.fire({
            title: "Voulez-vous supprimez cette vente ?",
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

                await deleteData('/invoices/'+id)
                    .then(res=>{
                        if (res.status === 200) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                text: "Suppression effectuer",
                                showConfirmButton: false,
                                timer: 1500
                            })
                            AllVenteFunction()
                        }
                    })
            }
        })
    }

    onMounted(()=>{
        AllVenteFunction()
    })

</script>
<style lang="">

</style>

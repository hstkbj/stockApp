<template>
    <div class="content container-fluid">

        <div class="card">
            <div class="card-body">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="content-invoice-header d-flex justify-content-between align-items-center">
                        <h5>
                            <RouterLink to="/vente" class="btn btn-secondary me-2">
                                <i class="fe fe-arrow-left"></i>
                            </RouterLink>
                            Détail de la facture
                        </h5>
                        <div v-if="invoice">
                            <RouterLink :to="'/edite-vente/' + invoice.id" class="btn btn-primary me-2">
                                <i class="fa fa-edit me-1"></i> Modifier
                            </RouterLink>
                            <button class="btn btn-danger" @click="deleteInvoice">
                                <i class="fa fa-trash me-1"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->

                <!-- Loader -->
                <div v-if="isLoading" class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>

                <div v-else-if="invoice" class="row justify-content-center">

                    <!-- Facture -->
                    <div class="col-lg-8">
                        <div class="card-table">
                            <div class="card-body">

                                <!-- Logo + Statut -->
                                <div class="invoice-item invoice-item-one">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="invoice-logo">
                                                <img src="/assets/img/logo.png" class="light-color-logo" alt="logo">
                                                <img src="/assets/img/logo-full-white.png" class="dark-white-logo" alt="logo">
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span :class="statusBadgeClass">{{ statusLabel }}</span>
                                            <p class="mt-2 mb-0 text-muted">
                                                <strong>{{ invoice.invoice_number }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="invoice-item invoice-item-date">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-start invoice-details">
                                                Date de facturation<span> : </span>
                                                <strong>{{ formatDate(invoice.due_at) }}</strong>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-start invoice-details">
                                                Date d'échéance<span> : </span>
                                                <strong>{{ formatDate(invoice.echeance_at) }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Client + Emplacement -->
                                <div class="invoice-item invoice-item-two">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="invoice-info">
                                                <strong class="customer-text-one">Facturé à<span> :</span></strong>
                                                <p class="invoice-details-two">
                                                    {{ invoice.client?.fullname ?? invoice.anonymous_customer_name }}<br>
                                                    <span v-if="invoice.client?.phone">{{ invoice.client.phone }}<br></span>
                                                    <span v-if="invoice.client?.email">{{ invoice.client.email }}<br></span>
                                                    <span v-if="invoice.client?.adresse">{{ invoice.client.adresse }}</span>
                                                    <span v-if="invoice.client?.ifu" class="text-muted"><br>IFU : {{ invoice.client.ifu }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="invoice-info invoice-info2">
                                                <strong class="customer-text-one">Informations<span> :</span></strong>
                                                <p class="invoice-details-two">
                                                    Emplacement : <strong class="text-capitalize">{{ invoice.emplacement?.nom }}</strong><br>
                                                    Créé par : <strong>{{ invoice.user?.full_name }}</strong><br>
                                                    Créé le : <strong>{{ formatDateTime(invoice.created_at) }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tableau des articles -->
                                <div class="invoice-item invoice-table-wrap">
                                    <div class="invoice-table-head">
                                        <h6>Articles :</h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table table-center table-hover mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Produit</th>
                                                            <th>Quantité</th>
                                                            <th>Prix unitaire</th>
                                                            <th>TVA</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in invoice.items" :key="item.id">
                                                            <td>{{ index + 1 }}</td>
                                                            <td>{{ item.product?.nom }}</td>
                                                            <td>{{ item.quantity }}</td>
                                                            <td>{{ formatCurrency(item.unit_price) }}</td>
                                                            <td>
                                                                <span v-if="item.vat_rate > 0" class="badge bg-warning text-dark">
                                                                    {{ item.vat_rate }}%
                                                                </span>
                                                                <span v-else class="badge bg-secondary">Exonéré</span>
                                                            </td>
                                                            <td>{{ formatCurrency(item.quantity * item.unit_price) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totaux -->
                                <div class="col-lg-12 col-md-12">
                                    <div class="invoice-total-card">
                                        <div class="invoice-total-box">
                                            <div class="invoice-total-inner">
                                                <p>Total HT <span>{{ formatCurrency(invoice.total_ht) }}</span></p>
                                                <p>TVA <span>{{ formatCurrency(invoice.total_tva) }}</span></p>
                                            </div>
                                            <div class="invoice-total-footer">
                                                <h4>Total TTC <span>{{ formatCurrency(invoice.total_ttc) }}</span></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mecefData" v-if="invoice.mecef">
                                    <div class="qrcode">
                                        <img v-if="qrImage" :src="qrImage" />
                                    </div>
                                    <div class="data">
                                        <div class="d-flex flex-column align-items-center justify-content-center mb-2">
                                            <span>Code MECeF/DGI</span>
                                            <span class="fw-bold">{{ invoice.mecef?.code_mecef_dgi }}</span>
                                        </div>
                                        <div class="other w-100 d-flex align-items-center justify-content-between">
                                            <div class="titles">
                                                <p>MECeF NIM:</p>
                                                <p>MECeF Compteurs:</p>
                                                <p>MECeF Heure:</p>
                                            </div>
                                            <div class="titleData">
                                                <p class="fw-bold text-end">{{ invoice.mecef?.nim }}</p>
                                                <p class="fw-bold text-end">{{ invoice.mecef?.counters }}</p>
                                                <p class="fw-bold text-end">{{ formatDateMecef(invoice.mecef?.mecef_datetime) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Sidebar : Statut + Timeline -->
                    <div class="col-lg-4">
                        <div class="card timeline-card">
                            <div class="card-body">

                                <!-- Changement de statut -->
                                <div class="input-block mb-4" v-if="invoice.status !== 'cancelled'">
                                    <label class="fw-bold mb-2">Changer le statut</label>
                                    <select class="form-select" v-model="selectedStatus" @change="updateStatus">
                                        <option value="draft">Brouillon</option>
                                        <option value="sent">Envoyée</option>
                                        <option value="paid">Payée</option>
                                        <option value="overdue">En retard</option>
                                        <option value="cancelled">Annulée</option>
                                    </select>
                                </div>
                                <div v-else class="alert alert-danger mb-4">
                                    Cette facture est annulée et ne peut plus être modifiée.
                                </div>

                                <!-- Résumé rapide -->
                                <div class="invoice-info invoice-info2 admin-invoice invoice-item mb-4">
                                    <strong class="customer-text-one">Résumé<span> :</span></strong>
                                    <p class="text-start invoice-details-two invoice-details mb-2">
                                        Facture : <strong>{{ invoice.invoice_number }}</strong>
                                    </p>
                                    <p class="text-start invoice-details-two invoice-details mb-2">
                                        Articles : <strong>{{ invoice.items?.length }}</strong>
                                    </p>
                                    <p class="text-start invoice-details-two invoice-details">
                                        Total TTC : <strong class="text-primary">{{ formatCurrency(invoice.total_ttc) }}</strong>
                                    </p>
                                </div>
                                <hr v-if="invoice.mecef?.status !== 'confirmed'">
                                <div class="normalize mb-3" v-if="invoice.mecef?.status !== 'confirmed'">
                                    <strong class="customer-text-one">Normalisation<span> :</span></strong>
                                    <form @submit.prevent="NormalizeInvoiceFunction">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <div class="form-group">
                                                    <label for="">Type de paiement :</label>
                                                    <select v-model="data.payment_type" :class="isEmpty.payment_type ? 'is-invalid border border-danger' : ''" class="form-select" name="" id="">
                                                        <option value="ESPECES" selected>Espèces</option>
                                                        <option value="VIREMENT">Virement</option>
                                                        <option value="CARTEBANCAIRE">Carte bancaire</option>
                                                        <option value="MOBILEMONEY">Mobile Money</option>
                                                        <option value="CHEQUES">Chèques</option>
                                                    </select>
                                                    <div v-if="isEmpty.payment_type" class="invalid-feedback">
                                                        {{ msgInput.payment_type }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 mb-3">
                                                <div class="form-group">
                                                    <label for="">Type de facture</label>
                                                    <select v-model="data.invoice_type" :class="isEmpty.invoice_type ? 'is-invalid border border-danger' : ''" class="form-select" name="" id="">
                                                        <option value="FV" selected>Facture de vente</option>
                                                        <option value="FA">Facture d'avoir</option>
                                                    </select>
                                                    <div v-if="isEmpty.invoice_type" class="invalid-feedback">
                                                        {{ msgInput.invoice_type }}
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary">Normaliser</button>
                                        </div>
                                    </form>
                                </div>
                                <hr>
                                <div class="d-flex flex-column align-items-start justify-content-start gap-2">
                                    <a @click="downloadPdf" class="cursor-pointer">
                                        Télécharger le PDF
                                    </a>
                                    <a @click="sendInvoiceByEmail" class="cursor-pointer">
                                        Envoyer par mail
                                    </a>
                                    <a @click="cancelledNomalizeInvoiceFunction" class="cursor-pointer">
                                        Créer un avoir total
                                    </a>
                                </div>
                                <hr>
                                <!-- Timeline -->
                                <strong class="customer-text-one">Timeline<span> :</span></strong>
                                <ul class="activity-feed">
                                    <li class="feed-item timeline-item">
                                        <span class="feed-text timeline-user">
                                            <strong>{{ invoice.user?.full_name }}</strong> a créé la facture
                                        </span>
                                        <div class="invoice-date">
                                            <span class="start-date">{{ formatDateTime(invoice.created_at) }}</span>
                                        </div>
                                    </li>
                                    <li class="feed-item timeline-item" v-if="invoice.updated_at !== invoice.created_at">
                                        <span class="feed-text timeline-user">
                                            <strong>{{ invoice.user?.full_name }}</strong> a modifié la facture
                                        </span>
                                        <div class="invoice-date">
                                            <span class="start-date">{{ formatDateTime(invoice.updated_at) }}</span>
                                        </div>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import { getSingleData, putData, deleteData, postData } from '../../plugins/api'
import QRCode from 'qrcode'
import axiosInstance from '../../plugins/axios'

const route  = useRoute()
const router = useRouter()

const invoice        = ref(null)
const isLoading      = ref(true)
const selectedStatus = ref('')
const qrImage = ref('')

const data = ref({
    payment_type:'ESPECES',
    invoice_type:'FV',
})
const isEmpty = ref({})
const msgInput = ref({})

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(value) {
    if (!value) return '—'
    return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric', month: 'long', day: 'numeric'
    }).format(new Date(value))
}

function formatDateMecef(date) {
    if (!date) return '';

    const d = new Date(date);

    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');

    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
}

function formatDateTime(value) {
    if (!value) return '—'
    return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(new Date(value))
}

function formatCurrency(value) {
    return new Intl.NumberFormat('fr-BJ', {
        style: 'currency', currency: 'XOF'
    }).format(value)
}

async function QrCodeFunction(qrcode){
    const qrData = qrcode

    if (qrData) {
        qrImage.value = await QRCode.toDataURL(qrData)
    }
}

async function downloadPdf() {
    Swal.fire({
        title: 'Génération du PDF...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    })

    try {
        const response = await axiosInstance.get(`/invoices/${invoice.value.id}/pdf`, {
            responseType: 'blob'  // ← crucial pour recevoir un fichier binaire
        })

        // Créer une URL temporaire depuis le blob
        const url  = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href  = url
        link.setAttribute('download', `facture-${invoice.value.invoice_number}.pdf`) // ← nom du fichier
        document.body.appendChild(link)
        link.click()       // ← déclenche le téléchargement automatiquement
        link.remove()
        window.URL.revokeObjectURL(url) // ← libère la mémoire

        Swal.close()

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Impossible de générer le PDF.',
        })
    }
}

async function sendInvoiceByEmail() {
    Swal.fire({
        title: 'Envoi en cours...',
        text: 'Génération et envoi de la facture par email.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    })

    try {
        const res = await postData(`/invoices/${invoice.value.id}/send-email`, {})

        if(res.status === 200){
            Swal.fire({
                icon: 'success',
                title: 'Email envoyé !',
                text: res.data.message,
                confirmButtonText: 'OK'
            })
        }


    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.response?.data?.message || 'Une erreur est survenue.',
        })
    }
}



// ─── Badge statut ─────────────────────────────────────────────────────────────

const statusLabel = computed(() => {
    const labels = {
        draft:     'Brouillon',
        sent:      'Envoyée',
        paid:      'Payée',
        overdue:   'En retard',
        cancelled: 'Annulée',
    }
    return labels[invoice.value?.status] ?? '—'
})

const statusBadgeClass = computed(() => {
    const classes = {
        draft:     'badge bg-secondary fs-6',
        sent:      'badge bg-info text-dark fs-6',
        paid:      'badge bg-success fs-6',
        overdue:   'badge bg-warning text-dark fs-6',
        cancelled: 'badge bg-danger fs-6',
    }
    return classes[invoice.value?.status] ?? 'badge bg-secondary fs-6'
})

// ─── Chargement ───────────────────────────────────────────────────────────────

async function loadInvoice() {
    isLoading.value = true
    try {
        const res = await getSingleData('/invoices/' + route.params.id)
        if (res.status === 200) {
            invoice.value        = res.data
            selectedStatus.value = res.data.status
            // Protection : mecef peut être absent ou vide
            const qrCode = invoice.value.mecef.qr_code
            if (qrCode) {
                QrCodeFunction(qrCode)
            }
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de charger la facture.' })
        router.push('/vente')
    } finally {
        isLoading.value = false
    }
}

// ─── Changement de statut ─────────────────────────────────────────────────────

async function updateStatus() {
    const result = await Swal.fire({
        title: 'Confirmer le changement ?',
        text: `Passer la facture en "${selectedStatus.value}" ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirmer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#002D5D',
        cancelButtonColor:  '#d33',
    })

    if (!result.isConfirmed) {
        // Annuler → remettre l'ancien statut dans le select
        selectedStatus.value = invoice.value.status
        return
    }

    Swal.fire({
        title: 'Mise à jour...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    })

    try {
        const res = await putData('/invoices/' + invoice.value.id + '/status', {
            status: selectedStatus.value
        })
        invoice.value = res.data
        Swal.fire({ icon: 'success', title: 'Statut mis à jour', showConfirmButton: false, timer: 1500 })
        loadInvoice()
    } catch (error) {
        selectedStatus.value = invoice.value.status
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.response?.data?.message || 'Une erreur est survenue.',
        })
    }
}

// ─── Suppression ──────────────────────────────────────────────────────────────

async function deleteInvoice() {
    const result = await Swal.fire({
        title: 'Supprimer la facture ?',
        text: 'Cette action est irréversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#d33',
        cancelButtonColor:  '#002D5D',
    })

    if (!result.isConfirmed) return

    Swal.fire({
        title: 'Suppression...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    })

    try {
        await deleteData('/invoices/' + invoice.value.id)
        Swal.fire({ icon: 'success', title: 'Facture supprimée', showConfirmButton: false, timer: 1500 })
            .then(() => router.push('/vente'))
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.response?.data?.message || 'Une erreur est survenue.',
        })
    }
}

// ─── Normalisation ──────────────────────────────────────────────────────────────
async function NormalizeInvoiceFunction(){
    // 1. Validation visuelle (votre code existant)
    for (const field in data.value) {
        isEmpty.value[field] = !data.value[field];
        msgInput.value[field] = `Veuillez remplir le champ ${field.replace('_', ' ')}`;
    }

    const allEmpty = Object.values(isEmpty.value).every(value => value === false);

    if (allEmpty) {

        Swal.fire({
            title: "Voulez-vous normalisé cette facture ?",
            text: "...",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#002D5D",
            confirmButtonText: "Normalisé",
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
                await postData(`invoices/${invoice.value.id}/normalize`,data.value).then(res=>{
                    if (res.status === 201) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            text: "Normalisation effectuer",
                            showConfirmButton: false,
                            timer: 1500
                        })
                        loadInvoice()
                    }
                })
            }
        }).catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Normalisation impossible',
                text: error.response?.data?.message || 'Une erreur est survenue',
                confirmButtonColor: '#002D5D',
                confirmButtonText: 'OK'
            })
        })

    }
}

// ─── Cancelled Normalisation ──────────────────────────────────────────────────────────────
async function cancelledNomalizeInvoiceFunction(){
    Swal.fire({
            title: "Voulez-vous annulé la normalisation de cette facture ?",
            text: "...",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#002D5D",
            confirmButtonText: "Oui",
            cancelButtonText: "Non"
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
                await postData(`invoices/${invoice.value.id}/cancelled`,data.value).then(res=>{
                    if (res.status === 200) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            text: "Opération effectuer",
                            showConfirmButton: false,
                            timer: 1500
                        })
                        loadInvoice()
                    }
                })
            }
        }).catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Normalisation impossible',
                text: error.response?.data?.message || 'Une erreur est survenue',
                confirmButtonColor: '#002D5D',
                confirmButtonText: 'OK'
            })
        })
}



onMounted(() => {
    loadInvoice()
})
</script>

<style scoped>
    .mecefData{
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 20px;
        border: 2px solid #000;
        border-radius: 10px;
        color: #000;
    }
    .mecefData .qrcode{
        width: 40%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mecefData .qrcode img{
        width: 120px;
        height: 120px;
    }
    .mecefData .data{
        width: 60%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>

<template>
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Mon profil</h5>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row" v-if="isLoading">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>

        <div class="row" v-else>
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="/assets/img/profiles/avatar-07.jpg" alt="avatar" class="rounded-circle mb-3" style="width: 110px; height: 110px; object-fit: cover;">
                        <h5 class="mb-1">{{ user.full_name }}</h5>
                        <p class="text-muted mb-2">{{ user.email }}</p>
                        <span class="badge bg-primary">{{ user.role?.name ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7">
                <!-- Informations générales -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="UpdateInfosFunction">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Nom complet</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :class="errors.full_name ? 'is-invalid border border-danger' : ''"
                                        v-model="infosForm.full_name"
                                        maxlength="255"
                                    >
                                    <div v-if="errors.full_name" class="invalid-feedback">{{ errors.full_name[0] }}</div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        :class="errors.email ? 'is-invalid border border-danger' : ''"
                                        v-model="infosForm.email"
                                        maxlength="255"
                                    >
                                    <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="isSavingInfos">
                                <span v-if="isSavingInfos" class="spinner-border spinner-border-sm me-2"></span>
                                Enregistrer les modifications
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Changement de mot de passe -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Changer le mot de passe</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="UpdatePasswordFunction">
                            <div class="row">
                                <div class="col-lg-12 mb-3">
                                    <label class="form-label">Mot de passe actuel</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        :class="errors.current_password ? 'is-invalid border border-danger' : ''"
                                        v-model="passwordForm.current_password"
                                        autocomplete="current-password"
                                    >
                                    <div v-if="errors.current_password" class="invalid-feedback">{{ errors.current_password[0] }}</div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        :class="errors.password ? 'is-invalid border border-danger' : ''"
                                        v-model="passwordForm.password"
                                        autocomplete="new-password"
                                    >
                                    <div v-if="errors.password" class="invalid-feedback">{{ errors.password[0] }}</div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        v-model="passwordForm.password_confirmation"
                                        autocomplete="new-password"
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="isSavingPassword">
                                <span v-if="isSavingPassword" class="spinner-border spinner-border-sm me-2"></span>
                                Changer le mot de passe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Swal from 'sweetalert2'
import { getSingleData, postData, putData } from '../../plugins/api'

const isLoading = ref(true)
const isSavingInfos = ref(false)
const isSavingPassword = ref(false)

const user = ref({})
const errors = ref({})

const infosForm = reactive({
    full_name: '',
    email: '',
})

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})

async function loadProfile() {
    isLoading.value = true
    try {
        const res = await getSingleData('/user')
        user.value = res.data.user
        infosForm.full_name = res.data.user.full_name
        infosForm.email = res.data.user.email
    } catch (error) {
        console.error(error)
        Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de charger votre profil.' })
    } finally {
        isLoading.value = false
    }
}

async function UpdateInfosFunction() {
    errors.value = {}
    isSavingInfos.value = true

    try {
        const res = await putData('/user/profile', {
            full_name: infosForm.full_name,
            email: infosForm.email,
        })

        user.value = res.data.user

        Swal.fire({
            position: "center",
            icon: "success",
            text: res.data.message || 'Profil mis à jour',
            showConfirmButton: false,
            timer: 1500
        })

        loadProfile()
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: error.response?.data?.message || 'Une erreur est survenue',
            })
        }
    } finally {
        isSavingInfos.value = false
    }
}

async function UpdatePasswordFunction() {
    errors.value = {}

    if (passwordForm.password !== passwordForm.password_confirmation) {
        errors.value = { password: ["Les mots de passe ne correspondent pas."] }
        return
    }

    isSavingPassword.value = true

    try {
        const res = await postData('/user/profile/change-password', {
            current_password: passwordForm.current_password,
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation,
        })

        Swal.fire({
            position: "center",
            icon: "success",
            text: res.data.message || 'Mot de passe changé avec succès',
            showConfirmButton: false,
            timer: 1500
        })

        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: error.response?.data?.message || 'Une erreur est survenue',
            })
        }
    } finally {
        isSavingPassword.value = false
    }
}

onMounted(() => {
    loadProfile()
})
</script>

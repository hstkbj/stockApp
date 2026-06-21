<template>

    <!-- Main Wrapper -->
    <div class="main-wrapper login-body">
        <div class="login-wrapper">
            <div class="container">

                <img class="img-fluid logo-dark mb-2 logo-color" src="assets/img/logo2.png" alt="Logo">
                <img class="img-fluid logo-light mb-2" src="assets/img/logo2-white.png" alt="Logo">
                <div class="loginbox">

                    <div class="login-right">
                        <div class="login-right-wrap">
                            <h1>Login</h1>
                            <p class="account-subtitle">Access to our dashboard</p>

                            <form @submit.prevent="LoginForm">
                                <div class="input-block mb-3">
                                    <label class="form-control-label">Adresse Email</label>
                                    <input type="email" :class="isEmpty.email ? 'is-invalid border border-danger' : ''" v-model="dataLogin.email" class="form-control">
                                    <div v-if="isEmpty.email" class="invalid-feedback">
                                        {{ msgInput.email }}
                                    </div>
                                </div>
                                <div class="input-block mb-3">
                                    <label class="form-control-label">Mot de passe</label>
                                    <div class="pass-group">
                                        <input :type="showpwd ? 'text' : 'password'" :class="isEmpty.password ? 'is-invalid border border-danger' : ''" v-model="dataLogin.password" class="form-control pass-input">
                                        <span @click="togglePwd" class="fas fa-eye toggle-password"></span>
                                    </div>
                                    <div v-if="isEmpty.password" class="invalid-feedback">
                                        {{ msgInput.password }}
                                    </div>
                                </div>
                                <div class="input-block mb-3">
                                    <div class="row">
                                        <div class="col-6">

                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="forgot-link" href="forgot-password.html">Forgot Password ?</a>
                                        </div>
                                    </div>
                                </div>
                                <button v-if="isLoader" class="btn btn-lg  btn-primary w-100" type="submit">
                                    <div class="spinner-border text-light" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </button>
                                <button v-else class="btn btn-lg  btn-primary w-100" type="submit">Login</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Main Wrapper -->

</template>
<script setup>

    import { onMounted,ref } from 'vue';
    import { useRouter } from 'vue-router';
    import {postData} from '../../plugins/api'

    const router = useRouter();
    const dataLogin = ref({
        email:'',
        password:''
    })

    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const showpwd = ref(false)

    async function LoginForm() {

        for (const field in dataLogin.value) {
            isEmpty.value[field] = !dataLogin.value[field];
            msgInput.value[field] = 'This field is empty';
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false)

        if (allEmpty) {
            isLoader.value = true
            await postData('/login', dataLogin.value).then(res => {
                if (res.status === 200) {
                    isLoader.value = false
                    localStorage.setItem('token', res.data.token)
                    // Rediriger vers la route sauvegardée ou par défaut
                    const redirectUrl = localStorage.getItem('redirectAfterLogin');
                    if (redirectUrl) {
                        // Forcer une redirection complète du navigateur
                        window.location.href = redirectUrl;
                        localStorage.removeItem('redirectAfterLogin');
                    } else {
                        //router.push('/');
                        window.location.href = "/"
                    }
                }
            }).catch(error => {
                if (error.response) {
                    if (error.response.status === 401) {
                        isLoader.value = false
                        isEmpty.value.email = true
                        isEmpty.value.password = true
                        msgInput.value.email = error.response.data.message
                        msgInput.value.password = error.response.data.message
                    } else {
                        console.error("Erreur du serveur :", error.response.data.message || "Veuillez réessayer plus tard.");
                    }
                }
            })
        }

    }

    const togglePwd = () => {
        showpwd.value = !showpwd.value
    }

</script>
<style lang="">

</style>

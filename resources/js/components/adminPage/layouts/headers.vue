<template>

    <!-- Header -->
        <div class="header header-one">
            <a href="index.html"  class="d-inline-flex d-sm-inline-flex align-items-center d-md-inline-flex d-lg-none align-items-center device-logo">
                    <img src="/assets/img/logo.png" class="img-fluid logo2" alt="Logo">
            </a>
            <div class="main-logo d-inline float-start d-lg-flex align-items-center d-none d-sm-none d-md-none">
                <div class="logo-white">
                    <a href="index.html">
                        <img src="/assets/img/logo-full-white.png" class="img-fluid logo-blue" alt="Logo">
                    </a>
                    <a href="index.html">
                        <img src="/assets/img/logo-small-white.png" class="img-fluid logo-small" alt="Logo">
                    </a>
                </div>
                <div class="logo-color">
                    <a href="index.html">
                        <img src="/assets/img/logo.png" class="img-fluid logo-blue" alt="Logo">
                    </a>
                    <a href="index.html">
                        <img src="/assets/img/logo-small.png" class="img-fluid logo-small" alt="Logo">
                    </a>
                </div>
            </div>
            <!-- Sidebar Toggle -->
            <a href="javascript:void(0);" id="toggle_btn">
                <span class="toggle-bars">
                    <span class="bar-icons"></span>
                    <span class="bar-icons"></span>
                    <span class="bar-icons"></span>
                    <span class="bar-icons"></span>
                </span>
            </a>
            <!-- /Sidebar Toggle -->

            <!-- Search -->
            <div class="top-nav-search">
                <form>
                    <input type="text" class="form-control" placeholder="Search here">
                    <button class="btn" type="submit"><img src="/assets/img/icons/search.svg" alt="img"></button>
                </form>
            </div>
            <!-- /Search -->

            <!-- Mobile Menu Toggle -->
            <a class="mobile_btn" id="mobile_btn">
                <i class="fas fa-bars"></i>
            </a>
            <!-- /Mobile Menu Toggle -->

            <!-- Header Menu -->
            <ul class="nav nav-tabs user-menu">

                <!-- Notifications -->
                <li class="nav-item dropdown flag-nav dropdown-heads">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button">
                        <i class="fe fe-bell"></i>
                        <span v-if="unreadCount > 0" class="badge rounded-pill bg-danger">{{ unreadCount }}</span>
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <div class="notification-title">Notifications</div>
                            <a href="javascript:void(0)" class="clear-noti d-flex align-items-center" @click="markAllAsRead">
                                Tout marquer comme lu <i class="fe fe-check-circle"></i>
                            </a>
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list" v-if="notifications.length > 0">
                                <li
                                    v-for="notif in notifications"
                                    :key="notif.id"
                                    class="notification-message"
                                >
                                    <a href="javascript:void(0)" @click="openNotificationModal(notif)">
                                        <div class="d-flex">
                                            <span class="avatar avatar-md" :class="{ active: !notif.read_at }">
                                                <i
                                                    class="fe fe-alert-triangle d-flex align-items-center justify-content-center h-100"
                                                    :class="notif.data.rupture ? 'text-danger' : 'text-warning'"
                                                ></i>
                                            </span>
                                            <div class="media-body">
                                                <p class="noti-details">
                                                    <span class="noti-title">{{ notif.data.title }}</span> — {{ notif.data.message }}
                                                </p>
                                                <p class="noti-time">
                                                    <span class="notification-time">{{ formatRelative(notif.created_at) }}</span>
                                                    <span v-if="!notif.read_at" class="badge bg-primary ms-2">Non lue</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                            <div v-else class="text-center text-muted py-4">
                                Aucune notification
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item  has-arrow dropdown-heads ">
                    <a href="javascript:void(0);" class="win-maximize">
                        <i class="fe fe-maximize"></i>
                    </a>
                </li>
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="user-link  nav-link" data-bs-toggle="dropdown">
                        <span class="user-img">
                            <img src="/assets/img/profiles/avatar-07.jpg" alt="img" class="profilesidebar">
                            <span class="animate-circle"></span>
                        </span>
                        <span class="user-content">
                            <span class="user-name">{{ currentUser.full_name }}</span>
                            <span class="user-details">{{ currentUser.role?.name }}</span>
                        </span>
                    </a>
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilemenu">
                            <div class="subscription-menu">
                                <ul>
                                    <li>
                                        <RouterLink class="dropdown-item" to="/profils">Profile</RouterLink>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="settings.html">Paramètre</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="subscription-logout">
                                <ul>
                                    <li class="pb-0">
                                        <a class="dropdown-item cursor-pointer" @click="LogoutFunction">Déconnexion</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- /User Menu -->

            </ul>

            <!-- /Header Menu -->

        </div>
    <!-- /Header -->

    <!-- Modal Détail Notification -->
    <div class="modal modal-top fade" id="notificationDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" v-if="selectedNotification">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">{{ selectedNotification.data.title }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ selectedNotification.data.message }}</p>

                    <div class="table-responsive mt-3" v-if="selectedNotification.data.nom">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Produit</th>
                                    <td>{{ selectedNotification.data.nom }}</td>
                                </tr>
                                <tr>
                                    <th>Code barre</th>
                                    <td>#{{ selectedNotification.data.code_barre }}</td>
                                </tr>
                                <tr>
                                    <th>Emplacement</th>
                                    <td class="text-capitalize">{{ selectedNotification.data.emplacement }}</td>
                                </tr>
                                <tr>
                                    <th>Stock actuel</th>
                                    <td>{{ selectedNotification.data.quantite }}</td>
                                </tr>
                                <tr>
                                    <th>Seuil d'alerte</th>
                                    <td>{{ selectedNotification.data.seuil_alerte }}</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        <span class="badge" :class="selectedNotification.data.rupture ? 'bg-danger' : 'bg-warning text-dark'">
                                            {{ selectedNotification.data.rupture ? 'Rupture de stock' : 'Seuil atteint' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-muted mt-3 mb-0">
                        <small>Reçue {{ formatRelative(selectedNotification.created_at) }}</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal">Fermer</button>
                    <button
                        v-if="selectedNotification.data.product_id"
                        class="btn btn-primary"
                        type="button"
                        @click="goToProduct"
                    >
                        Voir le produit
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>
<script setup>

    import { onBeforeUnmount, onMounted, ref, computed } from 'vue';
    import { getData, putData, postData } from '../../plugins/api';
    import { isAuthenticated } from '../../router';
    import { useRouter } from 'vue-router'


    const currentUser = ref({})
    const notifications = ref([])
    const unreadCount = ref(0)
    const selectedNotification = ref(null)
    const router = useRouter()



    let notificationModal
    let pollingInterval

    async function Userinfo() {
        currentUser.value = await isAuthenticated()
    }

    const productDetailsLink = computed(() => {
        if (!selectedNotification.value) return '/product'

        const isMagasin = selectedNotification.value.data.emplacement === 'magasin'

        return {
            path: isMagasin ? '/product-magasin' : '/product',
            query: { highlight: selectedNotification.value.data.product_id }
        }
    })

    function goToProduct() {
        notificationModal.hide()
        router.push(productDetailsLink.value)
    }

    async function AllNotificationsFunction() {
        await getData('/notifications').then(res => {
            if (res.status === 200) {
                notifications.value = res.data.notifications
                unreadCount.value   = res.data.unread_count
            }
        })
    }

    function openNotificationModal(notif) {
        selectedNotification.value = notif
        notificationModal.show()

        // Marquer comme lue à l'ouverture du modal
        if (!notif.read_at) {
            markAsRead(notif.id)
        }
    }

    async function markAsRead(id) {
        await putData(`/notifications/${id}/read`, {}).then(() => {
            const notif = notifications.value.find(n => n.id === id)
            if (notif && !notif.read_at) {
                notif.read_at = new Date().toISOString()
                unreadCount.value = Math.max(0, unreadCount.value - 1)
            }
        })
    }

    async function markAllAsRead() {
        await putData('/notifications/read-all', {}).then(() => {
            notifications.value.forEach(n => {
                if (!n.read_at) n.read_at = new Date().toISOString()
            })
            unreadCount.value = 0
        })
    }

    function formatRelative(dateStr) {
        const date = new Date(dateStr)
        const now  = new Date()
        const diffMs = now - date
        const diffMin = Math.floor(diffMs / 60000)

        if (diffMin < 1) return "à l'instant"
        if (diffMin < 60) return `il y a ${diffMin} min`
        const diffH = Math.floor(diffMin / 60)
        if (diffH < 24) return `il y a ${diffH}h`
        const diffJ = Math.floor(diffH / 24)
        if (diffJ === 1) return 'hier'
        if (diffJ < 7) return `il y a ${diffJ}j`

        return new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short' }).format(date)
    }

    async function LogoutFunction() {
        await postData('/logout',null).then(res=>{
            if (res.status === 200) {
                localStorage.removeItem('token')
                currentUser.value = {}
                window.location.href = '/login'
            }
        })
    }

    onMounted(()=>{
        Userinfo()
        AllNotificationsFunction()
        notificationModal = new bootstrap.Modal(document.getElementById('notificationDetailModal'))

        // Rafraîchit la liste toutes les 60 secondes pour voir les nouvelles alertes sans recharger la page
        pollingInterval = setInterval(AllNotificationsFunction, 60000)
    })

    onBeforeUnmount(()=>{
        if (pollingInterval) clearInterval(pollingInterval)
    })

</script>
<style lang="">

</style>


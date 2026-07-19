import { createRouter, createWebHistory } from "vue-router";
import { setCurrentUserPermissions, clearCurrentUserPermissions, canAccessPage } from "../plugins/permissions";
import axiosInstance from "../plugins/axios";

const routes = [
    {
        path:'/',
        meta: { requiresAuth: true },
        component: () => import('@/components/adminPage/layouts/contentWrapper.vue'),
        children:[
            {
                path: '',
                meta: { routeName: 'home' },
                component: () => import('@/components/adminPage/pages/home.vue'),
            },
            {
                path:'/client',
                meta: { routeName: 'client' },
                component: () => import('@/components/adminPage/pages/clients.vue'),
            },
            {
                path:'/category',
                meta: { routeName: 'category' },
                component: () => import('@/components/adminPage/pages/category.vue'),
            },
            {
                path:'/rayon',
                meta: { routeName: 'rayon' },
                component: () => import('@/components/adminPage/pages/rayon.vue'),
            },
            {
                path:'/product',
                meta: { routeName: 'product' },
                component: () => import('@/components/adminPage/pages/product.vue'),
            },
            {
                path:'/mouvement',
                meta: { routeName: 'mouvement' },
                component: () => import('@/components/adminPage/pages/mouvement.vue'),
            },
            {
                path:'/vente',
                meta: { routeName: 'vente' },
                component: () => import('@/components/adminPage/pages/vente.vue'),
            },
            {
                path:'/new-vente',
                meta: { routeName: 'vente' },
                component: () => import('@/components/adminPage/pages/newVente.vue'),
            },
            {
                path: '/edite-vente/:id?',
                meta: { routeName: 'vente' },
                component: () => import('@/components/adminPage/pages/newVente.vue'),
            },
            {
                path: '/details-vente/:id',
                meta: { routeName: 'vente' },
                component: () => import('@/components/adminPage/pages/detailVente.vue'),
            },
            {
                path: '/fournisseur',
                meta: { routeName: 'fournisseur' },
                component: () => import('@/components/adminPage/pages/fournisseur.vue'),
            },
            {
                path: '/approvisionnement',
                meta: { routeName: 'approvisionnement' },
                component: () => import('@/components/adminPage/pages/approvisionnement.vue'),
            },
            {
                path: '/product-magasin',
                meta: { routeName: 'product-magasin' },
                component: () => import('@/components/adminPage/pages/product-magasin.vue'),
            },
            {
                path: '/mouvement-magasin',
                meta: { routeName: 'mouvement-magasin' },
                component: () => import('@/components/adminPage/pages/mouvement-magasin.vue'),
            },
            {
                path: '/role',
                meta: { routeName: 'role' },
                component: () => import('@/components/adminPage/pages/role.vue'),
            },
            {
                path:'/user',
                meta: { routeName: 'user' },
                component: () => import('@/components/adminPage/pages/user.vue'),
            },
            {
                path:'/inventaire',
                meta: { routeName: 'inventaire' },
                component: () => import('@/components/adminPage/pages/inventaire.vue'),
            },
            {
                path:'/profils',
                component: ()=>import('@/components/adminPage/pages/profils.vue')
            }
        ]
    },
    {
        path:'/login',
        component: ()=>import('@/components/adminPage/pages/login.vue')
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
      if (to.hash) {
        return { el: to.hash, behavior: 'smooth', top: 0 };
      } else {
        return { top: 0 };
      }
    }
});

// 🔎 Vérification de l’authentification
export async function isAuthenticated() {
    try {
      const res = await axiosInstance.get('/user', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
      if (res.status === 200) {
        setCurrentUserPermissions(res.data.user);
        return res.data.user;
      }
    } catch (error) {
      console.error("Erreur lors de la vérification de l'authentification :", error);
      clearCurrentUserPermissions();
      return null;
    }
}

// 🧠 Middleware global
router.beforeEach(async (to, from, next) => {
    if (to.matched.some((record) => record.meta.requiresAuth)) {
      try {
        const auth = await isAuthenticated();
        const token = localStorage.getItem("token");

        if (auth && token) {

            const routeName = to.meta.routeName;

            if (routeName && !canAccessPage(routeName)) {
                if (to.path !== '/') {
                    next('/');
                } else {
                    next(false);
                }
            } else {
                next();
            }

            //next();

        } else {
          localStorage.setItem('redirectAfterLogin', to.fullPath);
          window.location.href = '/login';
        }
      } catch (error) {
        console.error("Erreur lors de la vérification de l'authentification :", error);
        next("/login");
      }
    } else {
      // Rediriger si déjà connecté et essaie d'aller sur /admins/login
      if (to.path === '/login') {
        const auth = await isAuthenticated();
        const token = localStorage.getItem("token");
        if (auth && token) {
          next('/');
        } else {
          next();
        }
      } else {
        next();
      }
    }
});

export default router;

import { createRouter, createWebHistory } from "vue-router";
import axiosInstance from "../plugins/axios";

const routes = [
    {
        path:'/',
        meta: { requiresAuth: true },
        component: () => import('@/components/adminPage/layouts/contentWrapper.vue'),
        children:[
            {
                path: '',
                component: () => import('@/components/adminPage/pages/home.vue'),
            },
            {
                path:'/client',
                component: () => import('@/components/adminPage/pages/clients.vue'),
            },
            {
                path:'/category',
                component: () => import('@/components/adminPage/pages/category.vue'),
            },
            {
                path:'/rayon',
                component: () => import('@/components/adminPage/pages/rayon.vue'),
            },
            {
                path:'/product',
                component: () => import('@/components/adminPage/pages/product.vue'),
            },
            {
                path:'/mouvement',
                component: () => import('@/components/adminPage/pages/mouvement.vue'),
            },
            {
                path:'/vente',
                component: () => import('@/components/adminPage/pages/vente.vue'),
            },
            {
                path:'/new-vente',
                component: () => import('@/components/adminPage/pages/newVente.vue'),
            },
            {
                path: '/edite-vente/:id?',
                component: () => import('@/components/adminPage/pages/newVente.vue'),
            },
            {
                path: '/details-vente/:id',
                component: () => import('@/components/adminPage/pages/detailVente.vue'),
            },
            {
                path: '/fournisseur',
                component: () => import('@/components/adminPage/pages/fournisseur.vue'),
            },
            {
                path: '/approvisionnement',
                component: () => import('@/components/adminPage/pages/approvisionnement.vue'),
            },
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
        return res.data.user;
      }
    } catch (error) {
      console.error("Erreur lors de la vérification de l'authentification :", error);
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

            // const role = auth?.role;

            // // Vérifier si la route est restreinte à certains rôles
            // if (to.meta.roles && !to.meta.roles.includes(role)) {
            //     next('/');  // Redirige vers l'accueil si rôle non autorisé
            // } else {
            //     next();
            // }

            next();

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

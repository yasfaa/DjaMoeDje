import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import login from '../views/Login.vue'
import adminDashboard from '../views/Admin/AdminDashboard.vue'
import editMenu from '../views/Admin/EditMenu.vue'
import adminProfile from '../views/Admin/AdminProfile.vue'
import adminMenu from '../views/Admin/AdminMenu.vue'
import detailMenu from '../views/User/DetailMenu.vue'
import profile from '../views/User/Profile.vue'
import cart from '../views/User/Cart.vue'
import customization from '../views/User/CartCustomization.vue'
import customize from '../views/User/MenuCustomization.vue'
import checkout from '../views/User/Checkout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/profile',
      name: 'Profile',
      component: profile,
    },
    {
      path: '/cart',
      name: 'cart',
      component: cart,
    },
    {
      path: '/customization/:id',
      name: 'customization',
      component: customization,
    },
    {
      path: '/menu/:menuId/customize',
      name: 'customize',
      component: customize,
    },
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/login',
      name: 'login',
      component: login
    },
    {
      path: '/admin/dashboard',
      name: 'Admin Dashboard',
      component: adminDashboard,
    },
    {
      path: '/admin/menu/:id',
      name: 'Admin Menu',
      component: adminMenu,
    },
    {
      path: '/admin/edit',
      name: 'Edit Menu',
      component: editMenu,
    },
    {
      path: '/menu/:id',
      name: 'Detail Menu',
      component: detailMenu,
    },
    {
      path: '/admin/profile',
      name: 'Admin PRofile',
      component: adminProfile,
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: checkout,
    },
  ]
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('token');
    if (token) {
      // User is authenticated, proceed to the route
      next();
    } else {
      // User is not authenticated, redirect to login
      next('/login');
    }
  } else {
    // Non-protected route, allow access
    next();
  }
});

export default router
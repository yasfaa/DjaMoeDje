import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import login from '../views/Login.vue'
import adminDashboard from '../views/Admin/AdminDashboard.vue'
import editMenu from '../views/Admin/EditMenu.vue'
import detailMenu from '../views/User/DetailMenu.vue'
import profile from '../views/User/Profile.vue'
import order from '../views/User/Cart.vue'

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
      component: order,
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
      path: '/admin/edit',
      name: 'Edit Menu',
      component: editMenu,
    },
    {
      path: '/menu/:id',
      name: 'Detail Menu',
      component: detailMenu,
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
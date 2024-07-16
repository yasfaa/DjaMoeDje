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
import order from '../views/User/Order.vue'
import orderDetail from '../views/User/OrderDetail.vue'
import adminOrder from '../views/Admin/AdminOrder.vue'
import adminOrderDetail from '../views/Admin/AdminOrderDetail.vue'

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
      name: 'Admin Profile',
      component: adminProfile,
    },
    {
      path: '/admin/order',
      name: 'Admin Order',
      component: adminOrder,
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: checkout,
    },
    {
      path: '/order',
      name: 'Order',
      component: order,
    },
    {
      path: '/orders/:id',
      name: 'Detail Order',
      component: orderDetail,
    },
    {
      path: '/admin/orders/:id',
      name: 'Admin Detail Order',
      component: adminOrderDetail,
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
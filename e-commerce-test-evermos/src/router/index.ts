import { createRouter, createWebHistory } from 'vue-router'
import homePage from '../views/homePage/Index.vue'
import checkoutPage from '../views/checkoutPage/Index.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: homePage
    },
    {
      path: '/checkout',
      name: 'checkout',
      component: checkoutPage,
      meta: { transition: 'slide-left' },
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue')
    }
  ]
})

export default router
// meta: { transition: 'slide-left' },
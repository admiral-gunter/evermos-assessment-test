<template>
    <div class="app-bar">
        <div class="title">My Shop</div>
        <router-link to="checkout" class="checkout-icon" style="text-decoration: none; color: inherit;">
            <!-- <div class="checkout-icon" @click=""> -->
            <span class="material-icons">shopping_cart</span>
            <span class="item-count">{{ ct.count }}</span>
            <!-- </div> -->
        </router-link>
    </div>
</template>

<script lang="ts" setup>
import { useCounterStore } from '@/stores/counter';


const ct = useCounterStore()
const cartCnt = localStorage.getItem('cart_cnt');

if (cartCnt) {
    ct.count = Number(cartCnt)
}

</script>
  
<style>
.app-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #3f51b5;
    /* Material Design color */
    padding: 16px;
    color: white;
}

.title {
    font-size: 24px;
}

.checkout-icon {
    position: relative;
    cursor: pointer;
}

.item-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 8px;
    font-size: 12px;
}
</style>
  
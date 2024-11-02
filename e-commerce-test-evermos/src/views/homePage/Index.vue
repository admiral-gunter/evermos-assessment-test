<script setup lang="ts">
import { ref } from 'vue';
import AppBar from '../../components/AppBar.vue'
import { useCounterStore } from '@/stores/counter';
//@ts-ignore
import { useSnackbar } from "vue3-snackbar";


interface Product {
    id: number;
    name: string;
    price: number;
    image_url: string;
}

const products = ref<Product[]>([]);


const snackbar = useSnackbar();

const ct = useCounterStore()

const addToCartBtn = (productId: number) => {
    // localStoragese
    const cartCnt = localStorage.getItem('cart_cnt');
    const cartProductIds = localStorage.getItem('cart_product_ids');

    if (!cartCnt) {
        localStorage.setItem('cart_cnt', '1');

    } else {
        const totCnt = Number(cartCnt) + Number(1)
        localStorage.setItem('cart_cnt', totCnt.toString());
    }


    if (!cartProductIds) {
        localStorage.setItem('cart_product_ids', `${productId}`);

    } else {
        localStorage.setItem('cart_product_ids', `${cartProductIds},${productId}`);
    }


    ct.increment()
    snackbar.add({
        type: 'success',
        text: 'This item added to your cart!'
    })
}

const URL = import.meta.env.VITE_API_URL + 'products'

fetch(URL)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        response.json().then((resp) => {
            if (resp['error']) {
                snackbar.add({
                    type: 'error',
                    text: resp['message']
                })

                return;
            }

            products.value = resp['data'];
        })
    })
    .catch(error => {
        snackbar.add({
            type: 'error',
            text: 'ERROR ' + error
        })
    });

</script>

<template>
    <AppBar />

    <div class="container">
        <h1>Product List</h1>
        <div class="product-grid">
            <div class="product-card" v-for="product in products" :key="product.id">
                <img :src="product.image_url" :alt="product.name">
                <h2>{{ product.name }}</h2>
                <p>${{ product.price }}</p>
                <button @click="addToCartBtn(product.id)">Add to Cart</button>
            </div>
        </div>
    </div>
    <vue3-snackbar bottom right :duration="4000"></vue3-snackbar>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

.container {
    margin: auto;
    padding: 20px;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

.product-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.product-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    width: 200px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.product-card img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}

.product-card h2 {
    font-size: 1.2em;
    margin: 10px 0;
}

.product-card p {
    color: #333;
}

.product-card button {
    padding: 10px 15px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.product-card button:hover {
    background-color: #218838;
}

@media (max-width: 768px) {
    .product-card {
        width: 100%;
        max-width: 300px;
    }
}
</style>

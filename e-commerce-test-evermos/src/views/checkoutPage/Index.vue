<template>
    <div>

        <h2>Your Cart</h2>
        <div v-if="cart.length > 0">
            <ul class="cart-list">
                <li v-for="(item, index) in cart" :key="index" class="cart-card">
                    <div>
                        <strong>{{ item.name }}</strong> - Quantity:
                        <input type="number" v-model.number="item.quantity" min="1" class="quantity-input"
                            @change="(e) => editQtyProduct(item.id, item.quantity, index, e)" />
                        <p>Price: ${{ item.price }} x {{ item.quantity }} = ${{ Number(item.price *
                            item.quantity).toFixed(2) }}</p>
                        <button @click="removeFromCart(index)">Remove</button>
                    </div>
                </li>
            </ul>
            <p style="margin-top: 1%; margin-bottom: 1%;">Total: ${{ total }}</p>
            <button style="
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;" @click="checkout">Checkout</button>
        </div>
        <div v-else>
            <p>Your cart is empty.</p>
        </div>



    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
//@ts-ignore
import { useSnackbar } from "vue3-snackbar";
import Swal from "sweetalert2";

const showModal = ref(false);
const snackbar = useSnackbar()

interface Product {
    id: number
    name: string
    description: string
    price: number
    quantity: number
}

const editQtyProduct = (id: number, qty: number, index: number, e: any) => {
    cart.value[index]['quantity'] = e.target.value

    var cartProductIds = localStorage.getItem('cart_product_ids')

    var itemQty = cartProductIds;

    if (!itemQty) return;

    var items = itemQty.split(",");
    var countToRemove = id;

    var QTY = 0

    cart.value.forEach((item: any) => {
        QTY += Number(item['quantity'])
    });



    if (QTY < items.length) {
        for (let i = 0; i < items.length; i++) {
            const element = items[i];

            if (id.toString() === element.toString()) {
                items.splice(i, 1);
            }

        }

        var updatedItemQty = items.join(",");
        localStorage.setItem('cart_product_ids', updatedItemQty)

        var countTotalCart = updatedItemQty.split(',').length.toString();

        localStorage.setItem('cart_cnt', countTotalCart)


    } else {
        let difference = QTY - items.length

        for (let i = 0; i < difference; i++) {

            cartProductIds += "," + id


        }
        if (!cartProductIds) return;

        var updatedItemQty = items.join(",");
        localStorage.setItem('cart_product_ids', cartProductIds)

        var countTotalCart = cartProductIds.split(',').length.toString();
        localStorage.setItem('cart_cnt', countTotalCart)
    }


}

const removeFromCart = (index: number) => {

    var cartProdIds = localStorage.getItem('cart_product_ids')
    if (!cartProdIds) return;

    var array = cartProdIds.split(",");
    var filteredArray = array.filter(item => item.toString() !== cart.value[index]["id"].toString());
    var result = filteredArray.join(",");

    localStorage.setItem('cart_product_ids', result)
    localStorage.setItem('cart_cnt', filteredArray.length.toString())

    cart.value.splice(index, 1)
}

const checkout = () => {
    type LoginFormResult = {
        username: string
        password: string
    }

    let usernameInput: HTMLInputElement
    let passwordInput: HTMLInputElement

    Swal.fire<LoginFormResult>({
        title: 'Please Fill the Form Below',
        html: `
    <input type="text" id="name" class="swal2-input" placeholder="Username">

    <input type="email" id="email" class="swal2-input" placeholder="Email">

    <input type="text" id="post_code" class="swal2-input" placeholder="Post Code">
`,
        confirmButtonText: 'Sign in',
        focusConfirm: false,
        didOpen: () => {
            const popup = Swal.getPopup()!
            var name = popup.querySelector('#name') as HTMLInputElement
            var email = popup.querySelector('#email') as HTMLInputElement
            var post_code = popup.querySelector('#post_code') as HTMLInputElement
        },
        preConfirm: () => {
            const popup = Swal.getPopup()!

            var name = popup.querySelector('#name') as HTMLInputElement
            var email = popup.querySelector('#email') as HTMLInputElement
            var post_code = popup.querySelector('#post_code') as HTMLInputElement
            if (!name.value || !email.value || !post_code.value) {
                Swal.showValidationMessage(`Please enter all required fields!`)
            }
            return { name, email }
        },
    }).then((value) => {
        const popup = Swal.getPopup()!
        var name = popup.querySelector('#name') as HTMLInputElement
        var email = popup.querySelector('#email') as HTMLInputElement
        var post_code = popup.querySelector('#post_code') as HTMLInputElement


        const URL = import.meta.env.VITE_API_URL + 'checkout';


        fetch(URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: name.value,
                email: email.value,
                post_code: post_code.value,
                cart_items: cart.value
            })
        })
            .then(response => response.json())
            .then(data => {
                // console.log('Success:', data);
                if (data['error']) {
                    Swal.fire({
                        title: "Error!",
                        text: `ERROR : ${data['message']}`,
                        icon: "error"
                    });
                } else {
                    Swal.fire({
                        title: "Success!",
                        text: "Success! 🎉 Your order has been placed. ",
                        icon: "success"
                    });
                    cart.value = []

                    localStorage.removeItem('cart_product_ids')
                    localStorage.removeItem('cart_cnt')
                }

            })
            .catch(error => console.error('Error:', error));

    })

    return;
}

const total = computed(() => {
    return Number(cart.value.reduce((sum: number, item: Product) => sum + item.price * item.quantity, 0)).toFixed(2)
})

const cart = ref<Product[]>([]);  // Initialize cart as an empty array

var productId = localStorage.getItem('cart_product_ids')

if (productId) {
    let productArray = productId.split(',');

    let countResult = productArray.reduce((acc: any, item) => {
        acc[item] = (acc[item] || 0) + 1;
        return acc;
    }, {});


    let uniqueProductIds = [...new Set(productId.split(','))].join(',');

    const URL = import.meta.env.VITE_API_URL + 'cart';

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ productIds: uniqueProductIds })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(resp => {
            if (resp['error']) {
                snackbar.add({
                    type: 'error',
                    text: resp['message']
                });

                return;
            }
            for (const key of Object.keys(countResult)) {
                for (let i = 0; i < resp['data'].length; i++) {
                    const element = resp['data'][i];
                    if (key == element['id']) {
                        element['quantity'] = countResult[key];
                    }

                }
            }


            cart.value = resp['data'];
        })
        .catch(error => {
            snackbar.add({
                type: 'error',
                text: 'ERROR ' + error
            });
        });

}


</script>

<style scoped>
h1,
h2 {
    color: #333;
}

ul {
    list-style-type: none;
    padding: 0;
}

.product-list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.product-card {
    background-color: #f9f9f9;
    border-radius: 12px;
    padding: 20px;
    width: 300px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-card button {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 8px;
    cursor: pointer;
}

.product-card button:hover {
    background-color: #2980b9;
}

/* Cart styling */
.cart-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-card {
    background-color: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cart-card button {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
}

.cart-card button:hover {
    background-color: #c0392b;
}

/* Quantity Input styling */
.quantity-input {
    width: 50px;
    margin-left: 10px;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
</style>
  
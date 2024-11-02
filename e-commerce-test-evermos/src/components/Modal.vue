<template>
    <div v-if="isVisible" class="modal-overlay" @click.self="close">
        <div class="modal-content">
            <button class="close-btn" @click="close">X</button>
            <slot></slot>
        </div>
    </div>
</template>
  
<script setup lang="ts">
import { defineProps, ref } from 'vue';

const props = defineProps<{
    isVisible: boolean;
    onClose: () => void;
}>();


const close = () => {
    props.onClose();
};
</script>
  
<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
}

.close-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>
  
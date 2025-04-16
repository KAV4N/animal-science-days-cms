<template>
    
    <div class="p-5 flex-grow-1">
        <router-view />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
name: 'DashboardView',
});
</script>
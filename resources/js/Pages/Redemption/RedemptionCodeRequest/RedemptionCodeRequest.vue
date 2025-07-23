<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { InputText, Button, Select, DatePicker, MultiSelect } from 'primevue';
import RequestForm from '@/Pages/Redemption/RedemptionCodeRequest/Partials/RequestForm.vue';
import RequestListing from '@/Pages/Redemption/RedemptionCodeRequest/Partials/RequestListing.vue';

const props = defineProps({
    products: Array,
})

</script>

<template>
    <AuthenticatedLayout :title="$t('public.redemption_code_request')">
        <RequestForm :products="products" />
        <RequestListing />
    </AuthenticatedLayout>
</template>

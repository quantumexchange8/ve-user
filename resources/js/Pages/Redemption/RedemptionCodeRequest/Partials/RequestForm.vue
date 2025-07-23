<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { InputText, Button, Select, DatePicker, MultiSelect } from 'primevue';

const props = defineProps({
    products: Array,
})

const form = useForm({
    name: '',
    meta_login: '',
    product_ids: [],
})

function submitForm() {
    form.product_ids = selectedProducts.value.map(product => product.value);

    form.post('/redemption/requestRedemptionCode', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedProducts.value = [];
        },
    });
}

const licenses = ref(props.products)
const selectedProducts = ref([]);

</script>

<template>
    <div class="flex flex-col items-center justify-center p-5">
        <div class="flex flex-col justify-center gap-5">
            <div class="flex flex-col justify-center gap-1">
                <span class="font-semibold text-lg">{{ $t('public.redemption_code_request') }}</span>
                <span class="font-medium text-sm">{{ $t('public.redemption_code_request_condition') }}</span>
            </div>
            <div class="flex flex-col justify-center gap-3">
                <span class="font-medium">{{ $t('public.redemption_code_request_form') }}</span>

                <form @submit.prevent="submitForm" novalidate class="flex flex-col justify-center gap-3">

                    <!-- Name -->
                    <div class="flex flex-col justify-center gap-1">
                        <InputLabel for="name" :value="$t('public.name')" :invalid="!!form.errors.name" />
                        <InputText
                            id="name"
                            v-model="form.name"
                            :placeholder="$t('public.enter_name')"
                            class="w-full"
                            :invalid="!!form.errors.name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Meta Login -->
                    <div class="flex flex-col justify-center gap-1">
                        <InputLabel for="meta_login" :value="$t('public.meta_login')" :invalid="!!form.errors.meta_login" />
                        <InputText
                            id="meta_login"
                            v-model="form.meta_login"
                            :placeholder="$t('public.enter_meta_login')"
                            class="w-full"
                            :invalid="!!form.errors.meta_login"
                        />
                        <InputError :message="form.errors.meta_login" />
                    </div>

                    <!-- Product -->
                    <div class="flex flex-col justify-center gap-1">
                        <InputLabel for="products" value="Products" :invalid="!!form.errors.product_ids" />
                        <MultiSelect
                            v-model="selectedProducts"
                            :options="licenses"
                            class="w-full"
                            :placeholder="$t('public.select_product')"
                            :maxSelectedLabels="1"
                            :selectedItemsLabel="`${selectedProducts?.length} ${$t('public.products_selected')}`"
                            :invalid="!!form.errors.product_ids"
                        >
                            <template #option="{option}">
                                <div class="flex flex-col">
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                            <template #value>
                                <div v-if="selectedProducts?.length === 1">
                                    <span>{{ selectedProducts[0].label }}</span>
                                </div>
                                <span v-else-if="selectedProducts?.length > 1">
                                    {{ selectedProducts?.length }} {{ $t('public.products_selected') }}
                                </span>
                                <span v-else class="text-gray-400">
                                    {{ $t('public.select_product') }}
                                </span>
                            </template>
                        </MultiSelect>
                        <InputError :message="form.errors.product_ids" />
                    </div>

                    <!-- Submit Button -->
                    <Button
                        :label="$t('public.submit')"
                        type="submit"
                        class="w-fit ml-auto"
                        :disabled="form.processing"
                    />
                </form>
            </div>
        </div>
    </div>
</template>

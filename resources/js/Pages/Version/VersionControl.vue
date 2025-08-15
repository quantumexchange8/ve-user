<script setup>
import { onMounted, ref, watch, watchEffect } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
  Accordion,
  AccordionPanel,
  AccordionHeader,
  AccordionContent,
  DataTable,
  Column,
  Button,
} from "primevue";
import { IconDownload } from '@tabler/icons-vue'
import { usePage, useForm, router } from "@inertiajs/vue3";

const openedPanels = ref([])
const data = ref([])

const getResults = async () => {
    try {
        const response = await fetch(route('version.getVersionHistory'))
        const results = await response.json()
        data.value = results.data
    } catch (error) {
        console.error('Error fetching version history:', error)
    }
}

onMounted(() => {
    getResults();
});

watch(() => usePage().props.toast, (toast) => {
        if (toast !== null) {
            getResults();
        }
    }
);

const downloadFile = (url) => {
    const filename = url.split('/').pop() || 'file'

    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

</script>

<template>
    <AuthenticatedLayout :title="$t('public.version_control')">
        <Accordion v-model:activeIndex="openedPanels" multiple>
            <AccordionPanel v-for="product in data" :key="product.id" :value="product.id">
                <AccordionHeader class="border-b">{{ product.name }}</AccordionHeader>
                <AccordionContent>
                    <DataTable :value="product.version_controls">
                        <Column field="version" :header="$t('public.version')" />
                        <Column field="remarks" :header="$t('public.remarks')" />
                        <Column :header="$t('public.download')">
                            <template #body="{ data }">
                                <Button
                                    rounded
                                    severity="secondary"
                                    icon="IconDownload"
                                    @click="downloadFile(data.download_url)"
                                    >
                                    <IconDownload class="w-5 h-5" />
                                </Button>
                            </template>
                        </Column>
                    </DataTable>
                </AccordionContent>
            </AccordionPanel>
        </Accordion>
    </AuthenticatedLayout>
</template>

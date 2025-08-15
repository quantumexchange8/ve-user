<script setup>
import {onMounted, ref, watch, watchEffect} from "vue";
import {CalendarIcon, ClockRewindIcon} from "@/Components/Icons/outline.jsx";
import { generalFormat } from "@/Composables/format.js";
import debounce from "lodash/debounce.js";
import { usePage, useForm, router} from "@inertiajs/vue3";
import dayjs from "dayjs";
import {
    IconCircleXFilled,
    IconSearch,
    IconX,
    IconAdjustments,
    IconAdjustmentsHorizontal,
    IconCloudDownload,
} from "@tabler/icons-vue";
import Empty from "@/Components/Empty.vue";
import {
    Button,
    Column,
    DataTable,
    Tag,
    InputText,
    Select,
    MultiSelect,
    DatePicker,
    ColumnGroup,
    Row,
    ProgressSpinner,
    Popover,
    RadioButton,
    Dialog,
    IconField,
} from "primevue";
import { FilterMatchMode } from '@primevue/core/api';
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const isLoading = ref(false);
const dt = ref(null);
const codeListing = ref();
const { formatRgbaColor, formatAmount, formatDateTime, formatNameLabel, formatSeverity } = generalFormat();
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
  end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
  status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

// const initialLoaded = ref(true);

watch(filters, debounce(() => {
    // if (initialLoaded.value) {
    //     initialLoaded.value = false;
    //     return;
    // }

    const { start_date, end_date } = filters.value;
    const start = start_date?.value;
    const end = end_date?.value;

    // Skip if only one date is filled
    if ((start && !end) || (!start && end)) return;

    first.value = 0;
    loadLazyData();
}, 300), { deep: true });

const abortController = ref(null);
const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    // Abort previous request
    if (abortController.value) {
        abortController.value.abort();
    }

    // Create new controller
    abortController.value = new AbortController();

    lazyParams.value = {
        ...lazyParams.value,
        first: event?.first || first.value,
        filters: filters.value,
    };

    setTimeout(async () => {
        try {
            const params = {
                page: JSON.stringify(event?.page + 1),
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value),
            };

            const url = route('redemption.getRedemptionCodeListing', params);

            const response = await fetch(url, {
                signal: abortController.value.signal,
            });

            const results = await response.json();
            codeListing.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Previous request aborted');
            } else {
                console.error('Fetch error:', error);
                codeListing.value = [];
                totalRecords.value = 0;
            }
        } finally {
            isLoading.value = false;
        }
    }, 100);
};

const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};

const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};

const onFilter = (event) => {
    lazyParams.value.filters = filters.value;
    loadLazyData(event);
};

onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    loadLazyData();
});

const selectedDate = ref([]);

const clearDate = () => {
    selectedDate.value = [];
};

watch(selectedDate, (newDateRange) => {
    if(Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange; 
        filters.value['start_date'].value = startDate;
        filters.value['end_date'].value = endDate;

        if(startDate !== null && endDate !== null){
            // loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
});

//filter status
const status = ref(['valid', 'redeemed']);
const statusValue = ref(filters.value.status.value);

watch(statusValue, (val) => {
    filters.value['status'].value = val;
});

const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
}

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

const clearFilter = () => {
    filters.value['global'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
    filters.value['status'].value = null;
    selectedDate.value = [];
};

watch(() => usePage().props.toast, (toast) => {
        if (toast !== null) {
            first.value = 0;
            loadLazyData();
        }
    }
);

// const rowClicked = async (data) => {
//     if (!data?.serial_number) return;

//     try {
//         await navigator.clipboard.writeText(data.serial_number);
//     } catch (err) {
//         console.error('Failed to copy serial number', err);
//     }
// };

</script>

<template>
    <AuthenticatedLayout :title="$t('public.redemption_code_listing')">
        <div class="flex flex-col items-center px-4 py-6 gap-5 self-stretch rounded-2xl border border-gray-200 bg-white shadow-table md:px-6 md:gap-5">
            <DataTable
                v-model:first="first"
                v-model:filters="filters"
                :value="codeListing"
                :rowsPerPageOptions="[10, 20, 50, 100]"
                lazy
                :paginator="codeListing?.length > 0"
                removableSort
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                :currentPageReportTemplate="$t('public.paginator_caption')"
                :rows="10"
                ref="dt"
                dataKey="id"
                selectionMode="single"
                :totalRecords="totalRecords"
                :loading="isLoading"
                @page="onPage($event)"
                @sort="onSort($event)"
                @filter="onFilter($event)"
            >
                <template #header>
                    <div class="flex flex-col md:flex-row gap-3 items-center self-stretch pb-3 md:pb-5">
                        <div class="relative w-full md:w-60">
                            <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                                <IconSearch size="20" stroke-width="1.25" />
                            </div>
                            <InputText v-model="filters['global'].value" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full md:w-60" />
                            <div
                                v-if="filters['global'].value !== null && filters['global'].value !== ''"
                                class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                                @click="clearFilterGlobal"
                            >
                                <IconCircleXFilled size="16" />
                            </div>
                        </div>
                        <div class="w-full flex flex-col gap-3 md:flex-row">
                            <div class="w-full md:w-[272px]">
                                <!-- <DatePicker
                                    v-model="selectedDate"
                                    selectionMode="range"
                                    :manualInput="false"
                                    :minDate="minDate"
                                    :maxDate="maxDate"
                                    dateFormat="dd/mm/yy"
                                    showIcon
                                    iconDisplay="input"
                                    placeholder="yyyy/mm/dd - yyyy/mm/dd"
                                    class="w-full md:w-[272px]"
                                />
                                <div
                                    v-if="selectedDate && selectedDate.length > 0"
                                    class="absolute top-2/4 -mt-2.5 right-4 text-gray-400 select-none cursor-pointer bg-white"
                                    @click="clearDate"
                                >
                                    <IconX size="20" />
                                </div> -->
                                <Button
                                    type="button"
                                    severity="secondary"
                                    rounded
                                    @click="toggle"
                                    class="flex gap-3 items-center justify-center py-3 w-full md:w-[130px]"
                                >
                                    <IconAdjustments size="20" color="#0C111D" stroke-width="1.25" />
                                    <div class="text-sm text-gray-950 font-medium">
                                        {{ $t('public.filter') }}
                                    </div>
                                </Button>
                            </div>
                            <!-- <div class="w-full flex flex-col md:flex-row justify-end gap-2">
                                <Button
                                    v-if="selectedFiles?.length > 0"
                                    variant="primary-flat"
                                    :disabled="selectedFiles?.length === 0"
                                    @click="openDialog(null, 'update');"
                                >
                                    {{ $t('public.update_status') }}
                                </Button>

                                <Button
                                    @click="openDialog(null, 'export');"
                                    class="w-full md:w-auto"
                                >
                                    {{ $t('public.export') }}
                                </Button>
                            </div> -->
                        </div>
                    </div>
                </template>
                <template #empty>
                    <Empty
                        :title="$t('public.empty_redemption_code_listing_title')"
                        :message="$t('public.empty_redemption_code_listing_message')"
                    />
                </template>
                <template #loading>
                    <div class="flex flex-col gap-2 items-center justify-center">
                        <ProgressSpinner strokeWidth="4" />
                        <span class="text-sm text-gray-700">{{ $t('public.loading_data_caption') }}</span>
                    </div>
                </template>
                <template v-if="codeListing?.length > 0">
                    <Column
                        field="created_at"
                        sortable
                        class="table-cell min-w-36"
                        :header="$t('public.date')"
                        headerClass="text-nowrap"
                    >
                        <template #body="{ data }">
                            <div>
                                <div>
                                    {{ data.created_at ? dayjs(data.created_at).format('YYYY-MM-DD') : '-' }}
                                </div>
                                <div class="text-xs text-surface-500">
                                    {{ data.created_at ? dayjs(data.created_at).format('HH:mm:ss') : '' }}
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="acc_name" :header="$t('public.name')" headerClass="text-nowrap" />
                    <Column field="meta_login" :header="$t('public.meta_login')" headerClass="text-nowrap" />
                    <Column field="product_name" :header="$t('public.product')" />
                    <Column
                        field="expired_date"
                        sortable
                        class="table-cell min-w-36"
                        :header="$t('public.expired_date')"
                    >
                        <template #body="{data}">
                            {{ data.expired_date ? dayjs(data.expired_date).format('YYYY-MM-DD') : '-' }}
                        </template>
                    </Column>
                    <Column field="status" :header="$t('public.status')" headerClass="text-nowrap" bodyClass="text-nowrap">
                        <template #body="{ data }">
                            <Tag :value="$t(`public.${data.status}`)" :severity="formatSeverity(data.status)" />
                        </template>
                    </Column>
                    <Column field="indicator" :header="$t('public.indicators_ea')">
                        <template #body="{ data }">
                            <template v-if="data.status === 'redeemed'">
                                <a 
                                    :href="data.indicator" 
                                    target="_blank" 
                                    rel="noopener noreferrer" 
                                    class="text-blue-500 underline"
                                >
                                    {{ data.indicator }}
                                </a>
                            </template>
                            <template v-else>
                                {{ data.indicator }}
                            </template>
                        </template>
                    </Column>
                    <Column field="serial_number" :header="$t('public.serial_number')" />
                </template>
            </DataTable>

            <Popover ref="op">
                <div class="flex flex-col gap-6 w-72">
                    <!-- Filter Date -->
                    <div class="flex flex-col gap-2 items-center self-stretch">
                        <div class="flex self-stretch text-sm text-surface-950">
                            {{ $t('public.filter_by_date') }}
                        </div>
                        <div class="relative w-full">
                            <DatePicker
                                v-model="selectedDate"
                                dateFormat="dd/mm/yy"
                                selectionMode="range"
                                placeholder="dd/mm/yyyy - dd/mm/yyyy"
                                class="w-full"
                            />
                            <div
                                v-if="selectedDate && selectedDate.length > 0"
                                class="absolute top-2/4 -mt-2 right-2 text-surface-400 select-none cursor-pointer bg-transparent"
                                @click="clearDate"
                            >
                                <IconX :size="15" strokeWidth="1.5"/>
                            </div>
                        </div>
                    </div>

                    <!-- Filter status -->
                    <div class="flex flex-col gap-2 items-center self-stretch">
                        <div class="flex self-stretch text-xs text-surface-950 font-semibold">
                            {{ $t('public.filter_by_status') }}
                        </div>
                        <div class="flex flex-col items-start w-full gap-1">
                            <div
                                v-for="option in status"
                                class="flex items-center gap-2 text-sm"
                            >
                                <RadioButton
                                    v-model="filters['status'].value"
                                    :inputId="option"
                                    :name="option"
                                    :value="option"
                                />
                                <label :for="option">{{ $t(`public.${option}`) }}</label>
                            </div>
                        </div>
                    </div>

                    <Button
                        type="button"
                        severity="info"
                        class="w-full"
                        @click="clearFilter()"
                        :label="$t('public.clear_all')"
                    />
                </div>
            </Popover>
        </div>
    </AuthenticatedLayout>
</template>
    
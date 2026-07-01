<script setup>
import { computed, ref } from 'vue'
import { ModelListSelect } from "vue-search-select"
import "vue-search-select/dist/VueSearchSelect.css"
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'

const toast = useToast()
const { t } = useI18n()

const props = defineProps({
  show: Boolean,
  client: Array,
  cars: Array, // selected cars preview
  formData: Object, // { pay_price_total, paid_amount_total, client_id, client_name, client_phone, note_pay, car_ids[] }
})

const emit = defineEmits(['close', 'submit'])

const showClient = ref(false)

const carsCount = computed(() => (props.cars || []).length)

const totalCost = computed(() => {
  return (props.cars || []).reduce((sum, c) => {
    const cost = (Number(c.purchase_price || 0)
      + Number(c.erbil_exp || 0)
      + Number(c.erbil_shipping || 0)
      + Number(c.dubai_exp || 0)
      + Number(c.dubai_shipping || 0))
    return sum + cost
  }, 0)
})

const remainingDebt = computed(() => {
  const sale = Number(props.formData?.pay_price_total || 0)
  const paid = Number(props.formData?.paid_amount_total || 0)
  return Math.max(0, Math.round(sale - paid))
})

const handlePaidChange = () => {
  const sale = Number(props.formData?.pay_price_total || 0)
  const paid = Number(props.formData?.paid_amount_total || 0)
  if (sale > 0 && paid > sale) {
    toast.warning(t('paid_exceeds_sale_warning') + ` (${Math.round(sale).toLocaleString()}).`)
    props.formData.paid_amount_total = sale
  }
}

const submit = () => {
  if (!carsCount.value) {
    toast.error(t('select_cars_first'))
    return
  }
  const sale = Number(props.formData?.pay_price_total || 0)
  if (sale <= 0) {
    toast.error(t('enter_valid_sale_price'))
    return
  }
  const cid = Number(props.formData?.client_id || 0)
  if (!showClient.value && cid <= 0) {
    toast.error(t('select_client'))
    return
  }
  if (showClient.value && !(props.formData?.client_name || '').trim()) {
    toast.error(t('enter_client_name_short'))
    return
  }
  emit('submit', props.formData)
}
</script>

<template>
  <Transition name="modal">
    <div v-if="show" class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container dark:bg-gray-900">
          <div class="modal-header">
            <slot name="header"></slot>
          </div>

          <div class="modal-body">
            <h2 class="text-center dark:text-gray-200 text-lg font-bold">
              {{ $t('group_sale_title') }} ({{ carsCount }})
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
              <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('cars_count') }}</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ carsCount }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('total_cost_approx') }}</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">${{ Math.round(totalCost).toLocaleString() }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('client_debt') }}</div>
                <div class="text-lg font-bold text-red-600 dark:text-red-400">${{ remainingDebt.toLocaleString() }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
              <div>
                <label class="dark:text-gray-200">{{ $t('sale_price_total') }}</label>
                <input
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.pay_price_total"
                />
              </div>
              <div>
                <label class="dark:text-gray-200">{{ $t('paid_amount_total') }}</label>
                <input
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.paid_amount_total"
                  @input="handlePaidChange"
                  :max="formData.pay_price_total || null"
                />
              </div>
            </div>

            <div class="mt-4">
              <label class="dark:text-gray-200">{{ $t('customer') }}</label>
              <div class="relative">
                <ModelListSelect
                  v-if="!showClient"
                  optionValue="id"
                  optionText="name"
                  v-model="formData.client_id"
                  :list="client"
                  :placeholder="$t('selectCustomer')"
                />
                <button
                  type="button"
                  @click="showClient=true;formData.client_id=0;formData.client_name='';formData.client_phone=''"
                  v-if="!showClient"
                  class="absolute left-0 top-0 h-full px-3 py-2 font-bold text-white bg-green-500 rounded-tl-lg rounded-bl-lg"
                >
                  {{ $t('addCustomer') }}
                </button>
              </div>

              <div v-if="showClient" class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                <div>
                  <label class="dark:text-gray-200">{{ $t('client_name') }}</label>
                  <input
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="formData.client_name"
                  />
                </div>
                <div>
                  <label class="dark:text-gray-200">{{ $t('phone') }}</label>
                  <input
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="formData.client_phone"
                  />
                </div>
                <div class="md:col-span-2">
                  <button
                    type="button"
                    @click="showClient=false"
                    class="px-3 py-2 text-sm font-bold text-white bg-pink-500 rounded"
                  >
                    {{ $t('selectCustomer') }}
                  </button>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <label class="dark:text-gray-200">{{ $t('note') }}</label>
              <input
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model="formData.note_pay"
              />
            </div>

            <div class="mt-4">
              <details class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                <summary class="cursor-pointer text-sm text-gray-700 dark:text-gray-200">{{ $t('show_selected_cars') }}</summary>
                <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                  <div v-for="c in (cars || [])" :key="c.id" class="py-1 border-b border-gray-200 dark:border-gray-700">
                    #{{ c.no }} — {{ c.name }} {{ c.pin ? '(' + c.pin + ')' : '' }}
                  </div>
                </div>
              </details>
            </div>
          </div>

          <div class="modal-footer">
            <button class="modal-default-button bg-gray-400" @click="emit('close')">
              {{ $t('close') }}
            </button>
            <button class="modal-default-button bg-blue-600" @click="submit">
              {{ $t('save') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: table;
  transition: opacity 0.3s ease;
}
.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}
.modal-container {
  width: 95%;
  max-width: 800px;
  margin: 0px auto;
  padding: 20px 30px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  font-family: Helvetica, Arial, sans-serif;
}
.modal-header h3 {
  margin-top: 0;
  color: #42b983;
}
.modal-body {
  margin: 10px 0;
}
.modal-footer {
  margin-top: 12px;
  text-align: right;
}
.modal-default-button {
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 700;
  margin-left: 8px;
}
</style>


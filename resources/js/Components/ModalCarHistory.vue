<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  formData: Object,
});

let carHistory = ref([]); // Store car history

// Watch for changes in the `show` prop and fetch history when the modal opens
watch(
  () => props.show,
  async (newValue) => {
    if (newValue && props.formData?.id) {
      await fetchCarHistory(props.formData.id);
    }
  }
);

// Fetch car history from the API
const fetchCarHistory = async (carId) => {
  try {
    const response = await axios.get(`/api/car/${carId}/history`);
    carHistory.value = response.data; // Assuming API returns an array of history
  } catch (error) {
    console.error("Error fetching car history:", error);
  }
};

// Restore a value from history
const restoreValue = async (historyId) => {
  try {
    const response = await axios.post(`/api/car/history/${historyId}/restore`);
    if (response.status === 200) {
      alert("Value restored successfully");
      await fetchCarHistory(props.formData.id); // Refresh history
    }
  } catch (error) {
    console.error("Error restoring value:", error);
    alert("Failed to restore value");
  }
};
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
            <!-- Car History Table -->
            <div class="mb-4 mx-5">
              <label class="dark:text-gray-200">تاريخ السيارة</label>
              <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400 mt-4">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                  <tr>
                    <th scope="col" class="px-1 py-3">{{ $t('date') }}</th>
                    <th scope="col" class="px-1 py-3">الحقل</th>
                    <!-- <th scope="col" class="px-1 py-3">{{ $t('old_value') }}</th> -->
                    <th scope="col" class="px-1 py-3">الوصف</th>
                    <th scope="col" class="px-1 py-3">المبلغ</th>

                    <!-- <th scope="col" class="px-1 py-3">{{ $t('actions') }}</th> -->
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="history in carHistory"
                    :key="history.id"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                  >
                    <td class="px-4 py-2 border dark:border-gray-900">{{ history.created_at.substring(0, 10) }}</td>
                    <td class="px-4 py-2 border dark:border-gray-900">  {{ $t(history.field) }}</td>
                    <!-- <td class="px-4 py-2 border dark:border-gray-900">{{ history.old_value }}</td> -->
                    <td class="px-4 py-2 border dark:border-gray-900">{{ history.description }}</td>
                    <td class="px-4 py-2 border dark:border-gray-900">${{ history.new_value - history.old_value }}</td>

                    
                    <!-- <td class="px-4 py-2 border dark:border-gray-900">
                      <button
                        class="py-1 px-3 bg-blue-500 text-white rounded"
                        @click="restoreValue(history.id)"
                      >
                        {{ $t('restore') }}
                      </button>
                    </td> -->
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Payment Form -->
            <div v-if="formData.pay_price - formData.paid_amount_pay != 0">
              <div class="mb-4 mx-5">
                <input
                  id="id"
                  type="text"
                  disabled
                  style="display: none;"
                  v-model="formData.id"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="amountPayment">{{ $t('amount') }}</label>
                <input
                  id="amountPayment"
                  type="number"
                  v-model="formData.amountPayment"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200"
                />
              </div>
              <div class="mb-4 mx-5">
                <label class="dark:text-gray-200" for="notePayment">{{ $t('note') }}</label>
                <input
                  id="notePayment"
                  type="text"
                  v-model="formData.notePayment"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200"
                />
              </div>
            </div>
          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3 bg-gray-500 rounded" @click="$emit('close');">
                  {{ $t('cancel') }}
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-rose-500 rounded"
                  :disabled="!formData.amountPayment"
                  @click="$emit('a', formData); formData = ''"
                >
                  {{ $t('yes') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

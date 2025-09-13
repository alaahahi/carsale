<script setup>
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const emit = defineEmits(['close', 'a', 'deletePayment', 'printReceipt']);

const props = defineProps({
  show: Boolean,
  company: Array,
  color:Array,
  carModel:Array,
  name:Array,
  client:Array,
  user:Array,
  expenses:Array,
  formData:Object
});
let showClient =  ref(false);

// حساب الدين المتبقي
const remainingDebt = computed(() => {
  return Math.round(props.formData.pay_price - props.formData.paid_amount_pay);
});

// مراقبة تغيير المبلغ المدخل
function handleAmountChange() {
  const maxAmount = remainingDebt.value;
  
  if (props.formData.amountPayment > maxAmount) {
    // إعطاء تحذير
    toast.warning(`المبلغ المدخل (${props.formData.amountPayment.toLocaleString()}) أكبر من الدين المتبقي (${maxAmount.toLocaleString()}). سيتم تعديل المبلغ للحد الأقصى المسموح.`);
    
    // تعديل المبلغ للحد الأقصى
    props.formData.amountPayment = maxAmount;
  }
}

// دوال لتحديد نوع الدفعة والألوان
function getPaymentTypeText(pay) {
  if (pay.type === 'in') {
    // التحقق من نوع الحساب حسب wallet_id أو user email
    if (pay.wallet?.user?.email === 'main@account.com') {
      return 'دخل للصندوق';
    } else if (pay.wallet?.user?.email === 'in@account.com') {
      return 'دخل';
    } else if (pay.wallet?.user?.email === 'debt@account.com') {
      return 'دفع دين';
    } else {
      return 'دخل';
    }
  } else if (pay.type === 'out') {
    return 'خرج';
  }
  return pay.type;
}

function getPaymentTypeClass(pay) {
  if (pay.type === 'in') {
    if (pay.wallet?.user?.email === 'main@account.com') {
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    } else if (pay.wallet?.user?.email === 'in@account.com') {
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
    } else if (pay.wallet?.user?.email === 'debt@account.com') {
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } else {
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
    }
  } else if (pay.type === 'out') {
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
  }
  return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
}

function getPaymentRowClass(pay) {
  if (pay.type === 'in') {
    if (pay.wallet?.user?.email === 'main@account.com') {
      return 'bg-blue-50 dark:bg-blue-900/20';
    } else if (pay.wallet?.user?.email === 'in@account.com') {
      return 'bg-emerald-50 dark:bg-emerald-900/20';
    } else if (pay.wallet?.user?.email === 'debt@account.com') {
      return 'bg-green-50 dark:bg-green-900/20';
    } else {
      return 'bg-emerald-50 dark:bg-emerald-900/20';
    }
  } else if (pay.type === 'out') {
    return 'bg-red-50 dark:bg-red-900/20';
  }
  return 'bg-white dark:bg-gray-800';
}

// دالة حذف الدفعة
function deletePayment(paymentId) {
     emit('deletePayment', paymentId);
 
}

// دالة طباعة الوصل
function printPaymentReceipt(payment) {
  emit('printReceipt', payment);
}

</script>
  <template>
    <Transition name="modal">
      <div v-if="show" class="modal-mask ">
        <div class="modal-wrapper ">
          <div class="modal-container dark:bg-gray-900">
            <div class="modal-header">
              <slot name="header"></slot>
            </div>

            <div class="modal-body">
              <div class="grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-3 lg:gap-3">
                <div className="mb-4 mx-5">
                  <label  class="dark:text-gray-200" for="user_id" >{{ $t('selling_price') }}</label>
                  <input
                  id="id"
                  type="text"
                  style="display: none;"
                  disabled
                  v-model="formData.id" />

                  <input
                  id="id"
                  type="text"
                  class=" mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                  :value="Math.round(formData.pay_price).toLocaleString()" />
                </div>
                <div className="mb-4 mx-5">
                  <label  class="dark:text-gray-200" for="user_id" >{{ $t('paid_amount') }}</label>
                  <input
                  id="id"
                  type="text"
                  disabled
                  class=" mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                  :value="Math.round(formData.paid_amount_pay).toLocaleString()" />
                </div>
                <div className="mb-4 mx-5">
                  <label  class="dark:text-gray-200" :for="userId">{{ $t('debtRemaining') }}</label>
                  <input
                  id="id"
                  type="text"
                  disabled
                  :value="Math.round(formData.pay_price - formData.paid_amount_pay).toLocaleString()"
                  class=" mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                   />
                </div>
              </div>
              <div className="mb-4 mx-5">
                <label  class="dark:text-gray-200" :for="userId">{{ $t('payments') }}</label>
                <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400" >
                                <tr>
                                  <th scope="col" class="px-1 py-3">{{ $t('date') }}</th>
                                  <th scope="col" class="px-1 py-3">{{ $t('amount') }}</th>
                                  <th scope="col" class="px-1 py-3">{{ $t('description') }}</th>
                                  <th scope="col" class="px-1 py-3">النوع</th>
                                  <th scope="col" class="px-1 py-3">العمليات</th>
                                 </tr>
                              </thead>
                              <tbody>
                                <template  v-for="(pay, index) in formData?.transactions" :key="index" >
                                <tr v-if="getPaymentTypeText(pay) == 'دخل'" :class="getPaymentRowClass(pay)" class="border-b hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td className="px-4 py-2 border dark:border-gray-900">{{ (pay.created_at).substring(0, 10)  }}</td>
                                    <td className="px-4 py-2 border dark:border-gray-900 td">{{ Math.round(pay.amount).toLocaleString() }}</td>
                                    <td className="px-4 py-2 border dark:border-gray-900 td">{{ pay.description }}</td>
                                    <td className="px-4 py-2 border dark:border-gray-900 td">
                                        <span :class="getPaymentTypeClass(pay)" class="px-2 py-1 rounded-full text-xs font-medium">
                                            {{ getPaymentTypeText(pay) }}
                                        </span>
                                    </td>
                                    <td className="px-4 py-2 border dark:border-gray-900 td">
                                        <div class="flex space-x-2">
                                            <button
                                                @click="printPaymentReceipt(pay)"
                                                class="px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
                                                title="طباعة وصل"
                                            >
                                                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                @click="deletePayment(pay.id)"
                                                class="px-3 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                                                title="حذف الدفعة"
                                            >
                                                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                </template>
                                <tr v-if="!formData?.transactions || formData.transactions.length === 0">
                                    <td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
                                        لا توجد دفعات مسجلة
                                    </td>
                                </tr>
                              </tbody>
                          </table>


              </div>
              <div v-if="formData.pay_price-formData.paid_amount_pay != 0">
              <div className="mb-4 mx-5">
                <input
                id="id"
                type="text"
                disabled
                style="display: none;"
                class=" mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                v-model="formData.id" />
              </div>
              <div className="mb-4 mx-5">
              <label  class="dark:text-gray-200" for="amountPayment" >{{ $t('amount') }}</label>
              <input
                id="amountPayment"
                type="number"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                v-model="formData.amountPayment"
                @input="handleAmountChange"
                :max="remainingDebt" />
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                الحد الأقصى المسموح: {{ remainingDebt.toLocaleString() }}
              </p>
              </div>
              <div className="mb-4 mx-5">
              <label  class="dark:text-gray-200" for="notePayment" >{{ $t('note') }} </label>
              <input
                id="notePayment"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                v-model="formData.notePayment" />
              </div>
              </div>
            </div>
  
            <div class="modal-footer my-2">
              <div class="flex flex-row">
                <div class="basis-1/2 px-4"> 
                  <button class="modal-default-button py-3  bg-gray-500 rounded"
                    @click="$emit('close');">{{ $t('cancel') }}</button>
                  </div>
                <div class="basis-1/2 px-4">
                <button class="modal-default-button py-3  bg-rose-500 rounded col-6"  @click="$emit('a',formData);formData=''" :disabled="!formData.amountPayment || formData.amountPayment <= 0">{{ $t('yes') }}</button>
                </div>

            </div>
  
     
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </template>
  
  <style>
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
    width: 50%;
    min-width: 350px;
    margin: 0px auto;
    padding: 20px  30px;
    padding-bottom: 60px;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.3s ease;
    border-radius: 10px;
  }
  
  .modal-header h3 {
    margin-top: 0;
    color: #42b983;
  }
  
  .modal-body {
    margin: 20px 0;
  }
  
  .modal-default-button {
    float: right;
    width: 100%;
    color: #fff;
  }
  
  /*
   * The following styles are auto-applied to elements with
   * transition="modal" when their visibility is toggled
   * by Vue.js.
   *
   * You can easily play with the modal transition by editing
   * these styles.
   */
  
  .modal-enter-from {
    opacity: 0;
  }
  
  .modal-leave-to {
    opacity: 0;
  }
  
  .modal-enter-from .modal-container,
  .modal-leave-to .modal-container {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }
  </style>
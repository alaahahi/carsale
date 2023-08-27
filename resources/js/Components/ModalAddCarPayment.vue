<script setup>
import { ref, computed } from 'vue';

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
                  disabled
                  class=" mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                  v-model="formData.pay_price" />
                </div>
                <div className="mb-4 mx-5">
                  <label  class="dark:text-gray-200" for="user_id" >{{ $t('paid_amount') }}</label>
                  <input
                  id="id"
                  type="text"
                  disabled
                  class=" mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                  v-model="formData.paid_amount_pay" />
                </div>
                <div className="mb-4 mx-5">
                  <label  class="dark:text-gray-200" :for="userId">{{ $t('debtRemaining') }}</label>
                  <input
                  id="id"
                  type="text"
                  disabled
                  :value="formData.pay_price-formData.paid_amount_pay"
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
                                </tr>
                              </thead>
                              <tbody>
                                <template  v-for="(pay, index) in formData?.transactions" :key="index" >
                                <tr  v-if="pay.type=='in'&pay.wallet_id=='27'"  class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td className="px-4 py-2 border dark:border-gray-900">{{ (pay.created_at).substring(0, 10)  }}</td>
                                    <td className="px-4 py-2 border dark:border-gray-900 td">{{ pay.amount }}</td>
                                    <td className="px-4 py-2 border dark:border-gray-900 td">{{ pay.description }}</td>
                                </tr>
                                </template>
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
                <label  class="dark:text-gray-200" for="user_id">{{ $t('user') }}</label>
                <select
                  v-model="formData.user_id"
                  id="name_id"
                  class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                  <option selected disabled>{{ $t('select_name') }}</option>
                  <template v-for="(card, index) in user" :key="index"  >
                    <option   :value="card.id">{{ card.name }}</option>
                  </template>
                </select>
              </div>
              <div className="mb-4 mx-5">
              <label  class="dark:text-gray-200" for="amountPayment" >{{ $t('amount') }}</label>
              <input
                id="amountPayment"
                type="number"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900 "
                v-model="formData.amountPayment" />
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
                <button class="modal-default-button py-3  bg-rose-500 rounded col-6"  @click="$emit('a',formData);formData=''" :disabled="!(formData.user_id && formData.amountPayment)">{{ $t('yes') }}</button>
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
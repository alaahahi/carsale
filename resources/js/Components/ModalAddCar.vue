<script setup>
import { ref, computed } from "vue";
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  formData: Object,
  user: Array,
});

const emit = defineEmits(['a', 'close']);

function VinApi (v){
  props.formData.car_type=''
    props.formData.year=''
    axios.get(`https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${v}?format=json`)
  .then(response => {
    props.formData.name=(response.data.Results[0].Make ? response.data.Results[0].Make:response.data.Results[0].Manufacturer)+' '+response.data.Results[0].Model
    props.formData.model=response.data.Results[0].ModelYear

  })
  .catch(error => {
    console.error(error);
  })
}
let showDubaiShipping = ref(false)
let showDubaiExp = ref(false)
let showErbilShipping = ref(false)
let showErbilExp = ref(false)

// دالة لتنسيق المدخل الرقمي للعرض (إزالة .00 للأعداد الصحيحة)
function formatNumericInput(value) {
  if (value === null || value === undefined || value === '') {
    return '';
  }
  const num = parseFloat(value);
  if (isNaN(num)) {
    return '';
  }
  // إذا كان عدد صحيح، عرضه بدون أرقام عشرية
  if (num % 1 === 0) {
    return num.toString();
  }
  // إذا كان عشري، عرضه كما هو
  return num.toString();
}

// دالة لتحليل المدخل الرقمي
function parseNumericInput(value) {
  if (value === '' || value === null || value === undefined) {
    return null;
  }
  // إزالة المسافات
  const cleaned = value.trim();
  if (cleaned === '') {
    return null;
  }
  const num = parseFloat(cleaned);
  if (isNaN(num)) {
    return null;
  }
  return num;
}

// دالة لإرسال البيانات مع تحويل null إلى 0
function submitForm() {
  const numericFields = ['purchase_price', 'dubai_exp', 'dubai_shipping', 'erbil_exp', 'erbil_shipping'];
  const dataToSend = { ...props.formData };
  numericFields.forEach(field => {
    if (dataToSend[field] === null || dataToSend[field] === '' || dataToSend[field] === undefined) {
      dataToSend[field] = 0;
    }
  });
  emit('a', dataToSend);
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
            <h2 class="text-center dark:text-gray-200">
              {{ $t("purchase_car") }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-2 lg:gap-4">
              <div className="mb-4 mx-5">
                <label class="dark:text-gray-200" for="pin">
                  {{ $t("vim") }}</label
                >
                <input
                  id="pin"
                  type="text"
                  @change="VinApi(formData.pin)"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.pin"
                />
              </div>
              <div className="mb-4 mx-5">
                <label class="dark:text-gray-200" for="color">{{
                  $t("color")
                }}</label>
                <input
                  type="text"
                  v-model="formData.color"
                  id="color"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                >
              </div>

              <div className="mb-4 mx-5">
                <label class="dark:text-gray-200" for="name">{{$t("car")}}</label>
                <input
                  v-model="formData.name"
                  id="name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900">
              </div>
           
              <div className="mb-4 mx-5">
                <label class="dark:text-gray-200" for="carmodel">{{
                  $t("year")
                }}</label>
                <input type="text"
                  v-model="formData.model"
                  id="carmodel"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900">
              </div>
   
          
      
              <div className="mb-4 mx-5">
                <label class="dark:text-gray-200" for="purchase_data">{{
                  $t("purchase_date")
                }}</label>
                <input
                  id="purchase_data"
                  type="date"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.purchase_data"
                />
              </div>
              <div className="mb-4 mx-5" v-if="formData.id">
                <label class="dark:text-gray-200" for="no">{{
                  $t("no")
                }}</label>
                <input
                  id="no"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.no"
                />
              </div> 
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="purchase_price">{{
                  $t("purchase_price")
                }}</label>
                <input
                  id="purchase_price"
                  type="text"
                  inputmode="decimal"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="formatNumericInput(formData.purchase_price)"
                  @input="formData.purchase_price = parseNumericInput($event.target.value)"
                />
              </div>

              <div className="mb-4 mx-5">
              <label class="dark:text-gray-200" for="source">{{
                $t("source")
              }}</label>
              <input
                id="source"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model="formData.source"
              />
            </div>
              <div className="mb-4 mx-5">
                <label class="dark:text-gray-200" for="note">{{
                  $t("note")
                }}</label>
                <input
                  id="note"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.note"
                />
                </div>
              </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-2 lg:gap-4">

              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="dubai_exp">{{
                  $t("dubai_expenses")
                }}</label>
                <input
                  @focus="showDubaiShipping = false; showDubaiExp = true;showErbilShipping = false; showErbilExp = false;"
                  id="dubai_exp"
                  type="text"
                  inputmode="decimal"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="formatNumericInput(formData.dubai_exp)"
                  @input="formData.dubai_exp = parseNumericInput($event.target.value)"
                />
              </div>
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="dubai_shipping">{{
                  $t("dubai_shipping")
                }}</label>
                <input
                  @focus="showDubaiShipping = true; showDubaiExp = false;showErbilShipping = false; showErbilExp = false;"
                  id="dubai_shipping"
                  type="text"
                  inputmode="decimal"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="formatNumericInput(formData.dubai_shipping)"
                  @input="formData.dubai_shipping = parseNumericInput($event.target.value)"
                />
              </div>
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="erbil_exp">{{
                  $t("erbil_expenses")
                }}</label>
                <input
                  @focus="showDubaiShipping = false; showDubaiExp = false;showErbilShipping = false; showErbilExp = true;"
                  id="erbil_exp"
                  type="text"
                  inputmode="decimal"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="formatNumericInput(formData.erbil_exp)"
                  @input="formData.erbil_exp = parseNumericInput($event.target.value)"
                />
              </div>
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="erbil_shipping">{{
                  $t("erbil_shipping")
                }}</label>
                <input
                  @focus="showDubaiShipping = false; showDubaiExp = false;showErbilShipping = true; showErbilExp = false;"
                  id="erbil_shipping"
                  type="text"
                  inputmode="decimal"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :value="formatNumericInput(formData.erbil_shipping)"
                  @input="formData.erbil_shipping = parseNumericInput($event.target.value)"
                />
              </div>
              <div className="mb-4 mx-5" v-if="showDubaiExp">
                <label class="dark:text-gray-200" for="exp_note">
                  وصف مصاريف دبي  
                </label>
                <input
                  id="exp_note"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.exp_note"
                />
              </div>
              <div className="mb-4 mx-5" v-if="showDubaiShipping">
                <label class="dark:text-gray-200" for="exp_note">
                  وصف شحن دبي  
                </label>
                <input
                  id="exp_note"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.exp_note"
                />
              </div>

              <div className="mb-4 mx-5" v-if="showErbilExp">
                <label class="dark:text-gray-200" for="exp_note">
                  وصف مصاريف اربيل  
                </label>
                <input
                  id="exp_note"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.exp_note"
                />
              </div>
              <div className="mb-4 mx-5" v-if="showErbilShipping">
                <label class="dark:text-gray-200" for="exp_note">
                  وصف  شحن اربيل  
                </label>
                <input
                  id="exp_note"
                  type="text"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.exp_note"
                />
              </div>
              </div>
  

          </div>

          <div class="modal-footer my-2">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-gray-500 rounded"
                  @click="$emit('close')"
                >
                  {{ $t("cancel") }}
                </button>
              </div>
              <div class="basis-1/2 px-4">
                <button
                  class="modal-default-button py-3 bg-rose-500 rounded col-6"
                  @click="submitForm()"
                  :disabled="!(formData.name)"
                >
                  {{ $t("yes") }}
                </button>
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
  max-width: 90%;
  max-height: 90vh;
  margin: 0px auto;
  padding: 20px 30px;
  padding-bottom: 60px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header h3 {
  margin-top: 0;
  color: #42b983;
}

.modal-body {
  margin: 20px 0;
  overflow-y: auto;
  overflow-x: hidden;
  flex: 1;
  max-height: calc(90vh - 150px);
  padding-right: 10px;
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #555;
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
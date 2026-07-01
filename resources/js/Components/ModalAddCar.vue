<script setup>
import { ref, computed, watch } from "vue";
import axios from 'axios';
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  show: Boolean,
  formData: Object,
  suppliers: Array,
  initialTab: {
    type: String,
    default: 'single',
  },
});

const emit = defineEmits(['a', 'close']);
const toast = useToast()
const { t } = useI18n()

const suppliersList = ref([])
watch(
  () => props.suppliers,
  (v) => {
    suppliersList.value = Array.isArray(v) ? [...v] : []
  },
  { immediate: true }
)

const activeTab = ref('single') // single | group
const sourceModeSingle = ref('supplier') // supplier | text
const sourceTextSingle = ref('')
const sourceModeGroup = ref('supplier') // supplier | text
const sourceTextGroup = ref('')
const groupForm = ref({
  group_count: 2,
  name: '',
  model: '',
  color: '',
  purchase_data: (() => {
    const d = new Date()
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
  })(),
  purchase_price: null,
  user_purchase_id: null,
  note: '',
})

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

  // المصدر: تاجر أو نص
  if (sourceModeSingle.value === 'text') {
    dataToSend.source = (sourceTextSingle.value || '').trim()
    dataToSend.user_purchase_id = null
  } else {
    // لا نرسل source حتى لا نمسح النص القديم
    delete dataToSend.source
  }

  emit('a', dataToSend);
}

function submitGroupForm() {
  const dataToSend = { ...groupForm.value, is_group: true };
  // normalize numbers
  dataToSend.group_count = Number(dataToSend.group_count || 0);
  if (dataToSend.purchase_price === null || dataToSend.purchase_price === '' || dataToSend.purchase_price === undefined) {
    dataToSend.purchase_price = 0;
  }

  // المصدر: تاجر أو نص
  if (sourceModeGroup.value === 'text') {
    dataToSend.source = (sourceTextGroup.value || '').trim()
    dataToSend.user_purchase_id = null
  } else {
    delete dataToSend.source
  }

  emit('a', dataToSend);
}

const showAddSupplier = ref(false)
const newSupplier = ref({ name: '', phone: '' })
const addingSupplier = ref(false)

async function addSupplier() {
  if (!newSupplier.value.name.trim()) {
    toast.error(t('enter_supplier_name'))
    return
  }
  addingSupplier.value = true
  try {
    const res = await axios.post('/api/suppliers', {
      name: newSupplier.value.name,
      phone: newSupplier.value.phone || null,
    })
    const created = res.data?.data
    if (created?.id) {
      suppliersList.value.push(created)
      // اختيار التاجر مباشرة
      if (activeTab.value === 'group') {
        groupForm.value.user_purchase_id = Number(created.id)
        sourceModeGroup.value = 'supplier'
      } else {
        props.formData.user_purchase_id = Number(created.id)
        sourceModeSingle.value = 'supplier'
      }
      newSupplier.value = { name: '', phone: '' }
      showAddSupplier.value = false
      toast.success(t('supplier_added_success'))
    }
  } catch (e) {
    toast.error(e.response?.data?.message || t('supplier_add_failed'))
  } finally {
    addingSupplier.value = false
  }
}

watch(
  () => props.show,
  (isOpen) => {
    if (!isOpen) return
    // عند فتح المودال للإضافة فقط
    if (!props.formData?.id) {
      activeTab.value = (props.initialTab === 'group' ? 'group' : 'single')
      sourceModeSingle.value = 'supplier'
      sourceTextSingle.value = ''
      sourceModeGroup.value = 'supplier'
      sourceTextGroup.value = ''
      // لو تم تمرير مورد مسبقاً (مثلاً من صفحة مشتريات الموردين)
      const supplierId = props.formData?.user_purchase_id ?? null
      if (supplierId !== null && supplierId !== undefined && supplierId !== '') {
        groupForm.value.user_purchase_id = Number(supplierId)
      }
    } else {
      activeTab.value = 'single'
      const hasSupplier = props.formData?.user_purchase_id !== null && props.formData?.user_purchase_id !== undefined && props.formData?.user_purchase_id !== ''
      const hasSourceText = (props.formData?.source || '').toString().trim().length > 0
      if (hasSupplier) {
        sourceModeSingle.value = 'supplier'
        sourceTextSingle.value = ''
      } else if (hasSourceText) {
        sourceModeSingle.value = 'text'
        sourceTextSingle.value = (props.formData?.source || '').toString()
      } else {
        sourceModeSingle.value = 'supplier'
        sourceTextSingle.value = ''
      }
    }
  },
  { immediate: true }
)

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

            <!-- Tabs -->
            <div class="flex gap-2 justify-center my-3" v-if="!formData?.id">
              <button
                type="button"
                @click="activeTab = 'single'"
                class="px-4 py-2 rounded text-sm font-semibold"
                :class="activeTab === 'single' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
              >
                {{ $t('single_car') }}
              </button>
              <button
                type="button"
                @click="activeTab = 'group'"
                class="px-4 py-2 rounded text-sm font-semibold"
                :class="activeTab === 'group' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
              >
                {{ $t('group_cars') }}
              </button>
            </div>

            <!-- GROUP TAB -->
            <div v-if="activeTab === 'group' && !formData?.id">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-2 lg:gap-4">
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="group_count">{{ $t('cars_count') }}</label>
                  <input
                    id="group_count"
                    type="number"
                    min="1"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="groupForm.group_count"
                  />
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_purchase_data">{{ $t("purchase_date") }}</label>
                  <input
                    id="g_purchase_data"
                    type="date"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="groupForm.purchase_data"
                  />
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_name">{{ $t("car") }}</label>
                  <input
                    id="g_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="groupForm.name"
                  />
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_model">{{ $t("year") }}</label>
                  <input
                    id="g_model"
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="groupForm.model"
                  />
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_color">{{ $t("color") }}</label>
                  <input
                    id="g_color"
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="groupForm.color"
                  />
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_purchase_price">{{ $t("purchase_price") }}</label>
                  <input
                    id="g_purchase_price"
                    type="text"
                    inputmode="decimal"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    :value="formatNumericInput(groupForm.purchase_price)"
                    @input="groupForm.purchase_price = parseNumericInput($event.target.value)"
                  />
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_user_purchase_id">{{ $t("source") }}</label>
                  <div class="flex gap-3 mt-1 text-sm text-gray-700 dark:text-gray-200">
                    <label class="inline-flex items-center gap-2">
                      <input type="radio" value="supplier" v-model="sourceModeGroup" />
                      <span>{{ $t('source_as_supplier') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                      <input type="radio" value="text" v-model="sourceModeGroup" />
                      <span>{{ $t('source_as_text') }}</span>
                    </label>
                  </div>

                  <div v-if="sourceModeGroup === 'text'" class="mt-2">
                    <input
                      type="text"
                      v-model="sourceTextGroup"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                      :placeholder="$t('source_text_placeholder')"
                    />
                  </div>

                  <div v-else class="mt-2">
                  <select
                    id="g_user_purchase_id"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model.number="groupForm.user_purchase_id"
                  >
                    <option :value="null">—</option>
                  <option v-for="u in (suppliersList || [])" :key="u.id" :value="u.id">
                      {{ u.name }}
                    </option>
                  </select>
                <button type="button" class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                        @click="showAddSupplier = !showAddSupplier">
                  + {{ $t('add_supplier') }}
                </button>
                  </div>
                </div>
                <div className="mb-4 mx-5">
                  <label class="dark:text-gray-200" for="g_note">{{ $t("note") }}</label>
                  <input
                    id="g_note"
                    type="text"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                    v-model="groupForm.note"
                  />
                </div>
              </div>

            </div>

            <!-- SINGLE TAB (existing) -->
            <div v-else>
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
              <label class="dark:text-gray-200" for="user_purchase_id">{{
                $t("source")
              }}</label>
              <div class="flex gap-3 mt-1 text-sm text-gray-700 dark:text-gray-200">
                <label class="inline-flex items-center gap-2">
                  <input type="radio" value="supplier" v-model="sourceModeSingle" />
                  <span>{{ $t('source_as_supplier') }}</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="radio" value="text" v-model="sourceModeSingle" />
                  <span>{{ $t('source_as_text') }}</span>
                </label>
              </div>

              <div v-if="sourceModeSingle === 'text'" class="mt-2">
                <input
                  type="text"
                  v-model="sourceTextSingle"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  :placeholder="$t('source_text_placeholder')"
                />
              </div>

              <div v-else class="mt-2">
              <select
                id="user_purchase_id"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                v-model.number="formData.user_purchase_id"
              >
                <option :value="null">—</option>
                <option v-for="u in (suppliersList || [])" :key="u.id" :value="u.id">
                  {{ u.name }}
                </option>
              </select>
              <button type="button" class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                      @click="showAddSupplier = !showAddSupplier">
                + {{ $t('add_supplier') }}
              </button>
              </div>
            </div>

            <!-- Add supplier inline -->
            <div v-if="showAddSupplier" class="mx-5 mt-2 p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('supplier_name') }}</label>
                  <input v-model="newSupplier.name" type="text"
                         class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-lg text-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('supplier_phone') }}</label>
                  <input v-model="newSupplier.phone" type="text"
                         class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-lg text-sm" />
                </div>
              </div>
              <div class="flex justify-end gap-2 mt-3">
                <button type="button" class="px-3 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-700 dark:text-gray-100"
                        @click="showAddSupplier = false">
                  {{ $t('cancel') }}
                </button>
                <button type="button" class="px-3 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white"
                        :disabled="addingSupplier"
                        @click="addSupplier">
                  {{ addingSupplier ? $t('loading') : $t('save') }}
                </button>
              </div>
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
                  {{ $t('dubai_exp_desc') }}
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
                  {{ $t('dubai_ship_desc') }}
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
                  {{ $t('erbil_exp_desc') }}
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
                  {{ $t('erbil_ship_desc') }}
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
                  v-if="activeTab === 'group' && !formData?.id"
                  class="modal-default-button py-3 bg-rose-500 rounded col-6"
                  @click="submitGroupForm()"
                  :disabled="!(groupForm.group_count && groupForm.purchase_data)"
                >
                  {{ $t("yes") }}
                </button>
                <button
                  v-else
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
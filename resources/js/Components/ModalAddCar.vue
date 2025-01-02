<script setup>
import { ref, computed } from "vue";
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  formData: Object,
  user: Array,
});
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
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.purchase_price"
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
                  id="dubai_exp"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dubai_exp"
                />
              </div>
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="dubai_shipping">{{
                  $t("dubai_shipping")
                }}</label>
                <input
                  id="dubai_shipping"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.dubai_shipping"
                />
              </div>
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="erbil_exp">{{
                  $t("erbil_expenses")
                }}</label>
                <input
                  id="erbil_exp"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.erbil_exp"
                />
              </div>
              <div className="mb-4 mx-5" >
                <label class="dark:text-gray-200" for="erbil_shipping">{{
                  $t("erbil_shipping")
                }}</label>
                <input
                  id="erbil_shipping"
                  type="number"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-900"
                  v-model="formData.erbil_shipping"
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
                  @click="
                    $emit('a', formData);
                    formData = '';
                  "
                  :disabled="!(formData.pin)"
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
  margin: 0px auto;
  padding: 20px 30px;
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
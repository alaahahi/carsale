<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import { TailwindPagination } from "laravel-vue-pagination";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

const laravelData = ref({});
const user_id = ref(0);
const isLoading = ref(0);


const showReceiveBtn = ref(0);
const getResults = async (page = 1) => {
  const response = await fetch(
    `/getIndexAccountsSelas?page=${page}&user_id=${user_id.value}`
  );
  laravelData.value = await response.json();
};


const props = defineProps({
  url: String,
  users:Array
});

const form = useForm();

let showModal = ref(false);
const pay = async (id) => {
  const response = await fetch(`/paySelse/${id}`);
  getResults();

};


const results = (id) => {
  if(id==0){
        return 'إنتظار تسليم الصندوق';
    }
    if(id==1){
        return 'تم التسليم';
    }
  if (id == 2) {
    return "مكتمل";
  }

};
function sendToCourt(id) {
  showModal.value = id;
}
function method1(id) {
  form.get(route("sentToCourt", id));
  getResults();
  showModal.value = false;
}
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
          حركة الحسابات 
      </h2>
    </template>
    <modal
      :show="showModal ? true : false"
      :data="showModal.toString()"
      @a="method1($event, arg1)"
      @close="showModal = false"
    >
      <template #header>
        <h3 class="text-center dark:text-gray-200">إدارة الاستمارات</h3>
      </template>
    </modal>
    <div v-if="$page.props.success">
      <div
        id="alert-2"
        class="p-4 mb-4 bg-red-100 rounded-lg dark:bg-red-200 text-center"
        role="alert"
      >
        <div class="ml-3 text-sm font-medium text-red-700 dark:text-red-800">
          {{ $page.props.success }}
        </div>
      </div>
    </div>
    <div class="py-12">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6  dark:bg-gray-900">
            <div class="flex flex-row">
              <div class="basis-1/2 px-4">
                <select @change="getResults()" v-model="user_id" id="default" class="pr-8 bg-gray-50 border border-gray-300 text-gray-900 mb-6 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500">
                  <option value="0" disabled>اختار الحساب</option>
                  <option v-for="(user, index) in users" :key="index" :value="user.id">{{ user.name }}</option>
                </select>
              </div>

            </div>
            <div class="flex flex-row">
              <div class="grow">
                <div class="pb-3">
                  <div class="mx-auto mx-7">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                      <div class="p-6  dark:bg-gray-900">
                        <div class="flex flex-row">
                          <div class="basis-1/3">
                            <div className="mb-4 mx-5">
                              <InputLabel for="invoice_number" value="الحساب" />
                              <TextInput
                                id="invoice_number"
                                type="text"
                                class="mt-1 block w-full"
                                :value="laravelData.sales?.name"
                                disabled
                              />
                            </div>
                          </div>
                          <div class="basis-1/3">
                            <div className="mb-4 mx-5">
                              <InputLabel for="date" value="لتاريخ" />
                              <TextInput
                                id="date"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="laravelData.date"
                                disabled
                              />
                            </div>
                          </div>
                          <div class="basis-1/3">
                            <div className="mb-4 mx-5">
                              <InputLabel for="totalAmount" value="المجموع بالدولار " />
                              <TextInput
                                id="totalAmount"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="laravelData.totalAmount"
                                disabled
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto shadow-md">
              <table class="w-full my-5">
                <thead
                  class="700 bg-rose-500 text-white text-center rounded-l-lg">
                  <tr class="bg-rose-500 rounded-l-lg mb-2 sm:mb-0">
                    <th className="px-4 py-2 border dark:border-gray-900 dark:text-gray-200">النوع</th>
                    <th className="px-4 py-2 border dark:border-gray-900 dark:text-gray-200">الوصف</th>
                    <th className="px-4 py-2 border dark:border-gray-900 dark:text-gray-200">المبلغ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="user in laravelData.data"
                    :key="user.id"  class="hover:bg-gray-800 text-center">
                  <td className="px-4 py-2 border dark:border-gray-900 dark:text-gray-200 td">{{ user.type }}</td>
                  <td className="px-4 py-2 border dark:border-gray-900 dark:text-gray-200 td">{{ user.description }}</td>
                  <td className="px-4 py-2 border dark:border-gray-900 dark:text-gray-200 td">{{ user.amount  }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-3 text-center" style="direction: ltr;">
              <TailwindPagination
                :data="laravelData"
                @pagination-change-page="getResults"
                :limit ="10"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
.td {
  max-width: 200px; /* can be 100% ellipsis will happen when contents exceed it */
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}
</style>
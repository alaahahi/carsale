<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import VueTailwindDatepicker from 'vue-tailwind-datepicker'
import ModalAddCar from "@/Components/ModalAddCar.vue";
import ModalAddSale from "@/Components/ModalAddSale.vue";
import ModalAddExpenses from "@/Components/ModalAddExpenses.vue";
import ModalAddGenExpenses from "@/Components/ModalAddGenExpenses.vue";
import ModalAddToBox from "@/Components/ModalAddToBox.vue";
import ModalSpanFromBox from "@/Components/ModalSpanFromBox.vue";
import ModalAddTransfers from "@/Components/ModalAddTransfers.vue";
import ModalAddCarPayment from "@/Components/ModalAddCarPayment.vue";
import ModalCarHistory from "@/Components/ModalCarHistory.vue";

import ModalDelCar from "@/Components/ModalDelCar.vue";
import show from "@/Components/icon/show.vue";
import imags from "@/Components/icon/imags.vue";
import trash from "@/Components/icon/trash.vue";
import edit from "@/Components/icon/edit.vue";
import pay from "@/Components/icon/pay.vue";

import { TailwindPagination } from "laravel-vue-pagination";
import axios from 'axios';


import { ref, onMounted } from 'vue';
onMounted(() => {
  const table = document.querySelector('.table-container');
  const thead = table.querySelector('thead');
  console.log(thead)
  table.addEventListener('scroll', () => {
    const scrollTop = table.scrollTop;
    thead.style.transform = `translateY(${scrollTop}px)`;
  });
});
let searchTerm = ref('');

let showModalCar =  ref(false);
let showModalCarSale =  ref(false);
let showModalAddGenExpenses =  ref(false);
let showModalToBox =  ref(false);
let showModalFromBox =  ref(false);
let showModalAddTransfers =  ref(false);
let showModalAddCarPayment =  ref(false);
let showModalCarHistory =  ref(false);
let showModalDelCar =  ref(false);

function openModalDelCar(form={}) {
  formData.value=form
  showModalDelCar.value = true;
}

function openAddCar(form={}) {
  if(!form.purchase_data){
    form.purchase_data = getTodayDate()
  }
  if(!form.source){
    form.source = ''
  }
  if(!form.dubai_exp){
    form.dubai_exp = 0
  }
  if(!form.dubai_shipping){
    form.dubai_shipping = 0
  }
  if(!form.erbil_exp){
    form.erbil_exp = 0
  }
  if(!form.erbil_shipping){
    form.erbil_shipping = 0
  }
  if(!form.purchase_price){
    form.purchase_price = 0
  }
    formData.value=form
    showModalCar.value = true;
}
function openSaleCar(form={}) {
    formData.value=form
    showModalCarSale.value = true;
}
function openAddGenExpenses(form={}) {
    formGenExpenses.value=form
    showModalAddGenExpenses.value = true;
}
function openAddToBox(form={}) {
    formData.value=form
    showModalToBox.value = true;
}
function openAddFromBox(form={}) {
    formData.value=form
    showModalFromBox.value = true;
}
function openAddTransfers(form={}) {
    formData.value=form
    showModalAddTransfers.value = true;
}
function openAddCarPayment(form={}) {

    formData.value=form
    showModalAddCarPayment.value = true;
}
function openModalCarHistory(form={}) {
formData.value=form
showModalCarHistory.value = true;
}
const formData = ref({});
const formGenExpenses = ref({});
const car = ref([]);


const dateValue = ref({
    startDate: '',
    endDate: ''
})
const countComp = ref()
const formatter = ref({
  date: 'D/MM/YYYY',
  month: 'MM'
})
const getResultsCar = async (page = 1) => {
    const response = await fetch(`/getIndexCar?page=${page}`);
    car.value = await response.json();
}
const getResultsCarSearch = async (q='',page = 1) => {
    const response = await fetch(`/getIndexCarSearch?page=${page}&q=${q}`);
    car.value = await response.json();
}
const options = ref({
  shortcuts: {
    today: 'اليوم',
    yesterday: 'البارحة',
    past: period => period + ' قبل يوم',
    currentMonth: 'الشهر الحالي',
    pastMonth: 'الشهر السابق'
  },
  footer: {
    apply: 'Terapkan',
    cancel: 'Batal'
  }
})
const dDate = (date) => {
  return date >= new Date() ;
}
const getcountComp = async () => {
    const response = await fetch(`getcount?start=${dateValue.value.startDate}&end=${dateValue.value.endDate}`);
    countComp.value = await response.json();
}
getcountComp()
const props =  defineProps({
    carCount:String,
    url: String,
    user: Array,
    profile:String,
    comp:String,
    working:String,
    cardCompany:String,
    cardRegister:String,
    balance:String,
    color:Array,
    company:Array,
    carModel:Array,
    name:Array,
    client:Array,
    expenses:Array,
    mainAccount:Object,
    outAccount:Object,
    inAccount:Object,
    transfersAccount:Object,
    debtAccount:Object,
    outSupplier:Object,
    debtSupplier:Object,
});
function confirmCar(V) {
  axios.post('/api/addCar',V)
  .then(response => {
    showModalCar.value = false;
      window.location.reload();
  })
  .catch(error => {
    console.error(error);
  })
}
function confirmPayCar(V) {
  axios.post('/api/payCar',V)
  .then(response => {
    showModalCarSale.value = false;
      window.location.reload();
  })
  .catch(error => {
    console.error(error);
  })
}
function conGenfirmExpenses(V) {
  fetch(`/GenExpenses?user_id=${V.user_id}&amount=${V.amount??0}&reason=${V.reason??''}&note=${V.note??''}`)
    .then(() => {
      showModalAddGenExpenses.value = false;
      window.location.reload();

    })
    .catch((error) => {
      
      console.error(error);
    });
}
function conAddTransfers(V) {
  fetch(`/addTransfers?user_id=${V.user_id}&amount=${V.amount??0}&note=${V.note??''}`)
    .then(() => {
      showModalAddTransfers.value = false;
       window.location.reload();
    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmAddToBox(V) {
  fetch(`/addToBox?user_id=${V.user_id}&amount=${V.amount??0}&note=${V.note??''}`)
    .then(() => {
      showModalToBox.value = false;
      window.location.reload();

    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmWithDrawFromBox(V) {
  fetch(`/withDrawFromBox?user_id=${V.user_id}&amount=${V.amount??0}&note=${V.note??''}`)
    .then(() => {
      showModalFromBox.value = false;
      window.location.reload();

    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmAddPayment(V) {
  fetch(`/addPaymentCar?car_id=${V.id}&user_id=${V.user_id}&pay_price=${V.pay_price??0}&amount=${V.amountPayment??0}&note=${V.notePayment??''}`)
    .then(() => {
      showModalFromBox.value = false;
      window.location.reload();

    })
    .catch((error) => {
      console.error(error);
    });
}
function confirmDelCar(V) {
  axios.post('/api/DelCar',V)
  .then(response => {
    showModalDelCar.value = false;
      window.location.reload();
  })
  .catch(error => {
    console.error(error);
  })


}
function getTodayDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}
getResultsCar();
</script>

<template>
    <Head title="Dashboard" />
    <ModalAddCar
            :formData="formData"
            :show="showModalCar ? true : false"
            :company="company"
            :name="name"
            :color="color"
            :user="user"
            :carModel="carModel"
            @a="confirmCar($event)"
            @close="showModalCar = false"
            >
        <template #header>
          </template>
    </ModalAddCar>
    <ModalAddSale
            :formData="formData"
            :show="showModalCarSale ? true : false"
            :company="company"
            :name="name"
            :color="color"
            :carModel="carModel"
            :client="client"
            @a="confirmPayCar($event)"
            @close="showModalCarSale = false"
            >
        <template #header>
          </template>
    </ModalAddSale>
    <ModalAddGenExpenses
            :formData="formData"
            :show="showModalAddGenExpenses ? true : false"
            :user="user"
            @a="conGenfirmExpenses($event)"
            @close="showModalAddGenExpenses = false"
            >
        <template #header>
          </template>
    </ModalAddGenExpenses>
    <ModalAddToBox
            :formData="formData"
            :expenses="expenses"
            :show="showModalToBox ? true : false"
            :user="user"
            @a="confirmAddToBox($event)"
            @close="showModalToBox = false"
            >
        <template #header>
          </template>
    </ModalAddToBox>
    <ModalSpanFromBox
            :formData="formData"
            :expenses="expenses"
            :show="showModalFromBox ? true : false"
            :user="user"
            @a="confirmWithDrawFromBox($event)"
            @close="showModalFromBox = false"
            >
        <template #header>
          </template>
    </ModalSpanFromBox>
    <ModalAddTransfers
            :formData="formData"
            :expenses="expenses"
            :show="showModalAddTransfers  ? true : false"
            :user="user"
            @a="conAddTransfers($event)"
            @close="showModalAddTransfers = false"
            >
        <template #header>
          </template>
    </ModalAddTransfers>
    <ModalCarHistory
            :formData="formData"
            :show="showModalCarHistory ? true : false"
            :user="user"
            @a="confirmAddPayment($event)"
            @close="showModalCarHistory = false"
            >
        <template #header>
          </template>
    </ModalCarHistory>
    <ModalAddCarPayment
            :formData="formData"
            :show="showModalAddCarPayment ? true : false"
            :user="user"
            @a="confirmAddPayment($event)"
            @close="showModalAddCarPayment = false"
            >
        <template #header>
          </template>
    </ModalAddCarPayment>
    <ModalDelCar
            :show="showModalDelCar ? true : false"
            :formData="formData"
            @a="confirmDelCar($event)"
            @close="showModalDelCar = false"
            >
          <template #header>
          هل متأكد من حذف السيارة
          ؟
          </template>
    </ModalDelCar>

    
    <AuthenticatedLayout>
        <div class="py-2">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 lg:gap-1">
                        <div>
                          <form class="flex items-center max-w-5xl">
                            <label  class="dark:text-gray-200" for="simple-search"  ></label>
                            <div class="relative w-full">
                              <div
                                class="
                                  absolute
                                  inset-y-0
                                  left-0
                                  flex
                                  items-center
                                  pl-3
                                  pointer-events-none
                                "
                              >
                                <svg
                                  aria-hidden="true"
                                  class="w-5 h-5 text-gray-500 dark:text-gray-200 dark:text-gray-400"
                                  fill="currentColor"
                                  viewBox="0 0 20 20"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                  ></path>
                                </svg>
                              </div>
                              <input
                                v-model="searchTerm"
                                @input="getResultsCarSearch(searchTerm)"
                                type="text"
                                id="simple-search"
                                class="
                                  bg-gray-50
                                  border border-gray-300
                                  text-gray-900 text-sm
                                  rounded-lg
                                  focus:ring-blue-500 focus:border-blue-500
                                  block
                                  w-full
                                  pl-10
                                  p-2.5
                                  dark:bg-gray-700
                                  dark:border-gray-600
                                  dark:placeholder-gray-400
                                  dark:text-white
                                  dark:focus:ring-blue-500
                                  dark:focus:border-blue-500
                                "
                                placeholder="بحث"
                                required
                              />
                            </div>
                          </form>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddGenExpenses()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-red-500 rounded">
                               {{ $t('genExpenses') }}
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddCar()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-green-500 rounded">
                            {{ $t('purchases') }} 
                          </button>
                        </div>
                        <div>
                          <a
                            type="button"
                            :href="route('getIndexCar')"
                            style="min-width:150px;"
                            className="px-6 mb-12 text-center mx-2 py-2 font-bold text-white bg-blue-600 rounded">
                            {{ $t('allCars') }}
                          </a>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddTransfers()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-indigo-600 rounded">
                            {{ $t('transfers') }} 
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddToBox()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-purple-600 rounded">
                            {{ $t('addToTheFund') }}  
                          </button>
                        </div>
                        <div>
                          <button
                            type="button"
                            @click="openAddFromBox()"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-pink-600 rounded">
                            {{ $t('withdrawFromTheFund') }}   
                          </button>
                        </div>
                        <div>
                          <a
                            type="button"
                            :href="route('clients')"
                            style="min-width:150px;"
                            className="px-6 mb-12 mx-2 text-center py-2 font-bold text-white bg-red-700 rounded">
                            {{ $t('clients') }}  
                          </a>
                        </div>
                      </div>
                      <div>
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg table-container scrollbar scrollbar-thumb-blue-600 scrollbar-thumb-rounded">
                          <table class="w-full text-sm text-right text-gray-500 dark:text-gray-200 dark:text-gray-400 text-center">
                              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center" >
                                  <tr>
                                      <th scope="col" class="px-1 py-3 text-base	" >
                                        {{ $t('no') }}  
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base	">
                                        {{ $t('vim') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('name') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('color') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('year') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('purchase_price') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('source') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">{{ $t('dubai_shipping') }}</th>
                                      <th scope="col" class="px-1 py-3 text-base">{{ $t('dubai_expenses') }}</th>
                                      <th scope="col" class="px-1 py-3 text-base">{{ $t('erbil_shipping') }}</th>
                                      <th scope="col" class="px-1 py-3 text-base">{{ $t('erbil_expenses') }}</th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('total_cost') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('sell_price') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('customer') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('remaining') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base">
                                        {{ $t('profit') }}
                                      </th>
                                      <th scope="col" class="px-1 py-3 text-base" style="width: 350px;">
                                        {{ $t('execute') }}
                                      </th>
                                  </tr>
                              </thead>
                              <tbody>
                                <tr v-for="car in car.data" :key="car.id" :class="car.results == 0?'bg-gray-100 dark:bg-gray-600':car.results == 1 ?'bg-red-100 dark:bg-red-900':car.results == 2 ?'bg-green-100 dark:bg-green-900':''"  class="bg-white border-b dark:bg-gray-900 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base ">{{ car.no }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.pin }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.name}}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.color }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.model }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.purchase_price }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.source  }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.dubai_shipping  }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.dubai_exp }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.erbil_shipping }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.erbil_exp }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.purchase_price + car.erbil_exp+car.erbil_shipping+car.dubai_exp+car.dubai_shipping }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.pay_price }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.client?.name }} </td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.results != 0 ? car.pay_price-car.paid_amount_pay :'' }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.results != 0  ? car.pay_price -(car.purchase_price + car.erbil_exp+car.erbil_shipping+car.dubai_exp+car.dubai_shipping):''   }}</td>
                                    <td className="border dark:border-gray-800 text-start px-2 py-2">
                                    <button
                                      tabIndex="1"
                                      
                                      class="px-2 py-1 text-base text-white mx-1 bg-slate-500 rounded"
                                      @click="openAddCar(car)"
                                    >
                                      <edit />
                                    </button>
          
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-purple-500 rounded"
                                      v-if="car.results == 0"
                                      @click="openSaleCar(car)"
                                    >
                                      <pay />
                                    </button>

                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-green-500 rounded"
                                      v-if="car.results != 0 && (car.pay_price - car.paid_amount_pay == 0)"
                                      @click="openAddCarPayment(car)"
                                    >
                                    <pay />
                                    </button>
                                    <button
                                      tabIndex="1"
                                      class="px-2 py-1 text-base text-white mx-1 bg-red-700 rounded"
                                      v-if="car.results == 1 && (car.pay_price - car.paid_amount_pay != 0)"
                                      @click="openAddCarPayment(car)"
                                    >
                                    <pay />
                                    </button>

                                    <button
                                      tabIndex="1"
                                      
                                      class="px-2 py-1 text-base text-white mx-1 bg-orange-500 rounded"
                                      @click="openModalDelCar(car)"
                                    >
                                      <trash />
                                    </button>
                                    <button
                                      tabIndex="1"
                                      
                                      class="px-2 py-1 text-base text-white mx-1 bg-green-500 rounded"
                                      @click="openModalCarHistory(car)"
                                    >
                                      <show />
                                    </button>
                                    </td>
                                </tr>
                              </tbody>
                          </table>
                        </div>
                        <div class="mt-3 text-center" style="direction: ltr;">
                          <TailwindPagination
                            :data="car"
                            @pagination-change-page="getResultsCar"
                            :limit ="10"
                            :item-classes="['bg-white','dark:bg-gray-600','text-gray-500','dark:text-gray-300','border-gray-300','dark:border-gray-900','hover:bg-gray-200']"
                            :activeClasses="[  'bg-rose-50','border-rose-500','text-rose-600',]"
                          />
                        </div>
                      </div>
                      <div>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7">     
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-orange-100 bg-orange-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                            </div>
                            <div class="mr-4" >
                              <h2 class="font-semibold ">{{ $t('capital') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ mainAccount }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold"> {{ $t('fundIncome') }} </h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ inAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('cash_out') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ outAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('debt_to_fund') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ debtAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                      
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('transfer') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ transfersAccount.wallet?.balance }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('total_car_count') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{carCount}}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-red-100 bg-red-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                              </svg>
                            </div>
                            <div class="mr-4">
                              <h2 class="font-semibold">{{ $t('cars_in_stock') }}</h2>
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{working}}</p>
                            </div>
                          </div>
                         
                         
                        </div>
                      </div>
                      </div>
                    </div>
                    </div>
                </div>
            </div>
        <div >
        <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6  dark:bg-gray-900" style="border-radius: 8px;">
                  <div class="flex flex-row">
                                    <div class="basis-1/4">
                                      <button
                                        type="button"
                                        @click="getcountComp()"
                                        style="width: 70%;"
                                        className="px-6 mb-12 mx-2 py-2 font-bold text-white bg-rose-500 rounded"
                                      >
                                      فلترة
                                      </button>
                                    </div>
                                    <div class="basis-3/4" style="direction: ltr;">
                                      <vue-tailwind-datepicker overlay :options="options" :disable-date="dDate"  i18n="ar"  as-single use-range v-model="dateValue" />
                                    </div>
                  </div>
                  <div class="flex pt-5 items-center">
                  <div class="mx-auto container align-middle">
                        <div class="grid grid-cols-2 gap-2" style="display: flow-root;">
                          <div class="shadow rounded-lg py-3 px-5 bg-white" >
                            <div class="flex flex-row justify-between items-center">
                              <div>
                                <h6 class="text-2xl">المعاملات المنجزة </h6>
                                <h4 class="text-black text-4xl font-bold text-rigth">{{countComp}}</h4>
                              </div>
                              <div>
                                <svg
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="h-12 w-12"
                                  fill="none"
                                  viewBox="0 0 24 24"
                                  stroke="#14B8A6"
                                  stroke-width="2"
                                >
                                  <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                                  />
                                </svg>
                              </div>
                            </div>
                            <div class="text-left flex flex-row justify-start items-center">
                              <span class="mr-1">
                                <svg
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="h-6 w-6"
                                  fill="none"
                                  viewBox="0 0 24 24"
                                  stroke="#14B8A6"
                                  stroke-width="2"
                                >
                                  <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                  />
                                </svg>
                              </span>
                             
                            </div>
                          </div>
                          <div class="shadow rounded-lg py-3 px-5 bg-white" v-if="false">
                            <div class="flex flex-row justify-between items-center">
                              <div>
                                <h6 class="text-2xl">Serials viewed</h6>
                                <h4 class="text-black text-4xl font-bold text-left">41</h4>
                              </div>
                              <div>
                                <svg
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="h-12 w-12"
                                  fill="none"
                                  viewBox="0 0 24 24"
                                  stroke="#EF4444"
                                  stroke-width="2"
                                >
                                  <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                                  />
                                </svg>
                              </div>
                            </div>
                            <div class="text-left flex flex-row justify-start items-center">
                              <span class="mr-1">
                                <svg
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="h-6 w-6"
                                  fill="none"
                                  viewBox="0 0 24 24"
                                  stroke="#EF4444"
                                  stroke-width="{2}"
                                >
                                  <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
                                  />
                                </svg>
                              </span>
                              <p><span class="text-red-500 font-bold">12%</span> in 7 days</p>
                            </div>
                          </div>
                        </div>
                      </div>
                  </div>
                </div>
                    </div> -->
    </div>   
    </AuthenticatedLayout>
</template>
<style scoped>
/* Add your preferred styling for the table here */
.table-container {
  max-height: 1000px; /* Adjust the maximum height as needed */
  overflow-y: scroll;
}


/* Style the scrollbars for webkit-based browsers (e.g., Chrome) */
.table-container::-webkit-scrollbar {
  width: 12px;
}

.table-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.table-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 6px;
}

/* Style the scrollbars for Firefox */
.table-container {
  scrollbar-width: thin;
  scrollbar-color: #888 #f1f1f1;
}

/* Add more styles as needed */
</style>

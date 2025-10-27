<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import VueTailwindDatepicker from 'vue-tailwind-datepicker'
import { useToast } from 'vue-toastification'
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

const toast = useToast();

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
let carStatusFilter = ref('');
let paymentStatusFilter = ref('');

let showModalCar =  ref(false);
let showModalCarSale =  ref(false);
let showModalAddGenExpenses =  ref(false);
let showModalToBox =  ref(false);
let showModalFromBox =  ref(false);
let showModalAddTransfers =  ref(false);
let showModalAddCarPayment =  ref(false);
let showModalCarHistory =  ref(false);
let showModalDelCar =  ref(false);
let showModalCarPayments =  ref(false);
let carPayments = ref([]);
let selectedCar = ref(null);
let showModalEditSalePrice = ref(false);
let showModalEditPaidAmount = ref(false);
let calculatingProfit = ref(false);
let profitCalculationResult = ref(null);
let showProfitModal = ref(false);

function openModalDelCar(form={}) {
  formData.value=form
  showModalDelCar.value = true;
}

function showCarPayments(car) {
  selectedCar.value = car;
  showModalCarPayments.value = true;
  
  // جلب الدفعات الخاصة بالسيارة
  axios.get(`/api/car-payments?car_id=${car.id}`)
    .then(response => {
      carPayments.value = response.data;
    })
    .catch(error => {
      console.error('Error fetching car payments:', error);
      toast.error('حدث خطأ في جلب الدفعات');
    });
}

function openEditSalePrice(car) {
  formData.value = car;
  showModalEditSalePrice.value = true;
}

function openEditPaidAmount(car) {
  formData.value = car;
  showModalEditPaidAmount.value = true;
}

function calculateCarProfit(car) {
  calculatingProfit.value = true;
  
  axios.post(`/api/cars/${car.id}/distribute-profit`)
    .then(response => {
      if (response.data.success) {
        profitCalculationResult.value = response.data;
        showProfitModal.value = true;
        toast.success('تم حساب وتوزيع الربح بنجاح');
        // تحديث بيانات السيارات
        getResultsCar();
      } else {
        toast.error(response.data.message || 'حدث خطأ في حساب الربح');
      }
    })
    .catch(error => {
      console.error('Error calculating profit:', error);
      toast.error('حدث خطأ في حساب الربح');
    })
    .finally(() => {
      calculatingProfit.value = false;
    });
}

function confirmEditSalePrice(data) {
  // إذا كان السعر الجديد صفر، إظهار تأكيد إضافي
  if (data.newPayPrice == 0) {
    if (!confirm('هل أنت متأكد من إلغاء بيع هذه السيارة؟ سيتم حذف العميل وإعادة السيارة لحالة غير مباعة.')) {
      return;
    }
  }
  
  axios.post('/api/editSalePrice', data)
    .then(response => {
      showModalEditSalePrice.value = false;
      toast.success(response.data.success || 'تم تعديل سعر البيع بنجاح');
      window.location.reload();
    })
    .catch(error => {
      console.error(error);
      toast.error('حدث خطأ في تعديل سعر البيع');
    });
}

function confirmEditPaidAmount(data) {
  axios.post('/api/editPaidAmount', data)
    .then(response => {
      showModalEditPaidAmount.value = false;
      toast.success(response.data.success || 'تم تعديل المبلغ المدفوع بنجاح');
      window.location.reload();
    })
    .catch(error => {
      console.error(error);
      toast.error('حدث خطأ في تعديل المبلغ المدفوع');
    });
}

function deletePayment(paymentId) {
  if (confirm('هل أنت متأكد من حذف هذه الدفعة؟')) {
    axios.delete(`/api/delete-payment/${paymentId}`)
      .then(response => {
        toast.success('تم حذف الدفعة وتحديث المبلغ المدفوع وإنشاء معاملة معاكسة بنجاح');
        // إعادة جلب الدفعات
        if (selectedCar.value) {
          showCarPayments(selectedCar.value);
        }
        // إعادة تحميل الصفحة لتحديث البيانات
        window.location.reload();
      })
      .catch(error => {
        console.error(error);
        toast.error('حدث خطأ في حذف الدفعة');
      });
  }
}

function printPaymentReceipt(payment) {
  // إنشاء محتوى الوصل
  const receiptContent = `
    <div style="text-align: center; font-family: Arial, sans-serif; padding: 20px;">
      <h2>وصل استلام دفعة</h2>
      <hr>
      <p><strong>رقم السيارة:</strong> ${selectedCar.value?.pin}</p>
      <p><strong>اسم السيارة:</strong> ${selectedCar.value?.name}</p>
      <p><strong>المبلغ:</strong> ${Number(payment.amount).toLocaleString()} دينار</p>
      <p><strong>البيان:</strong> ${payment.description}</p>
      <p><strong>التاريخ:</strong> ${new Date(payment.created_at).toLocaleDateString('en-US')}</p>
      <p><strong>المستخدم:</strong> ${payment.wallet?.user?.name || 'غير محدد'}</p>
      <hr>
      <p style="margin-top: 30px;">شكراً لتعاملكم معنا</p>
    </div>
  `;
  
  // فتح نافذة الطباعة
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <html>
      <head>
        <title>وصل استلام دفعة</title>
        <style>
          @media print {
            body { margin: 0; }
            @page { margin: 1cm; }
          }
        </style>
      </head>
      <body>
        ${receiptContent}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}

// معالجة حذف الدفعة من ModalAddCarPayment
function handleDeletePaymentFromModal(paymentId) {
  deletePayment(paymentId);
}

// معالجة طباعة الوصل من ModalAddCarPayment
function handlePrintReceiptFromModal(payment) {
  printPaymentReceipt(payment);
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
const stats = ref({
    totalIncome: 0,
    totalExpenses: 0,
    totalDebt: 0,
    totalFundIncome: 0,
    totalCapital: 0
});


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
    const params = new URLSearchParams({
        page: page,
        type: carStatusFilter.value,
        payment_status: paymentStatusFilter.value,
        q: searchTerm.value
    });
    const response = await fetch(`/getIndexCar?${params}`);
    const result = await response.json();
    car.value = result.data;
    stats.value = result.stats;
}

const getResultsCarSearch = async (q='',page = 1) => {
    const params = new URLSearchParams({
        page: page,
        q: q,
        type: carStatusFilter.value,
        payment_status: paymentStatusFilter.value
    });
    const response = await fetch(`/getIndexCarSearch?${params}`);
    car.value = await response.json();
}

// تطبيق الفلاتر
const applyFilters = () => {
    if (searchTerm.value) {
        getResultsCarSearch(searchTerm.value);
    } else {
        getResultsCar();
    }
}

// إعادة تعيين الفلاتر
const resetFilters = () => {
    searchTerm.value = '';
    carStatusFilter.value = '';
    paymentStatusFilter.value = '';
    getResultsCar();
}

// طباعة جدول السيارات
const printCarsTable = () => {
    // حساب المجاميع
    const totalPurchasePrice = car.value.data.reduce((sum, carItem) => sum + (Number(carItem.purchase_price) || 0), 0);
    const totalCost = car.value.data.reduce((sum, carItem) => {
        return sum + ((Number(carItem.purchase_price) || 0) + (Number(carItem.erbil_exp) || 0) + (Number(carItem.erbil_shipping) || 0) + (Number(carItem.dubai_exp) || 0) + (Number(carItem.dubai_shipping) || 0));
    }, 0);
    const totalSalePrice = car.value.data.reduce((sum, carItem) => sum + (Number(carItem.pay_price) || 0), 0);
    const totalRemaining = car.value.data.reduce((sum, carItem) => {
        return sum + (carItem.results != 0 ? (Number(carItem.pay_price) || 0) - (Number(carItem.paid_amount_pay) || 0) : 0);
    }, 0);
    const totalProfit = car.value.data.reduce((sum, carItem) => {
        if (carItem.results != 0) {
            const cost = (Number(carItem.purchase_price) || 0) + (Number(carItem.erbil_exp) || 0) + (Number(carItem.erbil_shipping) || 0) + (Number(carItem.dubai_exp) || 0) + (Number(carItem.dubai_shipping) || 0);
            return sum + ((Number(carItem.pay_price) || 0) - cost);
        }
        return sum;
    }, 0);
    
    const carsInStock = car.value.data.filter(c => c.results == 0).length;
    const carsSold = car.value.data.filter(c => c.results != 0).length;
    
    // إنشاء محتوى الطباعة الشامل
    const printContent = `
        <div style="font-family: Arial, sans-serif; padding: 20px;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="margin-bottom: 10px; color: #2563eb;">${props.systemConfig?.company_name || 'Salam Jalal Ayoub Company'}</h1>
                <h2 style="margin-bottom: 15px;">Cars Report</h2>
                <p style="font-size: 14px; color: #666;">${new Date().toLocaleDateString('en-US')}</p>
            </div>
            
            <!-- Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Cars</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #1f2937;">${car.value.total}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Cars in Stock</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #059669;">${carsInStock}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Cars Sold</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #dc2626;">${carsSold}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Profit</h3>
                    <p style="font-size: 16px; font-weight: bold; color: ${totalProfit >= 0 ? '#059669' : '#dc2626'};">$${totalProfit.toLocaleString()}</p>
                </div>
            </div>
            
            <!-- Financial Summary -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Purchase Price</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #1f2937;">$${totalPurchasePrice.toLocaleString()}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Cost</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #1f2937;">$${totalCost.toLocaleString()}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Sale Price</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #1f2937;">$${totalSalePrice.toLocaleString()}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Remaining</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #dc2626;">$${totalRemaining.toLocaleString()}</p>
                </div>
            </div>
            
            <!-- Detailed Table -->
            <h3 style="margin-bottom: 15px; text-align: center; color: #374151;">Cars Details</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">No</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Serial</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Name</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Color</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Year</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Purchase Price</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Total Cost</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Sale Price</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Client</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Remaining</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Profit</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${car.value.data.map(carItem => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.no}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.pin}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.name}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.color}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.model}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${Number(carItem.purchase_price || 0).toLocaleString()}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${((Number(carItem.purchase_price) || 0) + (Number(carItem.erbil_exp) || 0) + (Number(carItem.erbil_shipping) || 0) + (Number(carItem.dubai_exp) || 0) + (Number(carItem.dubai_shipping) || 0)).toLocaleString()}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${Number(carItem.pay_price || 0).toLocaleString()}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.client?.name || 'N/A'}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.results != 0 ? Number(carItem.pay_price - carItem.paid_amount_pay).toLocaleString() : ''}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${carItem.results != 0 ? Number(carItem.pay_price - ((Number(carItem.purchase_price) || 0) + (Number(carItem.erbil_exp) || 0) + (Number(carItem.erbil_shipping) || 0) + (Number(carItem.dubai_exp) || 0) + (Number(carItem.dubai_shipping) || 0))).toLocaleString() : ''}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">
                                ${carItem.results == 0 ? 'In Stock' : 
                                  carItem.results == 1 ? 'Sold (Partial)' : 
                                  carItem.results == 2 ? 'Sold (Complete)' : 'N/A'}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    // فتح نافذة الطباعة
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>تقرير السيارات</title>
                <style>
                    @media print {
                        body { margin: 0; }
                        @page { margin: 1cm; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
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
    systemConfig:Object,
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
    toast.success('تم بيع السيارة بنجاح!');
    showModalCarSale.value = false;
    window.location.reload();
  })
  .catch(error => {
    console.error(error);
    toast.error('حدث خطأ في بيع السيارة');
  })
}
function conGenfirmExpenses(V) {
  fetch(`/GenExpenses?amount=${V.amount??0}&reason=${V.reason??''}&note=${V.note??''}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        toast.success(data.message);
        showModalAddGenExpenses.value = false;
        window.location.reload();
      } else {
        toast.error('حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
      }
    })
    .catch((error) => {
      console.error(error);
      toast.error('حدث خطأ في الاتصال');
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
  fetch(`/addToBox?amount=${V.amount??0}&note=${V.note??''}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        toast.success(data.message);
        showModalToBox.value = false;
        window.location.reload();
      } else {
        toast.error('حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
      }
    })
    .catch((error) => {
      console.error(error);
      toast.error('حدث خطأ في الاتصال');
    });
}
function confirmWithDrawFromBox(V) {
  fetch(`/withDrawFromBox?amount=${V.amount??0}&note=${V.note??''}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        toast.success(data.message);
        showModalFromBox.value = false;
        window.location.reload();
      } else {
        toast.error('حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
      }
    })
    .catch((error) => {
      console.error(error);
      toast.error('حدث خطأ في الاتصال');
    });
}
function confirmAddPayment(V) {
  fetch(`/addPaymentCar?car_id=${V.id}&pay_price=${V.pay_price??0}&amount=${V.amountPayment??0}&note=${V.notePayment??''}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        toast.success(data.message);
        showModalFromBox.value = false;
        window.location.reload();
      } else {
        toast.error('حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
      }
    })
    .catch((error) => {
      console.error(error);
      toast.error('حدث خطأ في الاتصال');
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
            @deletePayment="handleDeletePaymentFromModal"
            @printReceipt="handlePrintReceiptFromModal"
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
    
    <!-- Modal لعرض دفعات السيارة -->
    <div v-if="showModalCarPayments" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        دفعات السيارة: {{ selectedCar?.name }} - {{ selectedCar?.pin }}
                    </h3>
                    <button @click="showModalCarPayments = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    التاريخ
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    المبلغ
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    البيان
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    المستخدم
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    العمليات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="payment in carPayments" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ new Date(payment.created_at).toLocaleDateString('en-US') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ Number(payment.amount).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ payment.description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ payment.wallet?.user?.name || 'غير محدد' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="printPaymentReceipt(payment)"
                                            class="px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
                                            title="طباعة وصل"
                                        >
                                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click="deletePayment(payment.id)"
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
                            <tr v-if="!carPayments || carPayments.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    لا توجد دفعات مسجلة لهذه السيارة
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal لتعديل سعر البيع -->
    <div v-if="showModalEditSalePrice" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        تعديل سعر البيع: {{ formData?.name }} - {{ formData?.pin }}
                    </h3>
                    <button @click="showModalEditSalePrice = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- السعر الحالي -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            السعر الحالي
                        </label>
                        <input
                            type="text"
                            disabled
                            :value="Math.round(formData?.pay_price || 0).toLocaleString()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <!-- السعر الجديد -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            السعر الجديد
                        </label>
                        <input
                            type="number"
                            v-model="formData.newPayPrice"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="أدخل السعر الجديد"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            ملاحظة: إدخال 0 سيؤدي إلى إلغاء بيع السيارة
                        </p>
                    </div>
                    
                    <!-- المبلغ المدفوع حالياً -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            المبلغ المدفوع حالياً
                        </label>
                        <input
                            type="text"
                            disabled
                            :value="Math.round(formData?.paid_amount_pay || 0).toLocaleString()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <!-- المبلغ المتبقي بعد التعديل -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            المبلغ المتبقي بعد التعديل
                        </label>
                        <input
                            type="text"
                            disabled
                            :value="Math.round((formData?.newPayPrice || 0) - (formData?.paid_amount_pay || 0)).toLocaleString()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <!-- ملاحظة -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ملاحظة
                        </label>
                        <textarea
                            v-model="formData.editNote"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="أدخل ملاحظة حول تعديل السعر"
                        ></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="showModalEditSalePrice = false"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                    >
                        إلغاء
                    </button>
                    <button
                        @click="confirmEditSalePrice(formData)"
                        :disabled="formData.newPayPrice === '' || formData.newPayPrice === null || formData.newPayPrice === undefined"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:bg-gray-400"
                    >
                        تأكيد التعديل
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <AuthenticatedLayout>
        <div class="py-2">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm ">
                <div class="p-6  dark:bg-gray-900">
                    <div class="flex flex-col">
                      <!-- فلاتر السيارات -->
                      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                        <!-- البحث -->
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
                                placeholder="بحث بالرقم التسلسلي"
                                required
                              />
                            </div>
                          </form>
                        </div>
                        
                        <!-- فلتر حالة السيارة -->
                        <div>
                          <select v-model="carStatusFilter" @change="applyFilters" 
                                  class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="">جميع السيارات</option>
                            <option value="0">في المخزن</option>
                            <option value="1">مباعة (غير مكتملة)</option>
                            <option value="2">مباعة (مكتملة)</option>
                          </select>
                        </div>
                        
                        <!-- فلتر حالة الدفع -->
                        <div>
                          <select v-model="paymentStatusFilter" @change="applyFilters" 
                                  class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="">جميع حالات الدفع</option>
                            <option value="unpaid">غير مدفوع</option>
                            <option value="partial">مدفوع جزئياً</option>
                            <option value="paid">مدفوع بالكامل</option>
                          </select>
                        </div>
                        
                        <!-- زر طباعة -->
                        <div>
                          <button @click="printCarsTable" 
                                  class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>طباعة</span>
                          </button>
                        </div>
                        
                        <!-- زر إعادة تعيين الفلاتر -->
                        <div>
                          <button @click="resetFilters" 
                                  class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>إعادة تعيين</span>
                          </button>
                        </div>
                      </div>
                      
                      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 lg:gap-1">
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
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ Number(car.purchase_price || 0).toLocaleString() }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.source  }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ Number(car.dubai_shipping || 0).toLocaleString() }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ Number(car.dubai_exp || 0).toLocaleString() }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ Number(car.erbil_shipping || 0).toLocaleString() }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ Number(car.erbil_exp || 0).toLocaleString() }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ (Number(car.purchase_price) || 0) + (Number(car.erbil_exp) || 0) + (Number(car.erbil_shipping) || 0) + (Number(car.dubai_exp) || 0) + (Number(car.dubai_shipping) || 0) }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ Number(car.pay_price || 0).toLocaleString() }}</td> 
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.client?.name }} </td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.results != 0 ? Number(car.pay_price - car.paid_amount_pay).toLocaleString() : '' }}</td>
                                    <td className="border dark:border-gray-800 text-center px-4 py-2 text-base">{{ car.results != 0 ? Number(car.pay_price - ((Number(car.purchase_price) || 0) + (Number(car.erbil_exp) || 0) + (Number(car.erbil_shipping) || 0) + (Number(car.dubai_exp) || 0) + (Number(car.dubai_shipping) || 0))).toLocaleString() : '' }}</td>
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
                                      v-if="car.results == 0 || car.results == ''"
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
                                            class="px-2 py-1 text-base text-white mx-1 bg-yellow-700 rounded"
                                            v-if="car.results != 0 "
                                            @click="openEditSalePrice(car)"
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
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ Math.round(stats.totalCapital).toLocaleString() }}</p>
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
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ Math.round(stats.totalFundIncome).toLocaleString() }}</p>
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
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ Math.round(stats.totalExpenses).toLocaleString() }}</p>
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
                              <p class="mt-2 text-sm text-gray-500 dark:text-gray-200">{{ Math.round(stats.totalDebt).toLocaleString() }}</p>
                            </div>
                          </div>
                          <div class="flex items-start rounded-xl dark:bg-gray-600 dark:text-gray-300 bg-white p-4 shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-purple-100 bg-purple-50">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
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
    
    <!-- Modal لتعديل المبلغ المدفوع -->
    <div v-if="showModalEditPaidAmount" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        تعديل المبلغ المدفوع: {{ formData?.name }} - {{ formData?.pin }}
                    </h3>
                    <button @click="showModalEditPaidAmount = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- المبلغ المدفوع حالياً -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            المبلغ المدفوع حالياً
                        </label>
                        <input
                            type="text"
                            disabled
                            :value="Math.round(formData?.paid_amount_pay || 0).toLocaleString()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <!-- المبلغ الجديد -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            المبلغ الجديد
                        </label>
                        <input
                            type="number"
                            v-model="formData.newPaidAmount"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="أدخل المبلغ الجديد"
                        />
                    </div>
                    
                    <!-- الفرق -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            الفرق
                        </label>
                        <input
                            type="text"
                            disabled
                            :value="Math.round((formData?.newPaidAmount || 0) - (formData?.paid_amount_pay || 0)).toLocaleString()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                    
                    <!-- ملاحظة -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            ملاحظة
                        </label>
                        <textarea
                            v-model="formData.editNote"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="أدخل ملاحظة حول التعديل"
                        ></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="showModalEditPaidAmount = false"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                    >
                        إلغاء
                    </button>
                    <button
                        @click="confirmEditPaidAmount(formData)"
                        :disabled="formData.newPaidAmount === '' || formData.newPaidAmount === null || formData.newPaidAmount === undefined"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:bg-gray-400"
                    >
                        تأكيد التعديل
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal لعرض نتائج حساب الربح -->
    <div v-if="showProfitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        نتائج حساب وتوزيع الربح
                    </h3>
                    <button @click="showProfitModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div v-if="profitCalculationResult" class="space-y-6">
                    <!-- معلومات السيارة -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3">معلومات السيارة</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">رقم السيارة</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ profitCalculationResult.car?.no }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">اسم السيارة</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ profitCalculationResult.car?.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">التكلفة الإجمالية</label>
                                <p class="text-sm text-gray-900 dark:text-white">${{ Number(profitCalculationResult.car?.total_cost || 0).toLocaleString() }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">سعر البيع</label>
                                <p class="text-sm text-gray-900 dark:text-white">${{ Number(profitCalculationResult.car?.sale_price || 0).toLocaleString() }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">الربح الإجمالي</label>
                                <p class="text-sm font-bold text-green-600 dark:text-green-400">${{ Number(profitCalculationResult.car?.profit || 0).toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تفاصيل المستثمرين -->
                    <div v-if="profitCalculationResult.investors && profitCalculationResult.investors.length > 0">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3">تفاصيل المستثمرين</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">اسم المستثمر</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المبلغ المستثمر</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">النسبة المئوية</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">نصيب الربح</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="investor in profitCalculationResult.investors" :key="investor.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ investor.user_name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${{ Number(investor.invested_amount || 0).toLocaleString() }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ Number(investor.percentage || 0).toFixed(2) }}%</td>
                                        <td class="px-4 py-2 text-sm font-bold text-green-600 dark:text-green-400">${{ Number(investor.profit_share || 0).toLocaleString() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- إجمالي الربح الموزع -->
                        <div class="mt-4 bg-green-50 dark:bg-green-900 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-green-800 dark:text-green-200">إجمالي الربح الموزع:</span>
                                <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                    ${{ Number(profitCalculationResult.investors.reduce((sum, investor) => sum + (investor.profit_share || 0), 0)).toLocaleString() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        لا يوجد مستثمرين في هذه السيارة
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="showProfitModal = false"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                    >
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
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

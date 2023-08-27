<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const form = useForm({
    name: '',
    phone:''
});
defineProps({
    usersType: Array,
    coordinators :Array,
    userSeles:String,
    userHospital:String,
    userDoctor:String,
});

const submit = () => {
    form.post(route('clientsStore'));
};

 
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout v-if="$page.props.auth.user.type_id==1">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                {{ $t('addCustomer') }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6  dark:bg-gray-900">

                        <div className="flex items-center justify-between mb-6">
                            <Link
                                className="px-6 py-2 text-white bg-gray-500 rounded-md focus:outline-none"
                                :href="route('users.index')"
                            >
                                {{ $t('return') }}
                            </Link>
                        </div>

                 

                    <form name="createForm" @submit.prevent="submit">
                    <div class="flex flex-col">
                        <div class="mb-4">
                        <InputLabel for="name" :value="$t('name')" />
                        <TextInput 
                            id="name" 
                            type="text" 
                            class="mt-1 block w-full" 
                            v-model="form.name" 
                            autofocus 
                        />
                        <span class="text-red-600" v-if="form.errors.name">
                            {{ $t('nameRequired') }}
                        </span>
                        </div>

                        <div class="mb-4">
                        <InputLabel for="phone" :value="$t('phone')" />
                        <TextInput 
                            id="phone" 
                            type="number" 
                            class="mt-1 block w-full" 
                            v-model="form.phone" 
                        />
                        </div>
                    </div>

                    <div class="mt-4">
                        <button
                        type="submit"
                        class="px-6 py-2 font-bold text-white bg-rose-500 rounded"
                        >
                        {{ $t('save') }}
                        </button>
                    </div>
                    </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
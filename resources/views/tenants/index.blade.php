@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">إدارة المستأجرين</h1>
        <a href="{{ route('tenants.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            إضافة مستأجر جديد
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @foreach($tenants as $tenant)
            <li>
                <div class="px-4 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">{{ substr($tenant->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $tenant->name }}</div>
                            <div class="text-sm text-gray-500">{{ $tenant->email }}</div>
                            <div class="text-sm text-gray-500">
                                الدومينات: 
                                @foreach($tenant->domains as $domain)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                        {{ $domain->domain }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($tenant->status === 'active') bg-green-100 text-green-800
                            @elseif($tenant->status === 'inactive') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $tenant->status === 'active' ? 'نشط' : ($tenant->status === 'inactive' ? 'غير نشط' : 'معلق') }}
                        </span>
                        
                        <div class="flex space-x-1">
                            <a href="{{ route('tenants.show', $tenant->id) }}" class="text-blue-600 hover:text-blue-900">
                                عرض
                            </a>
                            <a href="{{ route('tenants.edit', $tenant->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                تعديل
                            </a>
                            
                            @if($tenant->status === 'active')
                                <form method="POST" action="{{ route('tenants.suspend', $tenant->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                        تعليق
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('tenants.activate', $tenant->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                        تفعيل
                                    </button>
                                </form>
                            @endif
                            
                            <form method="POST" action="{{ route('tenants.destroy', $tenant->id) }}" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستأجر؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</div>
@endsection

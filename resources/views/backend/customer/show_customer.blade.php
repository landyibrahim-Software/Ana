<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8" style="direction: ltr; text-align: left;">
    
    {{-- HEADER SECTION --}}
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">پڕۆفایلی کڕیار</h1>
                </div>
                <p class="text-lg text-gray-600">{{ $customer->name }}</p>
                <div class="flex items-center mt-2 space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $customer->orders->count() }} Orders
                    </span>
                </div>
            </div>
            <div class="mt-4 md:mt-0"></div>
            
            <div class="mt-4 md:mt-0">
                <a href="{{ route('all.customer') }}"
                   class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-black font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    گەڕانەوە بۆ بەشی کڕیار
                </a>
            </div>
        </div>

        {{-- CALCULATE TOTALS --}}
        @php
            $total_paid = $customer->orders->sum('pay');
            $total_due = $customer->orders->sum('due');
            $total_amount = $total_paid + $total_due;
        @endphp

        {{-- FINANCIAL SUMMARY CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-500 rounded-2xl shadow-lg p-6 transform hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">کۆی گشتی پارەی دراو</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($total_paid, 2) }} <span class="text-lg text-green-700"></span>USD</p>
                    </div>
                    <div class="p-3 bg-green-200 rounded-xl">
                        <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-green-200">
                    <p class="text-sm text-green-700">{{ $total_amount > 0 ? round(($total_paid / $total_amount) * 100, 1) : 0 }}% کۆی گشتی</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-500 rounded-2xl shadow-lg p-6 transform hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 mb-1">کۆی گشتی قەرز</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($total_due, 2) }} <span class="text-lg text-red-700"></span>USD</p>
                    </div>
                    <div class="p-3 bg-red-200 rounded-xl">
                        <svg class="w-8 h-8 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-red-200">
                    <p class="text-sm text-red-700">{{ $total_amount > 0 ? round(($total_due / $total_amount) * 100, 1) : 0 }}% کۆی گشتی</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-2xl shadow-lg p-6 transform hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">کۆی گشتی بڕ</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($total_amount, 2) }} <span class="text-lg text-blue-700"></span>USD</p>
                    </div>
                    <div class="p-3 bg-blue-200 rounded-xl">
                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-blue-200">
                    <p class="text-sm text-blue-700">کۆی گشتی</p>
                </div>
            </div>
        </div>

        {{-- ORDERS TABLE SECTION --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">مێژوی داواکاریەکان</h2>
                    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $customer->orders->count() }} بسوڵە
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ژمارەی بسوڵە</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">کۆی گشتی بڕ</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">پارەی دراو</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">قەرز</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">دۆخ</th>
                        </tr>
                    </thead>
                    
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($customer->orders as $order)
                            @php
                                $invoice_due = $order->due;
                                $is_paid = $invoice_due == 0;
                                $payment_percentage = $order->total > 0 ? round(($order->pay / $order->total) * 100, 0) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <span class="text-indigo-800 font-bold">#{{ $order->id }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Invoice #{{ $order->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ number_format($order->total, 2) }} </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-green-600">{{ number_format($order->pay, 2) }} </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $payment_percentage }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $payment_percentage }}% paid</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold {{ $is_paid ? 'text-green-700' : 'text-red-700' }}">
                                        {{ number_format($invoice_due, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($is_paid)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            پارە دراو
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            قەرز
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($customer->orders->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">هیچ داواکاریەک نەدۆزرایەوە</h3>
                    <p class="mt-1 text-gray-500">ئەم کڕیارە هێشتا هیچ داواکاریێک نەدۆزرایەوە.</p>
                </div>
            @endif
        </div>

        {{-- FINAL SUMMARY --}}
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 mb-8">
            <h3 class="text-xl font-bold text-black mb-4">پوختەی پارەدان</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-800 bg-opacity-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-300">کۆی پارەی دراو</span>
                        <span class="text-2xl font-bold text-white">{{ number_format($total_paid, 2) }} USD</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $total_amount > 0 ? round(($total_paid / $total_amount) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="bg-gray-800 bg-opacity-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-300">کۆی گشتی قەرز</span>
                        <span class="text-2xl font-bold text-white">{{ number_format($total_due, 2) }} USD </span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full" style="width: {{ $total_amount > 0 ? round(($total_due / $total_amount) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM NAVIGATION --}}
        <div class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-600 mb-4 sm:mb-0">
                <span class="font-medium">Customer ID:</span> {{ $customer->id }}
                <span class="mx-2">•</span>
                <span>ئاخیر ئپدەیت: {{ now()->format('M d, Y') }}</span>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('all.customer') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 text-gray-800 font-medium rounded-xl hover:bg-gray-50 shadow-sm hover:shadow transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    هەموو کڕیارەکان
                </a>
                <button onclick="window.print()"
                   class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 text-gray-800 font-medium rounded-xl hover:bg-gray-50 shadow-sm hover:shadow transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    چاپکردنی پوختە
                </button>
            </div>
        </div>
    </div>
</div>

{{-- STYLE ENHANCEMENTS --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
    }
    
    table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th {
        background: linear-gradient(to bottom, #f9fbfa, #f3f4f6);
    }
    
    tr {
        transition: all 0.2s ease;
    }
    
    .rounded-xl {
        border-radius: 0.75rem;
    }
    
    .rounded-2xl {
        border-radius: 1rem;
    }
    
    .shadow-lg {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .shadow-xl {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
</x-app-layout>
@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Registered Devices</h1>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-gray-700">Authorized Tablets</h2>
            <button class="btn btn-sm btn-secondary">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Device
            </button>
        </div>

        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4">Device Name</th>
                    <th class="p-4">IP Address</th>
                    <th class="p-4">Last Active</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <tr>
                    <td class="p-4 font-bold text-gray-700">Main Kitchen Tablet</td>
                    <td class="p-4 font-mono text-gray-500">192.168.1.45</td>
                    <td class="p-4 text-green-600 text-sm">Just now</td>
                    <td class="p-4"><span
                            class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Authorized</span></td>
                    <td class="p-4 text-right"><button
                            class="text-red-500 hover:text-red-700 font-medium text-sm">Revoke</button></td>
                </tr>
                <!-- Placeholder row -->
                <tr>
                    <td class="p-4 font-bold text-gray-700">Pastry Section Tab</td>
                    <td class="p-4 font-mono text-gray-500">192.168.1.46</td>
                    <td class="p-4 text-gray-400 text-sm">2 hours ago</td>
                    <td class="p-4"><span
                            class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Authorized</span></td>
                    <td class="p-4 text-right"><button
                            class="text-red-500 hover:text-red-700 font-medium text-sm">Revoke</button></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
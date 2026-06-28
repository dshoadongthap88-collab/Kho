@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Hệ Thống Quản Lý Bảo Dưỡng (ERP)
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Dashboard cảnh báo & Danh sách thiết bị -->
            <div class="col-span-1 md:col-span-2 mb-8">
                @livewire('maintenance.asset-maintenance-dashboard')
            </div>

            <!-- Quản lý cập nhật ODO hàng loạt -->
            <div class="col-span-1 md:col-span-2 mb-8">
                @livewire('maintenance.daily-odo-manager')
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cập nhật ca làm việc -->
                <div class="col-span-1">
                    @livewire('maintenance.shift-log-form')
                </div>

                <!-- Xác nhận hoàn thành bảo dưỡng -->
                <div class="col-span-1">
                    @livewire('maintenance.ticket-completion-form')
                </div>
            </div>

            <!-- Danh sách phiếu bảo dưỡng -->
            <div class="col-span-1 md:col-span-2 mt-8">
                @livewire('maintenance.ticket-list')
            </div>

        </div>
    </div>
@endsection

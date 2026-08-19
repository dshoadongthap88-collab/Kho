<?php if (isset($component)) { $__componentOriginalc9242005886028143da563f7b99f0c87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc9242005886028143da563f7b99f0c87 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.warehouse-layout','data' => ['title' => 'Sản phẩm / Vật tư']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('warehouse-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sản phẩm / Vật tư']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="bg-white rounded-lg shadow p-2 border-t-4 border-pink-500">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Danh sách Sản phẩm / Vật tư</h2>
            <button class="bg-pink-600 hover:bg-pink-700 text-white font-medium py-2 px-4 rounded transition shadow-sm">
                + Thêm Sản phẩm
            </button>
        </div>
        <p class="text-gray-500 mb-6">Quản lý danh sách thành phẩm, nguyên vật liệu và cập nhật thông tin chung.</p>
        
        <div class="bg-blue-50 border-l-4 border-blue-400 p-2 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">Giao diện danh sách Sản phẩm đang được xây dựng (Coming soon). Chức năng CRUD cho Sản phẩm sẽ được hiển thị tại đây.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-600 font-medium text-sm">
                    <tr>
                        <th class="px-2 py-2 text-left">Mã SP</th>
                        <th class="px-2 py-2 text-left">Tên sản phẩm</th>
                        <th class="px-2 py-2 text-left">Phân loại</th>
                        <th class="px-2 py-2 text-left">Đơn vị</th>
                        <th class="px-2 py-2 text-left">Giá bán</th>
                        <th class="px-2 py-2 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-2 py-1.5 text-sm text-gray-500 text-center" colspan="6">Chưa có dữ liệu sản phẩm</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc9242005886028143da563f7b99f0c87)): ?>
<?php $attributes = $__attributesOriginalc9242005886028143da563f7b99f0c87; ?>
<?php unset($__attributesOriginalc9242005886028143da563f7b99f0c87); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc9242005886028143da563f7b99f0c87)): ?>
<?php $component = $__componentOriginalc9242005886028143da563f7b99f0c87; ?>
<?php unset($__componentOriginalc9242005886028143da563f7b99f0c87); ?>
<?php endif; ?>
<?php /**PATH D:\Project\resources\views\warehouse\products.blade.php ENDPATH**/ ?>
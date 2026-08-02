<?php if (isset($component)) { $__componentOriginalc9242005886028143da563f7b99f0c87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc9242005886028143da563f7b99f0c87 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.warehouse-layout','data' => ['title' => 'Khách hàng/NCC']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('warehouse-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Khách hàng/NCC']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
        <p class="text-sm"><strong>ℹ️ Thông báo:</strong> Module này đã được chia thành 2 module riêng biệt:</p>
        <ul class="mt-2 ml-4 text-sm">
            <li>• <a href="<?php echo e(route('warehouse.customer-supplier')); ?>" class="underline font-semibold hover:text-blue-900">📋 Danh sách Khách hàng/NCC</a></li>
            <li>• <a href="<?php echo e(route('warehouse.purchase-order')); ?>" class="underline font-semibold hover:text-blue-900">📦 Đơn đặt hàng</a></li>
        </ul>
    </div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('warehouse.contact-manager', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1326140283-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
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
<?php /**PATH D:\Project\resources\views\warehouse\contact-manager.blade.php ENDPATH**/ ?>
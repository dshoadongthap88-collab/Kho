<div class="h-[calc(100vh-8rem)] flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" wire:poll.3s>
    <!-- Header -->
    <div class="bg-sky-600 px-6 py-4 flex items-center justify-between shadow-sm z-10">
        <div class="flex items-center gap-3">
            <div class="bg-white p-2 rounded-full shadow-inner">
                <span class="text-xl">💬</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white leading-tight">Phòng Chat Chung Toàn Hệ Thống</h2>
                <p class="text-xs text-sky-100">Mọi nhân viên từ các dự án đều có thể trò chuyện tại đây</p>
            </div>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto p-6 bg-slate-50 space-y-6" id="chat-messages-container">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $isMe = $msg->user_id === auth()->id();
                // Render mentions
                $content = e($msg->content ?? '');
                // Bôi xanh và in đậm @TagName
                $content = preg_replace('/@([\p{L}\p{N}\s_]+?)(?=\s|$|<|,|\.|:)/u', '<span class="font-bold text-sky-600 bg-sky-50 px-1 rounded border border-sky-100">@$1</span>', $content);
                // Highlight nếu mình được tag
                $isMentioned = str_contains($content, '@' . auth()->user()->name);
            ?>
            <div class="flex group <?php echo e($isMe ? 'justify-end' : 'justify-start'); ?>">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMe): ?>
                    <button wire:click="setReply(<?php echo e($msg->id); ?>)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sky-500 transition-opacity p-2 rounded-full hover:bg-gray-100 mr-2 self-end mb-4" title="Trả lời">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="flex flex-col <?php echo e($isMe ? 'items-end' : 'items-start'); ?> max-w-[70%]">
                    <!-- Tên người gửi -->
                    <span class="text-[11px] text-gray-500 font-medium mb-1 px-1">
                        <?php echo e($isMe ? 'Bạn' : ($msg->sender->name ?? 'Hệ thống')); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isMe && isset($msg->sender->role)): ?>
                            - <?php echo e($msg->sender->role === 'admin' ? 'Admin' : 'Kho'); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </span>
                    
                    <!-- Nội dung bong bóng -->
                    <div class="relative px-4 py-3 text-sm <?php echo e($isMentioned && !$isMe ? 'ring-2 ring-yellow-400' : ''); ?> <?php echo e($msg->type === 'system' ? 'bg-amber-100 text-amber-900 border border-amber-200 rounded-xl' : ($isMe ? 'bg-sky-500 text-white rounded-2xl rounded-tr-sm shadow-md' : 'bg-white text-gray-800 rounded-2xl rounded-tl-sm shadow-sm border border-gray-100')); ?>">
                        
                        <!-- Reply block -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->repliedMessage): ?>
                            <div class="mb-2 p-2 text-xs rounded-lg border-l-4 <?php echo e($isMe ? 'bg-sky-600 border-sky-200 text-sky-100' : 'bg-gray-50 border-sky-400 text-gray-500'); ?>">
                                <div class="font-bold mb-1"><?php echo e($msg->repliedMessage->sender->name ?? 'Ai đó'); ?></div>
                                <div class="truncate opacity-80"><?php echo e($msg->repliedMessage->type === 'image' ? '[Hình ảnh]' : $msg->repliedMessage->content); ?></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->attachment_path): ?>
                            <div class="mb-2">
                                <a href="<?php echo e(asset('storage/' . $msg->attachment_path)); ?>" target="_blank">
                                    <img src="<?php echo e(asset('storage/' . $msg->attachment_path)); ?>" class="max-w-xs rounded-lg border <?php echo e($isMe ? 'border-sky-400' : 'border-gray-200'); ?> hover:opacity-90 transition-opacity">
                                </a>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->content): ?>
                            <div class="whitespace-pre-wrap <?php echo e($msg->type === 'system' ? 'font-bold' : ''); ?>"><?php echo $msg->type === 'system' ? $content : $content; ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="text-[10px] <?php echo e($isMe ? 'text-sky-100' : 'text-gray-400'); ?> mt-2 text-right flex justify-between items-center gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMentioned && !$isMe): ?> <span class="text-yellow-600 font-bold bg-yellow-100 px-1 rounded">Bạn được nhắc!</span> <?php else: ?> <span></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span><?php echo e($msg->created_at->format('H:i d/m')); ?></span>
                        </div>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isMe): ?>
                    <button wire:click="setReply(<?php echo e($msg->id); ?>)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sky-500 transition-opacity p-2 rounded-full hover:bg-gray-100 ml-2 self-end mb-4" title="Trả lời">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                <span class="text-4xl mb-3">📭</span>
                <p>Chưa có tin nhắn nào. Hãy là người bắt đầu!</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t border-gray-200 relative">
        
        <!-- Cửa sổ gợi ý @Mention (AlpineJS) -->
        <div x-data="mentionHandler()" class="absolute bottom-full left-0 w-full bg-white border-t border-gray-200 shadow-lg z-20 max-h-48 overflow-y-auto" x-show="showMentions" x-cloak>
            <ul class="py-1">
                <template x-for="(user, index) in filteredUsers" :key="user.id">
                    <li @click="selectUser(user.name)" class="px-4 py-2 cursor-pointer hover:bg-sky-50 text-sm flex items-center gap-2" :class="{'bg-sky-100': index === selectedIndex}">
                        <div class="w-6 h-6 bg-sky-500 text-white rounded-full flex items-center justify-center font-bold text-xs" x-text="user.name.charAt(0)"></div>
                        <span x-text="user.name" class="font-bold text-gray-800"></span>
                    </li>
                </template>
                <li x-show="filteredUsers.length === 0" class="px-4 py-2 text-sm text-gray-500 text-center">Không tìm thấy người nào</li>
            </ul>
        </div>

        <div class="p-4">
            <!-- Reply Preview box -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reply_to_id): ?>
                <?php
                    $replyMsg = \App\Models\ChatMessage::find($reply_to_id);
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($replyMsg): ?>
                <div class="mb-3 bg-sky-50 border-l-4 border-sky-400 p-2 rounded-r flex justify-between items-center text-sm shadow-sm relative animate-fade-in-up">
                    <div class="overflow-hidden">
                        <div class="font-bold text-sky-700">Đang trả lời <?php echo e($replyMsg->sender->name ?? 'Ai đó'); ?>:</div>
                        <div class="text-gray-600 truncate"><?php echo e($replyMsg->type === 'image' ? '[Hình ảnh]' : $replyMsg->content); ?></div>
                    </div>
                    <button type="button" wire:click="cancelReply" class="text-gray-400 hover:text-red-500 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form wire:submit.prevent="sendMessage" class="flex gap-3 items-end relative">
                <div class="flex-1 relative">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
                        <div class="absolute bottom-full mb-2 left-0">
                            <div class="relative inline-block">
                                <img src="<?php echo e($image->temporaryUrl()); ?>" class="h-24 rounded-lg border border-gray-200 shadow-sm">
                                <button type="button" wire:click="$set('image', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <textarea 
                        x-data 
                        x-ref="chatInput"
                        @keydown="handleKeydown"
                        @input="handleInput"
                        wire:model="message" 
                        id="chatTextarea"
                        rows="2" 
                        class="w-full resize-none rounded-xl border border-gray-300 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 p-3 pr-12 text-sm shadow-sm" 
                        placeholder="Nhập tin nhắn... Gõ @ để nhắc tên" 
                        wire:keydown.enter.prevent="sendMessage"></textarea>
                    
                    <div class="absolute right-2 bottom-3">
                        <input type="file" id="chat-image-upload" wire:model="image" class="hidden" accept="image/*">
                        <label for="chat-image-upload" class="cursor-pointer p-2 text-gray-400 hover:text-sky-500 transition-colors inline-block" title="Đính kèm hình ảnh">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </label>
                    </div>
                </div>

                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-colors flex items-center gap-2 h-[50px]">
                    <span>Gửi</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Script to scroll to bottom automatically & Handle Mentions -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            const container = document.getElementById('chat-messages-container');
            if (container) container.scrollTop = container.scrollHeight;
            
            Livewire.on('message-sent', () => {
                setTimeout(() => {
                    const container = document.getElementById('chat-messages-container');
                    if (container) container.scrollTop = container.scrollHeight;
                }, 100);
            });
        });

        // AlpineJS Mention Logic
        document.addEventListener('alpine:init', () => {
            Alpine.data('mentionHandler', () => ({
                users: <?php echo json_encode($activeUsers, 15, 512) ?>,
                showMentions: false,
                query: '',
                selectedIndex: 0,
                cursorPosition: 0,
                
                get filteredUsers() {
                    if (!this.query) return this.users;
                    const q = this.query.toLowerCase();
                    return this.users.filter(u => u.name.toLowerCase().includes(q) || (u.username && u.username.toLowerCase().includes(q)));
                },

                handleInput(e) {
                    const val = e.target.value;
                    const cursorPos = e.target.selectionStart;
                    
                    // Tìm chữ @ gần nhất trước con trỏ
                    const textBeforeCursor = val.substring(0, cursorPos);
                    const lastAtPos = textBeforeCursor.lastIndexOf('@');
                    
                    if (lastAtPos !== -1) {
                        // Check xem sau @ có khoảng trắng không, nếu có thì hủy bỏ
                        const textAfterAt = textBeforeCursor.substring(lastAtPos + 1);
                        if (!textAfterAt.includes(' ')) {
                            this.query = textAfterAt;
                            this.showMentions = true;
                            this.selectedIndex = 0;
                            this.cursorPosition = lastAtPos;
                            return;
                        }
                    }
                    this.showMentions = false;
                },

                handleKeydown(e) {
                    if (!this.showMentions) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (this.selectedIndex < this.filteredUsers.length - 1) this.selectedIndex++;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (this.selectedIndex > 0) this.selectedIndex--;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        e.stopPropagation(); // Ngăn Livewire submit form
                        if (this.filteredUsers.length > 0) {
                            this.selectUser(this.filteredUsers[this.selectedIndex].name);
                        }
                    } else if (e.key === 'Escape') {
                        this.showMentions = false;
                    }
                },

                selectUser(name) {
                    const input = document.getElementById('chatTextarea');
                    const val = input.value;
                    
                    const beforeAt = val.substring(0, this.cursorPosition);
                    const afterCursor = val.substring(input.selectionStart);
                    
                    // Nối chữ @, tên người dùng và một khoảng trắng
                    const newValue = beforeAt + '@' + name + ' ' + afterCursor;
                    
                    // Set value for alpine
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('message', newValue);
                    
                    this.showMentions = false;
                    
                    // Focus back to input
                    setTimeout(() => {
                        input.focus();
                        const newPos = this.cursorPosition + name.length + 2;
                        input.setSelectionRange(newPos, newPos);
                    }, 50);
                }
            }));
        });
    </script>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/warehouse-chat.blade.php ENDPATH**/ ?>
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
    <div class="flex-1 overflow-y-auto p-2 bg-slate-50 space-y-6" id="chat-messages-container">
        @forelse($messages as $msg)
            @php
                $isMe = $msg->user_id === auth()->id();
                // Render mentions
                $content = e($msg->content ?? '');
                // Bôi xanh và in đậm @TagName
                $content = preg_replace('/@([\p{L}\p{N}\s_]+?)(?=\s|$|<|,|\.|:)/u', '<span class="font-bold text-sky-600 bg-sky-50 px-1 rounded border border-sky-100">@$1</span>', $content);
                // Highlight nếu mình được tag
                $isMentioned = str_contains($content, '@' . auth()->user()->name);
            @endphp
            <div class="flex group {{ $isMe ? 'justify-end' : 'justify-start' }}">
                
                @if($isMe)
                    <button wire:click="setReply({{ $msg->id }})" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sky-500 transition-opacity p-2 rounded-full hover:bg-gray-100 mr-2 self-end mb-4" title="Trả lời">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    </button>
                @endif

                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[70%]">
                    <!-- Tên người gửi -->
                    <span class="text-[11px] text-gray-500 font-medium mb-1 px-1">
                        {{ $isMe ? 'Bạn' : ($msg->sender->name ?? 'Hệ thống') }}
                        @if(!$isMe && isset($msg->sender->role))
                            - {{ $msg->sender->role === 'admin' ? 'Admin' : 'Kho' }}
                        @endif
                    </span>
                    
                    <!-- Nội dung bong bóng -->
                    <div class="relative px-4 py-3 text-sm {{ $isMentioned && !$isMe ? 'ring-2 ring-yellow-400' : '' }} {{ $msg->type === 'system' ? 'bg-amber-100 text-amber-900 border border-amber-200 rounded-xl' : ($isMe ? 'bg-sky-500 text-white rounded-2xl rounded-tr-sm shadow-md' : 'bg-white text-gray-800 rounded-2xl rounded-tl-sm shadow-sm border border-gray-100') }}">
                        
                        <!-- Reply block -->
                        @if($msg->repliedMessage)
                            <div class="mb-2 p-2 text-xs rounded-lg border-l-4 {{ $isMe ? 'bg-sky-600 border-sky-200 text-sky-100' : 'bg-gray-50 border-sky-400 text-gray-500' }}">
                                <div class="font-bold mb-1">{{ $msg->repliedMessage->sender->name ?? 'Ai đó' }}</div>
                                <div class="truncate opacity-80">{{ $msg->repliedMessage->type === 'image' ? '[Hình ảnh]' : $msg->repliedMessage->content }}</div>
                            </div>
                        @endif

                        @if($msg->attachment_path)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $msg->attachment_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $msg->attachment_path) }}" class="max-w-xs rounded-lg border {{ $isMe ? 'border-sky-400' : 'border-gray-200' }} hover:opacity-90 transition-opacity">
                                </a>
                            </div>
                        @endif
                        
                        @if($msg->content)
                            <div class="whitespace-pre-wrap {{ $msg->type === 'system' ? 'font-bold' : '' }}">{!! $msg->type === 'system' ? $content : $content !!}</div>
                        @endif
                        
                        <div class="text-[10px] {{ $isMe ? 'text-sky-100' : 'text-gray-400' }} mt-2 text-right flex justify-between items-center gap-2">
                            @if($isMentioned && !$isMe) <span class="text-yellow-600 font-bold bg-yellow-100 px-1 rounded">Bạn được nhắc!</span> @else <span></span> @endif
                            <span>{{ $msg->created_at->format('H:i d/m') }}</span>
                        </div>
                    </div>
                </div>

                @if(!$isMe)
                    <button wire:click="setReply({{ $msg->id }})" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sky-500 transition-opacity p-2 rounded-full hover:bg-gray-100 ml-2 self-end mb-4" title="Trả lời">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    </button>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                <span class="text-4xl mb-3">📭</span>
                <p>Chưa có tin nhắn nào. Hãy là người bắt đầu!</p>
            </div>
        @endforelse
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

        <div class="p-2">
            <!-- Reply Preview box -->
            @if($reply_to_id)
                @php
                    $replyMsg = \App\Models\ChatMessage::find($reply_to_id);
                @endphp
                @if($replyMsg)
                <div class="mb-3 bg-sky-50 border-l-4 border-sky-400 p-2 rounded-r flex justify-between items-center text-sm shadow-sm relative animate-fade-in-up">
                    <div class="overflow-hidden">
                        <div class="font-bold text-sky-700">Đang trả lời {{ $replyMsg->sender->name ?? 'Ai đó' }}:</div>
                        <div class="text-gray-600 truncate">{{ $replyMsg->type === 'image' ? '[Hình ảnh]' : $replyMsg->content }}</div>
                    </div>
                    <button type="button" wire:click="cancelReply" class="text-gray-400 hover:text-red-500 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                @endif
            @endif

            <form wire:submit.prevent="sendMessage" class="flex gap-3 items-end relative">
                <div class="flex-1 relative">
                    @if($image)
                        <div class="absolute bottom-full mb-2 left-0">
                            <div class="relative inline-block">
                                <img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg border border-gray-200 shadow-sm">
                                <button type="button" wire:click="$set('image', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    @endif
                    
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
            @error('message') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            @error('image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                users: @json($activeUsers),
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
                    @this.set('message', newValue);
                    
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

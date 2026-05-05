<template>
    <div class="fixed bottom-6 right-6 z-[9999] font-sans">
        <!-- Chat Button -->
        <button 
            @click="toggleChat"
            class="bg-blue-600 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition transform hover:scale-105"
        >
            <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Chat Window -->
        <div v-if="isOpen" class="absolute bottom-16 right-0 w-[350px] sm:w-[400px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-200 transition-all" style="height: 550px;">
            <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-4 font-semibold flex justify-between items-center shadow-md">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm">Support Assistant</div>
                        <div class="text-xs text-blue-100 font-normal">Online - Replies instantly</div>
                    </div>
                </div>
                <button @click="toggleChat" class="text-white hover:text-gray-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-4 scroll-smooth" ref="chatBox">
                <div v-if="messages.length === 0" class="text-center text-gray-500 text-sm mt-10">
                    Hello! How can I help you today?
                </div>
                <div v-for="msg in messages" :key="msg.id" 
                     :class="['max-w-[85%] rounded-2xl px-4 py-2.5 text-sm shadow-sm', 
                              msg.role === 'user' 
                              ? 'bg-blue-600 text-white self-end rounded-br-none' 
                              : 'bg-white text-gray-800 self-start border border-gray-100 rounded-bl-none']">
                    {{ msg.content }}
                </div>
                <div v-if="isLoading" class="bg-white border border-gray-100 text-gray-800 self-start rounded-2xl rounded-bl-none px-4 py-3 text-sm flex gap-1 items-center shadow-sm">
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                </div>
            </div>

            <div class="p-3 bg-white border-t border-gray-100 flex gap-2">
                <input 
                    v-model="newMessage" 
                    @keyup.enter="sendMessage"
                    type="text" 
                    class="flex-1 bg-gray-100 border-transparent rounded-full px-5 py-2.5 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition text-sm"
                    placeholder="Type your message..."
                    :disabled="isLoading"
                />
                <button @click="sendMessage" :disabled="isLoading || !newMessage.trim()" class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-90" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ChatWidget',
    data() {
        return {
            isOpen: false,
            messages: [],
            newMessage: '',
            isLoading: false,
            sessionId: this.generateSessionId()
        }
    },
    mounted() {
        this.fetchHistory();
        
        // Optional: Listen for broadcast events if Reverb/Pusher is configured
        // if (window.Echo) {
        //     window.Echo.channel(`chat.${this.sessionId}`)
        //         .listen('.MessageReceived', (e) => {
        //             this.messages.push({ id: Date.now(), role: 'assistant', content: e.message });
        //             this.isLoading = false;
        //             this.scrollToBottom();
        //         });
        // }
    },
    methods: {
        toggleChat() {
            this.isOpen = !this.isOpen;
            if(this.isOpen) {
                setTimeout(() => this.scrollToBottom(), 100);
            }
        },
        generateSessionId() {
            let session = localStorage.getItem('chatbot_session');
            if(!session) {
                session = 'sess_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('chatbot_session', session);
            }
            return session;
        },
        async fetchHistory() {
            try {
                // Determine user_id if available via global config/meta
                let userIdParam = '';
                const userIdMeta = document.querySelector('meta[name="user-id"]');
                if (userIdMeta && userIdMeta.getAttribute('content')) {
                    userIdParam = `&user_id=${userIdMeta.getAttribute('content')}`;
                }

                const res = await fetch(`/api/chatbot/history?session_id=${this.sessionId}${userIdParam}`);
                if (res.ok) {
                    const data = await res.json();
                    this.messages = data.messages || [];
                    this.scrollToBottom();
                }
            } catch (e) {
                console.error('Failed to fetch chat history:', e);
            }
        },
        async sendMessage() {
            if(!this.newMessage.trim() || this.isLoading) return;

            const text = this.newMessage.trim();
            this.messages.push({ id: Date.now(), role: 'user', content: text });
            this.newMessage = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                let userId = null;
                const userIdMeta = document.querySelector('meta[name="user-id"]');
                if (userIdMeta) userId = userIdMeta.getAttribute('content');

                await fetch('/api/chatbot/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        message: text,
                        session_id: this.sessionId,
                        user_id: userId
                    })
                });

                // Polling fallback if WebSockets aren't set up
                let attempts = 0;
                const pollInterval = setInterval(async () => {
                    attempts++;
                    await this.fetchHistory();
                    
                    // Stop polling if we got a response or max attempts reached
                    const lastMsg = this.messages[this.messages.length - 1];
                    if ((lastMsg && lastMsg.role === 'assistant') || attempts >= 10) {
                        clearInterval(pollInterval);
                        this.isLoading = false;
                    }
                }, 2000);

            } catch (e) {
                console.error('Error sending message:', e);
                this.isLoading = false;
                this.messages.push({ id: Date.now(), role: 'system', content: 'Failed to send message. Please try again.' });
            }
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const box = this.$refs.chatBox;
                if(box) box.scrollTop = box.scrollHeight;
            });
        }
    }
}
</script>

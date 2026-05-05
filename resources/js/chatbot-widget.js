(function() {
    function initChatbot() {
        if (document.getElementById('chatbot-widget')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const userIdMeta = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        
        let sessionId = localStorage.getItem('chatbot_session');
        if (!sessionId) {
            sessionId = 'sess_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('chatbot_session', sessionId);
        }

        const style = document.createElement('style');
        style.innerHTML = `
            #chatbot-widget { position: fixed; bottom: 24px; right: 24px; z-index: 999999; font-family: ui-sans-serif, system-ui, sans-serif; }
            #chatbot-toggle { width: 60px; height: 60px; border-radius: 50%; background: #2563eb; color: white; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; transition: all 0.2s ease-in-out; }
            #chatbot-toggle:hover { background: #1d4ed8; transform: scale(1.05); }
            #chatbot-window { position: absolute; bottom: 80px; right: 0; width: 380px; height: 600px; max-height: 80vh; background: white; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: none; flex-direction: column; overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.3s ease; opacity: 0; transform: translateY(10px); }
            #chatbot-window.open { display: flex; opacity: 1; transform: translateY(0); }
            #chatbot-header { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; padding: 18px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 10; }
            #chatbot-header-info { display: flex; align-items: center; gap: 10px; }
            #chatbot-header-icon { width: 36px; height: 36px; background: white; color: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
            #chatbot-close { background: none; border: none; color: white; cursor: pointer; transition: transform 0.2s; }
            #chatbot-close:hover { transform: scale(1.1); }
            #chatbot-messages { flex: 1; padding: 20px; overflow-y: auto; background: #f9fafb; display: flex; flex-direction: column; gap: 14px; scroll-behavior: smooth; }
            .chatbot-msg { max-width: 85%; padding: 12px 16px; border-radius: 14px; font-size: 14px; line-height: 1.5; word-wrap: break-word; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            .chatbot-msg.user { background: #2563eb; color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
            .chatbot-msg.assistant { background: white; color: #1f2937; align-self: flex-start; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
            .chatbot-msg.system { text-align: center; color: #6b7280; font-size: 12px; align-self: center; background: none; border: none; box-shadow: none; padding: 4px; }
            #chatbot-input-area { padding: 16px; background: white; border-top: 1px solid #e5e7eb; display: flex; gap: 10px; }
            #chatbot-input { flex: 1; border: 1px solid transparent; background: #f3f4f6; border-radius: 9999px; padding: 12px 20px; outline: none; transition: all 0.2s; font-size: 14px; }
            #chatbot-input:focus { background: white; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
            #chatbot-send { background: #2563eb; color: white; border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3); }
            #chatbot-send:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
            #chatbot-send:hover:not(:disabled) { background: #1d4ed8; transform: translateY(-1px); }
            .chatbot-typing { display: flex; gap: 4px; align-items: center; padding: 12px 16px; }
            .chatbot-dot { width: 6px; height: 6px; background: #9ca3af; border-radius: 50%; animation: chatbot-bounce 1.4s infinite ease-in-out both; }
            .chatbot-dot:nth-child(1) { animation-delay: -0.32s; }
            .chatbot-dot:nth-child(2) { animation-delay: -0.16s; }
            @keyframes chatbot-bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
            @media (max-width: 450px) {
                #chatbot-widget { bottom: 12px; right: 12px; }
                #chatbot-window { width: calc(100vw - 24px); height: calc(100vh - 100px); }
            }
        `;
        document.head.appendChild(style);

        const container = document.createElement('div');
        container.id = 'chatbot-widget';
        container.innerHTML = `
            <div id="chatbot-window">
                <div id="chatbot-header">
                    <div id="chatbot-header-info">
                        <div id="chatbot-header-icon">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        </div>
                        <div>
                            <div style="font-size: 15px; letter-spacing: 0.5px;">Support Assistant</div>
                            <div style="font-size: 12px; font-weight: normal; opacity: 0.9;">Online - Replies instantly</div>
                        </div>
                    </div>
                    <button id="chatbot-close">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div id="chatbot-messages">
                    <div class="chatbot-msg system">Hello! How can I help you today?</div>
                </div>
                <div id="chatbot-input-area">
                    <input type="text" id="chatbot-input" placeholder="Type your message..." autocomplete="off" />
                    <button id="chatbot-send" disabled>
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="transform: rotate(90deg);"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                    </button>
                </div>
            </div>
            <button id="chatbot-toggle">
                <svg id="chatbot-toggle-icon" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <svg id="chatbot-toggle-close" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        document.body.appendChild(container);

        const toggleBtn = document.getElementById('chatbot-toggle');
        const chatWindow = document.getElementById('chatbot-window');
        const closeBtn = document.getElementById('chatbot-close');
        const toggleIcon = document.getElementById('chatbot-toggle-icon');
        const toggleClose = document.getElementById('chatbot-toggle-close');
        const messagesBox = document.getElementById('chatbot-messages');
        const input = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send');
        
        let isOpen = false;
        let isWaiting = false;

        function toggleChat() {
            isOpen = !isOpen;
            if (isOpen) {
                chatWindow.classList.add('open');
                toggleIcon.style.display = 'none';
                toggleClose.style.display = 'block';
                input.focus();
                scrollToBottom();
            } else {
                chatWindow.classList.remove('open');
                toggleIcon.style.display = 'block';
                toggleClose.style.display = 'none';
            }
        }

        toggleBtn.addEventListener('click', toggleChat);
        closeBtn.addEventListener('click', toggleChat);

        input.addEventListener('input', () => {
            sendBtn.disabled = input.value.trim() === '' || isWaiting;
        });

        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !sendBtn.disabled) sendMessage();
        });

        sendBtn.addEventListener('click', sendMessage);

        function scrollToBottom() {
            setTimeout(() => {
                messagesBox.scrollTop = messagesBox.scrollHeight;
            }, 50);
        }

        function addMessage(content, role) {
            const msg = document.createElement('div');
            msg.className = `chatbot-msg ${role}`;
            msg.textContent = content;
            messagesBox.appendChild(msg);
            scrollToBottom();
        }

        function showTyping() {
            const typing = document.createElement('div');
            typing.id = 'chatbot-typing';
            typing.className = 'chatbot-msg assistant chatbot-typing';
            typing.innerHTML = '<div class="chatbot-dot"></div><div class="chatbot-dot"></div><div class="chatbot-dot"></div>';
            messagesBox.appendChild(typing);
            scrollToBottom();
            isWaiting = true;
            sendBtn.disabled = true;
            input.disabled = true;
        }

        function hideTyping() {
            const typing = document.getElementById('chatbot-typing');
            if (typing) typing.remove();
            isWaiting = false;
            input.disabled = false;
            sendBtn.disabled = input.value.trim() === '';
            input.focus();
        }

        async function loadHistory() {
            try {
                let url = `/api/chatbot/history?session_id=${sessionId}`;
                if (userIdMeta) url += `&user_id=${userIdMeta}`;
                
                const res = await fetch(url);
                if (res.ok) {
                    const data = await res.json();
                    if (data.messages && data.messages.length > 0) {
                        messagesBox.innerHTML = '';
                        data.messages.forEach(m => addMessage(m.content, m.role));
                        scrollToBottom();
                    }
                }
            } catch (e) {
                console.error('Chatbot load error:', e);
            }
        }

        async function sendMessage() {
            const text = input.value.trim();
            if (!text || isWaiting) return;

            input.value = '';
            addMessage(text, 'user');
            showTyping();

            try {
                await fetch('/api/chatbot/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        message: text,
                        session_id: sessionId,
                        user_id: userIdMeta || null
                    })
                });

                let attempts = 0;
                const pollInterval = setInterval(async () => {
                    attempts++;
                    
                    let url = `/api/chatbot/history?session_id=${sessionId}`;
                    if (userIdMeta) url += `&user_id=${userIdMeta}`;
                    
                    const res = await fetch(url);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.messages && data.messages.length > 0) {
                            const lastMsg = data.messages[data.messages.length - 1];
                            if (lastMsg.role === 'assistant' && lastMsg.content) {
                                clearInterval(pollInterval);
                                hideTyping();
                                messagesBox.innerHTML = '';
                                data.messages.forEach(m => addMessage(m.content, m.role));
                                scrollToBottom();
                            }
                        }
                    }
                    
                    if (attempts >= 10) {
                        clearInterval(pollInterval);
                        hideTyping();
                        addMessage("Sorry, the server took too long to respond.", "system");
                    }
                }, 2000);

            } catch (e) {
                console.error(e);
                hideTyping();
                addMessage("An error occurred while sending the message.", "system");
            }
        }

        loadHistory();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbot);
    } else {
        initChatbot();
    }
})();

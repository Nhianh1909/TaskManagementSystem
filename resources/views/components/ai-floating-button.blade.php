{{-- AI Floating Button - Draggable & Always Visible --}}
<div id="ai-floating-container">
    {{-- Floating Button --}}
    <button id="ai-float-btn"
            class="fixed bottom-6 right-6 w-16 h-16 rounded-full shadow-2xl flex items-center justify-center text-white text-2xl cursor-move hover:scale-110 transition-transform duration-300 z-50"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
            title="AI Assistant">
        <span class="animate-pulse">✨</span>
    </button>

    {{-- Tooltip --}}
    <div id="ai-tooltip"
         class="fixed bottom-24 right-6 bg-gray-800 text-white text-xs px-3 py-2 rounded-lg shadow-lg opacity-0 pointer-events-none transition-opacity duration-300 z-50">
        Click để mở AI Assistant
    </div>

    {{-- Chat Widget --}}
    <div id="ai-chat-widget"
         class="fixed shadow-2xl rounded-2xl bg-white border border-gray-200 w-80 h-[480px] flex flex-col overflow-hidden transition-all duration-200 z-50"
         style="display: none;">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
            <div class="flex items-center gap-2">
                <span class="text-lg">🤖</span>
                <div>
                    <p class="text-sm font-semibold leading-tight">AI Assistant</p>
                    <p class="text-[11px] opacity-80">Hỏi tôi để tạo US/Task</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="ai-chat-clear" class="text-white/80 hover:text-white text-lg leading-none" title="Xóa lịch sử">🗑️</button>
                <button id="ai-chat-close" class="text-white/80 hover:text-white text-xl leading-none">×</button>
            </div>
        </div>

        {{-- Messages area --}}
        <div id="ai-chat-messages" class="flex-1 p-4 space-y-3 overflow-y-auto bg-gray-50 text-sm text-gray-800">
            <div class="flex gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
                <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                    Chào bạn! Gửi tin nhắn để bắt đầu.
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-200 p-3 bg-white">
            <div class="flex gap-2 items-center">
                <input id="ai-chat-input" type="text" placeholder="Nhập yêu cầu..." class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <button id="ai-chat-send" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">Gửi</button>
            </div>
            <p class="mt-2 text-[11px] text-gray-500">Mẹo: Hỏi "Tạo US cho Epic Payment"</p>
        </div>
    </div>

    {{-- Modal Edit User Stories (edit: added modal for better editing UX instead of small inline form in chat) --}}
    <div id="edit-us-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-2xl w-11/12 max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
            {{-- Modal Header (edit: modal header displaying approach name being edited) --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <h3 class="text-lg font-semibold">✏️ Edit User Stories</h3>
                <button id="modal-close-btn" class="text-white/80 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            {{-- Modal Body (edit: scrollable content displaying US list for editing) --}}
            <div id="modal-edit-content" class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Content will be rendered by JS --}}
            </div>

            {{-- Modal Footer (edit: save and cancel buttons at bottom of modal) --}}
            <div class="flex gap-3 px-6 py-4 bg-gray-50 border-t">
                <button id="modal-save-btn" class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    💾 Save All
                </button>
                <button id="modal-cancel-btn" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    ✖️ Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #ai-float-btn {
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        animation: glow 2s ease-in-out infinite;
    }

    @keyframes glow {
        0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.5); }
        50% { box-shadow: 0 0 30px rgba(102, 126, 234, 0.8), 0 0 40px rgba(118, 75, 162, 0.6); }
    }

    #ai-float-btn:active { transform: scale(0.95); }
    #ai-float-btn.dragging { cursor: grabbing; opacity: 0.8; }

    @media (max-width: 480px) {
        #ai-chat-widget { width: 90vw; height: 60vh; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const floatBtn = document.getElementById('ai-float-btn');
    const tooltip = document.getElementById('ai-tooltip');
    const chat = document.getElementById('ai-chat-widget');
    const chatClose = document.getElementById('ai-chat-close');
    const chatInput = document.getElementById('ai-chat-input');
    const chatSend = document.getElementById('ai-chat-send');

    let isDragging = false;
    let startX, startY;
    let initialLeft, initialTop;
    let hasMoved = false;
    let currentSessionId = null;
    const quickSprintContext = {
        sprint: null,
        backlog: [],
        selectedTaskIds: [],
        activeSprint: null,
        futureSprints: []
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const currentRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');

    console.log('🔐 CSRF Token:', csrfToken);
    console.log('📍 csrfToken element:', document.querySelector('meta[name="csrf-token"]'));

    // Restore chat history after page reload
    restoreChatHistory();

    function saveChatHistory() {
        const messagesArea = document.getElementById('ai-chat-messages');
        sessionStorage.setItem('ai_chat_html', messagesArea.innerHTML);
        sessionStorage.setItem('ai_session_id', currentSessionId);
        sessionStorage.setItem('ai_chat_open', chat.style.display !== 'none' ? 'true' : 'false');
    }

    // Expose to global scope for epic-list-refresh script
    window.saveChatHistory = saveChatHistory;

    function restoreChatHistory() {
        const savedHtml = sessionStorage.getItem('ai_chat_html');
        const savedSessionId = sessionStorage.getItem('ai_session_id');
        const wasOpen = sessionStorage.getItem('ai_chat_open') === 'true';
        const savedApproaches = sessionStorage.getItem('ai_approaches');

        if (savedHtml && savedSessionId) {
            const messagesArea = document.getElementById('ai-chat-messages');
            messagesArea.innerHTML = savedHtml;
            currentSessionId = savedSessionId;

            if (wasOpen) {
                setTimeout(() => {
                    chat.style.display = 'flex';
                    positionChatRelativeToButton();
                }, 100);
            }

            // Re-attach event listeners to buttons after restore
            if (savedApproaches) {
                const approaches = JSON.parse(savedApproaches);
                reattachApproachListeners(approaches);
            }

            // Re-attach suggestion button listeners
            reattachSuggestionListeners();

            console.log('✅ Restored chat history, session:', currentSessionId);
        }
    }

    // Cảnh báo nếu role là Scrum Master (không được tạo US)
    function warnIfScrumMaster() {
        if (currentRole === 'scrum_master') {
            appendAIMessage('⚠️ Chỉ Product Owner mới có thể tạo User Story. Bạn vẫn có thể xem/gợi ý, nhưng không thể tạo US.');
        }
    }

    function reattachApproachListeners(approaches) {
        // Find all approach cards and re-attach listeners
        const approachCards = document.querySelectorAll('[data-approach-idx]');
        approachCards.forEach((card, idx) => {
            const approach = approaches[idx];
            if (approach) {
                const btnSelect = card.querySelector('.btn-select');
                const btnEdit = card.querySelector('.btn-edit');

                if (btnSelect) {
                    btnSelect.onclick = (e) => {
                        e.stopPropagation();
                        selectApproach(approach, idx);
                    };
                }

                if (btnEdit) {
                    btnEdit.onclick = (e) => {
                        e.stopPropagation();
                        editApproach(approach, idx);
                    };
                }
            }
        });
        console.log('✅ Re-attached listeners to', approachCards.length, 'approach cards');
    }

    function reattachSuggestionListeners() {
        // Find all suggestion buttons and re-attach click handlers
        const suggestionBtns = document.querySelectorAll('.suggestion-btn');
        suggestionBtns.forEach(btn => {
            const label = btn.textContent.trim();
            let action = null;

            // Determine action from label
            if (label.includes('Tạo Sprint')) {
                action = 'create_sprint';
            } else if (label.includes('Tạo thêm US')) {
                action = 'generate_us';
            } else if (label.includes('Tạo US cho')) {
                // Epic suggestion - will be handled by epic_id
                return;
            }

            if (action) {
                btn.onclick = () => handleSuggestionClick({label, action});
            }
        });
        console.log('✅ Re-attached listeners to', suggestionBtns.length, 'suggestion buttons');

        // Đảm bảo nút hiển thị tất cả sprint tồn tại sau khi restore
        injectShowAllSprintsButton();
    }

    function injectShowAllSprintsButton() {
        // Chèn nút "Hiển thị tất cả Sprint" vào cùng khối suggestion nếu chưa có
        if (document.getElementById('btn-show-all-sprints')) return;
        const firstSuggestion = document.querySelector('.suggestion-btn');
        const parent = firstSuggestion?.parentElement;
        if (!parent) return;

        const btn = document.createElement('button');
        btn.id = 'btn-show-all-sprints';
        btn.className = 'block w-full text-left px-3 py-2 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg mt-2';
        btn.textContent = 'Hiển thị tất cả Sprint';
        btn.onclick = () => refreshSprintsSnapshot(renderStartSprintPrompt);
        parent.appendChild(btn);
    }

    // Tooltip
    floatBtn.addEventListener('mouseenter', () => {
        if (!isDragging) tooltip.style.opacity = '1';
    });
    floatBtn.addEventListener('mouseleave', () => {
        tooltip.style.opacity = '0';
    });

    // Draggable
    floatBtn.addEventListener('mousedown', (e) => {
        isDragging = true;
        hasMoved = false;
        floatBtn.classList.add('dragging');
        tooltip.style.opacity = '0';

        const rect = floatBtn.getBoundingClientRect();
        startX = e.clientX;
        startY = e.clientY;
        initialLeft = rect.left;
        initialTop = rect.top;
        document.body.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;

        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;

        if (Math.abs(deltaX) > 5 || Math.abs(deltaY) > 5) {
            hasMoved = true;
        }

        let newLeft = initialLeft + deltaX;
        let newTop = initialTop + deltaY;

        const maxLeft = window.innerWidth - floatBtn.offsetWidth - 10;
        const maxTop = window.innerHeight - floatBtn.offsetHeight - 10;

        newLeft = Math.max(10, Math.min(newLeft, maxLeft));
        newTop = Math.max(10, Math.min(newTop, maxTop));

        floatBtn.style.left = newLeft + 'px';
        floatBtn.style.top = newTop + 'px';
        floatBtn.style.right = 'auto';
        floatBtn.style.bottom = 'auto';

        tooltip.style.left = newLeft + 'px';
        tooltip.style.top = (newTop - 50) + 'px';
        tooltip.style.right = 'auto';
        tooltip.style.bottom = 'auto';

        if (chat.style.display !== 'none') {
            positionChatRelativeToButton();
        }
    });

    document.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            floatBtn.classList.remove('dragging');
            document.body.style.cursor = '';

            if (!hasMoved) {
                openAIChat();
            }
        }
    });

    // Open/close chat
    async function openAIChat() {
        if (chat.style.display === 'none') {
            positionChatRelativeToButton();
            chat.style.display = 'flex';
            tooltip.style.opacity = '0';

            warnIfScrumMaster();

            if (!currentSessionId) {
                await startAISession();
            }
        } else {
            chat.style.display = 'none';
        }
    }

    async function startAISession() {
        try {
            const response = await fetch('/ai/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    epic_id: window.currentEpicId || null,
                    workflow_stage: 'backlog'
                })
            });

            const data = await response.json();
            currentSessionId = data.session_id;
            console.log('✅ AI Session started:', currentSessionId);
        } catch (error) {
            console.error('❌ Error starting AI session:', error);
        }
    }

    chatClose.addEventListener('click', () => {
        chat.style.display = 'none';
    });

    // Modal event listeners (edit: add event listeners for edit modal)
    document.getElementById('modal-close-btn').addEventListener('click', cancelEdit);
    document.getElementById('modal-cancel-btn').addEventListener('click', cancelEdit);
    document.getElementById('modal-save-btn').addEventListener('click', saveEditedApproach);

    // Close modal when clicking backdrop (edit: click outside modal to close)
    document.getElementById('edit-us-modal').addEventListener('click', (e) => {
        if (e.target.id === 'edit-us-modal') {
            cancelEdit();
        }
    });

    // Clear chat history
    const chatClear = document.getElementById('ai-chat-clear');
    chatClear.addEventListener('click', () => {
        if (confirm('Bạn chắc chắn muốn xóa lịch sử chat? Hành động này không thể hoàn tác.')) {
            clearChatHistory();
        }
    });

    function clearChatHistory() {
        // Clear sessionStorage
        sessionStorage.removeItem('ai_chat_html');
        sessionStorage.removeItem('ai_session_id');
        sessionStorage.removeItem('ai_approaches');
        sessionStorage.removeItem('currentEpicTitle');

        // Reset chat UI
        const messagesArea = document.getElementById('ai-chat-messages');
        messagesArea.innerHTML = `
            <div class="flex gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
                <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                    Chào bạn! Gửi tin nhắn để bắt đầu.
                </div>
            </div>
        `;

        chatInput.value = '';
        currentSessionId = null;

        // Start new session
        startAISession();

        console.log('🗑️ Chat history cleared');
    }

    // Send message
    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text || !currentSessionId) return;

        const lower = text.toLowerCase();
        // Chặn Scrum Master gửi yêu cầu tạo US
        if (currentRole === 'scrum_master') {
            const isCreateUSIntent = lower.includes('tạo us') || lower.includes('create user story') || lower.includes('generate us') || lower.includes('tạo user story');
            if (isCreateUSIntent) {
                appendAIMessage('⚠️ Chỉ Product Owner mới có thể tạo User Story. Vui lòng nhờ PO thực hiện.');
                chatInput.value = '';
                return;
            }
        }

        if (tryHandleQuickSprintCommand(text)) {
            chatInput.value = '';
            return;
        }

        // Detect "Phân rã US #id" hoặc "Phân rã US: tên"
        if (tryHandleDecomposeUSCommand(text)) {
            chatInput.value = '';
            return;
        }

        appendUserMessage(text);
        chatInput.value = '';
        chatInput.disabled = true;
        chatSend.disabled = true;

        showTypingIndicator();

        try {
            // Set timeout 15 seconds
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000);

            const response = await fetch(`/ai/chat/${currentSessionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ message: text }),
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            removeTypingIndicator();

            appendAIMessage(data.message);

            // Hiển thị 3 approaches nếu có
            if (data.approaches && data.approaches.length > 0) {
                // Lưu approaches để restore sau reload
                sessionStorage.setItem('ai_approaches', JSON.stringify(data.approaches));
                appendApproaches(data.approaches);
            }

            if (data.suggestions && data.suggestions.length > 0) {
                appendSuggestions(data.suggestions);
            }

        } catch (error) {
            removeTypingIndicator();
            if (error.name === 'AbortError') {
                appendAIMessage('⏱️ Request timeout. Gemini đang xử lý lâu. Vui lòng thử lại sau.');
            } else {
                console.error('❌ Error sending message:', error);
                appendAIMessage('❌ Lỗi: ' + error.message);
            }
            removeTypingIndicator();
            appendAIMessage('Xin lỗi, đã có lỗi xảy ra.');
        } finally {
            chatInput.disabled = false;
            chatSend.disabled = false;
            chatInput.focus();
        }
    }

    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // UI functions
    function appendUserMessage(msg) {
        const area = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.className = 'flex gap-2 justify-end';
        div.innerHTML = `
            <div class="bg-indigo-600 text-white rounded-xl px-3 py-2 shadow-sm max-w-[220px]">${escapeHtml(msg)}</div>
            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">YOU</div>
        `;
        area.appendChild(div);
        area.scrollTop = area.scrollHeight;
        saveChatHistory();
    }

    function appendAIMessage(msg) {
        const area = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.className = 'flex gap-2';
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
            <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm max-w-[240px]">${escapeHtml(msg)}</div>
        `;
        area.appendChild(div);
        area.scrollTop = area.scrollHeight;
        saveChatHistory();
    }

    window.addEventListener('beforeunload', () => {
        saveChatHistory();
    });

    function showTypingIndicator() {
        const area = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.id = 'typing-indicator';
        div.className = 'flex gap-2';
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
            <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-bounce mx-1" style="animation-delay: 0.2s"></span>
                <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
            </div>
        `;
        area.appendChild(div);
        area.scrollTop = area.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.remove();
    }

    // Helper: format ISO timestamps (e.g., 2026-01-14T00:00:00.000000Z) to local YYYY-MM-DD
    function formatLocalDate(str) {
        try {
            const d = new Date(str);
            if (!isNaN(d.getTime())) {
                const offsetMs = d.getTimezoneOffset() * 60000;
                const local = new Date(d.getTime() - offsetMs);
                return local.toISOString().split('T')[0];
            }
        } catch (_) {}
        return str;
    }

    function appendApproaches(approaches) {
        const area = document.getElementById('ai-chat-messages');
        const container = document.createElement('div');
        container.className = 'space-y-3 pl-10 mb-3';

        approaches.forEach((approach, idx) => {
            // Normalize story points so UI always sees story_point
            const normalizedStories = (approach.stories || []).map(s => ({
                ...s,
                story_point: s.story_point ?? s.points ?? 0
            }));
            approach.stories = normalizedStories;

            const card = document.createElement('div');
            card.className = 'bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-200 rounded-lg p-3 text-xs hover:shadow-md transition';
            card.setAttribute('data-approach-idx', idx); // For restore

            const title = approach.name || `Approach ${idx + 1}`;
            const desc = approach.description || '';
            const points = approach.total_points || 0;
            const storiesHtml = normalizedStories
                .slice(0, 2)
                .map(s => `<div class="text-gray-600">• ${escapeHtml(s.title || '')}</div>`)
                .join('');

            card.innerHTML = `
                <div class="font-semibold text-indigo-700">${escapeHtml(title)}</div>
                <div class="text-gray-600 mt-1">${escapeHtml(desc)}</div>
                ${storiesHtml}
                ${approach.stories && approach.stories.length > 2 ? `<div class="text-gray-500">+${approach.stories.length - 2} more...</div>` : ''}
                <div class="text-indigo-600 font-semibold mt-2">📊 ${points} Story Points</div>
                <div class="flex gap-2 mt-3 pt-2 border-indigo-200">
                    <button class="btn-select flex-1 px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition text-xs font-medium">
                        ✅ Chọn & Lưu
                    </button>
                    <button class="btn-edit flex-1 px-3 py-1.5 bg-white border border-indigo-300 text-indigo-700 rounded hover:bg-indigo-50 transition text-xs font-medium">
                        ✏️ Chỉnh sửa
                    </button>
                </div>
            `;

            // Attach event listeners
            card.querySelector('.btn-select').onclick = (e) => {
                e.stopPropagation();
                selectApproach(approach, idx);
            };
            card.querySelector('.btn-edit').onclick = (e) => {
                e.stopPropagation();
                editApproach(approach, idx);
            };

            container.appendChild(card);
        });

        area.appendChild(container);
        area.scrollTop = area.scrollHeight;
        saveChatHistory();
    }

    // Variable to store approach being edited (edit: for modal access)
    let currentEditingApproach = null;

    // Edit approach - Show modal (edit: instead of inline form, now shows modal for better editing)
    function editApproach(approach, idx) {
        // Save approach info to temp variable (edit: for modal use)
        currentEditingApproach = approach;

        // Render modal content (edit: display edit form in modal)
        const modalContent = document.getElementById('modal-edit-content');

        let editFormHtml = `
            <div class="space-y-4">
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="font-semibold text-indigo-900 mb-1">📝 Approach: ${escapeHtml(approach.name)}</div>
                    <div class="text-sm text-indigo-700">Total: ${approach.total_points} Story Points</div>
                </div>
        `;

        approach.stories.forEach((story, idx) => {
            editFormHtml += `
                <div class="border border-gray-300 rounded-lg p-4 bg-white shadow-sm">
                    <div class="text-sm font-medium text-gray-700 mb-3">User Story #${idx + 1}</div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">📝 Title</label>
                            <input type="text"
                                   value="${escapeHtml(story.title || '')}"
                                   placeholder="Enter User Story title..."
                                   class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                   id="story-title-${idx}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">📋 Description</label>
                            <textarea
                                   placeholder="Enter description..."
                                   class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                                   rows="4"
                                   id="story-desc-${idx}">${escapeHtml(story.description || '')}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1.5">🎯 Story Points</label>
                                <input type="number"
                                       value="${story.story_point || 0}"
                                       placeholder="Points"
                                       class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                       id="story-points-${idx}"
                                       min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1.5">⭐ Priority</label>
                                <select class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" id="story-priority-${idx}">
                                    <option value="low" ${story.priority === 'low' ? 'selected' : ''}>🟢 Low</option>
                                    <option value="medium" ${story.priority === 'medium' ? 'selected' : ''}>🟡 Medium</option>
                                    <option value="high" ${story.priority === 'high' ? 'selected' : ''}>🔴 High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        editFormHtml += `</div>`;
        modalContent.innerHTML = editFormHtml;

        // Show modal (edit: use flex to display modal)
        const modal = document.getElementById('edit-us-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Focus on first title (edit: auto focus when opening modal)
        setTimeout(() => {
            const firstInput = modalContent.querySelector('#story-title-0');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    // Save edited approach from modal (edit: collect data from modal instead of inline form)
    function saveEditedApproach() {
        if (!currentEditingApproach) {
            alert('⚠️ Approach information not found!');
            return;
        }

        const approachName = currentEditingApproach.name;
        const storyCount = currentEditingApproach.stories.length;

        const stories = [];
        for (let i = 0; i < storyCount; i++) {
            const title = document.getElementById(`story-title-${i}`)?.value;
            const description = document.getElementById(`story-desc-${i}`)?.value;
            const points = parseInt(document.getElementById(`story-points-${i}`)?.value) || 0;
            const priority = document.getElementById(`story-priority-${i}`)?.value;

            if (title && title.trim()) {
                stories.push({
                    title: title.trim(),
                    description: description?.trim() || '',
                    story_point: points,
                    priority: priority || 'medium'
                });
            }
        }

        if (stories.length === 0) {
            alert('⚠️ Please enter at least 1 User Story!');
            return;
        }

        // Close modal first (edit: hide modal before sending request)
        closeEditModal();

        // Show save notification in chat
        appendUserMessage(`💾 Saving ${stories.length} edited User Stories`);
        showTypingIndicator();

        // Gửi request lưu về server (sửa: giữ nguyên logic lưu)
        fetch('/ai/us/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: currentSessionId,
                stories: stories,
                approach_name: approachName,
            })
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator();
            // Show success message
            appendAIMessage(data.message);
            if (data.suggestions) {
                appendSuggestions(data.suggestions);
            }

            if (data.message && data.message.includes('thành công')) {
                const epicTitle = sessionStorage.getItem('currentEpicTitle');
                window.dispatchEvent(new CustomEvent('us-saved', {
                    detail: {
                        stories: data.stories,
                        epic_title: epicTitle
                    }
                }));
            }
        })
        .catch(err => {
            removeTypingIndicator();
            console.error('Save error:', err);
            appendAIMessage('❌ Lỗi khi lưu US');
        });
    }

    // Close edit modal (edit: new function to close modal)
    function closeEditModal() {
        const modal = document.getElementById('edit-us-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        currentEditingApproach = null; // Reset temp data
    }

    // Cancel edit (edit: close modal and show notification)
    function cancelEdit() {
        closeEditModal();
        appendAIMessage('❌ Edit cancelled. You can select another approach.');
    }

    function selectApproach(approach, idx) {
        appendUserMessage(`✅ Chọn "${approach.name}" (${approach.total_points} points)`);

        // Lưu approach được chọn vào session storage
        sessionStorage.setItem('selectedApproach', JSON.stringify(approach));
        sessionStorage.setItem('selectedApproachName', approach.name);

        console.log('📤 Sending stories to save:', {
            stories: approach.stories,
            approachName: approach.name
        });

        showTypingIndicator();

        // Save stories
        fetch('/ai/us/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: currentSessionId,
                stories: approach.stories || [],
                approach_name: approach.name,
            })
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator();
            appendAIMessage(data.message);
            if (data.suggestions) {
                appendSuggestions(data.suggestions);
            }

            // Phát event để backlog tự refetch, không reload toàn trang
            if (data.message && data.message.includes('thành công')) {
                // Lấy epic hiện tại từ session
                const epicTitle = sessionStorage.getItem('currentEpicTitle');
                window.dispatchEvent(new CustomEvent('us-saved', {
                    detail: {
                        stories: data.stories,
                        epic_title: epicTitle // Pass epic title để biết mở epic nào
                    }
                }));
            }
        })
        .catch(err => {
            removeTypingIndicator();
            console.error('Save error:', err);
            appendAIMessage('❌ Lỗi khi lưu US');
        });
    }

    function appendSuggestions(suggestions) {
        // Nếu là Scrum Master, ẩn các đề xuất tạo/generate US
        const filtered = (currentRole === 'scrum_master')
            ? suggestions.filter(s => !(s.action === 'generate_us' || s.label?.toLowerCase().includes('tạo us')))
            : suggestions;

        if (!filtered.length) return;

        const area = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.className = 'space-y-2 pl-10';


        filtered.forEach(sug => {
            const btn = document.createElement('button');
            btn.className = 'suggestion-btn block w-full text-left px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 border border-blue-300 rounded-lg transition';
            btn.textContent = sug.label;
            btn.onclick = () => handleSuggestionClick(sug);
            div.appendChild(btn);
        });

        // Thêm nút Hiển thị tất cả Sprint
        const showAllBtn = document.createElement('button');
        showAllBtn.id = 'btn-show-all-sprints';
        showAllBtn.className = 'block w-full text-left px-3 py-2 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg mt-2';
        showAllBtn.textContent = 'Hiển thị tất cả Sprint';
        showAllBtn.onclick = () => refreshSprintsSnapshot(renderStartSprintPrompt);
        div.appendChild(showAllBtn);
    // ...existing code...
    // Hiển thị tất cả sprint khi bấm nút
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'btn-show-all-sprints') {
            renderStartSprintPrompt();
        }
    });

        area.appendChild(div);
        area.scrollTop = area.scrollHeight;
        saveChatHistory();
    }

    function handleSuggestionClick(suggestion) {
        appendUserMessage(suggestion.label);

        // Chặn Scrum Master thực thi các action tạo/generate US
        const labelLower = suggestion.label?.toLowerCase() || '';
        const isCreateUSAction = suggestion.action === 'generate_us' || labelLower.includes('tạo us');
        if (currentRole === 'scrum_master' && isCreateUSAction) {
            appendAIMessage('⚠️ Chỉ Product Owner mới có thể tạo User Story.');
            return;
        }

        // Handle specific actions
        if (suggestion.action === 'create_sprint') {
            handleCreateSprint();
            return;
        }

        if (suggestion.action === 'generate_us' || suggestion.label.includes('Tạo thêm US')) {
            handleGenerateMoreUS();
            return;
        }

        // Nếu có epic_id → set session epic, call generate endpoint trực tiếp
        if (suggestion.epic_id) {
            // Lưu epic title vào session để dùng khi expand epic sau khi save
            const epicTitle = suggestion.epic_title || suggestion.label.replace(/^Tạo US cho /i, '').trim();
            sessionStorage.setItem('currentEpicTitle', epicTitle);

            showTypingIndicator();

            fetch('/ai/generate-us', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    session_id: currentSessionId,
                    epic_id: suggestion.epic_id
                })
            })
            .then(res => res.json())
            .then(data => {
                removeTypingIndicator();
                if (data.message) {
                    appendAIMessage(data.message);
                }
                if (data.approaches && data.approaches.length > 0) {
                    // Lưu approaches để restore sau reload
                    sessionStorage.setItem('ai_approaches', JSON.stringify(data.approaches));
                    appendApproaches(data.approaches);
                }
                if (data.suggestions && data.suggestions.length > 0) {
                    appendSuggestions(data.suggestions);
                }
            })
            .catch(err => {
                removeTypingIndicator();
                console.error('Generate US error:', err);
                appendAIMessage('❌ Lỗi khi tạo US: ' + err.message);
            });
        } else {
            // Khác → gửi message text bình thường
            chatInput.value = suggestion.label;
            sendMessage();
        }
    }

    function handleCreateSprint() {
        appendAIMessage('📅 Để tạo sprint nhanh, hãy nhập: tạo sprint (tên) với (số ngày)');
    }

    function setupSprintFormListeners() {
        const durationBtns = document.querySelectorAll('.duration-preset');
        const startDateInput = document.getElementById('sprint-start-date');
        const endDateInput = document.getElementById('sprint-end-date');
        const customEdit = document.getElementById('custom-date-edit');
        const btnSaveDates = document.getElementById('btn-save-dates');
        const btnCreateSprint = document.getElementById('btn-create-sprint');
        const btnCancelSprint = document.getElementById('btn-cancel-sprint');
        const nameInput = document.getElementById('sprint-name');
        const goalInput = document.getElementById('sprint-goal');
        const usList = document.getElementById('sprint-us-list');

        const getLocalISODate = (dateObj) => {
            const offsetMs = dateObj.getTimezoneOffset() * 60000;
            const local = new Date(dateObj.getTime() - offsetMs);
            return local.toISOString().split('T')[0];
        };

        const ensureStartDate = () => {
            if (!startDateInput.value) {
                startDateInput.value = getLocalISODate(new Date());
            }
        };

        const setEndDateFromPreset = (days) => {
            ensureStartDate();
            const start = new Date(startDateInput.value);
            if (Number.isFinite(days)) {
                const end = new Date(start);
                end.setDate(end.getDate() + days - 1);
                endDateInput.value = getLocalISODate(end);
            }
        };

        if (nameInput) nameInput.addEventListener('input', updateSprintPreview);
        if (goalInput) goalInput.addEventListener('input', updateSprintPreview);
        if (usList) {
            usList.addEventListener('change', (e) => {
                if (e.target.classList.contains('sprint-us-checkbox')) {
                    updateSprintPreview();
                }
            });
        }

        durationBtns.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const raw = btn.dataset.days;
                if (raw === 'custom') {
                    customEdit.classList.remove('hidden');
                    document.getElementById('sprint-start-date-edit').value = startDateInput.value;
                    document.getElementById('sprint-end-date-edit').value = endDateInput.value;
                } else {
                    customEdit.classList.add('hidden');
                    const days = parseInt(raw, 10);
                    setEndDateFromPreset(days);
                }

                durationBtns.forEach(b => b.classList.remove('bg-blue-300', 'border-blue-500', 'bg-blue-500', 'border-blue-600', 'text-white', 'font-semibold', 'ring-2', 'ring-blue-400', 'ring-offset-1'));
                btn.classList.add('bg-blue-500', 'border-blue-600', 'text-white', 'font-semibold', 'ring-2', 'ring-blue-400', 'ring-offset-1');

                updateSprintPreview();
            };
        });

        // Default select 1 tuần preset
        const defaultBtn = Array.from(durationBtns).find(b => b.dataset.days === '7');
        if (defaultBtn) {
            setEndDateFromPreset(7);
            defaultBtn.click();
        } else {
            setEndDateFromPreset(7);
        }

        btnSaveDates.onclick = () => {
            const startEdit = document.getElementById('sprint-start-date-edit').value;
            const endEdit = document.getElementById('sprint-end-date-edit').value;

            if (!startEdit || !endEdit) {
                appendAIMessage('❌ Vui lòng nhập cả ngày bắt đầu và kết thúc');
                return;
            }

            startDateInput.value = startEdit;
            endDateInput.value = endEdit;
            customEdit.classList.add('hidden');
            appendAIMessage('✅ Đã cập nhật ngày');
            updateSprintPreview();
        };

        loadSprintUSList();

        btnCancelSprint.onclick = () => {
            closeSprintForm();
            appendAIMessage('❌ Đã hủy tạo Sprint');
        };

        btnCreateSprint.onclick = () => {
            saveSprintWithUS();
        };

        // Initial preview
        updateSprintPreview();
    }

    function updateSprintPreview() {
        const nameInput = document.getElementById('sprint-name');
        const goalInput = document.getElementById('sprint-goal');
        const startDateInput = document.getElementById('sprint-start-date');
        const endDateInput = document.getElementById('sprint-end-date');

        const previewName = document.getElementById('preview-name');
        const previewDates = document.getElementById('preview-dates');
        const previewGoal = document.getElementById('preview-goal');
        const previewUs = document.getElementById('preview-us');

        const name = nameInput?.value?.trim();
        const goal = goalInput?.value?.trim();
        const start = startDateInput?.value;
        const end = endDateInput?.value;

        if (previewName) previewName.textContent = `Tên: ${name || 'Chưa đặt'}`;
        if (previewDates) previewDates.textContent = (start && end) ? `Ngày: ${start} -> ${end}` : 'Ngày: --';
        if (previewGoal) previewGoal.textContent = goal ? `Goal: ${goal}` : 'Goal: (để trống)';

        let usSummary = 'US: Chọn nhanh ở trên';
        const usCheckboxes = document.querySelectorAll('.sprint-us-checkbox');
        if (usCheckboxes.length > 0) {
            const selectedLabels = Array.from(usCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.parentElement?.textContent?.trim())
                .filter(Boolean);

            if (selectedLabels.length > 0) {
                usSummary = 'US: ' + selectedLabels.join(' + ');
            } else {
                usSummary = 'US: Chưa chọn';
            }
        }

        if (previewUs) previewUs.textContent = usSummary;
    }

    function tryHandleQuickSprintCommand(message) {
        const lower = message.toLowerCase();
        if (!lower.includes('tạo') || !lower.includes('sprint')) return false;

        // Extract duration: supports "7 ngày" or "7 days"
        const durationMatch = message.match(/(\d+)\s*(ngày|days)/i);
        const days = durationMatch ? parseInt(durationMatch[1], 10) : 7;

        let name = null;
        // Prefer name inside parentheses: "sprint (My Name)"
        const parenMatch = message.match(/sprint\s*\(\s*([^)]*?)\s*\)/i);
        if (parenMatch && parenMatch[1]) {
            name = parenMatch[1].trim();
        } else {
            // Fallback: capture text after 'sprint' until a stop word (với/with/trong/vòng) or duration
            const nameMatch = message.match(/sprint\s+([^,]+?)(?:\s+với|\s+with|\s+trong|\s+vòng|\s*\d+\s*(ngày|days)|$)/i);
            if (nameMatch && nameMatch[1]) {
                name = nameMatch[1].trim();
            }
        }

        // Clean trailing artifacts like 'với', punctuation, unmatched parentheses
        if (name) {
            name = name
                .replace(/\s*(với|with)\s*$/i, '')
                .replace(/[\s,;:]+$/g, '')
                .replace(/^\)\s*|\s*\)$/g, '');
        }
        if (!name) {
            name = `Sprint ${new Date().toLocaleDateString('vi-VN')}`;
        }

        appendUserMessage(message);
        createQuickSprint(name, days);
        return true;
    }

    function createQuickSprint(name, days) {
        const today = new Date();
        const getLocalISO = (d) => {
            const offsetMs = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - offsetMs).toISOString().split('T')[0];
        };

        // Format any ISO timestamp (e.g., 2026-01-21T00:00:00.000000Z) to local YYYY-MM-DD
        const formatLocalDate = (str) => {
            try {
                const d = new Date(str);
                if (!isNaN(d.getTime())) {
                    const offsetMs = d.getTimezoneOffset() * 60000;
                    const local = new Date(d.getTime() - offsetMs);
                    return local.toISOString().split('T')[0];
                }
            } catch (_) {}
            return str; // fallback
        };

        appendAIMessage(`🛠️ Đang tạo Future Sprint "${escapeHtml(name)}" trong ${days} ngày...`);

        fetch('/tasksboard?format=json', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(meta => {
                const minStart = meta.minStartDate || getLocalISO(today);
                const todayISO = getLocalISO(today);
                const startISO = [todayISO, minStart].sort().pop(); // max of two ISO strings

                const start = new Date(startISO);
                const end = new Date(start);
                end.setDate(end.getDate() + days - 1);
                const endISO = getLocalISO(end);

                return { startISO, endISO };
            })
            .then(({ startISO, endISO }) => {
                return fetch('/future-sprints', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: name,
                goal: '',
                start_date: startISO,
                end_date: endISO,
            })
        })
        .then(async res => {
            const text = await res.text();
            let data = null;
            try { data = JSON.parse(text); } catch (_) {}

            if (!res.ok) {
                const msg = data?.message || text || 'Lỗi không rõ khi tạo Future Sprint';
                appendAIMessage('❌ Lỗi khi tạo Future Sprint: ' + msg);
                return;
            }

            if (!data || !data.sprint) {
                appendAIMessage(data?.message || '❌ Không tạo được Future Sprint');
                return;
            }

            quickSprintContext.sprint = data.sprint;
            const startDisp = data.sprint.start_date ? formatLocalDate(data.sprint.start_date) : startISO;
            const endDisp = data.sprint.end_date ? formatLocalDate(data.sprint.end_date) : endISO;
            appendAIMessage(`✅ Đã tạo Future Sprint "${escapeHtml(name)}" (${startDisp} → ${endDisp}).`);
            renderUSOptionsPrompt();
        })
        .catch(err => {
            appendAIMessage('❌ Lỗi khi tạo Future Sprint: ' + err.message);
        });
            })
            .catch(err => {
                appendAIMessage('❌ Lỗi khi lấy thông tin sprint trước đó: ' + err.message);
            });
    }

    function renderUSOptionsPrompt() {
        const area = document.getElementById('ai-chat-messages');
        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-2 pl-10';

        // Use unique IDs to avoid conflicts after reload
        const uniqueId = 'us-options-' + Date.now();
        const btnShowId = uniqueId + '-show';
        const btnManualId = uniqueId + '-manual';

        wrapper.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
            <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm text-sm space-y-2">
                <div class="font-semibold text-gray-700">Chọn cách thêm US vào Sprint:</div>
                <div class="flex flex-col gap-2">
                    <button id="${btnShowId}" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs font-medium">Hiển thị US để chọn (modal)</button>
                    <button id="${btnManualId}" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs">Tự set up (thủ công)</button>
                </div>
            </div>
        `;
        area.appendChild(wrapper);
        area.scrollTop = area.scrollHeight;

        // Attach listeners to newly created elements immediately
        const btnShow = wrapper.querySelector(`#${btnShowId}`);
        const btnManual = wrapper.querySelector(`#${btnManualId}`);

        if (btnShow) btnShow.onclick = () => loadBacklogAndRender(true);
        if (btnManual) btnManual.onclick = () => {
            appendAIMessage('👌 Bạn có thể tự set up Sprint trong Product Backlog.');
            refreshSprintsSnapshot(renderStartSprintPrompt);
        };
    }

    function loadBacklogAndRender(useModal = false) {
        appendAIMessage('📋 Đang tải US backlog để bạn chọn...');
        fetch('/tasksboard?format=json', {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            quickSprintContext.backlog = data.backlogTasks || [];
            quickSprintContext.activeSprint = data.activeSprint || null;
            quickSprintContext.futureSprints = data.futureSprints || [];
            if (useModal) {
                renderUSModal();
            } else {
                renderUSCheckboxList();
            }
        })
        .catch(err => {
            appendAIMessage('❌ Không tải được backlog: ' + err.message);
        });
    }

    function renderUSModal() {
        const existing = document.getElementById('ai-us-modal');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'ai-us-modal';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-40 z-[9999] flex items-center justify-center p-4';

        // Group US by epic
        const epicGroups = {};
        quickSprintContext.backlog.forEach(task => {
            const epic = task.epic_title || 'Không có Epic';
            if (!epicGroups[epic]) epicGroups[epic] = [];
            epicGroups[epic].push(task);
        });

        const groupHtml = Object.entries(epicGroups).map(([epic, tasks]) => `
            <div class="mb-4">
                <div class="font-semibold text-indigo-700 text-base mb-2">${escapeHtml(epic)}</div>
                <div class="space-y-2">
                    ${tasks.map(task => {
                        const points = task.storyPoints || task.story_point || 0;
                        return `
                        <label class="flex items-start gap-2 p-2 rounded hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100">
                            <input type="checkbox" class="us-select-checkbox mt-1" value="${task.id}">
                            <span class="flex-1 text-gray-800">
                                <span class="font-medium">${escapeHtml(task.title || 'User Story')}</span>
                                <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-semibold">${points} pts</span>
                            </span>
                        </label>
                        `;
                    }).join('')}
                </div>
            </div>
        `).join('');

        overlay.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[75vh] flex flex-col">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <div class="font-semibold text-gray-800">Chọn User Stories cho Sprint</div>
                    <button id="ai-us-close" class="text-gray-500 hover:text-gray-700 text-lg">×</button>
                </div>
                <div class="p-4 space-y-3 flex-1 overflow-y-auto">
                    ${groupHtml || '<div class="text-sm text-gray-500">Chưa có User Story trong backlog.</div>'}
                </div>
                <div class="px-4 py-3 border-t flex justify-end gap-2">
                    <button id="ai-us-cancel" class="px-3 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 text-sm">Đóng</button>
                    <button id="ai-us-save" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 text-sm">💾 Lưu US vào Sprint</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const closeModal = () => overlay.remove();
        document.getElementById('ai-us-close').onclick = closeModal;
        document.getElementById('ai-us-cancel').onclick = closeModal;
        document.getElementById('ai-us-save').onclick = () => {
            saveSelectedUS();
            closeModal();
        };
    }

    function renderUSCheckboxList() {
        const area = document.getElementById('ai-chat-messages');
        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-2 pl-10';

        const listHtml = quickSprintContext.backlog.map(task => `
            <label class="flex items-start gap-2 p-1 rounded hover:bg-gray-100 cursor-pointer text-xs">
                <input type="checkbox" class="us-select-checkbox mt-1" value="${task.id}">
                <span class="text-gray-700">${escapeHtml(task.title || 'US')}</span>
            </label>
        `).join('');

        wrapper.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
            <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm text-sm space-y-2">
                <div class="font-semibold text-gray-700">Chọn US muốn đưa vào Sprint:</div>
                <div id="us-checkbox-list" class="max-h-40 overflow-y-auto border border-gray-200 rounded p-2 bg-gray-50 space-y-1">
                    ${listHtml || '<div class="text-xs text-gray-500">Chưa có User Story trong backlog.</div>'}
                </div>
                <button id="btn-save-us" class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-xs font-medium">💾 Lưu US vào Sprint</button>
            </div>
        `;

        area.appendChild(wrapper);
        area.scrollTop = area.scrollHeight;

        const btnSave = wrapper.querySelector('#btn-save-us');
        btnSave.onclick = () => saveSelectedUS();
    }

    function saveSelectedUS() {
        const checkboxes = document.querySelectorAll('.us-select-checkbox');
        const ids = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

        if (!quickSprintContext.sprint) {
            appendAIMessage('❌ Chưa có sprint để gán.');
            return;
        }
        if (ids.length === 0) {
            appendAIMessage('⚠️ Vui lòng chọn ít nhất 1 US.');
            return;
        }

        appendAIMessage('⏳ Đang gán US vào Sprint...');

        const requests = ids.map(id => fetch(`/user-stories/${id}/assign-future-sprint`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ sprint_id: quickSprintContext.sprint.id })
        }).then(res => res.json()));

        Promise.all(requests)
            .then(() => {
                appendAIMessage('✅ Đã gán US vào Sprint.');
                // Refresh sprint list without page reload for smooth UX
                refreshSprintsSnapshot(renderStartSprintPrompt);
            })
            .catch(err => {
                appendAIMessage('❌ Lỗi khi gán US: ' + err.message);
            });
    }

    function refreshSprintsSnapshot(next) {
        fetch('/tasksboard?format=json', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                quickSprintContext.activeSprint = data.activeSprint || null;
                quickSprintContext.futureSprints = data.futureSprints || [];
                console.log('refreshSprintsSnapshot data:', { activeSprint: quickSprintContext.activeSprint, futureSprints: quickSprintContext.futureSprints });

                // Always call the callback to render
                if (typeof next === 'function') next();
            })
            .catch(err => {
                appendAIMessage('⚠️ Không tải được danh sách sprint: ' + err.message);
                if (typeof next === 'function') next();
            });
    }

    function renderStartSprintPrompt() {
        const area = document.getElementById('ai-chat-messages');
        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-2 pl-10';

        const active = quickSprintContext.activeSprint;
        const future = quickSprintContext.futureSprints || [];

        // Nếu có sprint đang chạy thì locked hết future sprint (kể cả sprint đang chạy)
        let startableId = null;
        if (!active) {
            for (let i = 0; i < future.length; i++) {
                const sp = future[i];
                startableId = sp.id;
                break;
            }
        }

        const futureList = future.map((sp, idx) => {
            let isLocked = false;
            let lockNote = '';
            let btnLabel = 'Start';
            let btnStyle = 'bg-blue-500 text-white hover:bg-blue-600';
            let disable = '';
            let dim = '';

            if (active) {
                isLocked = true;
                lockNote = '<div class="text-[11px] text-amber-600">🔒 Không thể start khi có sprint đang chạy</div>';
                btnLabel = 'Locked';
                btnStyle = 'bg-gray-300 text-gray-500 cursor-not-allowed';
                disable = 'disabled';
                dim = 'opacity-50 cursor-not-allowed';
            } else if (startableId && sp.id !== startableId) {
                isLocked = true;
                lockNote = '<div class="text-[11px] text-amber-600">🔒 Chỉ start sprint kế tiếp (tạo trước)</div>';
                btnLabel = 'Locked';
                btnStyle = 'bg-gray-300 text-gray-500 cursor-not-allowed';
                disable = 'disabled';
                dim = 'opacity-50 cursor-not-allowed';
            }

            const startDisp = sp.start_date ? formatLocalDate(sp.start_date) : '--';
            const endDisp = sp.end_date ? formatLocalDate(sp.end_date) : '--';

            return `
                <div class="border border-gray-200 rounded p-2 flex items-center justify-between text-xs ${dim}">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">${escapeHtml(sp.name)}</div>
                        <div class="text-gray-600">${startDisp} → ${endDisp}</div>
                        ${lockNote}
                    </div>
                    <div class="flex gap-1">
                        <button class="btn-add-us-sprint px-2 py-1 bg-green-500 text-white hover:bg-green-600 rounded text-xs" data-id="${sp.id}" title="Thêm US">+</button>
                        <button class="btn-start-sprint px-2 py-1 ${btnStyle} rounded text-xs" data-id="${sp.id}" ${disable}>${btnLabel}</button>
                    </div>
                </div>
            `;
        }).join('');

        wrapper.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">AI</div>
            <div class="bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm text-sm space-y-2">
                <div class="font-semibold text-gray-700">Bắt đầu Sprint?</div>
                ${active ? '<div class="text-xs text-red-600">Đang có sprint chạy: ' + escapeHtml(active.name || 'Sprint') + '</div>' : ''}
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    ${futureList || '<div class="text-xs text-gray-500">Chưa có future sprint nào.</div>'}
                </div>
            </div>
        `;

        area.appendChild(wrapper);
        area.scrollTop = area.scrollHeight;

        wrapper.querySelectorAll('.btn-start-sprint').forEach(btn => {
            btn.onclick = () => {
                const targetId = btn.dataset.id;
                if (startableId && targetId !== String(startableId)) {
                    appendAIMessage('🔒 Bạn chỉ có thể start sprint kế tiếp (theo thứ tự tạo).');
                    return;
                }
                startSprint(targetId);
            };
        });

        wrapper.querySelectorAll('.btn-add-us-sprint').forEach(btn => {
            btn.onclick = () => {
                const sprintId = btn.dataset.id;
                const sprint = quickSprintContext.futureSprints.find(s => s.id == sprintId);
                if (!sprint) {
                    appendAIMessage('❌ Không tìm thấy Sprint.');
                    return;
                }
                quickSprintContext.sprint = sprint;
                appendAIMessage(`📋 Đang tải US backlog để thêm vào "${escapeHtml(sprint.name)}"...`);
                loadBacklogAndRender(true);
            };
        });
    }

    function startSprint(sprintId) {
        appendAIMessage('🚀 Đang kích hoạt Sprint...');
        fetch(`/future-sprints/${sprintId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        })
        .then(async res => {
            const text = await res.text();
            let data = null;
            try { data = JSON.parse(text); } catch (_) {}

            if (!res.ok) {
                const msg = data?.message || text || 'Không start được Sprint';
                appendAIMessage('❌ Không start được Sprint: ' + msg);
                return;
            }

            appendAIMessage((data && data.message) ? data.message : '✅ Sprint đã được bắt đầu.');
        })
        .catch(err => {
            appendAIMessage('❌ Không start được Sprint: ' + err.message);
        });
    }

    function loadSprintUSList() {
        const usList = document.getElementById('sprint-us-list');
        usList.innerHTML = `
            <label class="flex items-center gap-2 text-xs p-1 cursor-pointer hover:bg-gray-100">
                <input type="checkbox" class="sprint-us-checkbox" value="all" checked>
                <span class="text-gray-600">✅ Tất cả US vừa tạo</span>
            </label>
            <label class="flex items-center gap-2 text-xs p-1 cursor-pointer hover:bg-gray-100">
                <input type="checkbox" class="sprint-us-checkbox" value="allow-custom">
                <span class="text-gray-600">📝 Thêm US khác (từ Epic này)</span>
            </label>
        `;
    }

    function saveSprintWithUS() {
        const name = document.getElementById('sprint-name').value?.trim();
        const goal = document.getElementById('sprint-goal').value?.trim();
        const startDate = document.getElementById('sprint-start-date').value;
        const endDate = document.getElementById('sprint-end-date').value;

        if (!name || !startDate || !endDate) {
            appendAIMessage('❌ Vui lòng nhập tên Sprint và chọn khoảng thời gian');
            return;
        }

        if (new Date(startDate) >= new Date(endDate)) {
            appendAIMessage('❌ Ngày bắt đầu phải trước ngày kết thúc');
            return;
        }

        appendUserMessage(`📅 Tạo Sprint "${name}" từ ${startDate} đến ${endDate}`);
        showTypingIndicator();

        fetch('/future-sprints', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: name,
                goal: goal,
                start_date: startDate,
                end_date: endDate,
            })
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator();
            closeSprintForm();

            if (data.message && data.message.includes('thành công')) {
                appendAIMessage(`✅ ${data.message}`);
            } else {
                appendAIMessage(data.message || '✅ Sprint tạo thành công!');
            }

            appendSuggestions([
                {label: '📋 Tạo thêm US', action: 'generate_us'},
                {label: '📅 Xem Sprint Planning', action: 'goto_sprint_planning'}
            ]);

            // Reload page to reflect newly created sprint in UI
            setTimeout(() => {
                try { window.location.reload(); } catch (_) {}
            }, 400);
        })
        .catch(err => {
            removeTypingIndicator();
            console.error('Create sprint error:', err);
            appendAIMessage('❌ Lỗi khi tạo Sprint: ' + err.message);
        });
    }

    function closeSprintForm() {
        const container = document.getElementById('sprint-form-container');
        if (container) container.remove();
    }

    function handleGenerateMoreUS() {
        // Don't show AI message here, let backend handle the response
        showTypingIndicator();

        // Fetch epic list
        fetch(`/ai/chat/${currentSessionId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ message: 'tạo us' })
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator();
            if (data.message) {
                appendAIMessage(data.message);
            }
            if (data.suggestions && data.suggestions.length > 0) {
                appendSuggestions(data.suggestions);
            }
        })
        .catch(err => {
            removeTypingIndicator();
            console.error('Error:', err);
            appendAIMessage('❌ Lỗi khi lấy danh sách Epic');
        });
    }

    // Phân rã US thành subtasks
    function tryHandleDecomposeUSCommand(message) {
        const lower = message.toLowerCase();
        if (!lower.includes('phân rã') && !lower.includes('decompose')) return false;
        if (!lower.includes('us')) return false;

        // Extract US ID: "Phân rã US #123" hoặc "Phân rã US: Đăng nhập"
        let usId = null;
        const idMatch = message.match(/#(\d+)/);
        if (idMatch) {
            usId = parseInt(idMatch[1]);
        }

        if (!usId) {
            appendUserMessage(message);
            appendAIMessage('❌ Không tìm thấy ID User Story. Vui lòng nhập "Phân rã US #id" (ví dụ: "Phân rã US #50").');
            return true;
        }

        appendUserMessage(message);
        appendAIMessage('🔄 Đang phân tích User Story và tạo gợi ý subtasks...');

        // If the AI call takes too long, show a gentle lag hint
        let _decomposeDone = false;
        const _lagTimer = setTimeout(() => {
            if (!_decomposeDone) {
                appendAIMessage('⏳ Đợi xíu nhé, AI đang xử lý hơi lâu một chút...');
            }
        }, 10000);

        fetch('/ai/decompose-us', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ us_id: usId, preset: 'minimal' })
        })
        .then(res => res.json())
        .then(data => {
            _decomposeDone = true;
            try { clearTimeout(_lagTimer); } catch(_) {}
            if (data.error) {
                appendAIMessage('❌ ' + data.error);
                return;
            }
            renderSubtasksSuggestions(data);
        })
        .catch(err => {
            _decomposeDone = true;
            try { clearTimeout(_lagTimer); } catch(_) {}
            console.error('Decompose error:', err);
            appendAIMessage('❌ Lỗi khi phân rã US: ' + err.message);
        });

        return true;
    }

    function renderSubtasksSuggestions(data) {
        const us = data.us;
        const subtasks = data.subtasks || [];
        const preset = data.preset;
        const teamMembers = data.team_members || [];

        // Tạo modal overlay thay vì render trong chat
        const overlay = document.createElement('div');
        overlay.id = 'subtask-suggestions-modal';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0;';

        const subtaskList = subtasks.map((st, idx) => {
            const priorityColor = st.priority === 'high' ? 'text-red-600' : st.priority === 'medium' ? 'text-yellow-600' : 'text-green-600';

            // Tạo dropdown assignee
            const assigneeOptions = teamMembers.map(member => {
                const selected = st.suggested_assignee_id === member.id ? 'selected' : '';
                return `<option value="${member.id}" ${selected}>${member.name} (${member.workload} SP)</option>`;
            }).join('');

            return `
                <label class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                    <input type="checkbox" class="subtask-checkbox mt-1.5 w-4 h-4" value="${idx}" checked>
                    <div class="flex-1 space-y-2">
                        <input type="text" class="subtask-title w-full px-2 py-1.5 border border-gray-300 rounded text-sm font-medium focus:ring-2 focus:ring-indigo-500" value="${escapeHtml(st.title)}" data-idx="${idx}">
                        <textarea class="subtask-description w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-600 focus:ring-2 focus:ring-indigo-500" rows="2" data-idx="${idx}" placeholder="Mô tả subtask...">${escapeHtml(st.description || '')}</textarea>
                        <div class="flex gap-3 items-center flex-wrap">
                            <div class="flex gap-2 items-center">
                                <span class="text-gray-600 text-xs font-medium">Priority:</span>
                                <select class="subtask-priority px-3 py-1 border border-gray-300 rounded text-sm ${priorityColor} focus:ring-2 focus:ring-indigo-500" data-idx="${idx}">
                                    <option value="low" ${st.priority === 'low' ? 'selected' : ''}>Low</option>
                                    <option value="medium" ${st.priority === 'medium' ? 'selected' : ''}>Medium</option>
                                    <option value="high" ${st.priority === 'high' ? 'selected' : ''}>High</option>
                                </select>
                            </div>
                            <div class="flex gap-2 items-center">
                                <span class="text-gray-600 text-xs font-medium">Assign To:</span>
                                <select class="subtask-assignee px-3 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500" data-idx="${idx}">
                                    <option value="">Unassigned</option>
                                    ${assigneeOptions}
                                </select>
                            </div>
                        </div>
                    </div>
                </label>
            `;
        }).join('');

        overlay.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col" style="margin: 20px;">
                <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div>
                        <div class="font-bold text-lg text-gray-800">Gợi ý phân rã US #${us.id}</div>
                        <div class="text-sm text-gray-600 mt-1">${escapeHtml(us.title)}</div>
                        <div class="text-xs text-gray-500 mt-1">Story Points: ${us.storyPoints || '--'} | Preset: <span class="font-medium">${preset === 'minimal' ? 'Tối thiểu' : 'Đầy đủ'}</span></div>
                    </div>
                    <button id="subtask-modal-close" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-6 space-y-2 flex-1 overflow-y-auto">
                    ${subtaskList || '<div class="text-center text-gray-500 py-8">Không có subtask nào.</div>'}
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-between gap-3">
                    <button id="btn-change-preset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium transition">
                        Đổi sang ${preset === 'minimal' ? 'Đầy đủ' : 'Tối thiểu'}
                    </button>
                    <div class="flex gap-2">
                        <button id="btn-cancel-subtasks" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium transition">Hủy</button>
                        <button id="btn-create-subtasks" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium shadow-lg transition">Tạo Subtasks</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Store data for create
        overlay.dataset.usId = us.id;
        overlay.dataset.subtasks = JSON.stringify(subtasks);
        overlay.dataset.preset = preset;
        overlay.dataset.teamMembers = JSON.stringify(teamMembers);
        overlay.dataset.usData = JSON.stringify(us); // 🔥 Lưu toàn bộ US data (bao gồm sprint_id)

        const closeModal = () => {
            overlay.remove();
            appendAIMessage('❌ Đã hủy tạo subtasks.');
        };

        document.getElementById('subtask-modal-close').onclick = closeModal;
        document.getElementById('btn-cancel-subtasks').onclick = closeModal;

        document.getElementById('btn-change-preset').onclick = () => {
            const newPreset = preset === 'minimal' ? 'full' : 'minimal';
            overlay.remove();
            appendAIMessage('🔄 Đang tạo lại với preset ' + (newPreset === 'minimal' ? 'Tối thiểu' : 'Đầy đủ') + '...');
            fetch('/ai/decompose-us', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ us_id: us.id, preset: newPreset })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    appendAIMessage('❌ ' + data.error);
                } else {
                    renderSubtasksSuggestions(data);
                }
            })
            .catch(err => appendAIMessage('❌ Lỗi: ' + err.message));
        };

        document.getElementById('btn-create-subtasks').onclick = () => createSubtasksFromModal(overlay);
    }
    function createSubtasksFromModal(modal) {
        const usId = parseInt(modal.dataset.usId);
        const subtasksData = JSON.parse(modal.dataset.subtasks);

        // 🔥 Lấy sprint_id từ response data (đã có sẵn từ AI decompose)
        const usData = JSON.parse(modal.dataset.usData || '{}');
        const sprintId = usData.sprint_id;
        console.log('🎯 Sprint ID từ US:', sprintId);

        // Lấy các subtask được chọn và cập nhật thông tin từ input
        const checkboxes = modal.querySelectorAll('.subtask-checkbox:checked');
        const selectedSubtasks = Array.from(checkboxes).map(cb => {
            const idx = parseInt(cb.value);
            const st = subtasksData[idx];
            const titleInput = modal.querySelector(`.subtask-title[data-idx="${idx}"]`);
            const descriptionInput = modal.querySelector(`.subtask-description[data-idx="${idx}"]`);
            const prioritySelect = modal.querySelector(`.subtask-priority[data-idx="${idx}"]`);
            const assigneeSelect = modal.querySelector(`.subtask-assignee[data-idx="${idx}"]`);

            return {
                title: titleInput ? titleInput.value : st.title,
                description: descriptionInput ? descriptionInput.value : st.description,
                priority: prioritySelect ? prioritySelect.value : (st.priority || 'medium'),
                assigned_to: assigneeSelect && assigneeSelect.value ? parseInt(assigneeSelect.value) : null,
                parent_id: usId,
                sprint_id: sprintId // 🔥 Truyền sprint_id để subtask hiện trong Task Board
            };
        });

        if (selectedSubtasks.length === 0) {
            appendAIMessage('⚠️ Vui lòng chọn ít nhất 1 subtask.');
            return;
        }

        modal.remove();
        appendAIMessage(`⏳ Đang tạo ${selectedSubtasks.length} subtasks...`);

        // Gọi API tạo từng subtask
        console.log('📤 Subtasks to create:', selectedSubtasks);
        const requests = selectedSubtasks.map(st => fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(st)
        }).then(async res => {
            const text = await res.text();
            let data = null;
            try { data = JSON.parse(text); } catch (_) {
                // Not JSON (likely HTML error/redirect)
                data = { error: `HTTP ${res.status}`, raw: text };
            }
            if (!res.ok) {
                const msg = data?.message || data?.error || `HTTP ${res.status}`;
                throw new Error(msg);
            }
            return data;
        }));

        Promise.all(requests)
            .then(results => {
                console.log('✅ API Results:', results);
                const successCount = results.filter(r => r.task || r.message?.includes('thành công')).length;
                appendAIMessage(`✅ Đã tạo ${successCount}/${selectedSubtasks.length} subtasks cho US #${usId}.`);

                // 🔥 Reload trang để hiển thị subtasks mới trong Task Board
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(err => {
                appendAIMessage('❌ Lỗi khi tạo subtasks: ' + err.message);
            });
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function positionChatRelativeToButton() {
        const rect = floatBtn.getBoundingClientRect();
        const chatRect = { width: chat.offsetWidth || 320, height: chat.offsetHeight || 480 };

        let left = rect.left + (rect.width / 2) - (chatRect.width / 2);
        let top = rect.top + rect.height + 10;

        const maxLeft = window.innerWidth - chatRect.width - 10;
        const maxTop = window.innerHeight - chatRect.height - 10;
        left = Math.max(10, Math.min(left, maxLeft));
        top = Math.max(10, Math.min(top, maxTop));

        chat.style.left = left + 'px';
        chat.style.top = top + 'px';
        chat.style.right = 'auto';
        chat.style.bottom = 'auto';
    }
});
</script>

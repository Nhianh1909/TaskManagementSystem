<script>

    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       OPEN EPIC AND USER STORY PANELS
    //*
    //******************************************************************************** *
    //==================================================================================
    // Mở Epic Panel
    function openEpicPanel(epicId) {
        closeAllPanels(); // Đóng tất cả panel khác trước
        const panel = document.getElementById('epic-panel-' + epicId);
        panel.classList.remove('hidden');
        // panel.classList.add('flex'); // Không cần vì <aside> bên trong đã có flex sẵn
    }

    // Đóng Epic Panel
    function closeEpicPanel(epicId) {
        const panel = document.getElementById('epic-panel-' + epicId);
        panel.classList.add('hidden');
        // panel.classList.remove('flex'); // Không cần vì chỉ toggle hidden là đủ
    }

    // =====================
    // Real-time comments (Polling)
    // =====================
    // Lưu interval theo storyId để clear khi đóng panel/đổi story
    const commentIntervals = {};
    // Lưu "key" đại diện cho danh sách comments hiện tại (để tránh re-render khi không đổi)
    const lastCommentKeys = {};
    // Theo dõi các comment đã bị thu gọn replies
    const collapsedCommentIds = new Set();

    function computeCommentsKey(comments) {
        if (!Array.isArray(comments) || comments.length === 0) return 'empty:0:0:0';

        let totalCount = 0; // Đếm tổng số comment/reply
        let newestTs = 0;   // Lưu timestamp (dạng số) mới nhất
        let newestId = 0;   // Lưu ID mới nhất
        //tạo ra biến toTs để chuyển ngày thành timestamp so sánh tgian cmt nào mới nhất, tính từ ngày 1/1/1970
        const toTs = (d) => (d ? new Date(d).getTime() : 0);

        // Đệ quy đếm tất cả comments và replies ở mọi cấp
        function countDeep(items) {
            if (!Array.isArray(items)) return;
            items.forEach(item => {
                //cứ thấy một comment thì bộ đếm tăng có bao nhiêu cmt
                totalCount++;
                //tạo hàm ts để lấy cột móc thời gian của bluan đó sau đó tính tgian timestamp
                const ts = toTs(item.created_at);
                //lặp qua mảng comment parent nếu như ko rỗng thì xuống vòng lặp dưới để kiểm tra
                if (ts > newestTs || (ts === newestTs && item.id > newestId)) {
                    newestTs = ts;
                    newestId = item.id;
                }
                //vòng lặp replies từ parent nếu có
                if (Array.isArray(item.replies) && item.replies.length > 0) {
                    countDeep(item.replies);
                }
            });
        }
        //gọi hàm để nó chạy
        countDeep(comments);
        // Kết hợp count + timestamp + ID để đảm bảo unique khi có comment mới
        return `${totalCount}:${newestTs}:${newestId}`;
    }
    //truyền vào 2 tham số là storyId và intervalMs với 3s
    function startCommentsPolling(storyId, intervalMs = 3000) {
        // Clear interval cũ nếu như đang có bộ đếm nào đang chạy song song
        if (commentIntervals[storyId]) {
            clearInterval(commentIntervals[storyId]);
        }
        // Tạo interval mới với hàm setInterval set time nội dung lặp đi lặp lại sau mỗi 3s
        commentIntervals[storyId] = setInterval(async () => {
            try {
                const res = await fetch(`/user-stories/${storyId}/comments`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (!res.ok) return;
                //tạo ra chữ ký hiện tại, để kiểm tra các mảng comment
                const currentKey = computeCommentsKey(data.comments || []);
                //nếu như key cuối gần nhất ko bằng key mới thì re-render
                if (lastCommentKeys[storyId] !== currentKey) {
                    const container = document.getElementById('comments-list-' + storyId);
                    //nếu có thay đổi thì hiển thị contrainer với data của comments mới
                    if (container) {
                        displayComments(container, data.comments || [], storyId);
                        lastCommentKeys[storyId] = currentKey;
                    }
                }
            } catch (e) {
                console.error('Polling comments error:', e);
            }
        }, intervalMs);
    }

    function stopCommentsPolling(storyId) {
        if (commentIntervals[storyId]) {
            clearInterval(commentIntervals[storyId]);
            delete commentIntervals[storyId];
        }
    }

    // Mở Story Panel
    function openStoryPanel(storyId) {
        closeAllPanels(); // Đóng tất cả panel khác trước
        const panel = document.getElementById('story-panel-' + storyId);
        panel.classList.remove('hidden');

        // Load comments khi mở panel + bật polling, then là sau khi tải xong lần đầu sẽ bật hàm thăm dò
        loadComments(storyId).then(() => startCommentsPolling(storyId));
    }

    // Đóng Story Panel
    function closeStoryPanel(storyId) {
        const panel = document.getElementById('story-panel-' + storyId);
        panel.classList.add('hidden');
        // panel.classList.remove('flex'); // Không cần vì chỉ toggle hidden là đủ
        // Tắt polling khi đóng panel
        stopCommentsPolling(storyId);
    }

    // Đóng tất cả panel (Epic và Story) trước khi mở một panel mới đã sử dụng trong các hàm open
    function closeAllPanels() {
        // Đóng tất cả Epic panels, công thức CSS [id^="epic-panel-"] là một CSS Attribute Selector, có nghĩa là
        // epic-panel- bắt đầu từ id NÀO ĐÓ
        document.querySelectorAll('[id^="epic-panel-"]').forEach(panel => {
            panel.classList.add('hidden');
            // panel.classList.remove('flex'); // Không cần
        });
        // Đóng tất cả Story panels
        document.querySelectorAll('[id^="story-panel-"]').forEach(panel => {
            panel.classList.add('hidden');
            // panel.classList.remove('flex'); // Không cần
        });
    }

    // --- 2. CREATE MODAL FUNCTIONS ---
    // Mở Create Epic Modal
    function openCreateModal() {
        const modal = document.getElementById('create-modal');// Lấy phần tử modal
        modal.classList.remove('hidden');
        // modal.classList.add('flex'); // Không cần, modal content bên trong đã có flex để căn giữa

        // Reset form về trống
        document.getElementById('epic-title').value = '';
        document.getElementById('epic-description').value = '';
    }

    // Đóng Create Epic Modal
    function closeCreateModal() {
        const modal = document.getElementById('create-modal');
        modal.classList.add('hidden');
        // modal.classList.remove('flex'); // Không cần
    }






    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       SUBMIT FORMS FUNCTIONS
    //*
    //******************************************************************************** *
    //==================================================================================

    // Submit Create Epic Form
    function submitCreateEpic(event) {
        // Chặn hành vi submit mặc định (không reload trang)
        event.preventDefault();

        // Lấy dữ liệu từ form
        const title = document.getElementById('epic-title').value;
        const description = document.getElementById('epic-description').value;

        let activeCommentsStoryId = null; // Track the active story ID
        // Validate
        if (!title.trim()) {
            alert('Please fill in the Epic title');
            return;
        }

        // Chuẩn bị dữ liệu để gửi
        const epicData = {
            title: title,
            description: description
        };

        console.log('Submitting epic:', epicData);

        // Gửi AJAX request đến backend
        fetch("{{ route('epics.store') }}", {  // URL = route đã tạo ở Bước 1
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Gửi dạng JSON
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')  // CSRF token bắt buộc
            },
            body: JSON.stringify({  // Chuyển dữ liệu thành JSON
                title: title,
                description: description
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const msg = data.message || `HTTP ${response.status}`;
                throw new Error(msg);
            }

            console.log('Epic created successfully:', data);
            closeCreateModal(); // Đóng modal
            alert('Epic created successfully!');
            window.location.reload(); // Reload trang để hiển thị epic mới
        })
        .catch(error => {
            console.error('Error creating epic:', error);
            alert(error.message || 'Failed to create epic. Please try again.');
        });
    }


    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       OPEN CREATE USER STORY MODAL
    //*
    //******************************************************************************** *
    //==================================================================================



    // --- 2B. CREATE USER STORY MODAL FUNCTIONS ---
    let currentEpicId = null; // Biến lưu epic_id hiện tại
    let epicTitles = {}; // Object lưu tên Epic theo ID
    //truyền title bằng null để tránh lỗi epicTitles undefined
    // Mở Create User Story Modal
    function openCreateStoryModal(epicId, epicTitle = null) {
        currentEpicId = epicId;
        const modal = document.getElementById('create-story-modal');
        modal.classList.remove('hidden');
        // modal.classList.add('flex'); // Không cần, modal content đã có flex để căn giữa

        // activeCommentsStoryId = storyId; // Track the active story ID
        // Reset form về trống
        document.getElementById('story-title').value = '';
        document.getElementById('story-description').value = '';
        document.getElementById('story-status').value = 'toDo';
        document.getElementById('story-points').value = '';
        document.getElementById('story-priority').value = 'medium';
        document.getElementById('story-assignee').value = '';

        // Hiển thị tên Epic (read-only)
        document.getElementById('story-epic-id').value = epicId;

        // Logic hiển thị tên: Ưu tiên tên truyền vào -> Tên trong mảng -> ID
        const displayTitle = epicTitle ? epicTitle : (epicTitles[epicId] || ('Epic #' + epicId));
        document.getElementById('story-epic-display').value = displayTitle;
    // Hiển thị tên Epic (read-only)
    // document.getElementById('story-epic-id').value = epicId;
    // const displayTitle = epicTitle != null ? epicTitle : (epicTitles[epicId] || ('Epic #' + epicId));
    // document.getElementById('story-epic-display').value = displayTitle;
    }

    // Đóng Create User Story Modal
    function closeCreateStoryModal() {
        const modal = document.getElementById('create-story-modal');
        modal.classList.add('hidden');
        // modal.classList.remove('flex'); // Không cần
        currentEpicId = null;
    }



    // =================================================================================
    //******************************************************************************** *
    //*
    //*                   SUBMIT FORMS CREATE USERSTORIES FUNCTIONS
    //*
    //******************************************************************************** *
    //==================================================================================



    // Submit Create User Story Form
    function submitCreateStory(event) {
        event.preventDefault();

        // ✅ Lấy dữ liệu từ form - Status mặc định là 'toDo' cho US trong Product Backlog
        const storyData = {
            title: document.getElementById('story-title').value,
            description: document.getElementById('story-description').value,
            status: 'toDo',  // ✅ Hardcode - User Stories in Product Backlog luôn là To Do
            storyPoints: document.getElementById('story-points').value || null,
            priority: document.getElementById('story-priority').value,
            assigned_to: document.getElementById('story-assignee').value || null,
            epic_id: currentEpicId
        };

        console.log('Submitting story:', storyData);

        // Gửi AJAX request đến backend
        fetch("{{ route('user-stories.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(storyData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('User Story created successfully:', data);
            closeCreateStoryModal();
            alert('User Story created successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error creating story:', error);
            alert('Failed to create user story. Please try again.');
        });
    }





    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       VIEW AND EDIT EPIC FUNCTIONS
    //*
    //******************************************************************************** *
    //==================================================================================






    // Toggle sang Edit Mode
    function toggleEditEpicMode(epicId) {
        // Ẩn View Mode, hiện Edit Mode
        document.getElementById('epic-view-' + epicId).classList.add('hidden');
        document.getElementById('epic-edit-' + epicId).classList.remove('hidden');

        // Đổi buttons: Ẩn Edit + Delete, Hiện Cancel + Save
        document.getElementById('epic-btn-edit-' + epicId).classList.add('hidden');
        document.getElementById('epic-btn-delete-' + epicId).classList.add('hidden');
        document.getElementById('epic-btn-cancel-' + epicId).classList.remove('hidden');
        document.getElementById('epic-btn-save-' + epicId).classList.remove('hidden');
    }

    // Hủy Edit Mode, quay về View Mode
    function cancelEditEpic(epicId) {
        // Hiện View Mode, ẩn Edit Mode
        document.getElementById('epic-view-' + epicId).classList.remove('hidden');
        document.getElementById('epic-edit-' + epicId).classList.add('hidden');

        // Đổi buttons: Hiện Edit + Delete, Ẩn Cancel + Save
        document.getElementById('epic-btn-edit-' + epicId).classList.remove('hidden');
        document.getElementById('epic-btn-delete-' + epicId).classList.remove('hidden');
        document.getElementById('epic-btn-cancel-' + epicId).classList.add('hidden');
        document.getElementById('epic-btn-save-' + epicId).classList.add('hidden');
    }



    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       SAVE AND DELETE EDIT EPIC
    //*
    //******************************************************************************** *
    //==================================================================================


    // Lưu thay đổi Epic
    function saveEditEpic(epicId) {
        // Lấy dữ liệu từ input
        const title = document.getElementById('epic-title-edit-' + epicId).value;
        const description = document.getElementById('epic-desc-edit-' + epicId).value;

        // Validate
        if (!title.trim()) {
            alert('Epic title is required');
            return;
        }

        console.log('Updating epic:', { epicId, title, description });

        // Gửi PATCH request đến server
        fetch('/epics/' + epicId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                title: title,
                description: description
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Epic updated successfully:', data);
            alert('Epic updated successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error updating epic:', error);
            alert('Failed to update epic. Please try again.');
        });
    }

    // Xóa Epic
    function deleteEpic(epicId) {
        if (!confirm('Are you sure you want to delete this Epic? This action cannot be undone.')) {
            return;
        }

        console.log('Deleting epic:', epicId);

        fetch('/epics/' + epicId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Epic deleted successfully:', data);
            alert('Epic deleted successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting epic:', error);
            alert('Failed to delete epic. Please try again.');
        });
    }

    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       VIEW AND EDIT USER STORIES FUNCTIONS
    //*
    //******************************************************************************** *
    //==================================================================================


    // Toggle sang Edit Mode
    function toggleEditStoryMode(storyId) {
        // Ẩn View Mode, hiện Edit Mode
        document.getElementById('story-view-' + storyId).classList.add('hidden');
        document.getElementById('story-edit-' + storyId).classList.remove('hidden');

        // Đổi buttons: Ẩn Edit + Delete, Hiện Cancel + Save
        document.getElementById('story-btn-edit-' + storyId).classList.add('hidden');
        document.getElementById('story-btn-delete-' + storyId).classList.add('hidden');
        document.getElementById('story-btn-cancel-' + storyId).classList.remove('hidden');
        document.getElementById('story-btn-save-' + storyId).classList.remove('hidden');
    }

    // Hủy Edit Mode, quay về View Mode
    function cancelEditStory(storyId) {
        // Hiện View Mode, ẩn Edit Mode
        document.getElementById('story-view-' + storyId).classList.remove('hidden');
        document.getElementById('story-edit-' + storyId).classList.add('hidden');

        // Đổi buttons: Hiện Edit + Delete, Ẩn Cancel + Save
        document.getElementById('story-btn-edit-' + storyId).classList.remove('hidden');
        document.getElementById('story-btn-delete-' + storyId).classList.remove('hidden');
        document.getElementById('story-btn-cancel-' + storyId).classList.add('hidden');
        document.getElementById('story-btn-save-' + storyId).classList.add('hidden');
    }

    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       SAVE AND DELETE EPIC FUNCTIONS
    //*
    //******************************************************************************** *
    //==================================================================================


    // Lưu thay đổi User Story
    function saveEditStory(storyId) {
        // Lấy dữ liệu từ input
        const storyData = {
            title: document.getElementById('story-title-edit-' + storyId).value,
            description: document.getElementById('story-desc-edit-' + storyId).value,
            status: document.getElementById('story-status-edit-' + storyId).value,
            storyPoints: document.getElementById('story-points-edit-' + storyId).value || null,
            priority: document.getElementById('story-priority-edit-' + storyId).value,
            assigned_to: document.getElementById('story-assignee-edit-' + storyId).value || null
        };

        // Validate
        if (!storyData.title.trim()) {
            alert('User Story title is required');
            return;
        }

        console.log('Updating story:', { storyId, ...storyData });

        // Gửi PATCH request đến server
        fetch('/user-stories/' + storyId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(storyData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('User Story updated successfully:', data);
            alert('User Story updated successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error updating story:', error);
            alert('Failed to update user story. Please try again.');
        });
    }

    // Xóa User Story
    function deleteStory(storyId) {
        if (!confirm('Are you sure you want to delete this User Story? This action cannot be undone.')) {
            return;
        }

        console.log('Deleting story:', storyId);

        fetch('/user-stories/' + storyId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                // Nếu response không OK (403, 500, etc.), đọc error message
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Failed to delete user story');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('User Story deleted successfully:', data);
            alert(data.message || 'User Story deleted successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting story:', error);
            alert('❌ Error: ' + error.message);
        });
    }

    // --- 3. EXPAND/COLLAPSE FUNCTIONS ---
    // Toggle hiển thị User Stories trong Epic
    function toggleExpand(epicId) {
        const storiesDiv = document.getElementById('stories-' + epicId);
        const expandBtn = document.getElementById('expand-btn-' + epicId);

        // Toggle class 'hidden' để hiện/ẩn
        storiesDiv.classList.toggle('hidden');

        // Xoay icon mũi tên
        expandBtn.classList.toggle('rotate-90');
    }

    // Toggle hiển thị User Stories trong Future Sprint
    function toggleFutureSprint(sprintId) {
        const storiesDiv = document.getElementById('sprint-stories-' + sprintId);
        const expandBtn = document.getElementById('expand-btn-sprint-' + sprintId);

        if (!storiesDiv || !expandBtn) return;

        storiesDiv.classList.toggle('hidden');
        // Rotate the chevron icon
        const svg = expandBtn.querySelector('svg');
        if (svg) svg.classList.toggle('rotate-90');
    }

    // =================================================================================
    //******************************************************************************** *
    //*
    //*                  FUTURE SPRINT MODAL FUNCTIONS
    //*
    //******************************************************************************** *
    //==================================================================================
    //Mở modal tạo Future Sprint
    function openCreateFutureSprintModal(){
        document.getElementById('create-future-sprint-modal').classList.remove('hidden');
    }
    //Đóng modal tạo Future Sprint
    function closeFutureSprintModal(){
        document.getElementById('create-future-sprint-modal').classList.add('hidden');
        document.getElementById('create-future-sprint-form').reset(); // Reset form về trống
    }
    document.addEventListener('DOMContentLoaded', function(){
        const form = document.getElementById('create-future-sprint-form');
        const startDateInput = document.getElementById('sprint-start-date');
        const endDateInput = document.getElementById('sprint-end-date');

        // ✅ Khi start date thay đổi, update min của end date
        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', function() {
                if (this.value) {
                    endDateInput.min = this.value;
                }
            });
        }

        if(form){
            form.addEventListener('submit', async function(e){
                e.preventDefault(); //ngăn chặn hành vi submit mặc định

                //Lấy dữ liệu từ form
                const formData = new FormData(this);
                const startDate = formData.get('start_date');
                const endDate = formData.get('end_date');

                // ✅ Validation phía client
                if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                    alert('End date must be after or equal to start date!');
                    return;
                }

                const data = {
                    name: formData.get('name'),
                    goal: formData.get('goal'),
                    start_date: startDate || null,
                    end_date: endDate || null,
                };

                try {
                    const response = await fetch('/future-sprints', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if(response.ok){
                        alert('Future Sprint created successfully!');
                        closeFutureSprintModal();
                        location.reload(); // Reload để hiển thị sprint mới
                    } else {
                        // ✅ Hiển thị lỗi validation từ server
                        if (result.errors) {
                            const errorMessages = Object.values(result.errors).flat().join('\n');
                            alert('Validation errors:\n' + errorMessages);
                        } else {
                            alert(result.message || 'Có lỗi xảy ra!');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tạo Future Sprint!');
                }
            })
        }
    })

    // Open Edit Future Sprint Modal
    async function openEditFutureSprintModal(sprintId) {
        try {
            // Fetch sprint data
            const response = await fetch(`/future-sprints/${sprintId}`);
            const sprint = await response.json();

            if (response.ok) {
                // Populate form
                document.getElementById('edit-sprint-id').value = sprint.id;
                document.getElementById('edit-sprint-name').value = sprint.name;
                document.getElementById('edit-sprint-goal').value = sprint.goal || '';
                document.getElementById('edit-sprint-start-date').value = sprint.start_date || '';
                document.getElementById('edit-sprint-end-date').value = sprint.end_date || '';

                // Show modal
                document.getElementById('edit-future-sprint-modal').classList.remove('hidden');
            } else {
                alert('Failed to load sprint data');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading sprint data');
        }
    }

    // ✅ Close Edit Future Sprint Modal
    function closeEditFutureSprintModal() {
        document.getElementById('edit-future-sprint-modal').classList.add('hidden');
        document.getElementById('edit-future-sprint-form').reset();
    }

    // ✅ Handle Edit Form Submit
    document.addEventListener('DOMContentLoaded', function() {
        const editForm = document.getElementById('edit-future-sprint-form');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const sprintId = document.getElementById('edit-sprint-id').value;
                const formData = new FormData(this);
                const data = {
                    name: formData.get('name'),
                    goal: formData.get('goal'),
                    start_date: formData.get('start_date') || null,
                    end_date: formData.get('end_date') || null,
                };

                try {
                    const response = await fetch(`/future-sprints/${sprintId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        alert('Future Sprint updated successfully!');
                        closeEditFutureSprintModal();
                        location.reload();
                    } else {
                        if (result.errors) {
                            const errorMessages = Object.values(result.errors).flat().join('\n');
                            alert('Validation errors:\n' + errorMessages);
                        } else {
                            alert(result.message || 'Error updating sprint');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error updating Future Sprint');
                }
            });
        }
    });

    // ✅ Delete Future Sprint
    async function deleteFutureSprint(sprintId) {
        if (!confirm('Are you sure you want to delete this Future Sprint? All stories will be moved back to Product Backlog.')) {
            return;
        }

        try {
            const response = await fetch(`/future-sprints/${sprintId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (response.ok) {
                alert('Future Sprint deleted successfully!');
                location.reload();
            } else {
                alert(result.message || 'Error deleting sprint');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting Future Sprint');
        }
    }

    // Toggle form gán sprint
    function toggleAssignSprintForm(storyId) {
  const el = document.getElementById('assign-sprint-form-' + storyId);
  if (!el) return;
  el.classList.toggle('hidden');
}

    // Gọi API để assign story vào Future Sprint
    async function assignStoryToFutureSprint(storyId) {
        const select = document.getElementById('assign-sprint-select-' + storyId);
        if (!select || !select.value) {
            alert('Please select a Future Sprint');
            return;
        }
        const sprintId = select.value;

        try {
            const res = await fetch(`/user-stories/${storyId}/assign-future-sprint`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ sprint_id: sprintId })
            });
            const data = await res.json();
            if (res.ok) {
            alert('Assigned to Future Sprint successfully!');
            location.reload(); // đơn giản: reload hiển thị ngay
            } else {
            alert(data.message || 'Failed to assign story.');
            }
        } catch (e) {
            console.error(e);
            alert('Error assigning story to sprint.');
        }
    }

// =================================================================================
//*                    DRAG & DROP REORDER USER STORIES
// =================================================================================
let draggedStoryElement = null;
let draggedStoryId = null;
let draggedFromScope = null; // "epic" hoặc "sprint"
let draggedFromScopeId = null; // epic_id hoặc sprint_id

// Bắt đầu kéo story
function dragStory(event) {
    //closet là để tìm ra phạm vi gần nhất của phần tử được kéo
    draggedStoryElement = event.target.closest('.story-item');
    draggedStoryId = draggedStoryElement.dataset.storyId;

    // Xác định scope dựa vào vị trí (drop-zone cha)
    const dropZone = draggedStoryElement.closest('.story-drop-zone');
    if (dropZone) {
        draggedFromScope = dropZone.dataset.scope;
        draggedFromScopeId = dropZone.dataset.scopeId;
    }

    // Hiệu ứng visual
    draggedStoryElement.style.opacity = '0.4';
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', draggedStoryId);
}

//cho phép thả (khi kéo qua vùng drop)
function allowDropStory(event){
    event.preventDefault();
    const dropZone = event.currentTarget;
    //kiểm tra chỉ cho reorder trong cùng scope
    const targetScope = dropZone.dataset.scope;
    const targetScopeId = dropZone.dataset.scopeId;

    if(draggedFromScope === targetScope && draggedFromScopeId === targetScopeId){
        //cho phép và thêm hiệu ứng
        dropZone.classList.add('bg-blue-50', 'border-2', 'border-dashed', 'border-blue-400');
        event.dataTransfer.dropEffect = 'move';
    }else{
        //không cho phép kéo Epic/Sprint khác, hiển thị icon cấm
        event.dataTransfer.dropEffect = 'none';
    }
}
// Bỏ hiệu ứng khi kéo ra ngoài
function dragLeaveStory(ev) {
    const dropZone = ev.currentTarget;
    dropZone.classList.remove('bg-blue-50', 'border-2', 'border-dashed', 'border-blue-400');
}
// Xử lý khi thả
async function dropStory(ev) {
    ev.preventDefault();
    const dropZone = ev.currentTarget;
    dragLeaveStory(ev);

    const targetScope = dropZone.dataset.scope;
    const targetScopeId = dropZone.dataset.scopeId;

    // LƯU ELEMENT NGAY ĐẦU để dùng sau
    const movedElement = draggedStoryElement;

    // Kiểm tra scope hợp lệ
    if (draggedFromScope !== targetScope || draggedFromScopeId !== targetScopeId) {
        alert('Cannot move stories between different Epics or Sprints. Use the + button instead.');
        resetDragState();
        return;
    }
    // Tính vị trí thả (insert before target item)
    const afterElement = getDragAfterElement(dropZone, ev.clientY);

    if (afterElement == null) {
        dropZone.appendChild(movedElement);
    } else {
        dropZone.insertBefore(movedElement, afterElement);
    }
    // Lấy danh sách IDs theo thứ tự mới
    const newOrder = Array.from(dropZone.querySelectorAll('.story-item'))
                          .map(item => item.dataset.storyId);
                          console.log('New order:', newOrder);
    // Gọi API cập nhật thứ tự
    try {
        const response = await fetch('/user-stories/reorder', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                scope: targetScope,
                scope_id: targetScopeId,
                ids: newOrder
            })
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Reorder failed');
        }

        console.log('Reorder successful:', result);

        // Reset drag state
        resetDragState();

        // Thêm hiệu ứng highlight cho story vừa di chuyển
        highlightReorderedStory(movedElement);

    } catch (error) {
        console.error('Error reordering:', error);
        alert('Failed to save new order: ' + error.message);
        location.reload(); // Rollback bằng cách reload
    }
}

// Hàm highlight story vừa được reorder
function highlightReorderedStory(element) {
    if (!element) {
        console.log('❌ No element to highlight');
        return;
    }

    console.log('✅ Highlighting element:', element);
    console.log('Element classes before:', element.className);

    // Thêm class highlight
    element.classList.add('story-reordered');

    console.log('Element classes after:', element.className);

    // Tự động xóa highlight sau 2 giây
    setTimeout(() => {
        element.classList.remove('story-reordered');
        console.log('🔄 Removed highlight');
    }, 2000);
}

// Hàm tính vị trí thả (dựa vào tọa độ Y chuột)
function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.story-item:not(.opacity-40)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Reset trạng thái sau khi thả
function resetDragState() {
    if (draggedStoryElement) {
        draggedStoryElement.style.opacity = '1';
    }
    draggedStoryElement = null;
    draggedStoryId = null;
    draggedFromScope = null;
    draggedFromScopeId = null;
}

// Reset khi kéo kết thúc (cả khi không thả)
document.addEventListener('dragend', function() {
    resetDragState();
});

// =================================================================================
//*                    COMMENTS MANAGEMENT FOR USER STORIES
// =================================================================================

// Load comments khi mở Story Panel
async function loadComments(storyId) {
    const commentsList = document.getElementById('comments-list-' + storyId);

    try {
        const response = await fetch(`/user-stories/${storyId}/comments`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (response.ok) {
            displayComments(commentsList, data.comments, storyId);
            // Cập nhật key hiện tại để polling so sánh về sau
            lastCommentKeys[storyId] = computeCommentsKey(data.comments || []);
        } else {
            commentsList.innerHTML = '<div class="text-red-500 text-sm">Failed to load comments.</div>';
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        commentsList.innerHTML = '<div class="text-red-500 text-sm">Error loading comments.</div>';
    }
}

// Hiển thị comments trong UI
function displayComments(container, comments, storyId) {
    if (!Array.isArray(comments) || comments.length === 0) {
        container.innerHTML = '<div class="text-gray-500 text-sm text-center py-4">No comments yet. Be the first to comment!</div>';
        return;
    }
    // Giữ scroll position nếu user đang đọc phía trên
    const previousScrollTop = container.scrollTop;
    const isAtBottom = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) < 10;
    //xóa các comment cũ đã hiển thị từ 3s trước, để thêm comment mới vào mảng
    container.innerHTML = '';
    comments.forEach(comment => {
        const commentDiv = createCommentElement(comment, storyId, 0, comment.id);
        container.appendChild(commentDiv);
    });

    // Nếu người dùng đang ở cuối danh sách, tự động kéo xuống để thấy reply mới
    if (isAtBottom) {//nếu người dùng đang ở cuối mà có cmt mới thì đặt vị trí = mới
        container.scrollTop = container.scrollHeight;
    } else {
        // Giữ nguyên vị trí đọc cũ
        container.scrollTop = previousScrollTop;
    }
}

// Tạo HTML element cho 1 comment
function createCommentElement(comment, storyId, level = 0, rootId = null, mentionName = null) {
    ///Tạo div bọc bên ngoài
    const div = document.createElement('div');
    const baseIndent = level * 12; // tăng lùi theo level: 0,12,24,..
    div.style.marginLeft = baseIndent + 'px';
    div.className = 'border-l-2 border-indigo-200 pl-3 py-2';
    div.id = 'comment-' + comment.id;
    ///////////////////////////////////////
    // Format thời gian
    const commentDate = new Date(comment.created_at);
    const timeAgo = getTimeAgo(commentDate);
    // Xử lý flatten replies: gom tất cả replies (mọi cấp) thành 1 cấp dưới parent
    const flatReplies = [];
    // hàm làm phẳng tất cả commment thành một level 1
    function collectFlatReplies(items, parentUserName) {
        if (!Array.isArray(items)) return;
        //vòng lặp để đào sâu vào bên trong các replies con, vòng lặp if sẽ đào sau replies con nữa
        items.forEach(child => {
            //gôm tất cả dữ liệu con vào mảng flatReplies có cả dữ liệu comment và tên người đó
            flatReplies.push({ node: child, replyTo: parentUserName });
            if (Array.isArray(child.replies) && child.replies.length > 0) {
                collectFlatReplies(child.replies, child.user?.name || '');
            }
        });
    }
    //dòng này kiểm tra làm phẳng comment nếu như có các điều kiện thõa ở dưới thì mới chạy được
    if (level === 0 && Array.isArray(comment.replies) && comment.replies.length > 0) {
        collectFlatReplies(comment.replies, comment.user?.name || '');
    }

    const hasReplies = level === 0 ? flatReplies.length > 0 : false;
    const replyCount = hasReplies ? flatReplies.length : 0;
    //biến isCollapsed để kiểm tra xem comment có bị collapse hay không
    const isCollapsed = collapsedCommentIds.has(comment.id);

    // Nếu là reply (level 1), hiển thị prefix @name nếu có yêu cầu mention
    const mentionPrefixHtml = mentionName ? `<span class="text-indigo-600 font-medium">@${mentionName}</span> ` : '';

    const replyBtnHtml = (level === 0)
        ? `<button onclick="toggleReplyForm(${(rootId ?? comment.id)}, ${storyId})" class="text-xs text-indigo-600 hover:text-indigo-800">Reply</button>`
        : `<button onclick=\"toggleReplyForm(${(rootId ?? comment.id)}, ${storyId}, '@${comment.user.name} ')\" class=\"text-xs text-indigo-600 hover:text-indigo-800\">Reply</button>`;
    //phần ruột bên trong comment
    div.innerHTML = `
        <div class="flex items-start justify-between mb-1">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-sm text-gray-800">${comment.user.name}</span>
                <span class="text-xs text-gray-500">${timeAgo}</span>
            </div>
            ${comment.user_id == {{ Auth::id() }} ? `
                <div class="flex gap-1">
                    <button onclick="editComment(${comment.id}, ${storyId})"
                            class="text-xs text-blue-600 hover:text-blue-800">Edit</button>
                    <button onclick="deleteComment(${comment.id}, ${storyId})"
                            class="text-xs text-red-600 hover:text-red-800">Delete</button>
                </div>
            ` : ''}
        </div>
        <div class="text-sm text-gray-700 whitespace-pre-wrap" id="comment-content-${comment.id}">${mentionPrefixHtml}${comment.content}</div>
        <div class="mt-2 flex items-center gap-3">
            ${replyBtnHtml}
            ${hasReplies ? `<button id="toggle-replies-btn-${comment.id}" onclick="toggleReplies(${comment.id})" class="text-xs text-gray-600 hover:text-gray-800">${isCollapsed ? `Show replies (${replyCount})` : `Hide replies (${replyCount})`}</button>` : ''}
        </div>
        ${level === 0 ? `
        <div id="reply-form-${comment.id}-${storyId}" class="mt-2 hidden">
            <form onsubmit="addReply(event, ${storyId}, ${comment.id})">
                <textarea id="reply-input-${comment.id}-${storyId}" rows="2" placeholder="Write a reply..."
                    class="w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" onclick="toggleReplyForm(${comment.id}, ${storyId})" class="px-2 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-3 py-1 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700">Reply</button>
                </div>
            </form>
        </div>
        ` : ''}
    `;
    // Wrapper cho replies để có thể collapse
    if (hasReplies && level === 0) {
        const repliesWrapper = document.createElement('div');
        repliesWrapper.id = `replies-wrapper-${comment.id}`;
        repliesWrapper.className = 'mt-2 space-y-2';
        if (isCollapsed) {
            repliesWrapper.style.display = 'none';
        }
        flatReplies.forEach(item => {
            const replyEl = createCommentElement(item.node, storyId, 1, (rootId ?? comment.id), item.replyTo);
            replyEl.classList.add('comment-item');
            repliesWrapper.appendChild(replyEl);
        });
        div.appendChild(repliesWrapper);
    }
    return div;
}
// hàm toggle xử lí nút show/hide replies với commentId của nút đó
function toggleReplies(commentId) {
    //lấy wrapper và button show hide
    const wrapper = document.getElementById(`replies-wrapper-${commentId}`);
    const btn = document.getElementById(`toggle-replies-btn-${commentId}`);
    if (!wrapper || !btn) return;
    const count = wrapper.children.length;//đếm số lượng replies bên trong (show(5) / hide(5))

    //nếu nút chứa replies đang bị ẩn
    if (wrapper.style.display === 'none') {
        //xóa thuộc tính none để hiện ra
        wrapper.style.display = '';
        collapsedCommentIds.delete(commentId);
        btn.textContent = `Hide replies (${count})`;
    } else {
        wrapper.style.display = 'none';
        collapsedCommentIds.add(commentId);
        btn.textContent = `Show replies (${count})`;
    }
}

// Toggle hiển thị form reply được gọi khi người dùng nhấp vào nút reply, mentionprefix('@An ')
function toggleReplyForm(commentId, storyId, mentionPrefix = '') {
    const el = document.getElementById(`reply-form-${commentId}-${storyId}`);
    if (!el) return;
    // Nếu có mentionPrefix, luôn mở form; nếu không toggle như cũ
    if (mentionPrefix) {
        el.classList.remove('hidden');
    } else {
        el.classList.toggle('hidden');
    }
    //biến textarea để tìm ô gõ reply
    const textarea = document.getElementById(`reply-input-${commentId}-${storyId}`);
    if (textarea && mentionPrefix) {
        if (!textarea.value.startsWith(mentionPrefix)) {//nếu ko bắt đầu bằng chuỗ @ten + text thì ta gán
            textarea.value = mentionPrefix + textarea.value;//gán @tên + text cần gõ
        }
        textarea.focus();//hàm đặt con tro
        try {//try catch để đảm bảo con trỏ di chuyển đúng vị trí cuối cùng
            const len = textarea.value.length;
            textarea.setSelectionRange(len, len);
        } catch (_) {}
    }
}

// Gửi reply cho một comment
async function addReply(event, storyId, parentId) {
    event.preventDefault();
    const textarea = document.getElementById(`reply-input-${parentId}-${storyId}`);
    if (!textarea) return;
    const content = textarea.value.trim();
    if (!content) return;

    try {
        const response = await fetch(`/user-stories/${storyId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ content, parent_id: parentId })
        });
        const data = await response.json();
        if (response.ok) {
            textarea.value = '';
            toggleReplyForm(parentId, storyId);
            // Refresh comments so both top-level and replies are up-to-date
            await loadComments(storyId);
        } else {
            alert(data.message || 'Failed to post reply.');
        }
    } catch (e) {
        console.error('Error posting reply:', e);
        alert('Error posting reply.');
    }
}

// Tính thời gian "time ago"
function getTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);

    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + 'm ago';
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + 'h ago';
    const days = Math.floor(hours / 24);
    if (days < 7) return days + 'd ago';

    return date.toLocaleDateString();
}

// Post comment mới
async function addComment(event, storyId) {
    event.preventDefault();

    const textarea = document.getElementById('comment-input-' + storyId);
    const content = textarea.value.trim();

    if (!content) return;

    try {
        const response = await fetch(`/user-stories/${storyId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ content: content })
        });

        const data = await response.json();

        if (response.ok) {
            // Clear textarea
            textarea.value = '';

            // Reload comments
            loadComments(storyId);
        } else {
            alert(data.message || 'Failed to post comment.');
        }
    } catch (error) {
        console.error('Error posting comment:', error);
        alert('Error posting comment.');
    }
}

// Edit comment
async function editComment(commentId, storyId) {
    const contentDiv = document.getElementById('comment-content-' + commentId);
    const currentContent = contentDiv.textContent;

    const newContent = prompt('Edit your comment:', currentContent);

    if (!newContent || newContent === currentContent) return;

    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ content: newContent })
        });

        const data = await response.json();

        if (response.ok) {
            contentDiv.textContent = newContent;
        } else {
            alert(data.message || 'Failed to edit comment.');
        }
    } catch (error) {
        console.error('Error editing comment:', error);
        alert('Error editing comment.');
    }
}

// Delete comment
async function deleteComment(commentId, storyId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;

    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        const data = await response.json();

        if (response.ok) {
            // Remove comment from DOM
            const commentDiv = document.getElementById('comment-' + commentId);
            if (commentDiv) commentDiv.remove();

            // Nếu không còn comment nào, hiển thị empty state
            const commentsList = document.getElementById('comments-list-' + storyId);
            if (commentsList.children.length === 0) {
                commentsList.innerHTML = '<div class="text-gray-500 text-sm text-center py-4">No comments yet. Be the first to comment!</div>';
            }
        } else {
            alert(data.message || 'Failed to delete comment.');
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
        alert('Error deleting comment.');
    // =================================================================================
    // Listen for AI-saved US event to reload backlog without full page refresh
    // =================================================================================
    window.addEventListener('us-saved', () => {
        console.log('🔄 US saved via AI, reloading page...');
        location.reload();
    });

    }
}

</script>

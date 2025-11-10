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

    // Mở Story Panel
    function openStoryPanel(storyId) {
        closeAllPanels(); // Đóng tất cả panel khác trước
        const panel = document.getElementById('story-panel-' + storyId);
        panel.classList.remove('hidden');
        // panel.classList.add('flex'); // Không cần vì <aside> bên trong đã có flex sẵn
    }

    // Đóng Story Panel
    function closeStoryPanel(storyId) {
        const panel = document.getElementById('story-panel-' + storyId);
        panel.classList.add('hidden');
        // panel.classList.remove('flex'); // Không cần vì chỉ toggle hidden là đủ
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
        .then(response => response.json())
        .then(data => {
            console.log('Epic created successfully:', data);
            closeCreateModal(); // Đóng modal
            alert('Epic created successfully!');
            window.location.reload(); // Reload trang để hiển thị epic mới
        })
        .catch(error => {
            console.error('Error creating epic:', error);
            alert('Failed to create epic. Please try again.');
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

        // Reset form về trống
        document.getElementById('story-title').value = '';
        document.getElementById('story-description').value = '';
        document.getElementById('story-status').value = 'toDo';
        document.getElementById('story-points').value = '';
        document.getElementById('story-priority').value = 'medium';
        document.getElementById('story-assignee').value = '';

    // Hiển thị tên Epic (read-only)
    document.getElementById('story-epic-id').value = epicId;
    const displayTitle = epicTitle != null ? epicTitle : (epicTitles[epicId] || ('Epic #' + epicId));
    document.getElementById('story-epic-display').value = displayTitle;
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

        // Lấy dữ liệu từ form
        const storyData = {
            title: document.getElementById('story-title').value,
            description: document.getElementById('story-description').value,
            status: document.getElementById('story-status').value,
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
        .then(response => response.json())
        .then(data => {
            console.log('User Story deleted successfully:', data);
            alert('User Story deleted successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting story:', error);
            alert('Failed to delete user story. Please try again.');
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

        if(form){
            form.addEventListener('submit', async function(e){
                e.preventDefault(); //ngăn chặn hành vi submit mặc định
                //Lấy dữ liệu từ form
                const formData = new FormData(this);
                const data = {
                    name: formData.get('name'),
                    goal: formData.get('goal'),
                    start_date: formData.get('start_date') || null,
                    end_date: formData.get('end_date') || null,
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
                        alert(result.message || 'Có lỗi xảy ra!');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tạo Future Sprint!');
                }
            })
        }
    })
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
        //không cho phép kéo Epic/Sprint khác
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

</script>

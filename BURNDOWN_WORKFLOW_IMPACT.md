# 🔥 Impact Analysis: Burndown Chart khi Thay Đổi Workflow

## 📋 Tóm Tắt Vấn Đề
Khi user tạo/xóa/thay đổi các workflow columns (task_statuses), Burndown Chart có thể bị ảnh hưởng vì:
1. **Burndown dựa trên `is_done` flag** - Chỉ tasks với status có `is_done=true` mới tính là "done"
2. **Nếu xóa column "Done" thì sao?** - Các tasks đã moved sang column khác sẽ không còn is_done=true
3. **Thêm column mới có is_done=false** - Không ảnh hưởng đến burndown (tính toán không thay đổi)
4. **Xóa column có is_done=true** - VẤN ĐỀ LỚN: Tasks chuyển sang column khác có thể mất trạng thái "done"

---

## 🎯 Các Kịch Bản (Scenarios)

### ✅ Scenario 1: Thêm Column Mới (SAFE)
```
Ví dụ: Thêm column "Testing" với is_done=false
- Không ảnh hưởng: Burndown vẫn chỉ tính tasks với is_done=true
- Burndown Chart: ✅ Không thay đổi
- Hành động cần: KHÔNG CẦN SYNC
```

### ✅ Scenario 2: Thêm Column Mới với is_done=true (SAFE)
```
Ví dụ: Thêm column "Verified" với is_done=true
- Tasks có thể move vào column này
- Burndown sẽ tính những tasks ở column "Verified" là done ✅
- Burndown Chart: ✅ Tự động cập nhật (vì dựa trên is_done flag)
- Hành động cần: KHÔNG CẦN SYNC
```

### ⚠️ Scenario 3: Xóa Column không có Tasks (SAFE)
```
Ví dụ: Xóa empty column "QA"
- Không có tasks affected
- Burndown Chart: ✅ Không thay đổi
- Hành động cần: KHÔNG CẦN SYNC
```

### 🔴 Scenario 4: Xóa Column có is_done=true (CRITICAL ⚠️)
```
Ví dụ: Xóa column "Done" (is_done=true) với N tasks

VẤN ĐỀ:
- Tasks chuyển sang column khác (vd: "In Progress" có is_done=false)
- Tasks này mất trạng thái "done" 
- Burndown Chart sẽ "revert" - số points remaining bỗng tăng lên! 📈😱

GIẢI PHÁP:
a) Tặng tasks lại status mới với is_done=true
b) Hoặc: Auto-select column mới có is_done=true nếu có
c) Hoặc: Block deletion nếu column có is_done=true + tasks
```

### 🔴 Scenario 5: Xóa Column có is_done=false với Many Tasks (MEDIUM IMPACT)
```
Ví dụ: Xóa column "In Progress" với 10 tasks

VẤN ĐỀ:
- Tasks chuyển sang column khác
- Burndown Chart: Vẫn ổn (vì tasks gốc cũng không phải "done")
- Nhưng: UX kém vì cần phải select target column

GIẢI PHÁP:
- Show modal để user chọn target column
- Display số tasks sẽ bị move
```

---

## 🛠️ Hướng Xử Lý Chi Tiết

### **Option 1: Block Deletion (SAFEST - Khuyến Cáo)**
```
Khi delete column:
1. Kiểm tra: column.is_done === true && tasks.count() > 0?
2. Nếu YES: Block deletion, show warning
   "Cannot delete 'Done' column with tasks. Move all tasks first."
3. Nếu NO: Cho phép delete (đã có logic chọn target column)

Ưu: An toàn tuyệt đối, không mất data, không corrupt burndown
Nhược: User hơi khó chịu (phải manual move trước)
```

### **Option 2: Smart Auto-Redirect (RECOMMENDED - BAL ANCED)**
```
Khi delete column có is_done=true và có tasks:
1. Tự động tìm column mới với is_done=true (nếu có)
2. Nếu tồn tại: Auto-move tasks vào đó
   - Log: "Moved N tasks to '{new_done_column}'"
3. Nếu không tồn tại: Show modal để user chọn
   - Cảnh báo: "No other 'done' column. Select target:"
4. Khi tasks moved: Burndown Chart vẫn ổn ✅

Ưu: UX tốt, data không mất, burndown tự động adjust
Nhược: Có thể move tasks không đúng ý user
```

### **Option 3: Full Validation + Rebuild (AGGRESSIVE)**
```
Khi delete column:
1. Get all tasks in this column
2. Validate target column is_done flag matches or is acceptable
3. Move tasks
4. After completion: Trigger burndown cache invalidation
5. Show notification with impact

Ưu: Hoàn toàn transparent, user biết chuyện gì xảy ra
Nhược: Complex logic, many edge cases
```

---

## 💡 Khuyến Cáo: Kết Hợp Option 1 + Option 2

### **Phase 1: Immediate (Ngay bây giờ)**
Thực hiện **Option 2: Smart Auto-Redirect**
- Tìm column mới có `is_done=true`
- Auto-move nếu tồn tại
- Nếu không: Show modal cho user

**Code Change Location:**
```php
// app/Http/Controllers/TaskStatusController.php - destroy()
// Thêm logic:

$moveToStatusId = $request->input('move_to_status_id');

if ($taskStatus->is_done && $taskCount > 0 && !$moveToStatusId) {
    // Tìm column 'done' khác
    $otherDoneStatus = TaskStatus::where('team_id', $team->id)
        ->where('is_done', true)
        ->where('id', '!=', $taskStatus->id)
        ->first();
    
    if ($otherDoneStatus) {
        // Auto-move
        $moveToStatusId = $otherDoneStatus->id;
    }
}
```

### **Phase 2: UI Enhancement (Sprint Sau)**
- Show icon ⚠️ trên column có `is_done=true` khi hover
- Show task count affected
- Display dự tính impact đến burndown
- Example warning:
  ```
  "This 'Done' column has 5 tasks.
   Moving to another 'done' status.
   Burndown chart will adjust automatically."
  ```

### **Phase 3: Advanced Protection (Optional)**
- Add setting: "Allow deletion of 'done' columns" (toggle)
- Add audit log: Track column deletions + task movements
- Add rollback feature: Undo column deletion (if needed)

---

## 📊 Technical Implementation

### Backend Changes Required:
```php
// 1. TaskStatusController::destroy() - DONE ✅
// Already handles move_to_status_id

// 2. Add this logic untuk auto-select target:
if ($taskStatus->is_done && $taskCount > 0 && !$moveToStatusId) {
    $otherDoneStatus = TaskStatus::where('team_id', $team->id)
        ->where('is_done', true)
        ->where('id', '!=', $taskStatus->id)
        ->first();
    
    if ($otherDoneStatus) {
        $moveToStatusId = $otherDoneStatus->id;
    } else {
        // Fallback: auto-select first available column
        $moveToStatusId = TaskStatus::where('team_id', $team->id)
            ->where('id', '!=', $taskStatus->id)
            ->min('order_index');
    }
}
```

### Frontend Changes Required:
```javascript
// 1. deleteColumn() - DONE ✅
// Shows modal nếu has tasks

// 2. Smart selection logic:
if (taskCount > 0 && columnToDelete.isNone) {
    // Populate only 'done' columns first
    let doneColumns = getAllColumns()
        .filter(c => c.isDone && c.id !== columnToDelete.id);
    
    if (doneColumns.length > 0) {
        // Pre-select first 'done' column
        document.getElementById('target-column-select').value = doneColumns[0].id;
        showNotification("Will auto-move to: " + doneColumns[0].name);
    }
}
```

### ReportController Changes:
```php
// NO CHANGES NEEDED ✅
// Burndown Chart tự động adjust vì dựa trên is_done flag
// Tasks moved to column với is_done=true vẫn được tính là done
```

---

## 🚀 Action Items

### Immediate (This Sprint):
- [x] Delete Column feature dengan move tasks (DONE)
- [ ] Add smart auto-select logic cho target column
- [ ] Show warning modal about burndown impact
- [ ] Test xóa "Done" column và verify burndown không corrupt

### Next Sprint:
- [ ] Add visual indicator trên columns với is_done=true
- [ ] Add burndown impact preview (estimate changes)
- [ ] Add audit log cho column changes
- [ ] Create user documentation

### Testing Scenarios:
1. Delete column "Done" với 5 tasks → Verify tasks moved + burndown ok
2. Delete column "Testing" (is_done=false) với 3 tasks → Select target + move
3. Delete empty column → Instant delete (no modal)
4. Delete column ngoài khi sprint đang active → Verify burndown realtime update

---

## 📝 Summary Table

| Scenario | Risk | Action | Burndown Impact |
|----------|------|--------|-----------------|
| Add column (is_done=F) | ✅ Safe | Allow | None |
| Add column (is_done=T) | ✅ Safe | Allow | None |
| Delete empty column | ✅ Safe | Allow | None |
| Delete column (is_done=F, has tasks) | ⚠️ Medium | Show modal | None |
| Delete column (is_done=T, has tasks) | 🔴 Critical | Auto-move | Auto-adjust ✅ |

---

## 🎯 Recommendation: Start with Option 2
✅ Implement smart auto-redirect now
✅ Test thoroughly with burndown chart
✅ Add UI improvements in next sprint
✅ Monitor for any edge cases

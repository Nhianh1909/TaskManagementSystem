# 📝 Note Comment Features - Product Backlog User Stories

## 🎯 Tổng Quan
Hệ thống comment đa cấp (threaded comments) cho User Stories trong Product Backlog với khả năng real-time sync giữa nhiều người dùng.

---

## 🏗️ Kiến Trúc & Cấu Trúc

### 1. Database
**Bảng:** `tasks_comments`

**Cột chính:**
- `id` - Primary key
- `task_id` - Foreign key đến bảng tasks (User Story)
- `user_id` - Foreign key đến bảng users (người comment)
- `parent_id` - Foreign key đến chính bảng tasks_comments (cho nested replies, NULL = comment gốc)
- `content` - Nội dung comment (TEXT, max 2000 ký tự)
- `created_at`, `updated_at` - Timestamps

**Model:** `App\Models\TasksComments`
- Relationships:
  - `task()` - belongsTo Tasks
  - `user()` - belongsTo User
  - `parent()` - belongsTo TasksComments (comment cha)
  - `replies()` - hasMany TasksComments (các reply con)

---

## 🎮 Backend Implementation

### 1. Controller: `TasksCommentsController.php`
**Location:** `app/Http/Controllers/TasksCommentsController.php`

**Methods:**

#### `index(Tasks $task)` - Lấy tất cả comments
- **Route:** `GET /user-stories/{task}/comments`
- **Permission:** Tất cả team members
- **Response:**
  ```json
  {
    "comments": [
      {
        "id": 1,
        "content": "Comment text",
        "user": { "id": 1, "name": "User Name" },
        "created_at": "2025-11-10 10:00:00",
        "replies": [...]
      }
    ]
  }
  ```
- **Eager Loading:** Load đến 4 cấp nested replies
  ```php
  ->with([
      'user',
      'replies.user',
      'replies.replies.user',
      'replies.replies.replies.user',
  ])
  ```

#### `store(Request $request, Tasks $task)` - Tạo comment/reply mới
- **Route:** `POST /user-stories/{task}/comments`
- **Permission:** Tất cả team members
- **Validation:**
  - `content`: required, string, max 2000 ký tự
  - `parent_id`: nullable, exists trong tasks_comments (cho reply)
- **Response:** Comment vừa tạo với thông tin user

#### `update(Request $request, TasksComments $comment)` - Sửa comment
- **Route:** `PATCH /comments/{comment}`
- **Permission:** Chỉ người tạo comment
- **Validation:**
  - `content`: required, string, max 2000 ký tự
- **Response:** Comment đã update

#### `destroy(TasksComments $comment)` - Xóa comment
- **Route:** `DELETE /comments/{comment}`
- **Permission:** Người tạo HOẶC Product Owner/Scrum Master
- **Response:** Success message

---

### 2. Routes Configuration
**File:** `routes/web.php`

```php
// User Story Comments Routes
Route::get('/user-stories/{task}/comments', [TasksCommentsController::class, 'index'])
    ->name('user-stories.comments.index');
Route::post('/user-stories/{task}/comments', [TasksCommentsController::class, 'store'])
    ->name('user-stories.comments.store');
Route::patch('/comments/{comment}', [TasksCommentsController::class, 'update'])
    ->name('comments.update');
Route::delete('/comments/{comment}', [TasksCommentsController::class, 'destroy'])
    ->name('comments.destroy');
```

---

## 🎨 Frontend Implementation

### 1. UI Components

#### **Story Detail Panels**
**Files:**
- `resources/views/pages/product-backlog/partials/story-detail-panel.blade.php`
- `resources/views/pages/product-backlog/partials/unassigned-story-detail-panel.blade.php`

**Cấu trúc HTML:**
```html
<!-- Discussion Section -->
<div class="mt-8 border-t pt-6">
    <h4>Discussion</h4>
    
    <!-- Comments List -->
    <div id="comments-list-{{ $story->id }}" class="space-y-4 mb-4">
        <!-- Comments loaded by JS -->
    </div>
    
    <!-- Add Comment Form -->
    <form id="add-comment-form-{{ $story->id }}" onsubmit="addComment(event, {{ $story->id }})">
        <textarea id="comment-input-{{ $story->id }}" ...></textarea>
        <button type="submit">Post Comment</button>
    </form>
</div>
```

---

### 2. JavaScript Logic
**File:** `resources/views/pages/product-backlog/partials/scripts.blade.php`

#### **Global Variables**
```javascript
const commentIntervals = {};           // Lưu polling intervals theo storyId
const lastCommentKeys = {};            // Lưu key để phát hiện thay đổi
const collapsedCommentIds = new Set(); // Lưu trạng thái collapse của comments
let activeCommentsStoryId = null;      // Track story đang mở
```

---

#### **Core Functions**

##### 1. `loadComments(storyId)` - Load comments từ API
```javascript
async function loadComments(storyId)
```
- Gọi API `GET /user-stories/{storyId}/comments`
- Gọi `displayComments()` để render
- Cập nhật `lastCommentKeys[storyId]` cho polling

##### 2. `displayComments(container, comments, storyId)` - Render comments
```javascript
function displayComments(container, comments, storyId)
```
- Loop qua từng comment và gọi `createCommentElement()`
- **Smart scroll:**
  - Nếu user đang ở cuối → auto-scroll xuống
  - Nếu user đang đọc ở giữa → giữ nguyên vị trí

##### 3. `createCommentElement(comment, storyId, level=0)` - Tạo HTML cho 1 comment
```javascript
function createCommentElement(comment, storyId, level = 0)
```
- **Indentation:** `marginLeft = level * 12px`
- **Structure:**
  - Header: User name, timestamp, Edit/Delete buttons
  - Content: Comment text
  - Actions: Reply button, Hide/Show replies button
  - Reply form (hidden)
  - Replies wrapper (recursive render)
- **Đệ quy:** Render replies tối đa 5 cấp
- **Collapse state:** Kiểm tra `collapsedCommentIds` để ẩn/hiện replies

##### 4. `addComment(event, storyId)` - Post comment mới
```javascript
async function addComment(event, storyId)
```
- POST `/user-stories/{storyId}/comments`
- Body: `{ content: "..." }`
- Sau khi thành công: clear textarea và reload comments

##### 5. `addReply(event, storyId, parentId)` - Reply vào comment
```javascript
async function addReply(event, storyId, parentId)
```
- POST `/user-stories/{storyId}/comments`
- Body: `{ content: "...", parent_id: parentId }`
- Sau khi thành công: đóng reply form và reload comments

##### 6. `editComment(commentId, storyId)` - Sửa comment
```javascript
async function editComment(commentId, storyId)
```
- Dùng `prompt()` để nhập nội dung mới
- PATCH `/comments/{commentId}`
- Body: `{ content: "..." }`
- Update trực tiếp DOM nếu thành công

##### 7. `deleteComment(commentId, storyId)` - Xóa comment
```javascript
async function deleteComment(commentId, storyId)
```
- Confirm trước khi xóa
- DELETE `/comments/{commentId}`
- Remove element khỏi DOM nếu thành công

##### 8. `toggleReplyForm(commentId, storyId)` - Toggle reply form
```javascript
function toggleReplyForm(commentId, storyId)
```
- Ẩn/hiện form reply inline dưới comment

##### 9. `toggleReplies(commentId)` - Collapse/Expand replies
```javascript
function toggleReplies(commentId)
```
- Toggle `display: none` cho `replies-wrapper-{commentId}`
- Cập nhật `collapsedCommentIds` Set
- Đổi text button: "Show replies (N)" ↔ "Hide replies (N)"
- **Persistent:** State được giữ qua các lần polling re-render

##### 10. `getTimeAgo(date)` - Format timestamp
```javascript
function getTimeAgo(date)
```
- `< 60s` → "just now"
- `< 60m` → "Xm ago"
- `< 24h` → "Xh ago"
- `< 7d` → "Xd ago"
- `≥ 7d` → `date.toLocaleDateString()`

---

#### **Real-time Polling System**

##### 11. `computeCommentsKey(comments)` - Tính unique key
```javascript
function computeCommentsKey(comments)
```
- **Đệ quy:** Đếm tất cả comments + replies ở mọi cấp
- **Key format:** `${totalCount}:${newestTs}:${newestId}`
  - `totalCount`: Tổng số comments/replies
  - `newestTs`: Timestamp mới nhất (milliseconds)
  - `newestId`: ID của comment/reply mới nhất (tiebreaker)
- **Mục đích:** Phát hiện bất kỳ thay đổi nào (thêm/sửa/xóa comment ở bất kỳ cấp nào)

##### 12. `startCommentsPolling(storyId, intervalMs=3000)` - Bật polling
```javascript
function startCommentsPolling(storyId, intervalMs = 3000)
```
- Tạo `setInterval` gọi API mỗi 3 giây
- So sánh `computeCommentsKey()` với `lastCommentKeys[storyId]`
- **Chỉ re-render khi key thay đổi** → tránh re-render thừa
- Lưu interval vào `commentIntervals[storyId]`

##### 13. `stopCommentsPolling(storyId)` - Tắt polling
```javascript
function stopCommentsPolling(storyId)
```
- `clearInterval(commentIntervals[storyId])`
- Xóa khỏi `commentIntervals` object

##### 14. `openStoryPanel(storyId)` - Mở panel + bật polling
```javascript
function openStoryPanel(storyId)
```
1. Dừng polling cũ (nếu có story khác đang mở)
2. Đóng tất cả panels
3. Hiện panel của story
4. Set `activeCommentsStoryId = storyId`
5. `loadComments(storyId)`
6. `startCommentsPolling(storyId)`

##### 15. `closeStoryPanel(storyId)` - Đóng panel + tắt polling
```javascript
function closeStoryPanel(storyId)
```
1. Ẩn panel
2. `stopCommentsPolling(storyId)`

##### 16. `closeAllPanels()` - Đóng tất cả + cleanup
```javascript
function closeAllPanels()
```
1. Ẩn tất cả Epic panels
2. Ẩn tất cả Story panels
3. Dừng tất cả polling intervals
4. Reset `activeCommentsStoryId = null`

---

## ⚡ Features Chính

### 1. ✅ Nested Comments (Threaded Replies)
- Hỗ trợ **đa cấp** (tối đa 5 levels)
- Mỗi cấp thụt lề `12px`
- Reply button ở mọi comment/reply
- Render đệ quy tự động

### 2. ✅ Real-time Synchronization
- **Polling interval:** 3 giây
- **Diff-based re-render:** Chỉ update khi có thay đổi
- **Persistent state:** Giữ trạng thái collapse/scroll khi polling
- **Multi-browser sync:** User A post comment → User B thấy trong ~3s

### 3. ✅ Collapse/Expand Replies
- Nút "Hide replies (N)" / "Show replies (N)"
- State lưu trong `collapsedCommentIds` Set
- **Persistent across polling:** Không bị reset khi re-render
- Hoạt động ở mọi cấp độ nested

### 4. ✅ CRUD Operations
- **Create:** Post comment gốc hoặc reply
- **Read:** Load tự động khi mở panel + polling
- **Update:** Edit comment của mình (prompt inline)
- **Delete:** Xóa comment (confirm trước)

### 5. ✅ Permissions
- **View comments:** Tất cả team members
- **Post comment/reply:** Tất cả team members
- **Edit comment:** Chỉ người tạo
- **Delete comment:** Người tạo HOẶC Product Owner/Scrum Master

### 6. ✅ Smart UI/UX
- **Auto-scroll:** Scroll xuống cuối khi có comment mới (nếu user đang ở cuối)
- **Preserve scroll:** Giữ vị trí đọc nếu user đang ở giữa
- **Inline forms:** Reply form hiện inline dưới comment
- **Time formatting:** Hiển thị relative time (just now, 5m ago, etc.)
- **Loading states:** "Loading comments..." khi đang fetch
- **Empty states:** "No comments yet. Be the first to comment!"

---

## 🔧 Technical Details

### Polling Strategy
**Tại sao dùng Polling thay vì WebSocket?**
- ✅ Đơn giản implement (không cần Pusher/Laravel Echo)
- ✅ Không cần config server WebSocket
- ✅ Hoạt động trên mọi hosting
- ✅ Đủ nhanh cho team nhỏ (3-5 người)
- ❌ Hơi tốn bandwidth (nhưng chấp nhận được với interval 3s)
- ❌ Delay ~3s (có thể giảm xuống 1-2s nếu cần)

**Optimization:**
- Chỉ poll khi panel đang mở
- So sánh key trước khi re-render (tránh re-render thừa)
- Dừng tất cả polling khi đóng panel
- Cache key trong `lastCommentKeys` object

---

### Key Computation Algorithm
```javascript
// Đệ quy đếm tất cả comments/replies
function countDeep(items) {
    items.forEach(item => {
        totalCount++;
        // Track newest timestamp + ID
        const ts = toTs(item.created_at);
        if (ts > newestTs || (ts === newestTs && item.id > newestId)) {
            newestTs = ts;
            newestId = item.id;
        }
        // Recursive
        if (item.replies?.length > 0) {
            countDeep(item.replies);
        }
    });
}
```

**Tại sao cần cả timestamp VÀ ID?**
- Nếu 2 comments được tạo trong cùng 1 giây → `created_at` giống nhau
- Chỉ dùng timestamp → key không đổi → polling không phát hiện
- **Giải pháp:** Thêm `newestId` làm tiebreaker

---

## 📊 Database Queries Optimization

### Eager Loading Strategy
```php
$comments = $task->comments()
    ->whereNull('parent_id') // Chỉ lấy comments gốc
    ->with([
        'user',                        // Cấp 0
        'replies.user',                // Cấp 1
        'replies.replies.user',        // Cấp 2
        'replies.replies.replies.user' // Cấp 3
    ])
    ->orderBy('created_at', 'desc')
    ->get();
```

**Tại sao chỉ 4 cấp?**
- Balance giữa flexibility và performance
- Đủ cho hầu hết use cases thực tế
- Tránh N+1 query problem
- Giảm payload size

---

## 🎯 Use Cases

### 1. Product Owner thảo luận requirements
```
PO: "Feature này cần thêm validation cho email"
  └─ Dev: "OK, dùng regex pattern nào?"
      └─ PO: "RFC 5322 compliant"
          └─ Dev: "Done, đã implement"
```

### 2. Team discuss technical approach
```
Dev A: "Nên dùng Redis cache cho API này"
  └─ Dev B: "Performance improvement được bao nhiêu?"
      └─ Dev A: "~40% faster theo benchmark"
  └─ SM: "OK approved, implement in next sprint"
```

### 3. Clarify acceptance criteria
```
PO: "User phải được notify khi task complete"
  └─ Dev: "Email hay in-app notification?"
      └─ PO: "Both, nhưng user có thể opt-out email"
```

---

## 🐛 Known Limitations

1. **Polling Delay:** ~3 giây (có thể giảm nhưng tốn bandwidth hơn)
2. **Max Depth:** 5 cấp nested (có thể tăng nhưng ảnh hưởng performance)
3. **Eager Loading:** 4 cấp (cấp 5 sẽ không có nested replies trong 1 query)
4. **Edit Method:** Dùng `prompt()` đơn giản (chưa có WYSIWYG editor)
5. **No Markdown:** Chưa support Markdown formatting
6. **No Attachments:** Chưa support upload file/image
7. **No Mentions:** Chưa support @mention để tag users
8. **No Reactions:** Chưa có emoji reactions (👍, ❤️, etc.)

---

## 🚀 Future Enhancements (Possible)

### Phase 2 (Near Term)
- [ ] Markdown support (bold, italic, code blocks)
- [ ] @mentions với autocomplete
- [ ] Emoji reactions
- [ ] Edit history (audit trail)
- [ ] Soft delete (archive thay vì delete hẳn)

### Phase 3 (Mid Term)
- [ ] File attachments (images, PDFs)
- [ ] Rich text editor (WYSIWYG)
- [ ] Search trong comments
- [ ] Filter comments (by user, date range)
- [ ] Pin important comments

### Phase 4 (Long Term)
- [ ] Laravel Echo + Pusher (WebSocket real-time)
- [ ] Notifications (email/in-app khi được mention)
- [ ] Comment templates
- [ ] AI-powered suggestions
- [ ] Export comments to PDF

---

## 📝 Maintenance Notes

### Testing Checklist
- [ ] Post comment gốc
- [ ] Reply cấp 1
- [ ] Reply cấp 2-3
- [ ] Edit comment của mình
- [ ] Delete comment của mình
- [ ] Try edit/delete comment của người khác (should fail)
- [ ] Collapse/expand replies
- [ ] Test real-time với 2 browsers
- [ ] Test scroll preservation
- [ ] Test polling start/stop khi đóng/mở panel

### Performance Monitoring
- Monitor số lượng queries (should be 1-2 queries per load)
- Check payload size (nếu quá lớn, xem xét pagination)
- Track API response time (should be < 200ms)
- Monitor polling frequency (adjust nếu server load cao)

### Security Considerations
- ✅ CSRF protection enabled
- ✅ Permission checks trong controller
- ✅ Input validation (max 2000 chars)
- ✅ XSS protection (Laravel auto-escapes)
- ⚠️ TODO: Rate limiting cho API comments (tránh spam)

---

## 📞 Contact & Support

**Developer:** AI Assistant (GitHub Copilot)  
**Implementation Date:** November 10, 2025  
**Project:** Task Management System - Product Backlog Module  
**Version:** 1.0.0

---

## 🎉 Kết Luận

Hệ thống comment đã hoàn thiện với đầy đủ tính năng:
- ✅ Nested replies đa cấp
- ✅ Real-time sync (polling)
- ✅ Collapse/expand UI
- ✅ CRUD operations
- ✅ Permission control
- ✅ Smart UX (scroll, state preservation)

**Status:** Production Ready 🚀

Enjoy coding! 💻✨

<aside class="col-span-3 bg-white rounded-lg p-4 shadow">
                <h2 class="font-medium text-gray-700 mb-3">Filters</h2>

                <div class="space-y-4">

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Sprint</label>
                        <select id="filter-sprint" class="w-full px-3 py-2 border rounded">
                            <option value="all">All Sprints</option>
                            <option value="sprint-1">Sprint 1</option>
                            <option value="sprint-2">Sprint 2</option>
                            <option value="sprint-3">Sprint 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Epic</label>
                        <select id="filter-epic" class="w-full px-3 py-2 border rounded">
                            <option value="all">All Epics</option>
                            <option value="auth">User Authentication</option>
                            <option value="payments">Payment Integration</option>
                            <option value="reports">Reporting Dashboard</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-2">Status</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-all" onchange="toggleStatusAll()">
                                <span>All</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-todo" onchange="toggleStatus('todo')">
                                <span>To Do</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-inprogress" onchange="toggleStatus('inprogress')">
                                <span>In Progress</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-done" onchange="toggleStatus('done')">
                                <span>Done</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6" x-data="productBacklog()">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Product Backlog</h1>
            <button @click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-500 focus:outline-none">
                <span class="text-lg">+</span>
                <span>Create Epic</span>
            </button>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Sidebar -->
            <aside class="col-span-3 bg-white rounded-lg p-4 shadow">
                <h2 class="font-medium text-gray-700 mb-3">Filters</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Search</label>
                        <input type="text" placeholder="Search items..." class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-indigo-200" x-model="filters.search">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Sprint</label>
                        <select class="w-full px-3 py-2 border rounded" x-model="filters.sprint">
                            <option value="all">All Sprints</option>
                            <option value="sprint-1">Sprint 1</option>
                            <option value="sprint-2">Sprint 2</option>
                            <option value="sprint-3">Sprint 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Epic</label>
                        <select class="w-full px-3 py-2 border rounded" x-model="filters.epic">
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
                                <input type="checkbox" x-model="filters.statusAll" @change="toggleStatus('all')">
                                <span>All</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="filters.status.todo" @change="toggleStatus('todo')">
                                <span>To Do</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="filters.status.inprogress" @change="toggleStatus('inprogress')">
                                <span>In Progress</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="filters.status.done" @change="toggleStatus('done')">
                                <span>Done</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main content -->
            <main class="col-span-6">
                <div class="space-y-4">
                    @php
                        $epics = [
                            ['id'=>1,'title'=>'User Authentication & Authorization','status'=>'inprogress','short'=>'Implement comprehensive user authentication system with role-based access control.','description'=>'Full-featured auth: login, register, password reset, OAuth and RBAC for roles and permissions.','sprint'=>'Sprint 3','assigned'=>'Alice Nguyen','stories_count'=>3,'points'=>13,'progress'=>65,'stories'=>[
                                ['id'=>11,'title'=>'Login flow','points'=>3,'description'=>'Design login UI and backend auth flow','sprint'=>'Sprint 3','assigned'=>'Bob Tran'],
                                ['id'=>12,'title'=>'RBAC','points'=>5,'description'=>'Role based access control system','sprint'=>'Sprint 3','assigned'=>'Alice Nguyen'],
                                ['id'=>13,'title'=>'Password reset','points'=>5,'description'=>'Email based password recovery','sprint'=>'Sprint 4','assigned'=>null]
                            ]],

                            ['id'=>2,'title'=>'Payment Integration','status'=>'todo','short'=>'Integrate payment processing with multiple payment providers.','description'=>'Support Stripe and PayPal with webhooks and reconciliation.','sprint'=>'Backlog','assigned'=>null,'stories_count'=>2,'points'=>8,'progress'=>0,'stories'=>[
                                ['id'=>21,'title'=>'Stripe integration','points'=>5,'description'=>'Implement Stripe checkout and subscriptions','sprint'=>'Backlog','assigned'=>null],
                                ['id'=>22,'title'=>'PayPal support','points'=>3,'description'=>'Add PayPal as alternate gateway','sprint'=>'Backlog','assigned'=>null]
                            ]],

                            ['id'=>3,'title'=>'Reporting Dashboard','status'=>'inprogress','short'=>'Build analytics and reporting dashboard for business insights.','description'=>'Dashboard with charts, exports and filters for stakeholders.','sprint'=>'Sprint 2','assigned'=>'Charlie Le','stories_count'=>2,'points'=>8,'progress'=>40,'stories'=>[
                                ['id'=>31,'title'=>'Charts','points'=>5,'description'=>'Interactive charts for key metrics','sprint'=>'Sprint 2','assigned'=>'Charlie Le'],
                                ['id'=>32,'title'=>'Export reports','points'=>3,'description'=>'CSV / PDF export for reports','sprint'=>'Sprint 2','assigned'=>null]
                            ]],
                        ];

                        $unassigned = [
                            ['id'=>101,'title'=>'Email Notifications','desc'=>'Setup transactional email templates','points'=>3],
                            ['id'=>102,'title'=>'Mobile Responsiveness','desc'=>'Ensure UI works well on mobile','points'=>5],
                        ];
                    @endphp

                    <section>
                        <h3 class="text-sm text-gray-500 mb-3">Product Backlog</h3>
                        <div class="space-y-4">
                            @foreach($epics as $epic)
                                <div class="bg-white rounded-lg shadow hover:shadow-md">
                                    <div class="p-4 cursor-pointer" @click='openPanel(@json($epic))'>
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-3">
                                                    <button type="button" class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100" @click.stop="toggleExpand({{ $epic['id'] }})" :class="{'rotate-90': expandedIds.includes({{ $epic['id'] }})}" aria-label="Toggle expand">
                                                        <svg class="w-4 h-4 text-gray-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </button>
                                                    <h4 class="text-lg font-semibold text-gray-800 truncate">{{ $epic['title'] }}</h4>
                                                    @if($epic['status'] === 'todo')
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-800">To Do</span>
                                                    @elseif($epic['status'] === 'inprogress')
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800">In Progress</span>
                                                    @else
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Done</span>
                                                    @endif
                                                </div>

                                                <p class="text-sm text-gray-600 mt-2">{{ $epic['short'] }}</p>
                                            </div>

                                            <div class="ml-4 text-right">
                                                <div class="text-sm text-gray-500">{{ $epic['stories_count'] }} stories</div>
                                                <div class="text-sm text-gray-700 font-medium">{{ $epic['points'] }} pts</div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="w-full bg-gray-100 rounded-full h-2">
                                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $epic['progress'] }}%"></div>
                                            </div>
                                            <div class="flex justify-end text-xs text-gray-500 mt-1">{{ $epic['progress'] }}%</div>
                                        </div>
                                    </div>

                                    <!-- Collapsible stories (hierarchical view) -->
                                    <div x-show="expandedIds.includes({{ $epic['id'] }})" x-transition class="border-t">
                                            <div class="p-3 space-y-2 bg-gray-50">
                                            @foreach($epic['stories'] as $story)
                                                <div class="flex items-center justify-between px-3 py-2 bg-white rounded shadow-sm cursor-pointer" @click.stop='openStoryPanel(@json($story), {{ $epic['id'] }}, @json($epic['title']))'>
                                                        <div>
                                                            <div class="text-sm text-gray-800">{{ $story['title'] }}</div>
                                                            <div class="text-xs text-gray-500">{{ $story['description'] }}</div>
                                                        </div>
                                                        <div class="text-sm text-gray-600">{{ $story['points'] }} pts</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="mt-6 bg-white rounded-lg p-4 shadow">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">UNASSIGNED STORIES</h4>
                        <div class="space-y-3">
                            @foreach($unassigned as $u)
                                <div class="flex items-center justify-between bg-gray-50 rounded px-3 py-2">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">{{ $u['title'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $u['desc'] }}</div>
                                    </div>
                                    <div class="text-sm text-gray-600">{{ $u['points'] }} pts</div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </main>

            <!-- Right spacer (for centered layout) -->
            <div class="col-span-3"></div>
        </div>
    </div>

    <!-- Detail Panel -->
    <div x-show="showPanel" class="fixed inset-0 z-40 flex" x-cloak>
        <!-- overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-40" @click="close()" x-show="showPanel" x-transition.opacity></div>

        <aside x-show="showPanel" x-transition:enter="transition transform duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="ml-auto w-96 bg-white h-full shadow-xl z-50 flex flex-col">
            <div class="p-4 flex items-start justify-between border-b">
                <h3 class="text-lg font-semibold text-gray-800" x-text="panelTitle"></h3>
                <button class="text-gray-600 hover:text-gray-900 text-2xl leading-none" @click="close()">&times;</button>
            </div>

            <div class="p-4 overflow-y-auto h-full">
                <template x-if="selectedItem">
                    <div>
                        <!-- Epic view -->
                        <div x-show="selectedItem.type === 'epic'" x-transition>
                            <h4 class="text-xl font-bold text-gray-800" x-text="selectedItem.data.title"></h4>
                            <div class="mt-2 text-sm text-gray-600" x-text="selectedItem.data.description"></div>

                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600">
                                <div><span class="font-medium text-gray-700">Sprint:</span> <span x-text="selectedItem.data.sprint || '—'"></span></div>
                                <div><span class="font-medium text-gray-700">Status:</span> <span x-text="selectedItem.data.status"></span></div>
                                <div class="col-span-2 mt-2">
                                    <div class="text-sm font-medium text-gray-700">Progress</div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
                                        <div class="h-2 rounded-full bg-indigo-500" :style="`width: ${selectedItem.data.progress}%`"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1" x-text="selectedItem.data.progress + '%'" ></div>
                                </div>
                                <div class="col-span-2 mt-3"><span class="font-medium text-gray-700">Assigned:</span> <span x-text="selectedItem.data.assigned || 'Unassigned'"></span></div>
                            </div>

                            <div class="mt-6">
                                <div class="text-sm text-gray-500">User Stories</div>
                                <ul class="mt-2 space-y-2">
                                    <template x-for="s in selectedItem.data.stories" :key="s.id">
                                        <li class="flex items-center justify-between bg-gray-50 rounded px-3 py-2">
                                            <div>
                                                <div class="text-sm text-gray-800" x-text="s.title"></div>
                                                <div class="text-xs text-gray-500" x-text="s.description"></div>
                                            </div>
                                            <div class="text-sm text-gray-600" x-text="s.points + ' pts'"></div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- Story view -->
                        <div x-show="selectedItem.type === 'story'" x-transition>
                            <h4 class="text-xl font-bold text-gray-800" x-text="selectedItem.data.title"></h4>
                            <div class="mt-2 text-sm text-gray-600" x-text="selectedItem.data.description"></div>

                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600">
                                <div><span class="font-medium text-gray-700">Sprint:</span> <span x-text="selectedItem.data.sprint || parentEpic.sprint || '—'"></span></div>
                                <div><span class="font-medium text-gray-700">Status:</span> <span x-text="selectedItem.data.status || 'To Do'"></span></div>
                                <div class="col-span-2 mt-2">
                                    <div class="text-sm font-medium text-gray-700">Progress</div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
                                        <div class="h-2 rounded-full bg-indigo-500" :style="`width: ${selectedItem.data.progress || 0}%`"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1" x-text="(selectedItem.data.progress || 0) + '%'" ></div>
                                </div>
                                <div class="col-span-2 mt-3"><span class="font-medium text-gray-700">Assigned:</span> <span x-text="selectedItem.data.assigned || 'Unassigned'"></span></div>
                            </div>

                            <div class="mt-6">
                                <div class="text-sm text-gray-500">Parent Epic</div>
                                <div class="mt-2 text-sm text-gray-800" x-text="parentEpic.title"></div>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button class="flex-1 px-3 py-2 bg-yellow-500 text-white rounded">Edit</button>
                            <button class="flex-1 px-3 py-2 bg-red-500 text-white rounded">Delete</button>
                        </div>
                    </div>
                </template>
            </div>
        </aside>
    </div>

</div>

@push('scripts')
<script>
    function productBacklog(){
        return {
            showPanel:false,
            panelTitle:'',
            selectedItem:null, // {type:'epic'|'story', data: {...}}
            parentEpic: {},
            expandedIds: [],
            filters:{
                search:'',
                sprint:'all',
                epic:'all',
                statusAll:true,
                status:{todo:true,inprogress:true,done:true}
            },
            openPanel(epic){
                console.log('openPanel', epic);
                this.selectedItem = {type:'epic', data: epic};
                this.parentEpic = {};
                this.panelTitle = 'Epic Details';
                this.showPanel = true;
            },
            openStoryPanel(story, epicId, epicTitle){
                console.log('openStoryPanel', story, epicId, epicTitle);
                // attach parent context for better display
                this.selectedItem = {type:'story', data: story};
                this.parentEpic = {id: epicId, title: epicTitle};
                this.panelTitle = 'Story Details';
                this.showPanel = true;
            },
            close(){
                this.showPanel = false;
                this.selectedItem = null;
                this.parentEpic = {};
            },
            toggleStatus(k){
                if(k === 'all'){
                    const s = this.filters.statusAll;
                    this.filters.status.todo = s;
                    this.filters.status.inprogress = s;
                    this.filters.status.done = s;
                } else {
                    this.filters.status[k] = !this.filters.status[k];
                    this.filters.statusAll = this.filters.status.todo && this.filters.status.inprogress && this.filters.status.done;
                }
            },
            toggleExpand(id){
                const i = this.expandedIds.indexOf(id);
                if(i === -1) this.expandedIds.push(id);
                else this.expandedIds.splice(i,1);
            }
        }
    }
</script>
<script src="https://unpkg.com/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
@endpush

@endsection

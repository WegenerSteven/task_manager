<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
</head>
<body>

<div id="app">
    <header>
        <h1>Task Manager</h1>
        <span class="subtitle">Cytonn Internship Challenge 2026</span>
    </header>

    <div class="container">

        <!-- Alert -->
        <div v-if="alert.message" :class="['alert', 'alert-' + alert.type]">
            @{{ alert.message }}
        </div>

        <!-- Create Task -->
        <div class="card">
            <h2>Create New Task</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Title</label>
                    <input v-model="form.title" type="text" placeholder="Task title..." />
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input v-model="form.due_date" type="date" :min="today" />
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select v-model="form.priority">
                        <option value="">Select...</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary" @click="createTask" :disabled="loading">
                        @{{ loading ? 'Saving...' : 'Add Task' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Task List -->
        <div class="card">
            <div class="filters">
                <h2> Tasks</h2>
                <label>Filter by status:</label>
                <select v-model="filterStatus" @change="fetchTasks">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
                <button class="btn btn-primary btn-sm report-toggle" @click="showReport = !showReport">
                    @{{ showReport ? 'Hide Report' : 'Daily Report' }}
                </button>
            </div>

            <!-- Daily Report -->
            <div v-if="showReport" class="report-section">
                <div class="report-controls">
                    <label>Date:</label>
                    <input type="date" v-model="reportDate" class="report-date-input" />
                    <button class="btn btn-primary btn-sm" @click="fetchReport">Generate</button>
                </div>
                <div v-if="report" class="report-grid">
                    <div
                        v-for="priority in ['high', 'medium', 'low']"
                        :key="priority"
                        :class="['report-card', priority]">
                        <h3>@{{ priority }}</h3>
                        <p>Pending: <span>@{{ report.summary[priority].pending }}</span></p>
                        <p>In Progress: <span>@{{ report.summary[priority].in_progress }}</span></p>
                        <p>Done: <span>@{{ report.summary[priority].done }}</span></p>
                    </div>
                </div>
                <p v-else class="report-empty">Select a date and click Generate.</p>
            </div>

            <!-- Loading -->
            <div v-if="fetching" class="spinner">Loading tasks...</div>

            <!-- Empty -->
            <div v-else-if="tasks.length === 0" class="empty">
                <p>No tasks found. Create one above!</p>
            </div>

            <!-- Table -->
            <div v-else class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="task in tasks" :key="task.id">
                            <td>@{{ task.id }}</td>
                            <td>@{{ task.title }}</td>
                            <td>@{{ task.due_date }}</td>
                            <td>
                                <span :class="['badge', 'badge-' + task.priority]">
                                    @{{ task.priority }}
                                </span>
                            </td>
                            <td>
                                <span :class="['badge', 'badge-' + task.status]">
                                    @{{ task.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button
                                        v-if="task.status !== 'done'"
                                        class="btn btn-success btn-sm"
                                        @click="advanceStatus(task)">
                                        @{{ task.status === 'pending' ? '▶ Start' : '✔ Complete' }}
                                    </button>
                                    <button
                                        v-if="task.status === 'done'"
                                        class="btn btn-danger btn-sm"
                                        @click="deleteTask(task)">
                                        Delete
                                    </button>
                                    <span v-if="task.status === 'done'" class="done-label"> Completed</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/tasks.js') }}"></script>

</body>
</html>
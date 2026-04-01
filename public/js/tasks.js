const { createApp, ref, onMounted } = Vue;

createApp({
    setup() {

        // ── State ──────────────────────────────────────────────
        const tasks        = ref([]);
        const filterStatus = ref('');
        const fetching     = ref(false);
        const loading      = ref(false);
        const showReport   = ref(false);
        const report       = ref(null);
        const reportDate   = ref(new Date().toISOString().split('T')[0]);
        const alert        = ref({ message: '', type: '' });
        const today        = new Date().toISOString().split('T')[0];

        const form = ref({ title: '', due_date: '', priority: '' });

        // ── Helpers ────────────────────────────────────────────
        function showAlert(message, type = 'error') {
            alert.value = { message, type };
            setTimeout(() => alert.value = { message: '', type: '' }, 4000);
        }

        function extractErrorMessage(error) {
            if (error.response?.data?.errors) {
                return Object.values(error.response.data.errors).flat().join(' ');
            }
            return error.response?.data?.message || 'Something went wrong.';
        }

        // ── API calls ──────────────────────────────────────────
        async function fetchTasks() {
            fetching.value = true;
            try {
                const params = filterStatus.value ? { status: filterStatus.value } : {};
                const res    = await axios.get('/api/tasks', { params });
                tasks.value  = Array.isArray(res.data) ? res.data : [];
            } catch (error) {
                showAlert('Failed to load tasks.');
            } finally {
                fetching.value = false;
            }
        }

        async function createTask() {
            if (!form.value.title || !form.value.due_date || !form.value.priority) {
                showAlert('Please fill in all fields.');
                return;
            }
            loading.value = true;
            try {
                await axios.post('/api/tasks', form.value);
                form.value = { title: '', due_date: '', priority: '' };
                showAlert('Task created successfully!', 'success');
                await fetchTasks();
            } catch (error) {
                showAlert(extractErrorMessage(error));
            } finally {
                loading.value = false;
            }
        }

        async function advanceStatus(task) {
            const next = task.status === 'pending' ? 'in_progress' : 'done';
            try {
                await axios.patch(`/api/tasks/${task.id}/status`, { status: next });
                showAlert(`Task moved to "${next.replace('_', ' ')}"`, 'success');
                await fetchTasks();
            } catch (error) {
                showAlert(extractErrorMessage(error));
            }
        }

        async function deleteTask(task) {
            if (!confirm(`Delete "${task.title}"?`)) return;
            try {
                await axios.delete(`/api/tasks/${task.id}`);
                showAlert('Task deleted successfully.', 'success');
                await fetchTasks();
            } catch (error) {
                showAlert(extractErrorMessage(error));
            }
        }

        async function fetchReport() {
            try {
                const res = await axios.get('/api/tasks/report', { params: { date: reportDate.value } });
                report.value = res.data;
            } catch (error) {
                showAlert(extractErrorMessage(error));
            }
        }

        // ── Lifecycle ──────────────────────────────────────────
        onMounted(fetchTasks);

        return {
            tasks, form, filterStatus, fetching, loading,
            showReport, report, reportDate, alert, today,
            fetchTasks, createTask, advanceStatus, deleteTask, fetchReport,
        };
    }
}).mount('#app');
<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const breadcrumbs = [{ title: 'Authors', href: '/authors' }];

const authors = ref([]);
const search = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = 15;

const showModal = ref(false);
const editingAuthor = ref(null);

const form = reactive({
    first_name: '',
    last_name: '',
    middle_name: '',
});

const errors = ref({});

const fetchAuthors = async () => {
    const res = await axios.get('/api/authors', {
        params: { search: search.value, page: currentPage.value },
        headers: { Accept: 'application/json' },
    });
    authors.value = res.data.data;
    lastPage.value = res.data.meta.last_page;
};

const resetForm = () => {
    form.first_name = '';
    form.last_name = '';
    form.middle_name = '';
    editingAuthor.value = null;
    errors.value = {};
};

const openCreateModal = () => {
    resetForm();
    showModal.value = true;
};

const openEditModal = (author) => {
    form.first_name = author.first_name;
    form.last_name = author.last_name;
    form.middle_name = author.middle_name || '';
    editingAuthor.value = author;
    errors.value = {};
    showModal.value = true;
};

const saveAuthor = async () => {
    try {
        if (editingAuthor.value) {
            await axios.put(`/api/authors/${editingAuthor.value.id}`, form);
        } else {
            await axios.post('/api/authors', form);
        }
        showModal.value = false;
        fetchAuthors();
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
        }
    }
};

const deleteAuthor = async (id) => {
    if (!confirm('Delete this author?')) return;
    await axios.delete(`/api/authors/${id}`);
    fetchAuthors();
};

onMounted(fetchAuthors);
watch([search, currentPage], fetchAuthors);
</script>

<template>
    <Head title="Authors" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 flex flex-col gap-4">
            <div class="flex justify-between items-center">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search authors..."
                    class="border px-2 py-1 rounded"
                />
                <button
                    @click="openCreateModal"
                    class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700"
                >
                    Add Author
                </button>
            </div>

            <!-- Authors Table -->
            <table class="w-full text-left border rounded">
                <thead>
                <tr class="bg-gray-100">
                    <th class="p-2">Last Name ↑</th>
                    <th class="p-2">First Name</th>
                    <th class="p-2">Middle Name</th>
                    <th class="p-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="author in authors" :key="author.id">
                    <td class="p-2">{{ author.last_name }}</td>
                    <td class="p-2">{{ author.first_name }}</td>
                    <td class="p-2">{{ author.middle_name || '-' }}</td>
                    <td class="p-2 flex gap-2">
                        <button @click="openEditModal(author)" class="text-blue-600">Edit</button>
                        <button @click="deleteAuthor(author.id)" class="text-red-600">Delete</button>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex items-center gap-4 mt-2">
                <button
                    :disabled="currentPage <= 1"
                    @click="currentPage--"
                    class="px-3 py-1 border rounded disabled:opacity-50"
                >
                    Prev
                </button>
                <span>Page {{ currentPage }} of {{ lastPage }}</span>
                <button
                    :disabled="currentPage >= lastPage"
                    @click="currentPage++"
                    class="px-3 py-1 border rounded disabled:opacity-50"
                >
                    Next
                </button>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-full max-w-md">
                    <h2 class="text-xl mb-4">
                        {{ editingAuthor ? 'Edit Author' : 'Add Author' }}
                    </h2>

                    <div class="flex flex-col gap-3">
                        <div>
                            <input
                                v-model="form.last_name"
                                placeholder="Last Name"
                                class="w-full border px-2 py-1 rounded"
                            />
                            <div class="text-red-500 text-sm" v-if="errors?.last_name">
                                {{ errors.last_name[0] }}
                            </div>
                        </div>

                        <div>
                            <input
                                v-model="form.first_name"
                                placeholder="First Name"
                                class="w-full border px-2 py-1 rounded"
                            />
                            <div class="text-red-500 text-sm" v-if="errors?.first_name">
                                {{ errors.first_name[0] }}
                            </div>
                        </div>

                        <input
                            v-model="form.middle_name"
                            placeholder="Middle Name (optional)"
                            class="w-full border px-2 py-1 rounded"
                        />

                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="showModal = false" class="px-4 py-1 bg-gray-200 rounded">
                                Cancel
                            </button>
                            <button
                                @click="saveAuthor"
                                class="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

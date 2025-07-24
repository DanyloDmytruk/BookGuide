<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const breadcrumbs = [{ title: 'Books', href: '/books' }];

const books = ref([]);
const authors = ref([]);
const searchTitle = ref('');
const searchAuthor = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const showModal = ref(false);
const editingBook = ref(null);
const errors = ref({});

const form = reactive({
    title: '',
    description: '',
    image: null,
    publication_date: '',
    authors: [] as number[],
});

const fetchBooks = async () => {
    const res = await axios.get('/api/books', {
        params: {
            title: searchTitle.value,
            author: searchAuthor.value,
            page: currentPage.value,
        },
    });
    books.value = res.data.data;
    lastPage.value = res.data.meta.last_page;
};

const fetchAuthors = async () => {
    const res = await axios.get('/api/authors');
    authors.value = res.data.data;
};

const openCreateModal = () => {
    editingBook.value = null;
    Object.assign(form, {
        title: '',
        description: '',
        image: null,
        publication_date: '',
        authors: [],
    });
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (book) => {
    editingBook.value = book;
    Object.assign(form, {
        title: book.title,
        description: book.description,
        image: null,
        publication_date: book.publication_date,
        authors: book.authors.map((a) => a.id),
    });
    errors.value = {};
    showModal.value = true;
};

const saveBook = async () => {
    try {
        const data = new FormData();
        data.append('title', form.title);
        data.append('description', form.description || '');
        data.append('publication_date', form.publication_date);
        form.authors.forEach((id) => data.append('author_ids[]', id.toString()));
        if (form.image) data.append('image', form.image);
        if (editingBook.value) {
            data.append('_method', 'PUT');
            await axios.post(`/api/books/${editingBook.value.id}`, data);
        } else {
            await axios.post('/api/books', data);
        }
        showModal.value = false;
        fetchBooks();
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
        }
    }
};

const deleteBook = async (id: number) => {
    if (confirm('Delete this book?')) {
        await axios.delete(`/api/books/${id}`);
        fetchBooks();
    }
};

onMounted(() => {
    fetchBooks();
    fetchAuthors();
});

watch([searchTitle, searchAuthor, currentPage], fetchBooks);
</script>

<template>
    <Head title="Books" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 flex flex-col gap-4">
            <!-- Filters -->
            <div class="flex flex-col md:flex-row gap-2 justify-between items-start md:items-center">
                <input
                    v-model="searchTitle"
                    type="text"
                    placeholder="Search by book title..."
                    class="border px-2 py-1 rounded w-full md:w-1/2"
                />
                <input
                    v-model="searchAuthor"
                    type="text"
                    placeholder="Search by author name..."
                    class="border px-2 py-1 rounded w-full md:w-1/3"
                />
                <button
                    @click="openCreateModal"
                    class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700"
                >
                    Add Book
                </button>
            </div>

            <!-- Book Table -->
            <table class="w-full text-left border rounded">
                <thead>
                <tr class="bg-gray-100">
                    <th class="p-2">Title</th>
                    <th class="p-2">Authors</th>
                    <th class="p-2">Published</th>
                    <th class="p-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="book in books" :key="book.id">
                    <td class="p-2">{{ book.title }}</td>
                    <td class="p-2">
                        <div class="flex flex-col">
                            <span v-for="author in book.authors" :key="author.id">
                                {{ author.last_name }} {{ author.first_name }}
                            </span>
                        </div>
                    </td>
                    <td class="p-2">{{ book.publication_date }}</td>
                    <td class="p-2 flex gap-2">
                        <button @click="openEditModal(book)" class="text-blue-600">Edit</button>
                        <button @click="deleteBook(book.id)" class="text-red-600">Delete</button>
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
                <div class="bg-white rounded p-6 w-full max-w-lg space-y-4">
                    <h2 class="text-xl">{{ editingBook ? 'Edit Book' : 'Add Book' }}</h2>

                    <input
                        v-model="form.title"
                        placeholder="Title"
                        class="w-full border p-2 rounded"
                    />
                    <div v-if="errors?.title" class="text-red-500 text-sm">{{ errors.title[0] }}</div>

                    <textarea
                        v-model="form.description"
                        placeholder="Description (optional)"
                        class="w-full border p-2 rounded"
                    />

                    <input
                        type="date"
                        v-model="form.publication_date"
                        class="w-full border p-2 rounded"
                    />

                    <label class="block font-semibold">Authors:</label>
                    <select
                        v-model="form.authors"
                        multiple
                        class="w-full border p-2 rounded h-32"
                    >
                        <option
                            v-for="author in authors"
                            :key="author.id"
                            :value="author.id"
                        >
                            {{ author.last_name }} {{ author.first_name }}
                        </option>
                    </select>
                    <div v-if="errors?.authors" class="text-red-500 text-sm">{{ errors.authors[0] }}</div>

                    <input
                        type="file"
                        @change="(e) => form.image = e.target.files[0]"
                    />
                    <div v-if="errors?.image" class="text-red-500 text-sm">{{ errors.image[0] }}</div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button @click="showModal = false" class="bg-gray-200 px-4 py-1 rounded">Cancel</button>
                        <button @click="saveBook" class="bg-blue-600 text-white px-4 py-1 rounded">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

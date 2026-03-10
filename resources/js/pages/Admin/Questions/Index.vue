<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { Plus, Edit, Trash2, Search, HelpCircle, Image as ImageIcon } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';

interface CategoryData {
    id: number;
    title: string;
}

interface QuestionData {
    id: number;
    type: string;
    title: string;
    point: number;
    difficulty: number;
    sub_category: CategoryData;
    creator: { id: number; name: string };
    created_at: string;
}

interface Props {
    questions: {
        data: QuestionData[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    subCategories: CategoryData[];
    filters: {
        search?: string;
        sub_category_id?: number | string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bank Soal', href: '/questions' },
];

const search = ref(props.filters.search || '');
const selectedSubCategoryId = ref<string>(props.filters.sub_category_id ? String(props.filters.sub_category_id) : 'all');

const showDeleteModal = ref(false);
const deletingQuestion = ref<QuestionData | null>(null);

watch([search, selectedSubCategoryId], debounce(([newSearch, newSubCategoryId]) => {
    router.get('/questions', { 
        search: newSearch, 
        sub_category_id: newSubCategoryId === 'all' ? null : newSubCategoryId 
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

function openCreate() {
    router.get('/questions/create');
}

function editQuestion(question: QuestionData) {
    router.get(`/questions/${question.id}/edit`);
}

function confirmDelete(question: QuestionData) {
    deletingQuestion.value = question;
    showDeleteModal.value = true;
}

function deleteQuestion() {
    if (!deletingQuestion.value) return;
    
    router.delete(`/questions/${deletingQuestion.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
}

function getQuestionTypeLabel(type: string) {
    switch (type) {
        case 'multiple_choice': return 'Pilihan Ganda';
        case 'true_false': return 'Benar/Salah';
        case 'short_essay': return 'Isian Singkat';
        case 'essay': return 'Uraian';
        default: return type;
    }
}

function stripHtml(html: string) {
    const tmp = document.createElement('DIV');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}
</script>

<template>
    <Head title="Bank Soal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Bank Soal</h1>
                    <p class="text-sm text-muted-foreground">Kelola basis data pertanyaan untuk ujian Anda.</p>
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Soal
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative w-full sm:max-w-xs">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="search"
                        placeholder="Cari soal..."
                        class="w-full bg-background pl-8"
                    />
                </div>
                <div class="w-full sm:max-w-xs flex items-center gap-2">
                    <Label for="sub-category-filter" class="shrink-0 text-sm font-medium">Sub Kategori</Label>
                    <Select v-model="selectedSubCategoryId">
                        <SelectTrigger id="sub-category-filter" class="w-full">
                            <SelectValue placeholder="Semua Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Kategori</SelectItem>
                            <SelectItem v-for="category in subCategories" :key="category.id" :value="String(category.id)">
                                {{ category.title }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Data Table -->
            <div class="rounded-md border bg-background flex-1 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="h-10 px-4 text-left font-medium">Soal</th>
                                <th class="h-10 px-4 text-left font-medium">Tipe</th>
                                <th class="h-10 px-4 text-left font-medium">Sub Kategori</th>
                                <th class="h-10 px-4 text-center font-medium">Bobot</th>
                                <th class="h-10 px-4 text-center font-medium">Kesulitan</th>
                                <th class="h-10 px-4 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="questions.data.length === 0">
                                <td colspan="6" class="h-24 text-center text-muted-foreground">
                                    Tidak ada data soal ditemukan.
                                </td>
                            </tr>
                            <tr
                                v-for="question in questions.data"
                                :key="question.id"
                                class="border-t transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-start gap-2 max-w-lg">
                                        <HelpCircle class="w-4 h-4 mt-0.5 shrink-0 text-primary" />
                                        <div class="line-clamp-2" :title="stripHtml(question.title)">
                                            {{ stripHtml(question.title) || '(Tanpa Teks)' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold">
                                        {{ getQuestionTypeLabel(question.type) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">{{ question.sub_category.name }}</td>
                                <td class="px-4 py-3 align-top text-center">{{ question.point }}</td>
                                <td class="px-4 py-3 align-top text-center">
                                    <span class="inline-flex items-center gap-1 text-yellow-600">
                                        <span v-for="i in question.difficulty" :key="i">★</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="icon" class="h-8 w-8 text-primary" @click="editQuestion(question)" title="Edit">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button variant="outline" size="icon" class="h-8 w-8 text-destructive" @click="confirmDelete(question)" title="Hapus">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-auto border-t p-4" v-if="questions.links.length > 3">
                    <Pagination :links="questions.links" />
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="showDeleteModal"
            title="Hapus Soal"
            description="Apakah Anda yakin ingin menghapus soal ini? Semua data jawaban dan gambar terkait akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan."
            confirm-text="Hapus"
            cancel-text="Batal"
            variant="destructive"
            @confirm="deleteQuestion"
        />
    </AppLayout>
</template>

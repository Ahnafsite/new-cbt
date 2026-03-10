<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { BreadcrumbItem } from '@/types';

interface ClassData {
    id: number;
    title: string;
    year: string;
    user_id: number | null;
    students_count: number;
    user?: { id: number; name: string };
}

interface TeacherOption {
    id: number;
    name: string;
}

interface Props {
    classes: {
        data: ClassData[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    teachers: TeacherOption[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Kelas', href: '/classes' },
];

// Modal state
const showModal = ref(false);
const showDeleteDialog = ref(false);
const editingClass = ref<ClassData | null>(null);
const deletingClass = ref<ClassData | null>(null);

const form = useForm({
    title: '',
    year: new Date().toISOString().slice(0, 10),
    user_id: '' as string | number,
});

function openCreate() {
    editingClass.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(classItem: ClassData) {
    editingClass.value = classItem;
    form.title = classItem.title;
    form.year = classItem.year.slice(0, 10);
    form.user_id = classItem.user_id ? String(classItem.user_id) : '';
    form.clearErrors();
    showModal.value = true;
}

function submit() {
    if (editingClass.value) {
        form.put(`/classes/${editingClass.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/classes', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
}

function confirmDelete(classItem: ClassData) {
    deletingClass.value = classItem;
    showDeleteDialog.value = true;
}

function deleteClass() {
    if (!deletingClass.value) return;
    router.delete(`/classes/${deletingClass.value.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingClass.value = null;
        },
    });
}
</script>

<template>
    <Head title="Kelas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Kelas</h1>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kelas
                </Button>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-4">
                <SearchInput
                    :model-value="filters.search || ''"
                    route-name="classes.index"
                    placeholder="Cari kelas..."
                />
            </div>

            <!-- Table -->
            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">#</th>
                                <th class="px-4 py-3 text-left font-medium">Nama Kelas</th>
                                <th class="px-4 py-3 text-left font-medium">Tahun</th>
                                <th class="px-4 py-3 text-left font-medium">Jumlah Siswa</th>
                                <th class="px-4 py-3 text-left font-medium">Wali Kelas</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in classes.data"
                                :key="item.id"
                                class="border-b transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ classes.from + index }}
                                </td>
                                <td class="px-4 py-3 font-medium">{{ item.title }}</td>
                                <td class="px-4 py-3">
                                    {{ new Date(item.year).getFullYear() }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                        {{ item.students_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ item.user?.name || '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-sky-600 hover:text-sky-700 hover:bg-sky-50 dark:text-sky-400 dark:hover:text-sky-300 dark:hover:bg-sky-950" @click="router.visit(`/classes/${item.id}/students`)">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-950" @click="openEdit(item)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950" @click="confirmDelete(item)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="classes.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    Tidak ada kelas ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="classes.total > 0"
                :links="classes.links"
                :from="classes.from"
                :to="classes.to"
                :total="classes.total"
            />
        </div>

        <!-- Create / Edit Modal -->
        <Dialog :open="showModal" @update:open="showModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ editingClass ? 'Edit Kelas' : 'Tambah Kelas' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ editingClass ? 'Perbarui detail kelas.' : 'Isi detail untuk kelas baru.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Nama Kelas</Label>
                        <Input id="title" v-model="form.title" placeholder="Contoh: XII IPA 1" />
                        <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="year">Tahun</Label>
                        <Input id="year" v-model="form.year" type="date" />
                        <p v-if="form.errors.year" class="text-sm text-destructive">{{ form.errors.year }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="user_id">Wali Kelas</Label>
                        <Select v-model="form.user_id" @update:model-value="(val: any) => form.user_id = val">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih wali kelas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="teacher in teachers"
                                    :key="teacher.id"
                                    :value="String(teacher.id)"
                                >
                                    {{ teacher.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.user_id" class="text-sm text-destructive">{{ form.errors.user_id }}</p>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showModal = false" :disabled="form.processing">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingClass ? 'Perbarui' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus Kelas"
            :description="`Apakah Anda yakin ingin menghapus kelas '${deletingClass?.title}'? Semua siswa di kelas ini juga akan dihapus.`"
            confirm-label="Hapus"
            @close="showDeleteDialog = false"
            @confirm="deleteClass"
        />
    </AppLayout>
</template>

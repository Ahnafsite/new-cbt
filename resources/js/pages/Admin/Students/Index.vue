<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Upload, FileSpreadsheet, Download, CheckCircle2, XCircle, AlertTriangle } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
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
import axios from 'axios';

interface StudentData {
    id: number;
    user_id: number;
    class_id: number;
    user: { id: number; name: string; email: string };
    class: { id: number; title: string; year: string };
}

interface ClassOption {
    id: number;
    title: string;
}

interface PreviewRow {
    row: number;
    nama: string;
    email: string;
    password: string;
    kelas: string;
    errors?: string[];
}

interface Props {
    students: {
        data: StudentData[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    classes: ClassOption[];
    filters: {
        search?: string;
        class_id?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Siswa', href: '/students' },
];

// CRUD state
const showModal = ref(false);
const showDeleteDialog = ref(false);
const editingStudent = ref<StudentData | null>(null);
const deletingStudent = ref<StudentData | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    class_id: '' as string | number,
});

const selectedClassFilter = ref(props.filters.class_id || '');

// Import state
const showImportModal = ref(false);
const importStep = ref<'upload' | 'preview'>('upload');
const importFile = ref<File | null>(null);
const importing = ref(false);
const previewing = ref(false);
const previewData = ref<{
    valid: PreviewRow[];
    invalid: PreviewRow[];
    total: number;
    valid_count: number;
    invalid_count: number;
    temp_path: string;
} | null>(null);

function filterByClass(value: string) {
    selectedClassFilter.value = value;
    router.get('/students', {
        search: props.filters.search || undefined,
        class_id: value === 'all' ? undefined : value || undefined,
    }, { preserveState: true, replace: true });
}

function openCreate() {
    editingStudent.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(student: StudentData) {
    editingStudent.value = student;
    form.name = student.user.name;
    form.email = student.user.email;
    form.password = '';
    form.class_id = student.class_id;
    form.clearErrors();
    showModal.value = true;
}

function submit() {
    if (editingStudent.value) {
        form.put(`/students/${editingStudent.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/students', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
}

function confirmDelete(student: StudentData) {
    deletingStudent.value = student;
    showDeleteDialog.value = true;
}

function deleteStudent() {
    if (!deletingStudent.value) return;
    router.delete(`/students/${deletingStudent.value.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingStudent.value = null;
        },
    });
}

// Import functions
function openImportModal() {
    importStep.value = 'upload';
    importFile.value = null;
    previewData.value = null;
    showImportModal.value = true;
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        importFile.value = target.files[0];
    }
}

async function previewImport() {
    if (!importFile.value) return;

    previewing.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);

    try {
        const response = await axios.post('/students/import/preview', formData);
        previewData.value = response.data;
        importStep.value = 'preview';
    } catch (error: any) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            const firstError = Object.values(errors).flat()[0] as string;
            toast.error(firstError);
        } else {
            toast.error('Terjadi kesalahan saat memproses file.');
        }
    } finally {
        previewing.value = false;
    }
}

async function confirmImport() {
    if (!previewData.value) return;
    importing.value = true;

    try {
        router.post('/students/import', {
            temp_path: previewData.value.temp_path,
        }, {
            onSuccess: () => {
                showImportModal.value = false;
                previewData.value = null;
                importFile.value = null;
            },
            onFinish: () => {
                importing.value = false;
            },
        });
    } catch {
        importing.value = false;
        toast.error('Gagal mengimpor data siswa.');
    }
}
</script>

<template>
    <Head title="Siswa" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Siswa</h1>
                <div class="flex gap-2">
                    <a
                        href="/students/template"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-8 px-3"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Unduh Template
                    </a>
                    <Button variant="outline" @click="openImportModal">
                        <Upload class="mr-2 h-4 w-4" />
                        Impor Excel
                    </Button>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Siswa
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <SearchInput
                    :model-value="filters.search || ''"
                    route-name="students.index"
                    placeholder="Cari berdasarkan nama atau email..."
                    :filters="{ class_id: filters.class_id }"
                />
                <Select :model-value="selectedClassFilter || 'all'" @update:model-value="filterByClass">
                    <SelectTrigger class="w-full sm:w-[200px]">
                        <SelectValue placeholder="Semua Kelas" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Kelas</SelectItem>
                        <SelectItem v-for="cls in classes" :key="cls.id" :value="String(cls.id)">
                            {{ cls.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Table -->
            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">#</th>
                                <th class="px-4 py-3 text-left font-medium">Nama</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Kelas</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in students.data" :key="student.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 text-muted-foreground">{{ students.from + index }}</td>
                                <td class="px-4 py-3 font-medium">{{ student.user.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ student.user.email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                        {{ student.class.title }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-950" @click="openEdit(student)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950" @click="confirmDelete(student)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="students.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Tidak ada siswa ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="students.total > 0" :links="students.links" :from="students.from" :to="students.to" :total="students.total" />
        </div>

        <!-- Create / Edit Modal -->
        <Dialog :open="showModal" @update:open="showModal = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ editingStudent ? 'Edit Siswa' : 'Tambah Siswa' }}</DialogTitle>
                    <DialogDescription>{{ editingStudent ? 'Perbarui detail siswa.' : 'Buat akun siswa baru.' }}</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap</Label>
                        <Input id="name" v-model="form.name" placeholder="Nama siswa" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" placeholder="email@siswa.com" />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password">
                            Kata Sandi
                            <span v-if="editingStudent" class="text-xs text-muted-foreground">(kosongkan jika tidak ingin mengubah)</span>
                        </Label>
                        <Input id="password" v-model="form.password" type="password" placeholder="Minimal 6 karakter" />
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="class_id">Kelas</Label>
                        <Select v-model="form.class_id" @update:model-value="(val: any) => form.class_id = val">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih kelas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="cls in classes" :key="cls.id" :value="String(cls.id)">{{ cls.title }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.class_id" class="text-sm text-destructive">{{ form.errors.class_id }}</p>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showModal = false" :disabled="form.processing">Batal</Button>
                        <Button type="submit" :disabled="form.processing">{{ editingStudent ? 'Perbarui' : 'Simpan' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Import Modal -->
        <Dialog :open="showImportModal" @update:open="showImportModal = $event">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Impor Siswa</DialogTitle>
                    <DialogDescription>
                        {{ importStep === 'upload' ? 'Unggah file Excel (.xlsx) untuk menambahkan siswa secara massal.' : 'Tinjau data sebelum mengimpor.' }}
                    </DialogDescription>
                </DialogHeader>

                <!-- Step 1: Upload -->
                <div v-if="importStep === 'upload'" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="import-file">File Excel</Label>
                        <Input id="import-file" type="file" accept=".xlsx,.xls" @change="handleFileChange" />
                    </div>
                    <div class="rounded-md border bg-muted/50 p-3">
                        <div class="flex items-start gap-2">
                            <FileSpreadsheet class="mt-0.5 h-4 w-4 text-green-600" />
                            <div class="text-xs text-muted-foreground">
                                <p class="font-medium text-foreground">Format Template:</p>
                                <p>Kolom: <code>nama</code>, <code>email</code>, <code>password</code>, <code>kelas</code></p>
                                <p>Baris pertama harus berisi header kolom.</p>
                                <a href="/students/template" class="mt-1 inline-flex items-center gap-1 text-primary hover:underline">
                                    <Download class="h-3 w-3" />
                                    Unduh template Excel
                                </a>
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showImportModal = false">Batal</Button>
                        <Button @click="previewImport" :disabled="!importFile || previewing">
                            {{ previewing ? 'Memproses...' : 'Pratinjau' }}
                        </Button>
                    </DialogFooter>
                </div>

                <!-- Step 2: Preview -->
                <div v-if="importStep === 'preview' && previewData" class="space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-1 rounded-md border bg-green-50 p-3 dark:bg-green-950/30">
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                                <span class="text-sm font-medium text-green-700 dark:text-green-400">{{ previewData.valid_count }} data valid</span>
                            </div>
                        </div>
                        <div v-if="previewData.invalid_count > 0" class="flex-1 rounded-md border bg-red-50 p-3 dark:bg-red-950/30">
                            <div class="flex items-center gap-2">
                                <XCircle class="h-4 w-4 text-red-600" />
                                <span class="text-sm font-medium text-red-700 dark:text-red-400">{{ previewData.invalid_count }} data tidak valid</span>
                            </div>
                        </div>
                    </div>

                    <!-- Invalid Rows -->
                    <div v-if="previewData.invalid.length > 0" class="space-y-2">
                        <h4 class="flex items-center gap-1.5 text-sm font-medium text-red-700 dark:text-red-400">
                            <AlertTriangle class="h-4 w-4" />
                            Data Tidak Valid (tidak akan diimpor)
                        </h4>
                        <div class="max-h-40 overflow-y-auto rounded-md border">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b bg-red-50/50 dark:bg-red-950/20">
                                        <th class="px-3 py-2 text-left font-medium">Baris</th>
                                        <th class="px-3 py-2 text-left font-medium">Nama</th>
                                        <th class="px-3 py-2 text-left font-medium">Email</th>
                                        <th class="px-3 py-2 text-left font-medium">Kelas</th>
                                        <th class="px-3 py-2 text-left font-medium">Kesalahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in previewData.invalid" :key="row.row" class="border-b">
                                        <td class="px-3 py-2">{{ row.row }}</td>
                                        <td class="px-3 py-2">{{ row.nama || '-' }}</td>
                                        <td class="px-3 py-2">{{ row.email || '-' }}</td>
                                        <td class="px-3 py-2">{{ row.kelas || '-' }}</td>
                                        <td class="px-3 py-2">
                                            <ul class="list-inside list-disc text-red-600 dark:text-red-400">
                                                <li v-for="(err, i) in row.errors" :key="i">{{ err }}</li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Valid Rows -->
                    <div v-if="previewData.valid.length > 0" class="space-y-2">
                        <h4 class="flex items-center gap-1.5 text-sm font-medium text-green-700 dark:text-green-400">
                            <CheckCircle2 class="h-4 w-4" />
                            Data Valid (siap diimpor)
                        </h4>
                        <div class="max-h-40 overflow-y-auto rounded-md border">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b bg-green-50/50 dark:bg-green-950/20">
                                        <th class="px-3 py-2 text-left font-medium">Baris</th>
                                        <th class="px-3 py-2 text-left font-medium">Nama</th>
                                        <th class="px-3 py-2 text-left font-medium">Email</th>
                                        <th class="px-3 py-2 text-left font-medium">Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in previewData.valid" :key="row.row" class="border-b">
                                        <td class="px-3 py-2">{{ row.row }}</td>
                                        <td class="px-3 py-2">{{ row.nama }}</td>
                                        <td class="px-3 py-2">{{ row.email }}</td>
                                        <td class="px-3 py-2">{{ row.kelas }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="importStep = 'upload'">Kembali</Button>
                        <Button @click="confirmImport" :disabled="importing || previewData.valid_count === 0">
                            {{ importing ? 'Mengimpor...' : `Impor ${previewData.valid_count} Siswa` }}
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus Siswa"
            :description="`Apakah Anda yakin ingin menghapus '${deletingStudent?.user?.name}'? Akun pengguna siswa ini juga akan dihapus.`"
            confirm-label="Hapus"
            @close="showDeleteDialog = false"
            @confirm="deleteStudent"
        />
    </AppLayout>
</template>

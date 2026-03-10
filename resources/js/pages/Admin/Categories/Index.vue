<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Plus, Edit, Trash2, FolderTree, Folder } from 'lucide-vue-next';

interface SubCategory {
    id: number;
    category_id: number;
    title: string;
    desc: string | null;
}

interface Category {
    id: number;
    title: string;
    desc: string | null;
    sub_categories: SubCategory[];
}

const props = defineProps<{
    categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kategori Soal', href: '/categories' },
];

// --- Category Form ---
const showCategoryModal = ref(false);
const editingCategory = ref<Category | null>(null);
const categoryForm = useForm({
    title: '',
    desc: '',
});

function openCreateCategory() {
    editingCategory.value = null;
    categoryForm.reset();
    categoryForm.clearErrors();
    showCategoryModal.value = true;
}

function openEditCategory(category: Category) {
    editingCategory.value = category;
    categoryForm.title = category.title;
    categoryForm.desc = category.desc || '';
    categoryForm.clearErrors();
    showCategoryModal.value = true;
}

function saveCategory() {
    if (editingCategory.value) {
        categoryForm.put(`/categories/${editingCategory.value.id}`, {
            onSuccess: () => {
                showCategoryModal.value = false;
            },
        });
    } else {
        categoryForm.post('/categories', {
            onSuccess: () => {
                showCategoryModal.value = false;
            },
        });
    }
}

// --- SubCategory Form ---
const showSubCategoryModal = ref(false);
const editingSubCategory = ref<SubCategory | null>(null);
const activeParentCategory = ref<Category | null>(null);
const subCategoryForm = useForm({
    title: '',
    desc: '',
});

function openCreateSubCategory(category: Category) {
    editingSubCategory.value = null;
    activeParentCategory.value = category;
    subCategoryForm.reset();
    subCategoryForm.clearErrors();
    showSubCategoryModal.value = true;
}

function openEditSubCategory(subCategory: SubCategory, category: Category) {
    editingSubCategory.value = subCategory;
    activeParentCategory.value = category;
    subCategoryForm.title = subCategory.title;
    subCategoryForm.desc = subCategory.desc || '';
    subCategoryForm.clearErrors();
    showSubCategoryModal.value = true;
}

function saveSubCategory() {
    if (editingSubCategory.value) {
        subCategoryForm.put(`/sub-categories/${editingSubCategory.value.id}`, {
            onSuccess: () => {
                showSubCategoryModal.value = false;
            },
        });
    } else if (activeParentCategory.value) {
        subCategoryForm.post(`/categories/${activeParentCategory.value.id}/sub-categories`, {
            onSuccess: () => {
                showSubCategoryModal.value = false;
            },
        });
    }
}

// --- Delete ---
const itemToDelete = ref<{ type: 'category' | 'subcategory'; id: number; title: string } | null>(null);
const showDeleteConfirm = ref(false);

function confirmDeleteCategory(category: Category) {
    itemToDelete.value = { type: 'category', id: category.id, title: category.title };
    showDeleteConfirm.value = true;
}

function confirmDeleteSubCategory(subCategory: SubCategory) {
    itemToDelete.value = { type: 'subcategory', id: subCategory.id, title: subCategory.title };
    showDeleteConfirm.value = true;
}

function handleDelete() {
    if (!itemToDelete.value) return;

    const url = itemToDelete.value.type === 'category' 
        ? `/categories/${itemToDelete.value.id}` 
        : `/sub-categories/${itemToDelete.value.id}`;
        
    router.delete(url, {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            itemToDelete.value = null;
        },
    });
}
</script>

<template>
    <Head title="Kategori Soal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kategori & Sub Kategori Soal</h1>
                    <p class="text-sm text-muted-foreground">Kelola struktur kategori untuk Bank Soal.</p>
                </div>
                <Button @click="openCreateCategory">
                    <Plus class="w-4 h-4 mr-2" /> Kategori Baru
                </Button>
            </div>

            <Card v-if="categories.length === 0" class="flex items-center justify-center p-8 text-center bg-muted/30">
                <div class="flex flex-col items-center gap-2">
                    <FolderTree class="h-8 w-8 text-muted-foreground" />
                    <h3 class="font-medium text-lg">Belum ada kategori</h3>
                    <p class="text-sm text-muted-foreground">Mulai dengan menambahkan kategori utama pertama Anda.</p>
                    <Button variant="outline" class="mt-4" @click="openCreateCategory">Buat Kategori</Button>
                </div>
            </Card>

            <Accordion v-else type="multiple" class="w-full space-y-4">
                <AccordionItem
                    v-for="category in categories"
                    :key="category.id"
                    :value="`category-${category.id}`"
                    class="border rounded-lg bg-card text-card-foreground shadow-sm px-4"
                >
                    <AccordionTrigger class="hover:no-underline py-4">
                        <div class="flex items-center justify-between w-full pr-4">
                            <div class="flex items-center gap-3 text-left">
                                <Folder class="h-5 w-5 text-primary" />
                                <div>
                                    <h3 class="font-semibold text-base">{{ category.title }}</h3>
                                    <p class="text-xs text-muted-foreground font-normal line-clamp-1">{{ category.desc || 'Tidak ada deskripsi' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2" @click.stop>
                                <Button variant="ghost" size="sm" @click="openCreateSubCategory(category)">
                                    <Plus class="w-4 h-4 mr-1" /> Sub
                                </Button>
                                <Button variant="ghost" size="icon" @click="openEditCategory(category)">
                                    <Edit class="w-4 h-4 text-blue-500" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="confirmDeleteCategory(category)">
                                    <Trash2 class="w-4 h-4 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="pt-0 pb-4 border-t mt-2">
                        <div class="pl-8 pr-4 pt-4 space-y-3">
                            <div v-if="category.sub_categories.length === 0" class="text-sm text-muted-foreground italic py-2">
                                Belum ada sub-kategori.
                            </div>
                            <div 
                                v-for="sub in category.sub_categories" 
                                :key="sub.id"
                                class="flex items-center justify-between p-3 rounded-md bg-muted/50 border hover:bg-muted/80 transition-colors"
                            >
                                <div>
                                    <div class="font-medium text-sm">{{ sub.title }}</div>
                                    <div class="text-xs text-muted-foreground">{{ sub.desc }}</div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditSubCategory(sub, category)">
                                        <Edit class="w-3.5 h-3.5 text-blue-500" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="confirmDeleteSubCategory(sub)">
                                        <Trash2 class="w-3.5 h-3.5 text-destructive" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>

        <!-- Category Modal -->
        <Dialog :open="showCategoryModal" @update:open="showCategoryModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingCategory ? 'Edit Kategori' : 'Tambah Kategori' }}</DialogTitle>
                    <DialogDescription>
                        Kategori utama untuk mengelompokkan bank soal.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveCategory" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">Judul Kategori</Label>
                        <Input id="title" v-model="categoryForm.title" placeholder="Misal: Matematika Dasar" :class="{ 'border-destructive': categoryForm.errors.title }" />
                        <p v-if="categoryForm.errors.title" class="text-xs text-destructive">{{ categoryForm.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="desc">Deskripsi (Opsional)</Label>
                        <Textarea id="desc" v-model="categoryForm.desc" placeholder="Penjelasan singkat kategori ini" rows="3" :class="{ 'border-destructive': categoryForm.errors.desc }" />
                        <p v-if="categoryForm.errors.desc" class="text-xs text-destructive">{{ categoryForm.errors.desc }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showCategoryModal = false">Batal</Button>
                        <Button type="submit" :disabled="categoryForm.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- SubCategory Modal -->
        <Dialog :open="showSubCategoryModal" @update:open="showSubCategoryModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingSubCategory ? 'Edit Sub Kategori' : 'Tambah Sub Kategori' }}</DialogTitle>
                    <DialogDescription v-if="activeParentCategory">
                        Sub kategori untuk <strong>{{ activeParentCategory.title }}</strong>.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveSubCategory" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="sub-title">Judul Sub Kategori</Label>
                        <Input id="sub-title" v-model="subCategoryForm.title" placeholder="Misal: Aljabar Linear" :class="{ 'border-destructive': subCategoryForm.errors.title }" />
                        <p v-if="subCategoryForm.errors.title" class="text-xs text-destructive">{{ subCategoryForm.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="sub-desc">Deskripsi (Opsional)</Label>
                        <Textarea id="sub-desc" v-model="subCategoryForm.desc" placeholder="Penjelasan singkat sub kategori" rows="3" :class="{ 'border-destructive': subCategoryForm.errors.desc }" />
                        <p v-if="subCategoryForm.errors.desc" class="text-xs text-destructive">{{ subCategoryForm.errors.desc }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showSubCategoryModal = false">Batal</Button>
                        <Button type="submit" :disabled="subCategoryForm.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :show="showDeleteConfirm"
            :title="`Hapus ${itemToDelete?.type === 'category' ? 'Kategori' : 'Sub Kategori'}`"
            :message="`Apakah Anda yakin ingin menghapus '${itemToDelete?.title}'? Semua ${itemToDelete?.type === 'category' ? 'sub-kategori dan ' : ''}soal yang terhubung akan ikut terhapus permanen.`"
            confirm-text="Hapus Permanen"
            @close="showDeleteConfirm = false"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>

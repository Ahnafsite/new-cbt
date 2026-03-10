<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { BreadcrumbItem } from '@/types';

interface RoomData {
    id: number;
    code: string;
    name: string;
}

interface Props {
    rooms: {
        data: RoomData[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ruangan', href: '/rooms' },
];

const showModal = ref(false);
const showDeleteDialog = ref(false);
const editingRoom = ref<RoomData | null>(null);
const deletingRoom = ref<RoomData | null>(null);

const form = useForm({
    code: '',
    name: '',
});

function openCreate() {
    editingRoom.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(room: RoomData) {
    editingRoom.value = room;
    form.code = room.code;
    form.name = room.name;
    form.clearErrors();
    showModal.value = true;
}

function submit() {
    if (editingRoom.value) {
        form.put(`/rooms/${editingRoom.value.id}`, {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post('/rooms', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}

function confirmDelete(room: RoomData) {
    deletingRoom.value = room;
    showDeleteDialog.value = true;
}

function deleteRoom() {
    if (!deletingRoom.value) return;
    router.delete(`/rooms/${deletingRoom.value.id}`, {
        onSuccess: () => { showDeleteDialog.value = false; deletingRoom.value = null; },
    });
}
</script>

<template>
    <Head title="Ruangan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Ruangan</h1>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Ruangan
                </Button>
            </div>

            <div class="flex items-center gap-4">
                <SearchInput
                    :model-value="filters.search || ''"
                    route-name="rooms.index"
                    placeholder="Cari ruangan..."
                />
            </div>

            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">#</th>
                                <th class="px-4 py-3 text-left font-medium">Kode</th>
                                <th class="px-4 py-3 text-left font-medium">Nama</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(room, index) in rooms.data" :key="room.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 text-muted-foreground">{{ rooms.from + index }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md bg-muted px-2 py-1 text-xs font-mono font-medium">{{ room.code }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium">{{ room.name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-950" @click="openEdit(room)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950" @click="confirmDelete(room)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="rooms.data.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">Tidak ada ruangan ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="rooms.total > 0" :links="rooms.links" :from="rooms.from" :to="rooms.to" :total="rooms.total" />
        </div>

        <Dialog :open="showModal" @update:open="showModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingRoom ? 'Edit Ruangan' : 'Tambah Ruangan' }}</DialogTitle>
                    <DialogDescription>{{ editingRoom ? 'Perbarui detail ruangan.' : 'Tambah ruangan ujian baru.' }}</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="code">Kode</Label>
                        <Input id="code" v-model="form.code" placeholder="Contoh: R-001" />
                        <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="name">Nama</Label>
                        <Input id="name" v-model="form.name" placeholder="Contoh: Lab Komputer 1" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showModal = false" :disabled="form.processing">Batal</Button>
                        <Button type="submit" :disabled="form.processing">{{ editingRoom ? 'Perbarui' : 'Simpan' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus Ruangan"
            :description="`Apakah Anda yakin ingin menghapus ruangan '${deletingRoom?.name}'?`"
            confirm-label="Hapus"
            @close="showDeleteDialog = false"
            @confirm="deleteRoom"
        />
    </AppLayout>
</template>

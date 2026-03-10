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

interface UserData {
    id: number;
    name: string;
    email: string;
    roles: { id: number; name: string }[];
}

interface RoleOption {
    id: number;
    name: string;
}

interface Props {
    users: {
        data: UserData[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
    roles: RoleOption[];
    filters: {
        search?: string;
        role?: string;
    };
}

const props = defineProps<Props>();


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengguna', href: '/users' },
];

const showModal = ref(false);
const showDeleteDialog = ref(false);
const editingUser = ref<UserData | null>(null);
const deletingUser = ref<UserData | null>(null);
const selectedRoleFilter = ref(props.filters.role || '');

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
});

function filterByRole(value: string) {
    selectedRoleFilter.value = value;
    router.get('/users', {
        search: props.filters.search || undefined,
        role: value === 'all' ? undefined : value || undefined,
    }, { preserveState: true, replace: true });
}

function openCreate() {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(user: UserData) {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.roles[0]?.name || '';
    form.clearErrors();
    showModal.value = true;
}

function submit() {
    if (editingUser.value) {
        form.put(`/users/${editingUser.value.id}`, {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post('/users', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
}

function confirmDelete(user: UserData) {
    deletingUser.value = user;
    showDeleteDialog.value = true;
}

function deleteUser() {
    if (!deletingUser.value) return;
    router.delete(`/users/${deletingUser.value.id}`, {
        onSuccess: () => { showDeleteDialog.value = false; deletingUser.value = null; },
    });
}

function roleBadgeClass(role: string) {
    const map: Record<string, string> = {
        super_admin: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        teacher: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        student: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    };
    return map[role] || 'bg-muted text-muted-foreground';
}
</script>

<template>
    <Head title="Pengguna" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Pengguna</h1>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Pengguna
                </Button>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <SearchInput
                    :model-value="filters.search || ''"
                    route-name="users.index"
                    placeholder="Cari pengguna..."
                    :filters="{ role: filters.role }"
                />
                <Select :model-value="selectedRoleFilter || 'all'" @update:model-value="filterByRole">
                    <SelectTrigger class="w-full sm:w-[180px]">
                        <SelectValue placeholder="Semua Peran" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Peran</SelectItem>
                        <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                            {{ role.name.replace('_', ' ') }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">#</th>
                                <th class="px-4 py-3 text-left font-medium">Nama</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Peran</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in users.data" :key="user.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 text-muted-foreground">{{ users.from + index }}</td>
                                <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="roleBadgeClass(role.name)"
                                    >
                                        <Shield class="mr-1 h-3 w-3" />
                                        {{ role.name.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-950" @click="openEdit(user)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950" @click="confirmDelete(user)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">Tidak ada pengguna ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="users.total > 0" :links="users.links" :from="users.from" :to="users.to" :total="users.total" />
        </div>

        <Dialog :open="showModal" @update:open="showModal = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ editingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}</DialogTitle>
                    <DialogDescription>{{ editingUser ? 'Perbarui akun pengguna.' : 'Buat akun pengguna baru.' }}</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nama</Label>
                        <Input id="name" v-model="form.name" placeholder="Nama lengkap" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" placeholder="user@email.com" />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password">
                            Kata Sandi
                            <span v-if="editingUser" class="text-xs text-muted-foreground">(kosongkan jika tidak ingin mengubah)</span>
                        </Label>
                        <Input id="password" v-model="form.password" type="password" placeholder="Minimal 6 karakter" />
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="role">Peran</Label>
                        <Select v-model="form.role" @update:model-value="(val: any) => form.role = val">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih peran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name.replace('_', ' ') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showModal = false" :disabled="form.processing">Batal</Button>
                        <Button type="submit" :disabled="form.processing">{{ editingUser ? 'Perbarui' : 'Simpan' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus Pengguna"
            :description="`Apakah Anda yakin ingin menghapus '${deletingUser?.name}'?`"
            confirm-label="Hapus"
            @close="showDeleteDialog = false"
            @confirm="deleteUser"
        />
    </AppLayout>
</template>

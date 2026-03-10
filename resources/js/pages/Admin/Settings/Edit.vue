<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Save, ImageIcon } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

interface SettingData {
    id: number;
    school_name: string | null;
    address: string | null;
    city: string;
    phone: string | null;
    email: string | null;
    logo: string | null;
    chairman: string;
    nip: string | null;
}

interface Props {
    setting: SettingData;
}

const props = defineProps<Props>();


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pengaturan', href: '/admin/settings' },
];

const form = useForm({
    _method: 'PUT',
    school_name: props.setting.school_name || '',
    address: props.setting.address || '',
    city: props.setting.city || '',
    phone: props.setting.phone || '',
    email: props.setting.email || '',
    logo: null as File | null,
    chairman: props.setting.chairman || '',
    nip: props.setting.nip || '',
});

const logoPreview = ref<string | null>(null);

const displayLogo = computed(() => {
    if (logoPreview.value) return logoPreview.value;
    if (props.setting.logo) return `/storage/${props.setting.logo}`;
    return null;
});

function handleLogoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.logo = target.files[0];
        // Create preview URL
        logoPreview.value = URL.createObjectURL(target.files[0]);
    }
}

function submit() {
    form.post('/admin/settings', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            logoPreview.value = null;
        },
    });
}
</script>

<template>
    <Head title="Pengaturan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Pengaturan Sekolah</h1>
            </div>

            <div class="max-w-2xl rounded-lg border bg-card p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <!-- Logo -->
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="logo">Logo Sekolah</Label>
                            <div class="flex items-start gap-4">
                                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted">
                                    <img
                                        v-if="displayLogo"
                                        :src="displayLogo"
                                        alt="Logo Sekolah"
                                        class="h-full w-full object-cover"
                                    />
                                    <ImageIcon v-else class="h-8 w-8 text-muted-foreground" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <Input id="logo" type="file" accept="image/*" @change="handleLogoChange" />
                                    <p class="text-xs text-muted-foreground">Format: JPG, PNG, SVG. Maksimal 2MB.</p>
                                    <p v-if="form.errors.logo" class="text-sm text-destructive">{{ form.errors.logo }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="school_name">Nama Sekolah</Label>
                            <Input id="school_name" v-model="form.school_name" placeholder="Nama sekolah" />
                            <p v-if="form.errors.school_name" class="text-sm text-destructive">{{ form.errors.school_name }}</p>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="address">Alamat</Label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="Alamat sekolah"
                            />
                            <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="city">Kota</Label>
                            <Input id="city" v-model="form.city" placeholder="Kota" />
                            <p v-if="form.errors.city" class="text-sm text-destructive">{{ form.errors.city }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Telepon</Label>
                            <Input id="phone" v-model="form.phone" placeholder="Nomor telepon" />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="Email sekolah" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="chairman">Kepala Sekolah</Label>
                            <Input id="chairman" v-model="form.chairman" placeholder="Nama kepala sekolah" />
                            <p v-if="form.errors.chairman" class="text-sm text-destructive">{{ form.errors.chairman }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="nip">NIP</Label>
                            <Input id="nip" v-model="form.nip" placeholder="Nomor NIP" />
                            <p v-if="form.errors.nip" class="text-sm text-destructive">{{ form.errors.nip }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-2 h-4 w-4" />
                            Simpan Pengaturan
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

defineProps<{
    show: boolean;
    title?: string;
    description?: string;
    confirmLabel?: string;
    confirmVariant?: 'default' | 'destructive';
    processing?: boolean;
}>();

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Dialog :open="show" @update:open="emit('close')">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title || 'Apakah Anda yakin?' }}</DialogTitle>
                <DialogDescription>
                    {{ description || 'Tindakan ini tidak dapat dibatalkan.' }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="emit('close')" :disabled="processing">
                    Batal
                </Button>
                <Button
                    :variant="confirmVariant || 'destructive'"
                    @click="emit('confirm')"
                    :disabled="processing"
                >
                    {{ confirmLabel || 'Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

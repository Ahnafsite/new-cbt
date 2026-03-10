<script setup lang="ts">
import { ref, watch } from 'vue';
import { UploadCloud, X, FileImage } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: File[];
    existingImages?: { id: number; path: string; url: string }[];
    maxFiles?: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', files: File[]): void;
    (e: 'removeExisting', id: number): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const previewUrls = ref<{ file: File; url: string }[]>([]);

// Generate previews for newly selected files
watch(
    () => props.modelValue,
    (files) => {
        // Clean up old object URLs
        previewUrls.value.forEach((p) => URL.revokeObjectURL(p.url));
        
        previewUrls.value = files.map(file => ({
            file,
            url: URL.createObjectURL(file)
        }));
    },
    { deep: true }
);

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (!target.files?.length) return;
    
    addFiles(Array.from(target.files));
    // Reset input so same file can be selected again if removed
    if (fileInput.value) fileInput.value.value = '';
}

function handleDrop(event: DragEvent) {
    isDragging.value = false;
    if (!event.dataTransfer?.files.length) return;
    addFiles(Array.from(event.dataTransfer.files));
}

function addFiles(newFiles: File[]) {
    const validImageFiles = newFiles.filter(file => file.type.startsWith('image/'));
    
    if (props.maxFiles) {
        const totalExisting = props.existingImages?.length || 0;
        const currentFilesCount = props.modelValue.length;
        const availableSlots = props.maxFiles - (totalExisting + currentFilesCount);
        
        if (availableSlots <= 0) return;
        validImageFiles.splice(availableSlots);
    }

    emit('update:modelValue', [...props.modelValue, ...validImageFiles]);
}

function removeFile(index: number) {
    const newFiles = [...props.modelValue];
    newFiles.splice(index, 1);
    emit('update:modelValue', newFiles);
}

function removeExisting(id: number) {
    emit('removeExisting', id);
}

const triggerFileInput = () => fileInput.value?.click();
</script>

<template>
    <div class="space-y-4">
        <div
            class="relative flex flex-col items-center justify-center w-full min-h-[150px] border-2 border-dashed rounded-lg transition-colors"
            :class="[
                isDragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/25 hover:bg-muted/50 hover:border-primary/50',
                ((props.modelValue.length + (existingImages?.length || 0)) >= (maxFiles || 5)) ? 'opacity-50 pointer-events-none' : 'cursor-pointer'
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
        >
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                class="hidden"
                @change="handleFileSelect"
            />
            
            <div class="flex flex-col items-center justify-center py-6 text-center px-4">
                <div class="p-3 mb-3 bg-muted rounded-full">
                    <UploadCloud class="w-6 h-6 text-muted-foreground" />
                </div>
                <p class="mb-1 text-sm font-semibold">Klik untuk unggah atau seret dan lepas</p>
                <p class="text-xs text-muted-foreground">
                    SVG, PNG, JPG atau GIF (Maks. {{ maxFiles || 5 }} gambar)
                </p>
            </div>
        </div>

        <!-- Previews -->
        <div v-if="previewUrls.length > 0 || (existingImages && existingImages.length > 0)" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <!-- Existing Images -->
            <div v-for="img in existingImages" :key="img.id" class="relative group aspect-square rounded-md overflow-hidden bg-muted border">
                <img :src="img.url" class="object-cover w-full h-full" alt="Existing question image" />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <Button variant="destructive" size="icon" class="w-8 h-8 rounded-full" @click="removeExisting(img.id)">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
            </div>

            <!-- New Image Previews -->
            <div v-for="(preview, index) in previewUrls" :key="preview.url" class="relative group aspect-square rounded-md overflow-hidden bg-muted border">
                <img :src="preview.url" class="object-cover w-full h-full" alt="Preview" />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <Button variant="destructive" size="icon" class="w-8 h-8 rounded-full" @click="removeFile(index)">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
                <div class="absolute top-1 left-1 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded backdrop-blur-sm">
                    Baru
                </div>
            </div>
        </div>
    </div>
</template>

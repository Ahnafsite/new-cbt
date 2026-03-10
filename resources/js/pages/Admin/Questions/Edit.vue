<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Save, ArrowLeft, Trash2, Plus } from 'lucide-vue-next';
import WysiwygEditor from '@/components/WysiwygEditor.vue';
import ImageDropzone from '@/components/ImageDropzone.vue';

interface SubCategory {
    id: number;
    category_id: number;
    title: string;
}

interface Category {
    id: number;
    title: string;
    sub_categories: SubCategory[];
}

interface QuestionImage {
    id: number;
    path: string;
}

interface QuestionAnswer {
    id: number;
    title: string;
    image: string | null;
    is_true: boolean;
}

interface Question {
    id: number;
    sub_category_id: number;
    type: string;
    title: string;
    point: number;
    difficulty: number;
    images: QuestionImage[];
    answers: QuestionAnswer[];
}

const props = defineProps<{
    question: Question;
    categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bank Soal', href: '/questions' },
    { title: 'Edit Soal', href: `/questions/${props.question.id}/edit` },
];

const mappedExistingImages = props.question.images.map(img => ({
    id: img.id,
    path: img.path,
    url: `/storage/${img.path}`
}));

// Identify the parent category for the selected sub_category manually on load
const getInitialCategoryId = () => {
    for (const cat of props.categories) {
        if (cat.sub_categories.some(sub => sub.id === props.question.sub_category_id)) {
            return String(cat.id);
        }
    }
    return null;
};

const selectedCategory = ref<string | null>(getInitialCategoryId());

const filteredSubCategories = computed(() => {
    if (!selectedCategory.value) return [];
    const cat = props.categories.find(c => String(c.id) === selectedCategory.value);
    return cat ? cat.sub_categories : [];
});

const initialAnswers = props.question.answers.length > 0 ? props.question.answers.map((ans) => ({
    title: ans.title,
    image: null as File | null,
    existing_image_path: ans.image,
    is_true: ans.is_true,
})) : [
    { title: '', image: null as File | null, existing_image_path: null as string | null, is_true: true },
    { title: '', image: null as File | null, existing_image_path: null as string | null, is_true: false },
    { title: '', image: null as File | null, existing_image_path: null as string | null, is_true: false },
    { title: '', image: null as File | null, existing_image_path: null as string | null, is_true: false },
];

const form = useForm({
    _method: 'put',
    type: props.question.type,
    sub_category_id: String(props.question.sub_category_id),
    title: props.question.title,
    point: props.question.point,
    difficulty: props.question.difficulty,
    question_images: [] as File[],
    replace_images: false,
    answers: initialAnswers,
});

watch(selectedCategory, (newVal, oldVal) => {
    if (oldVal !== null) {
        form.sub_category_id = ''; // Only clear if the user actively changes it after initial load
    }
});

function addAnswerRow() {
    form.answers.push({ title: '', image: null, existing_image_path: null, is_true: false });
}

function removeAnswerRow(index: number) {
    if (form.answers.length <= 2) {
        alert('Soal pilihan ganda minimal memiliki 2 opsi jawaban.');
        return;
    }
    
    const removingTrue = form.answers[index].is_true;
    form.answers.splice(index, 1);
    
    if (removingTrue && form.answers.length > 0) {
        form.answers[0].is_true = true;
    }
}

function selectTrueAnswer(index: number) {
    form.answers.forEach((ans, i) => {
        ans.is_true = i === index;
    });
}

function handleAnswerImageChange(event: Event, index: number) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.answers[index].image = target.files[0];
        form.answers[index].existing_image_path = null;
    } else {
        form.answers[index].image = null;
    }
}

function removeAnswerImage(index: number, fileInputRef: HTMLInputElement | null) {
    form.answers[index].image = null;
    form.answers[index].existing_image_path = null;
    if (fileInputRef) {
        fileInputRef.value = '';
    }
}

function onQuestionImagesUpdated() {
    form.replace_images = true;
}

function removeExistingQuestionImage(id: number) {
    alert("Untuk mengubah gambar soal saat ini, cukup unggah kumpulan gambar baru. Gambar lama akan otomatis terganti.");
}

function submit() {
    form.post(`/questions/${props.question.id}`, {
        forceFormData: true,
        onError: (errors) => {
            console.error('Submission errors:', errors);
        }
    });
}
</script>

<template>
    <Head title="Edit Soal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 lg:p-6 lg:max-w-5xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="router.get('/questions')">
                    <ArrowLeft class="w-4 h-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Soal</h1>
                    <p class="text-sm text-muted-foreground">Perbarui informasi soal dan opsi jawaban yang ada.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Data Master Soal -->
                <Card>
                    <CardHeader>
                        <CardTitle>Data Utama Soal</CardTitle>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Kategori Induk <span class="text-destructive">*</span></Label>
                            <Select v-model="selectedCategory">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Kategori Utama" />
                                </SelectTrigger>
                                <SelectContent>
                                    <template v-for="cat in categories" :key="cat.id">
                                        <SelectItem :value="String(cat.id)">
                                            {{ cat.title }}
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="sub_category_id">Sub Kategori Ujian <span class="text-destructive">*</span></Label>
                            <Select v-model="form.sub_category_id" :disabled="!selectedCategory">
                                <SelectTrigger :class="{ 'border-destructive': form.errors.sub_category_id }">
                                    <SelectValue placeholder="Pilih sub kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <template v-for="sub in filteredSubCategories" :key="sub.id">
                                        <SelectItem :value="String(sub.id)">
                                            {{ sub.title }}
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.sub_category_id" class="text-xs text-destructive">{{ form.errors.sub_category_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Tipe Pertanyaan <span class="text-destructive">*</span></Label>
                            <Select v-model="form.type">
                                <SelectTrigger :class="{ 'border-destructive': form.errors.type }">
                                    <SelectValue placeholder="Pilih tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="multiple_choice">Pilihan Ganda</SelectItem>
                                    <SelectItem value="true_false">Benar / Salah</SelectItem>
                                    <SelectItem value="short_essay">Isian Singkat</SelectItem>
                                    <SelectItem value="essay">Uraian</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.type" class="text-xs text-destructive">{{ form.errors.type }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="difficulty">Tingkat Kesulitan</Label>
                            <Select v-model="form.difficulty" :model-value="String(form.difficulty)" @update:model-value="(v) => form.difficulty = Number(v)">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">1 - Sangat Mudah</SelectItem>
                                    <SelectItem value="2">2 - Mudah</SelectItem>
                                    <SelectItem value="3">3 - Sedang</SelectItem>
                                    <SelectItem value="4">4 - Sulit</SelectItem>
                                    <SelectItem value="5">5 - Sangat Sulit</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.difficulty" class="text-xs text-destructive">{{ form.errors.difficulty }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="point">Bobot Poin</Label>
                            <Input id="point" type="number" min="1" v-model.number="form.point" :class="{ 'border-destructive': form.errors.point }" />
                            <p v-if="form.errors.point" class="text-xs text-destructive">{{ form.errors.point }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Konten Soal -->
                <Card>
                    <CardHeader>
                        <CardTitle>Isi Pertanyaan</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2 relative">
                            <Label>Teks Pertanyaan <span class="text-destructive">*</span></Label>
                            <WysiwygEditor v-model="form.title" />
                            <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="flex flex-col">
                                <span>Gambar Pertanyaan</span>
                                <span class="text-xs text-muted-foreground font-normal mt-0.5">Mengunggah gambar baru akan menggantikan semua gambar sebelumnya.</span>
                            </Label>
                            <ImageDropzone 
                                v-model="form.question_images" 
                                :existing-images="form.replace_images ? [] : mappedExistingImages" 
                                :max-files="5" 
                                @update:model-value="onQuestionImagesUpdated"
                                @remove-existing="removeExistingQuestionImage"
                            />
                            <p v-if="form.errors.question_images" class="text-xs text-destructive">{{ form.errors.question_images }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Opsi Jawaban: Pilihan Ganda -->
                <Card v-if="form.type === 'multiple_choice'">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Opsi Jawaban</CardTitle>
                        </div>
                        <Button type="button" variant="outline" size="sm" @click="addAnswerRow">
                            <Plus class="w-4 h-4 mr-2" /> Tambah Opsi
                        </Button>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p v-if="form.errors.answers" class="text-sm font-medium text-destructive mb-2">{{ form.errors.answers }}</p>
                        
                        <div
                            v-for="(answer, index) in form.answers"
                            :key="index"
                            class="flex flex-col sm:flex-row gap-4 items-start border p-4 rounded-lg relative"
                            :class="{ 'border-green-500 bg-green-500/5': answer.is_true }"
                        >
                            <div class="pt-2">
                                <RadioGroup :model-value="answer.is_true ? 'true' : 'false'" @update:model-value="selectTrueAnswer(index)">
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem value="true" :id="`answer-correct-${index}`" />
                                    </div>
                                </RadioGroup>
                            </div>
                            
                            <div class="flex-1 grid grid-cols-1 gap-3 w-full">
                                <div class="flex items-center justify-between">
                                    <Label class="font-semibold" :for="`answer-text-${index}`">
                                        Jawaban {{ String.fromCharCode(65 + index) }}
                                        <span v-if="answer.is_true" class="ml-2 text-xs text-green-600 font-bold bg-green-100 px-2 py-0.5 rounded">BENAR</span>
                                    </Label>
                                    <Button v-if="form.answers.length > 2" type="button" variant="ghost" size="icon" class="text-destructive h-6 w-6" @click="removeAnswerRow(index)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                                <Input
                                    :id="`answer-text-${index}`"
                                    type="text"
                                    placeholder="Teks Opsi Jawaban..."
                                    v-model="answer.title"
                                    :class="{ 'border-destructive': form.errors[`answers.${index}.title`] }"
                                />
                                <p v-if="form.errors[`answers.${index}.title`]" class="text-xs text-destructive mt-1">{{ form.errors[`answers.${index}.title`] }}</p>
                                
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="flex-1">
                                        <Label :for="`answer-img-${index}`" class="text-xs mb-1 block text-muted-foreground w-max cursor-pointer">UNGGAH GAMBAR BARU</Label>
                                        <Input
                                            :id="`answer-img-${index}`"
                                            type="file"
                                            accept="image/*"
                                            class="text-xs"
                                            @change="(e) => handleAnswerImageChange(e, index)"
                                        />
                                    </div>
                                    <div v-if="answer.image" class="w-12 h-12 border rounded shrink-0 relative overflow-hidden bg-muted group">
                                        <img :src="URL.createObjectURL(answer.image)" class="w-full h-full object-cover" />
                                        <button type="button" @click="() => removeAnswerImage(index, document.getElementById(`answer-img-${index}`) as HTMLInputElement)" class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                        <div class="absolute top-0 right-0 bg-primary text-white text-[8px] px-1">Baru</div>
                                    </div>
                                    <div v-else-if="answer.existing_image_path" class="w-12 h-12 border rounded shrink-0 relative overflow-hidden bg-muted group">
                                        <img :src="`/storage/${answer.existing_image_path}`" class="w-full h-full object-cover" />
                                        <button type="button" @click="() => removeAnswerImage(index, null)" class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex items-center justify-end gap-4 pb-12">
                    <Button type="button" variant="outline" @click="router.get('/questions')" :disabled="form.processing">Batal</Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

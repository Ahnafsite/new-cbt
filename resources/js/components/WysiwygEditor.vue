<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { watch, onBeforeUnmount } from 'vue';
import {
    Bold, IterationCcw, Italic, Strikethrough, Code, Heading1, Heading2, List, ListOrdered, Quote, Undo, Redo, Minus
} from 'lucide-vue-next';
import { Toggle } from '@/components/ui/toggle';
import { Separator } from '@/components/ui/separator';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [StarterKit],
    editorProps: {
        attributes: {
            class: 'min-h-[150px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 prose prose-sm dark:prose-invert max-w-none',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        const isSame = editor.value?.getHTML() === value;
        if (isSame) return;
        editor.value?.commands.setContent(value, false);
    }
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<template>
    <div class="flex flex-col gap-2 relative">
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-1 rounded-md border bg-muted/40 sticky top-0 z-10">
            <Toggle size="sm" :pressed="editor.isActive('bold')" @update:pressed="editor.chain().focus().toggleBold().run()">
                <Bold class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" :pressed="editor.isActive('italic')" @update:pressed="editor.chain().focus().toggleItalic().run()">
                <Italic class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" :pressed="editor.isActive('strike')" @update:pressed="editor.chain().focus().toggleStrike().run()">
                <Strikethrough class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" :pressed="editor.isActive('code')" @update:pressed="editor.chain().focus().toggleCode().run()">
                <Code class="w-4 h-4" />
            </Toggle>
            
            <Separator orientation="vertical" class="h-6 mx-1" />
            
            <Toggle size="sm" :pressed="editor.isActive('heading', { level: 1 })" @update:pressed="editor.chain().focus().toggleHeading({ level: 1 }).run()">
                <Heading1 class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" :pressed="editor.isActive('heading', { level: 2 })" @update:pressed="editor.chain().focus().toggleHeading({ level: 2 }).run()">
                <Heading2 class="w-4 h-4" />
            </Toggle>

            <Separator orientation="vertical" class="h-6 mx-1" />

            <Toggle size="sm" :pressed="editor.isActive('bulletList')" @update:pressed="editor.chain().focus().toggleBulletList().run()">
                <List class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" :pressed="editor.isActive('orderedList')" @update:pressed="editor.chain().focus().toggleOrderedList().run()">
                <ListOrdered class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" :pressed="editor.isActive('blockquote')" @update:pressed="editor.chain().focus().toggleBlockquote().run()">
                <Quote class="w-4 h-4" />
            </Toggle>
            
            <Separator orientation="vertical" class="h-6 mx-1" />

            <Toggle size="sm" @update:pressed="editor.chain().focus().setHorizontalRule().run()">
                <Minus class="w-4 h-4" />
            </Toggle>

            <Separator orientation="vertical" class="h-6 mx-1" />

            <Toggle size="sm" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()">
                <Undo class="w-4 h-4" />
            </Toggle>
            <Toggle size="sm" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()">
                <Redo class="w-4 h-4" />
            </Toggle>
        </div>
        
        <EditorContent :editor="editor" class="focus-within:ring-1 focus-within:ring-ring rounded-md overflow-hidden" />
    </div>
</template>

<style>
/* Prose overrides for better fit within the form */
.ProseMirror {
    outline: none !important;
}
.ProseMirror p.is-editor-empty:first-child::before {
    color: hsl(var(--muted-foreground));
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>

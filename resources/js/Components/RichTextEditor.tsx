import {
    AlignCenter,
    AlignLeft,
    AlignRight,
    Bold,
    Eraser,
    Italic,
    Link as LinkIcon,
    List,
    ListOrdered,
    Underline,
} from 'lucide-react';
import { useEffect, useRef } from 'react';

type Props = {
    label: string;
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    error?: string;
};

const allowedTags = new Set(['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'H2', 'H3', 'H4', 'A']);

function sanitizeHtml(html: string) {
    const document = new DOMParser().parseFromString(html, 'text/html');
    document.body.querySelectorAll('*').forEach(element => {
        if (element.tagName === 'DIV') {
            const paragraph = document.createElement('p');
            paragraph.append(...Array.from(element.childNodes));
            element.replaceWith(paragraph);
            return;
        }
        if (!allowedTags.has(element.tagName)) {
            element.replaceWith(...Array.from(element.childNodes));
            return;
        }
        Array.from(element.attributes).forEach(attribute => {
            if (element.tagName !== 'A' || attribute.name !== 'href') element.removeAttribute(attribute.name);
        });
        if (element.tagName === 'A') {
            const href = element.getAttribute('href') || '';
            if (!/^(https?:|mailto:|#)/i.test(href)) element.removeAttribute('href');
        }
    });
    return document.body.innerHTML;
}

export default function RichTextEditor({ label, value, onChange, placeholder = 'Escribí el contenido…', error }: Props) {
    const editorRef = useRef<HTMLDivElement>(null);
    const savedRangeRef = useRef<Range | null>(null);

    useEffect(() => {
        if (editorRef.current && editorRef.current.innerHTML !== value) editorRef.current.innerHTML = sanitizeHtml(value || '');
    }, [value]);

    const saveSelection = () => {
        const selection = window.getSelection();
        if (selection?.rangeCount) savedRangeRef.current = selection.getRangeAt(0).cloneRange();
    };

    const restoreSelection = () => {
        editorRef.current?.focus();
        if (!savedRangeRef.current) return;
        const selection = window.getSelection();
        selection?.removeAllRanges();
        selection?.addRange(savedRangeRef.current);
    };

    const emitChange = () => {
        if (editorRef.current) onChange(sanitizeHtml(editorRef.current.innerHTML));
    };

    const command = (name: string, argument?: string) => {
        restoreSelection();
        document.execCommand(name, false, argument);
        emitChange();
        saveSelection();
    };

    const addLink = () => {
        restoreSelection();
        const href = window.prompt('Dirección del enlace');
        if (href && /^(https?:|mailto:|#)/i.test(href)) command('createLink', href);
    };

    const tools = [
        { label: 'Negrita', icon: Bold, command: 'bold' },
        { label: 'Cursiva', icon: Italic, command: 'italic' },
        { label: 'Subrayado', icon: Underline, command: 'underline' },
        { label: 'Lista', icon: List, command: 'insertUnorderedList' },
        { label: 'Lista numerada', icon: ListOrdered, command: 'insertOrderedList' },
        { label: 'Alinear a la izquierda', icon: AlignLeft, command: 'justifyLeft' },
        { label: 'Centrar', icon: AlignCenter, command: 'justifyCenter' },
        { label: 'Alinear a la derecha', icon: AlignRight, command: 'justifyRight' },
    ];

    return <div>
        <div className="mb-2 flex items-end justify-between gap-4">
            <label className="text-[12px] font-semibold text-[#35363a]">{label}</label>
            <span className="text-[10px] tracking-[.08em] text-[#89939a]">TEXTO ENRIQUECIDO</span>
        </div>
        <div className={`overflow-hidden rounded-lg border bg-white transition focus-within:border-[#1954b7] focus-within:ring-4 focus-within:ring-[#1954b7]/8 ${error ? 'border-red-400' : 'border-[#dfe4e7]'}`}>
            <div className="flex flex-wrap items-center gap-0.5 border-b border-[#e8ecee] bg-[#f8fafb] p-2">
                <select
                    aria-label="Formato del bloque"
                    defaultValue=""
                    onMouseDown={saveSelection}
                    onChange={event => {
                        command('formatBlock', event.target.value);
                        event.target.value = '';
                    }}
                    className="mr-2 h-9 cursor-pointer rounded-md border border-[#dfe4e7] bg-white px-2 text-[11px] text-[#4f5a62] outline-none"
                >
                    <option value="" disabled>Formato</option>
                    <option value="p">Párrafo</option>
                    <option value="h2">Título H2</option>
                    <option value="h3">Título H3</option>
                    <option value="h4">Título H4</option>
                    <option value="blockquote">Cita</option>
                </select>
                {tools.map(tool => <button
                    key={tool.command}
                    type="button"
                    title={tool.label}
                    aria-label={tool.label}
                    onMouseDown={event => { event.preventDefault(); command(tool.command); }}
                    className="grid size-9 cursor-pointer place-items-center rounded-md text-[#66727a] transition hover:bg-white hover:text-[#1954b7]"
                ><tool.icon size={15} strokeWidth={1.8}/></button>)}
                <span className="mx-1 h-5 w-px bg-[#d8dde1]"/>
                <button type="button" title="Insertar enlace" aria-label="Insertar enlace" onMouseDown={event => { event.preventDefault(); addLink(); }} className="grid size-9 cursor-pointer place-items-center rounded-md text-[#66727a] transition hover:bg-white hover:text-[#1954b7]"><LinkIcon size={15}/></button>
                <button type="button" title="Quitar formato" aria-label="Quitar formato" onMouseDown={event => { event.preventDefault(); command('removeFormat'); }} className="grid size-9 cursor-pointer place-items-center rounded-md text-[#66727a] transition hover:bg-white hover:text-[#1954b7]"><Eraser size={15}/></button>
            </div>
            <div
                ref={editorRef}
                contentEditable
                suppressContentEditableWarning
                data-placeholder={placeholder}
                onFocus={saveSelection}
                onKeyUp={saveSelection}
                onMouseUp={saveSelection}
                onInput={emitChange}
                onBlur={emitChange}
                onPaste={event => {
                    event.preventDefault();
                    document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
                    emitChange();
                }}
                className="admin-rich-text min-h-[210px] px-4 py-4 text-[14px] leading-7 text-[#35363a] outline-none empty:before:pointer-events-none empty:before:text-[#9ca4aa] empty:before:content-[attr(data-placeholder)]"
            />
        </div>
        {error && <p className="mt-1 text-[11px] text-red-600">{error}</p>}
    </div>;
}

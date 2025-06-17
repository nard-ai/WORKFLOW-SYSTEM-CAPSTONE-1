<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Confirm IOM Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Review and Sign Your IOM Request</h3>

                    {{-- Request Details Summary --}}
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">To Department</p>
                                <p class="mt-1">{{ $formData['to_department_name'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject/Re</p>
                                <p class="mt-1">{{ $formData['iom_re'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Priority</p>
                                <p class="mt-1">{{ $formData['iom_priority'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Purpose</p>
                                <p class="mt-1">{{ $formData['iom_purpose'] }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Description/Body</p>
                                <p class="mt-1 whitespace-pre-wrap">{{ $formData['iom_body'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Signature Selection Form --}}
                    <form method="POST" action="{{ route('request.store') }}" class="space-y-6">
                        @csrf
                        {{-- Hidden fields to re-submit all data --}}
                        @foreach($formData as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $subKey => $subValue)
                                    <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        {{-- Name Input --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Full Name
                            </label>
                            <input type="text" 
                                id="name" 
                                name="name" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700"
                                style="text-transform: uppercase;"
                                value="{{ Auth::user()->employeeInfo->FirstName }} {{ Auth::user()->employeeInfo->LastName }}"
                                readonly
                                required>
                        </div>

                        {{-- Signature Style Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Choose your signature style
                            </label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                <div id="signatureStyles" class="grid grid-cols-2 gap-4">
                                    {{-- Signature styles will be loaded here --}}
                                </div>
                                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                                    Select a style and your name will be converted to a signature
                                </div>
                                <span id="signatureError" class="hidden text-sm text-red-500 block text-center mt-2">
                                    Please select a signature style
                                </span>
                            </div>
                        </div>

                        <input type="hidden" id="signature" name="signature">
                        <input type="hidden" id="signatureStyle" name="signatureStyle">

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end space-x-4 mt-6 pt-6 border-t dark:border-gray-700">
                            <a href="{{ route('request.edit_before_confirmation') }}" 
                               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                {{ __('Back to Edit') }}
                            </a>
                            <x-primary-button>
                                {{ __('Submit Request') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Mr+Dafoe&family=Homemade+Apple&family=Pacifico&family=Dancing+Script&display=swap');
    
    .signature-style {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        cursor: pointer;
        padding: 1rem;
    }

    .signature-style.selected {
        border-color: #2563eb;
        background-color: #eff6ff;
        box-shadow: 0 0 0 2px #3b82f6;
    }

    .signature-style:hover:not(.selected) {
        border-color: #93c5fd;
        background-color: #f8fafc;
    }

    .preview-text {
        font-size: 1.75rem;
        line-height: 1.2;
        text-align: center;
        width: 100%;
        color: #1f2937;
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .style-name {
        font-size: 0.75rem;
        color: #6b7280;
        text-align: center;
        width: 100%;
    }

    .signature-style::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(to right, #2563eb, #3b82f6);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .signature-style.selected::after {
        opacity: 1;
    }
</style>

<script>
let selectedStyle = null;

// Function to convert text to base64 image
function textToImage(text, fontFamily) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas size
    canvas.width = 600;
    canvas.height = 150;
    
    // Configure text style
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#1f2937';
    
    // Calculate font size based on text length
    const maxFontSize = 72;
    const minFontSize = 48;
    const calculatedSize = Math.max(minFontSize, Math.min(maxFontSize, 800 / text.length));
    
    ctx.font = `${calculatedSize}px ${fontFamily}`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // Add subtle shadow
    ctx.shadowColor = 'rgba(0, 0, 0, 0.1)';
    ctx.shadowBlur = 2;
    ctx.shadowOffsetY = 2;
    
    // Draw text
    ctx.fillText(text.toUpperCase(), canvas.width / 2, canvas.height / 2);
    
    return canvas.toDataURL('image/png');
}

// Load signature styles
function loadSignatureStyles() {
    fetch('{{ route("signature-styles.index") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('signatureStyles');
            container.innerHTML = '';
            data.forEach(style => {
                const div = document.createElement('div');
                div.className = 'signature-style';
                
                const signatureText = document.createElement('div');
                signatureText.className = 'preview-text';
                signatureText.style.fontFamily = style.font_family;
                signatureText.textContent = 'Your Signature';
                
                const styleName = document.createElement('div');
                styleName.className = 'style-name';
                styleName.textContent = style.name;
                
                div.appendChild(signatureText);
                div.appendChild(styleName);
                div.onclick = () => selectStyle(style.id, style.font_family, div);
                container.appendChild(div);
            });

            // Update previews with current name if exists
            const currentName = document.getElementById('name').value;
            if (currentName) {
                updateAllPreviews(currentName);
            }
        });
}

// Function to update all signature previews
function updateAllPreviews(name) {
    const displayText = name.trim() ? name.toUpperCase() : 'Your Signature';
    document.querySelectorAll('.preview-text').forEach(preview => {
        preview.textContent = displayText;
    });
}

function selectStyle(styleId, fontFamily, element) {
    // Update selection state
    document.querySelectorAll('.signature-style').forEach(div => {
        div.classList.remove('selected');
    });
    element.classList.add('selected');
    selectedStyle = { id: styleId, fontFamily: fontFamily };
    document.getElementById('signatureStyle').value = styleId;
    
    // Update signature preview with uppercase name
    const name = document.getElementById('name').value.toUpperCase();
    updateAllPreviews(name);
}

// Handle form submission
document.querySelector('form').addEventListener('submit', function(e) {
    if (!selectedStyle) {
        e.preventDefault();
        document.getElementById('signatureError').classList.remove('hidden');
        return;
    }

    // Convert signature text to image
    const name = document.getElementById('name').value;
    const signatureImage = textToImage(name, selectedStyle.fontFamily);
    document.getElementById('signature').value = signatureImage;
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadSignatureStyles();
    
    // Handle name input changes
    document.getElementById('name').addEventListener('input', function() {
        updateAllPreviews(this.value);
    });
});
</script> 
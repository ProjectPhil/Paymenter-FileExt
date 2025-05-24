<x-dynamic-component :component="$getFieldWrapperView()":field="$field">
    <div x-data="{
            state: $wire.{{ $applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')') }},
            uploadFile(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('file', file);
                formData.append('disk', '{{ $field->getDisk() }}');
                formData.append('directory', '{{ $field->getDirectory() }}');

                fetch('/filament/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.path) {
                        this.state = data.path;
                    }
                })
                .catch(error => {
                    console.error('Error uploading file:', error);
                });
            }
        }"
    >
        <div class="flex items-center space-x-4">
            <input
                type="file"
                x-on:change="uploadFile"
                accept="{{ implode(',', $field->getAcceptedFileTypes()) }}"
                class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-primary-50 file:text-primary-700
                    hover:file:bg-primary-100"
            >
            <div x-show="state" class="text-sm text-gray-500">
                <span x-text="state"></span>
            </div>
        </div>
    </div>
</x-dynamic-component> 
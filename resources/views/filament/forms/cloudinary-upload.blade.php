<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="cloudinaryUpload(@entangle($getStatePath()))" class="space-y-2">
        <button 
            type="button"
            @click="openWidget"
            class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700"
        >
            Upload Images
        </button>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4" x-show="images.length > 0">
            <template x-for="(image, index) in images" :key="index">
                <div class="relative group">
                    <img :src="image" class="w-full h-32 object-cover rounded-lg">
                    <button 
                        type="button"
                        @click="removeImage(index)"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <script>
        function cloudinaryUpload(state) {
            return {
                images: state || [],
                widget: null,
                
                init() {
                    // Load Cloudinary Upload Widget script
                    if (!window.cloudinary) {
                        const script = document.createElement('script');
                        script.src = 'https://upload-widget.cloudinary.com/global/all.js';
                        script.onload = () => this.initWidget();
                        document.head.appendChild(script);
                    } else {
                        this.initWidget();
                    }
                },
                
                initWidget() {
                    this.widget = cloudinary.createUploadWidget({
                        cloudName: '{{ config('cloudinary.cloud_name') }}',
                        uploadPreset: '{{ config('cloudinary.upload_preset') }}',
                        folder: 'properties',
                        multiple: true,
                        maxFiles: 10,
                        maxFileSize: 10000000,
                        sources: ['local', 'camera'],
                        clientAllowedFormats: ['jpg', 'jpeg', 'png', 'webp'],
                        resourceType: 'image',
                    }, (error, result) => {
                        if (!error && result && result.event === 'success') {
                            this.images.push(result.info.secure_url);
                            state = this.images;
                        }
                    });
                },
                
                openWidget() {
                    if (this.widget) {
                        this.widget.open();
                    }
                },
                
                removeImage(index) {
                    this.images.splice(index, 1);
                    state = this.images;
                }
            }
        }
    </script>
</x-dynamic-component>
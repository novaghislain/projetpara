<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        {{ magicRequest.title }}
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Dépôt sécurisé de documents
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        
        <div v-if="$page.props.flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-600 p-4 rounded-md">
            {{ $page.props.flash.success }}
        </div>

        <p class="text-sm text-gray-700 mb-6">{{ magicRequest.description }}</p>

        <div class="mb-6" v-if="magicRequest.requested_documents">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Documents attendus :</h4>
            <ul class="list-disc pl-5 text-sm text-gray-600">
                <li v-for="(doc, i) in magicRequest.requested_documents" :key="i">
                    {{ doc }}
                </li>
            </ul>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
          <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative hover:bg-gray-50 transition-colors">
            <div class="space-y-1 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <div class="flex text-sm text-gray-600 justify-center">
                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                  <span>Sélectionnez des fichiers</span>
                  <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple @change="handleFileUpload">
                </label>
                <p class="pl-1">ou glissez-déposez</p>
              </div>
              <p class="text-xs text-gray-500">
                PDF, PNG, JPG jusqu'à 20MB
              </p>
            </div>
          </div>

          <div v-if="form.files.length > 0" class="mt-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Fichiers sélectionnés :</h4>
              <ul class="text-sm text-gray-600">
                  <li v-for="(file, index) in form.files" :key="index" class="flex justify-between items-center py-1">
                      <span class="truncate">{{ file.name }}</span>
                      <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">Retirer</button>
                  </li>
              </ul>
          </div>

          <div class="mt-6">
            <button type="submit" :disabled="form.processing || form.files.length === 0" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
              {{ form.processing ? 'Envoi en cours...' : 'Transmettre les documents' }}
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    magicRequest: Object
});

const form = useForm({
    files: []
});

function handleFileUpload(e) {
    const selected = Array.from(e.target.files);
    form.files = [...form.files, ...selected];
}

function removeFile(index) {
    form.files.splice(index, 1);
}

function submit() {
    form.post(route('magic-links.submit', props.magicRequest.uuid), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

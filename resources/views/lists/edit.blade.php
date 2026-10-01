<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold text-white mb-6">Edit List</h1>
        <form method="POST" action="{{ route('lists.update', $list) }}" class="space-y-4">
            @csrf @method('PUT')
            @include('lists._form')
            <button class="bg-sf-blue hover:bg-sf-blue-dark text-white px-4 py-2 rounded">Update List</button>
        </form>
    </div>
</x-app-layout>

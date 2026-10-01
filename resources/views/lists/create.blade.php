<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold text-white mb-6">Create List</h1>
        <form method="POST" action="{{ route('lists.store') }}" class="space-y-4">
            @csrf
            @include('lists._form')
            <button class="bg-sf-blue hover:bg-sf-blue-dark text-white px-4 py-2 rounded">Create List</button>
        </form>
    </div>
</x-app-layout>

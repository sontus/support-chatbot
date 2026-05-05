<!-- Use your application's layout -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Admin</title>
    <!-- Assuming TailwindCSS is available -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-8">

<div class="container mx-auto max-w-6xl">
    <h1 class="text-3xl font-bold mb-8">Support Chatbot - Admin Panel</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Knowledge Base -->
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h2 class="text-xl font-bold mb-4">Add Knowledge Base Article</h2>
            <form action="{{ route('chatbot.admin.train') }}" method="POST" class="mb-8">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700">Title / Question</label>
                    <input type="text" name="title" class="w-full border-gray-300 border rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200 focus:border-blue-400" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700">Content / Answer</label>
                    <textarea name="content" class="w-full border-gray-300 border rounded-md shadow-sm p-2 focus:ring focus:ring-blue-200 focus:border-blue-400" rows="5" required></textarea>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md transition-colors">Add to Knowledge Base</button>
            </form>

            <h3 class="font-bold text-lg mb-3">Existing Knowledge Base</h3>
            <div class="space-y-3">
                @forelse($knowledge as $item)
                    <div class="border border-gray-200 p-3 rounded-md bg-gray-50">
                        <h4 class="font-semibold">{{ $item->title }}</h4>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $item->content }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 italic text-sm">No knowledge base articles found.</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $knowledge->links() ?? '' }}
            </div>
            
            <form action="{{ route('chatbot.admin.sync') }}" method="POST" class="mt-6 border-t pt-4">
                @csrf
                <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Re-sync AI Embeddings</button>
            </form>
        </div>

        <!-- Conversations -->
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h2 class="text-xl font-bold mb-4">Recent Conversations</h2>
            <div class="space-y-4">
                @forelse($conversations as $conv)
                    <div class="border border-gray-200 p-4 rounded-md flex justify-between items-center hover:bg-gray-50 transition-colors cursor-pointer">
                        <div>
                            <div class="font-medium">
                                @if($conv->user_id)
                                    User ID: {{ $conv->user_id }}
                                @else
                                    Session: {{ substr($conv->session_id, 0, 8) }}...
                                @endif
                            </div>
                            <div class="text-sm text-gray-500 mt-1">Messages: {{ $conv->messages_count }} | {{ $conv->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $conv->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($conv->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 italic text-sm">No conversations found.</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $conversations->links() ?? '' }}
            </div>
        </div>
    </div>
</div>

</body>
</html>

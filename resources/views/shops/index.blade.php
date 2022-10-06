<x-app-layout>

    <div class="container max-w-7xl mx-auto px-4 md:px-12 pb-3 mt-3">
        <x-flash-message :message="session('notice')" />
        <div class="flex flex-wrap -mx-1 lg:-mx-4 mb-4">
            @foreach ($shops as $shop)
                <article class="w-full px-4 md:w-1/2 text-xl text-gray-800 leading-normal">
                    <a href="{{ route('shops.show', $shop) }}">
                        <h2 class="font-bold font-sans break-normal text-gray-900 pt-6 pb-1 text-3xl md:text-4xl">{{ $shop->title }}</h2>
                        <h3>{{ $shop->user->name }}</h3>
                        <p class="text-sm mb-2 md:text-base font-normal text-gray-600">
                            現在時刻: <span class="text-red-400 font-bold">{{ date('Y-m-d H:i:s') }}</span>
                            記事作成日: {{ $shop->created_at }}
                        </p>
                        <img class="w-full mb-2" src="{{ Storage::url($shop->image_path) }}" alt="">
                        <p class="text-gray-700 text-base">{{ Str::limit($shop->description, 50) }}</p>
                    </a>
                        <p class="font-black">お気に入り数：{{ $shop->favorites->count() }}</p>
                </article>
            @endforeach
        </div>
        {{ $shops->links() }}
        </div>
    </div>
</x-app-layout>
